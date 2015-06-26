<?php namespace Concrete\Package\Schedulizer\Src\Api\Resource {

    use Permissions;
    use \Concrete\Package\Schedulizer\Src\Bin\EntityCloner;
    use \Concrete\Package\Schedulizer\Src\Calendar;
    use \Concrete\Package\Schedulizer\Src\Event;
    use \Concrete\Package\Schedulizer\Src\EventTime;
    use \Concrete\Package\Schedulizer\Src\EventTimeNullify;
    use \Concrete\Package\Schedulizer\Src\EventTag;
    use \Concrete\Package\Schedulizer\Src\EventCategory;
    use \Concrete\Package\Schedulizer\Src\Attribute\Key\SchedulizerEventKey;
    use \Concrete\Package\Schedulizer\Src\Api\ApiException;
    use \Symfony\Component\HttpFoundation\Response;

    /**
     * Class EventResource
     * @todo: permissions, determine user via API key?
     * @package Concrete\Package\Schedulizer\Src\Api\Resource
     */
    class EventResource extends \Concrete\Package\Schedulizer\Src\Api\ApiDispatcher {

        /**
         * Get an event by its ID.
         * @param $id
         * @throws ApiException
         * @throws \Exception
         */
        protected function httpGet( $id ){
            $this->setResponseData($this->getEventByID($id));
        }

        /**
         * Create a new event.
         * @note: if post method comes through with a route params /attributes,
         * then we're handling updating attributes on an existing entity. Also note,
         * whenever you save an event, the first request that comes through is to create
         * the entity, and a second request will always be issued sending the attributes
         * AFTER the event is created. C5's attribute system is just... yikes.
         * @todo: permissions
         */
        protected function httpPost(){
            // Handle saving attributes
            if( is_array($this->routeParams) && $this->routeParams[0] === 'attributes' ){
                // $this->getEventByID handles errors if event doesn't exist or is invalid
                $eventObj = $this->getEventByID($this->routeParams[1]);
                // Before we update, do a permissions check (not on a per-event basis, but
                // whether the user can ADD events to the calendar...)
                if( $eventObj->getCalendarObj()->getPermissions()->canEditEvents() ){
                    $this->saveAttributesAgainst($eventObj);
                    $this->setResponseData((object)array('ok' => true));
                    return;
                }
                throw ApiException::permissionInvalid('You do not have permission to edit events on this Calendar.');
            }

            $data = $this->scrubbedPostData();
            $calendarObj = Calendar::getByID($data->calendarID);
            // Ensure calendar exists
            if( empty($calendarObj) || !is_object($calendarObj) ){
                throw ApiException::dependentResourceInvalid('Calendar does not exist to create an event for.');
            }
            // Check user has permissions to add events
            if( ! $calendarObj->getPermissions()->canEditEvents() ){
                throw ApiException::permissionInvalid('You do not have permission to edit events on this Calendar.');
            }
            // Make sure at least 1 time setting exists
            if( empty($data->_timeEntities) ){
                throw ApiException::generic('At least 1 time setting must be passed in _timeEntities property.');
            }

            // Set
            $data->ownerID = ($this->currentUser()->getUserID() >= 1) ? $this->currentUser()->getUserID() : 0;
            // Create the event object
            $eventObj = Event::create($data);
            // Loop through and create associated time entities
            foreach((array)$data->_timeEntities AS $timeEntityData){
                $timeEntityData->eventID   = $eventObj->getID();
                $timeEntityData->versionID = $eventObj->getVersionID();
                EventTime::createWithWeeklyRepeatSettings($timeEntityData);
            }
            // Handle tags @todo: can the user create tags? if not then only get and process existing ones
            foreach((array)$data->_tags AS $tagEntityData){
                /** @var $tagObj EventTag */
                $tagObj = EventTag::createOrGetExisting($tagEntityData);
                $tagObj->tagEvent($eventObj);
            }
            // Handle categories @todo: can the user create categories? if not then only get and process existing ones
            foreach((array)$data->_categories AS $categoryEntityData){
                /** @var $categoryEntityData EventCategory */
                $categoryObj = EventCategory::createOrGetExisting($categoryEntityData);
                $categoryObj->categorizeEvent($eventObj);
            }

            $this->setResponseData($eventObj);
            $this->setResponseCode(Response::HTTP_CREATED);
        }

        /**
         * Update an event by its ID.
         * @param $id
         * @throws ApiException
         * @throws \Exception
         * @todo: copy nullifiers from previous version to new version!
         */
        public function httpPut( $id ){
            $data = $this->scrubbedPostData();
            if( empty($data->_timeEntities) ){
                throw ApiException::generic('At least 1 time setting must be passed in _timeEntities property.');
            }
            // Get existing event (getEventByID method throws exception on no existence...)
            $eventObj = $this->getEventByID($id);
            // Ensure permissions
            if( ! $eventObj->getCalendarObj()->getPermissions()->canEditEvents() ){
                throw ApiException::permissionInvalid('You do not have permission to edit events on this Calendar.');
            }

            // Now, update the event...
            $eventObj->update($data);
            // Process time entities
            foreach((array)$data->_timeEntities AS $timeEntityData){
                $timeEntityData->eventID   = $eventObj->getID();
                $timeEntityData->versionID = $eventObj->getVersionID();
                $newEventTimeObj = EventTime::createWithWeeklyRepeatSettings($timeEntityData);

                // Clone existing nullifiers to the new EventTime
                if( !empty($timeEntityData->id) ){
                    $existingTimeEntityObj = EventTime::getByID($timeEntityData->id);
                    if( is_object($existingTimeEntityObj) ){
                        $nullifiers = $existingTimeEntityObj->getEventTimeNullifiers();
                        if( !empty($nullifiers) ){
                            foreach($nullifiers AS $nullifierObj){
                                $cloned = EntityCloner::cloneInMemoryAndSetProps($nullifierObj, array(
                                    'id'          => null,
                                    'eventTimeID' => $newEventTimeObj->getID()
                                ));
                                $cloned->save();
                            }
                        }
                    }
                }
            }

            // Handle tags
            foreach((array)$data->_tags AS $tagEntityData){
                /** @var $tagObj EventTag */
                $tagObj = EventTag::createOrGetExisting($tagEntityData);
                $tagObj->tagEvent($eventObj);
            }

            // Handle categories @todo: can the user create categories? if not then only get and process existing ones
            foreach((array)$data->_categories AS $categoryEntityData){
                /** @var $categoryEntityData EventCategory */
                $categoryObj = EventCategory::createOrGetExisting($categoryEntityData);
                $categoryObj->categorizeEvent($eventObj);
            }

            $this->setResponseData($eventObj);
            $this->setResponseCode(Response::HTTP_ACCEPTED);
        }

        /**
         * Delete an event by its ID.
         * @param $id
         * @throws ApiException
         */
        public function httpDelete( $id ){
            $eventObj = $this->getEventByID($id);
            if( ! $eventObj->getCalendarObj()->getPermissions()->canDeleteEvents() ){
                throw ApiException::permissionInvalid('You do not have permission to delete events on this Calendar.');
            }
            $eventObj->delete();
            $this->setResponseData((object)array('ok' => true));
            $this->setResponseCode(Response::HTTP_ACCEPTED);
        }

        /**
         * All methods that need to access an event entity can use this
         * as it has all exception checking built in.
         * @param $id
         * @return Event
         * @throws ApiException
         */
        protected function getEventByID( $id ){
            // Check ID param exists
            if( empty($id) ){
                throw ApiException::invalidRoute('Route parameter:ID required.');
            }
            // Try to load entity
            $eventObj = Event::getByID($id);
            // Check result
            if( empty($eventObj) ){
                throw ApiException::notFound();
            }
            return $eventObj;
        }

        /**
         * When attribute form gets POSTed, this handles saving.
         * @param Event $eventObj
         */
        protected function saveAttributesAgainst( Event $eventObj ){
            // This is hacky as f*ck, but internally the saveAttributeForm only
            // uses the $_POST values in order to parse the send attributes. Since
            // we are serializing and sending from the front-end, we have to set
            // $_POST to $_REQUEST.
            $_POST = $_REQUEST;
            $attrList = SchedulizerEventKey::getList();
            foreach($attrList AS $akObj){
                $akObj->saveAttributeForm($eventObj);
            }
        }
    }

}
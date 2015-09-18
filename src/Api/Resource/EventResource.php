<?php namespace Concrete\Package\Schedulizer\Src\Api\Resource {

    use Events; // Concrete5 Sysstem Events!
    use File;
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
    use \Concrete\Package\Schedulizer\Src\SystemEvents\EventOnSave AS SystemEventOnSave;
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
         * @todo: error handling (ie. if $id isn't set and we're supposed to be getting an
         * event object instead of a thumbnail, ALSO - errors w/ the 4-level-deep nested if statements)
         */
        protected function httpGet( $id, $subAction = null ){
            switch($subAction):
                case 'image_path':
                    $eventObj = $this->getEventByID($id);
                    if( $eventObj ){
                        $fileID = $eventObj->getFileID();
                        if( $fileID ){
                            $eventFileObj = File::getByID($fileID);
                            if( is_object($eventFileObj) ){
                                $this->setResponseData((object) array(
                                    'url' => $eventFileObj->getThumbnailURL('event_thumb')
                                ));
                            }
                        }
                    }
                    break;

                default:
                    $this->setResponseData($this->getEventByID($id));
            endswitch;
        }

        /**
         * Create a new event.
         * @note: if post method comes through with a route params /attributes,
         * then we're handling updating attributes on an existing entity. Also note,
         * whenever you save an event, the first request that comes through is to create
         * the entity, and a second request will always be issued sending the attributes
         * AFTER the event is created.
         */
        protected function httpPost(){
            // Handle saving attributes
            if( is_array($this->routeParams) && $this->routeParams[0] === 'attributes' ){
                // $this->getEventByID handles errors if event doesn't exist or is invalid
                $eventObj = $this->getEventByID($this->routeParams[1]);
                // Does user have permission to work on the calendar?
                if( ! is_object($eventObj) || ! ($eventObj->getCalendarObj()->getPermissions()->canEditEvents()) ){
                    throw ApiException::permissionInvalid('You do not have permission to edit events on this Calendar.');
                }
                // Save
                $this->saveAttributesAgainst($eventObj);
                $this->setResponseCode(Response::HTTP_NO_CONTENT);
                return;
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

            // Basic data validations
            $this->validation($data);

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

            // Are we firing a request for approval email?
            if( (bool) $this->scrubbedPostData()->__requestApproval ){
                Events::dispatch('schedulizer.request_approval', new SystemEventOnSave($eventObj));
            }

            $this->setResponseData($eventObj);
            $this->setResponseCode(Response::HTTP_CREATED);
        }

        /**
         * Update an event by its ID.
         * @param $id
         * @throws ApiException
         * @throws \Exception
         */
        public function httpPut( $id, $subAction = null ){
            // Get existing event (getEventByID method throws exception on no existence...)
            $eventObj = $this->getEventByID($id);
            $data     = $this->scrubbedPostData();

            // Ensure permissions
            if( ! $eventObj->getCalendarObj()->getPermissions()->canEditEvents() ){
                throw ApiException::permissionInvalid('You do not have permission to edit events on this Calendar.');
            }

            // So we don't have to go through all the event version creation, we can
            // use a shortcut to update just the isActive property.
            if( $subAction === 'update_active_status' ){
                $eventObj->setActiveStatusWithoutVersioning( $this->scrubbedPostData()->isActive );
                $this->setResponseCode(Response::HTTP_NO_CONTENT);
                return;
            }

            // Basic data validations
            $this->validation($data);

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

            // Are we firing a request for approval email?
            if( (bool) $this->scrubbedPostData()->__requestApproval ){
                Events::dispatch('schedulizer.request_approval', new SystemEventOnSave($eventObj));
            }

            $this->setResponseData($eventObj);
            $this->setResponseCode(Response::HTTP_OK);
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
            $this->setResponseCode(Response::HTTP_NO_CONTENT);
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

        /**
         * Basic validations when creating/updating an entity.
         * @param $data
         * @throws ApiException
         * @return bool true|false
         */
        protected function validation( $data ){
            // Make sure title is set
            if( empty($data->title) || $data->title === '' ){
                throw ApiException::validationError('Title is required.');
            }

            // Is at least 1 time entity being provided?
            if( empty($data->_timeEntities) ){
                throw ApiException::validationError('At least 1 time setting is required.');
            }

            // If here, we can assume at least 1 _timeEntities exists
            foreach($data->_timeEntities AS $eventTimeData){
                if( $eventTimeData->repeatTypeHandle === EventTime::REPEAT_TYPE_HANDLE_WEEKLY ){
                    if( ! is_array($eventTimeData->weeklyDays) || empty($eventTimeData->weeklyDays) ){
                        throw ApiException::validationError('Having an event repeat weekly requires at least 1 weekday to be chosen.');
                    }
                }
            }
        }
    }

}
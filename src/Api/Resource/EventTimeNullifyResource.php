<?php namespace Concrete\Package\Schedulizer\Src\Api\Resource {

    use \DateTime;
    use \DateTimeZone;
    use \Concrete\Package\Schedulizer\Src\EventTime;
    use \Concrete\Package\Schedulizer\Src\EventTimeNullify;
    use \Concrete\Package\Schedulizer\Src\Api\ApiException;
    use \Symfony\Component\HttpFoundation\Response;

    /**
     * Class EventTimeNullifyResource
     * @todo: permissions, determine user via API key?
     * @package Concrete\Package\Schedulizer\Src\Api\Resource
     */
    class EventTimeNullifyResource extends \Concrete\Package\Schedulizer\Src\Api\ApiDispatcher {

        /**
         * Get either a list of event time nullifiers associated with an eventTimeID, or a
         * specific event time nullifier if an id is passed.
         * @param $id
         * @throws ApiException
         * @throws \Exception
         */
        protected function httpGet( $eventTimeID, $id = null ){
            // If $eventTimeID is null, return nothing
            if( empty($eventTimeID) ){
                $this->setResponseCode(Response::HTTP_NO_CONTENT);
                return;
            }

            // If $id is null, we're getting a list of nullifiers associated with an eventTime
            if( empty($id) ){
                $list = EventTimeNullify::fetchAllByEventTimeID($eventTimeID);
                if( is_null($list) ){
                    $this->setResponseCode(Response::HTTP_NO_CONTENT);
                    return;
                }
                $this->setResponseData($list);
                return;
            }
            // Otherwise, we're fetching a specific nullifier
            $this->setResponseData($this->getEventTimeNullifyByID($id));
        }

        /**
         * Create a new event.
         */
        protected function httpPost( $eventTimeID ){
            /** @var $eventTimeObj EventTime */
            $eventTimeObj = EventTime::getByID($eventTimeID);
            if( ! $eventTimeObj ){
                throw ApiException::dependentResourceInvalid('Invalid Event Time resource to apply nullifier for.');
            }

            // Permission check
            if ( ! $eventTimeObj->getEventObject()->getCalendarObj()->getPermissions()->canEditEvents()){
                throw ApiException::permissionInvalid('You do not have permission to modify events on this calendar.');
            }

            // Proceed
            $hideOnDate = new DateTime($this->scrubbedPostData()->hideOnDate, new DateTimeZone('UTC'));
            $hideOnDate->setTime(0,0,0);
            // Create
            $nullifierObj = EventTimeNullify::create(array(
                'eventTimeID' => $eventTimeObj->getID(),
                'hideOnDate'  => $hideOnDate->format('Y-m-d H:i:s')
            ));
            $this->setResponseData($nullifierObj);
            $this->setResponseCode(Response::HTTP_CREATED);
        }

        /**
         * Delete a calendar by its ID.
         * @param $id
         * @throws ApiException
         */
        public function httpDelete( $eventTimeID, $id ){
            $nulliferObj = $this->getEventTimeNullifyByID($id);
            $eventObj    = $this->getEventTimeNullifyByID($id)->getEventObject();
            if( ! $eventObj->getCalendarObj()->getPermissions()->canEditEvents() ){
                throw ApiException::permissionInvalid('You do not have permission to modify events on this calendar.');
            }

            $nulliferObj->delete();
            $this->setResponseCode(Response::HTTP_ACCEPTED);
        }

        /**
         * All methods that need to access an EventTimeNullify entity can use this
         * as it has all exception checking built in.
         * @param $id
         * @return EventTimeNullify
         * @throws ApiException
         */
        protected function getEventTimeNullifyByID( $id ){
            // Check ID param exists
            if( empty($id) ){
                throw ApiException::invalidRoute('Route parameter:ID required.');
            }
            // Try to load entity
            $eventTimeNullifyObj = EventTimeNullify::getByID($id);
            // Check result
            if( empty($eventTimeNullifyObj) ){
                throw ApiException::notFound();
            }
            return $eventTimeNullifyObj;
        }
    }

}
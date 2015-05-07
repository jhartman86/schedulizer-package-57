<?php namespace Concrete\Package\Schedulizer\Src\Api\Resource {

    use \Concrete\Package\Schedulizer\Src\Calendar;
    use \Concrete\Package\Schedulizer\Src\Event;
    use \Concrete\Package\Schedulizer\Src\Api\ApiException;
    use \Symfony\Component\HttpFoundation\Response;

    class CalendarResource extends \Concrete\Package\Schedulizer\Src\Api\ApiDispatcher {

        /**
         * Get a calendar by its ID OR pass /events as parameter after id and
         * optionally a third as 'verbose' to get a verbose list of args
         * @param $id
         * @param $subResource string|null
         * @param $specificity string|null
         * @throws ApiException
         * @throws \Exception
         */
        protected function httpGet( $id, $subResource = null, $specificity = null ){
            // Are we fetching the non-repeating event records associated w/ the calendar?
            if( $subResource === 'events' ){
                if( $specificity === 'verbose' ){
                    $this->setResponseData(Event::fetchAllByCalendarID($id));
                    return;
                }
                $this->setResponseData(Event::fetchSimpleByCalendarID($id));
                return;
            }
            // We're fetching a specific calendar record
            $this->setResponseData($this->getCalendarBy($id));
        }

        /**
         * Create a new calendar
         * @todo: permissions, pass user (api key determines?), and timezone options
         */
        protected function httpPost(){
            $data = $this->scrubbedPostData();
            $data->ownerID = ($this->currentUser()->getUserID() >= 1) ? $this->currentUser()->getUserID() : 0;
            $calendarObj = Calendar::create($data);
            $this->setResponseData($calendarObj);
            $this->setResponseCode(Response::HTTP_CREATED);
        }

        /**
         * Update a calendar by its ID.
         * @param $id
         * @throws ApiException
         * @throws \Exception
         */
        public function httpPut( $id ){
            $calendarObj = $this->getCalendarBy($id);
            $calendarObj->update($this->scrubbedPostData());
            $this->setResponseData($calendarObj);
            $this->setResponseCode(Response::HTTP_ACCEPTED);
        }

        /**
         * Delete a calendar by its ID.
         * @param $id
         * @throws ApiException
         */
        public function httpDelete( $id ){
            $this->getCalendarBy($id)->delete();
            $this->setResponseData((object)array('ok' => true));
            $this->setResponseCode(Response::HTTP_ACCEPTED);
        }

        /**
         * All methods that need to access a calendar entity can use this
         * as it has all exception checking built in.
         * @param $id
         * @return Calendar
         * @throws ApiException
         */
        protected function getCalendarBy( $id ){
            // Check ID param exists
            if( empty($id) ){
                throw ApiException::invalidRoute('Route parameter:ID required.');
            }
            // Try to load entity
            $calendarObj = Calendar::getByID($id);
            // Check result
            if( empty($calendarObj) ){
                throw ApiException::notFound();
            }
            return $calendarObj;
        }
    }

}
<?php namespace Concrete\Package\Schedulizer\Src\Api\Resource {

    use Concrete\Core\Job\Event;
    use \DateTime;
    use \DateTimeZone;
    use \Concrete\Package\Schedulizer\Src\EventList;
    use \Concrete\Package\Schedulizer\Src\Api\ApiException;

    class EventListResource extends \Concrete\Package\Schedulizer\Src\Api\ApiDispatcher {

        /**
         * List resource get method.
         * @param null $calendarID
         * @throws ApiException
         */
        protected function httpGet( $calendarID = null ){
            try {
                $eventListObj = new EventList(array($calendarID));
                $this->setFullTextSearchOn($eventListObj);
                $this->setCalendarIDsOn($eventListObj);
                $this->setFilterByTagsOn($eventListObj);
                $this->setStartDate($eventListObj);
                $this->setEndDate($eventListObj);
                $this->setFetchColumns($eventListObj);
                $this->setResponseData($eventListObj->getSerializable());
            }catch(\Exception $e){
                throw ApiException::generic($e->getMessage());
            }
        }

        private function setFullTextSearchOn( EventList $eventList ){
            if( !empty($this->requestParams()->keywords) ){
                $eventList->setFullTextSearch($this->requestParams()->keywords);
            }
        }

        /**
         * eg. ?calendars=1,17,22
         * @param EventList $eventList
         */
        private function setCalendarIDsOn( EventList $eventList ){
            if( !empty($this->requestParams()->calendars) ){
                $eventList->setCalendarIDs(explode(',', $this->requestParams()->calendars));
            }
        }

        /**
         * eg. ?tags=12,83,15
         * @param EventList $eventList
         */
        private function setFilterByTagsOn( EventList $eventList ){
            if( !empty($this->requestParams()->tags) ){
                $eventList->filterByTagIDs(explode(',', $this->requestParams()->tags));
            }
        }

        /**
         * eg. ?start=2015-04-02
         * @param EventList $eventList
         */
        private function setStartDate( EventList $eventList ){
            if( !empty($this->requestParams()->start) ){
                $eventList->setStartDate(new DateTime($this->requestParams()->start, new DateTimeZone('UTC')));
            }
        }

        /**
         * eg. ?end=2015-04-02
         * @param EventList $eventList
         */
        private function setEndDate( EventList $eventList ){
            if( !empty($this->requestParams()->end) ){
                $eventList->setEndDate(new DateTime($this->requestParams()->end, new DateTimeZone('UTC')));
            }
        }

        /**
         * Comma-delimited list of include fields.
         * eg. ?fields=eventID,calendarID
         * @param EventList $eventList
         */
        private function setFetchColumns( EventList $eventList ){
            if( !empty($this->requestParams()->fields) ){
                $eventList->includeColumns(explode(',', $this->requestParams()->fields));
            }
        }

    }

}
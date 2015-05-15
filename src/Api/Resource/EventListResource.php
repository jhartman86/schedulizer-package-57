<?php namespace Concrete\Package\Schedulizer\Src\Api\Resource {

    use Concrete\Core\Job\Event;
    use \DateTime;
    use \DateTimeZone;
    use \Concrete\Package\Schedulizer\Src\EventList;
    use \Concrete\Package\Schedulizer\Src\Api\ApiException;

    class EventListResource extends \Concrete\Package\Schedulizer\Src\Api\ApiDispatcher {

        /** @var $eventListObj EventList */
        protected $eventListObj;

        /**
         * List resource get method.
         * @param null $calendarID
         * @throws ApiException
         */
        protected function httpGet( $calendarID = null ){
            try {
                $this->eventListObj = new EventList(array($calendarID));
                $this->setFullTextSearchOn()
                     ->setCalendarIDsOn()
                     ->setFilterByTagsOn()
                     ->setStartDate()
                     ->setEndDate()
                     ->setFetchColumns()
                     ->setIncludeFilePath()
                     ->setIncludePagePath()
                     ->setGrouping()
                     ->setResultLimit()
                     ->setAttributeFetchers();
                $this->setResponseData($this->eventListObj->getSerializable());
            }catch(\Exception $e){
                throw ApiException::generic($e->getMessage());
            }
        }

        /**
         * eg. ?keywords=lorem+ipsum+dolor
         * @return $this
         */
        private function setFullTextSearchOn(){
            if( !empty($this->requestParams()->keywords) ){
                $this->eventListObj->setFullTextSearch($this->requestParams()->keywords);
            }
            return $this;
        }

        /**
         * eg. ?calendars=1,17,22
         * @return $this
         */
        private function setCalendarIDsOn(){
            if( !empty($this->requestParams()->calendars) ){
                $this->eventListObj->setCalendarIDs(explode(',', $this->requestParams()->calendars));
            }
            return $this;
        }

        /**
         * eg. ?tags=12,83,15
         * @return $this
         */
        private function setFilterByTagsOn(){
            if( !empty($this->requestParams()->tags) ){
                $this->eventListObj->filterByTagIDs(explode(',', $this->requestParams()->tags));
            }
            return $this;
        }

        /**
         * eg. ?start=2015-04-02
         * @return $this
         */
        private function setStartDate(){
            if( !empty($this->requestParams()->start) ){
                $this->eventListObj->setStartDate(new DateTime($this->requestParams()->start, new DateTimeZone('UTC')));
            }
            return $this;
        }

        /**
         * eg. ?end=2015-04-02
         * @return $this
         */
        private function setEndDate(){
            if( !empty($this->requestParams()->end) ){
                $this->eventListObj->setEndDate(new DateTime($this->requestParams()->end, new DateTimeZone('UTC')));
            }
            return $this;
        }

        /**
         * Comma-delimited list of include fields.
         * eg. ?fields=eventID,calendarID
         * @return $this
         */
        private function setFetchColumns(){
            if( !empty($this->requestParams()->fields) ){
                $this->eventListObj->includeColumns(explode(',', $this->requestParams()->fields));
            }
            return $this;
        }

        /**
         * Simply needs to be set and we'll fetch the relative file path
         * and include it in the results.
         * @return $this
         */
        private function setIncludeFilePath(){
            if( !empty($this->requestParams()->filepath) ){
                $this->eventListObj->setIncludeFilePathInResults(true);
            }
            return $this;
        }

        /**
         * Simply needs to be set and we'll fetch the page path.
         * @return $this
         */
        private function setIncludePagePath(){
            if( !empty($this->requestParams()->pagepath) ){
                $this->eventListObj->setIncludePagePathInResults(true);
            }
            return $this;
        }

        /**
         * Define grouping settings
         * @return $this
         */
        private function setGrouping(){
            if( !empty($this->requestParams()->grouping) ){
                $this->eventListObj->setEventGrouping(true);
            }
            return $this;
        }

        /**
         * Include attributes to fetch by key.
         * @return $this
         */
        private function setAttributeFetchers(){
            if( !empty($this->requestParams()->attributes) ){
                $this->eventListObj->setAttributesToFetch(explode(',', $this->requestParams()->attributes));
            }
            return $this;
        }

        /**
         * Set query result limit.
         * @return $this
         */
        private function setResultLimit(){
            $limit = (int)$this->requestParams()->limit;
            if( !empty($this->requestParams()->limit) && $limit >= 1 ){
                $this->eventListObj->setTotalLimit($limit);
            }
            return $this;
        }

    }

}
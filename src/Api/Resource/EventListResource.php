<?php namespace Concrete\Package\Schedulizer\Src\Api\Resource {

    use Concrete\Core\Job\Event;
    use \DateTime;
    use \DateTimeZone;
    use \Concrete\Package\Schedulizer\Src\EventList;
    use \Concrete\Package\Schedulizer\Src\CollectionEventList;
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
                $this->eventListObj = $this->listObj($calendarID);
                //$this->eventListObj = new EventList(array($calendarID));
                $this->setCollectionFilter()
                     ->setUseMasterCollectionFilter()
                     ->setFullTextSearchOn()
                     ->setIncludeInactiveEvents()
                     ->setCalendarIDsOn()
                     ->setFilterByTagsOn()
                     ->setFilterByCategoriesOn()
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
         * For collections, we extend the default EventList class and add some more defaults
         * as well as change the query up internally, so we need to first do a check to see
         * if we should be using the CollectionEventList or just the normal EventList class.
         * Just keeps it cleaner.
         * eg. ?dashboard_collection_search=id (just needs to be set)
         */
        private function listObj( $calendarID = null ){
            // If dashboard_collection_search is set, we know we're going to use the CollectionEventList
            // class, AND whatever ID is passed as that parameter.
            if( ! empty($this->requestParams()->dashboard_collection_search) ){
                $list = new CollectionEventList($this->requestParams()->dashboard_collection_search);
                $list->setFilterByDiscrepancies(isset($this->requestParams()->discrepancies));
                return $list;
            }
            return new EventList(array($calendarID));
        }

        /**
         * eg. ?collection_id=1
         * @return $this
         */
        private function setCollectionFilter(){
            if( !empty($this->requestParams()->collection_id) ){
                $this->eventListObj->setSchedulizerCollectionID($this->requestParams()->collection_id);
            }
            return $this;
        }

        /**
         * Instead of specifying an explicit collectionID, we can just say filter
         * by the master collection.
         * eg. ?master_collection=true|1 (just needs to be set)
         * @return $this
         */
        private function setUseMasterCollectionFilter(){
            if( !empty($this->requestParams()->master_collection) ){
                $this->eventListObj->setFilterByMasterCollection(true);
            }
            return $this;
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
         * Simply needs to be set in the query parameter
         * eg. ?includeinactives=
         */
        private function setIncludeInactiveEvents(){
            if( !empty($this->requestParams()->includeinactives) ){
                $this->eventListObj->setIncludeInactiveEvents(true);
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
         * eg. ?categories=12,17,92
         * @return $this
         */
        private function setFilterByCategoriesOn(){
            if( !empty($this->requestParams()->categories) ){
                $this->eventListObj->filterByCategoryIDs(explode(',', $this->requestParams()->categories));
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
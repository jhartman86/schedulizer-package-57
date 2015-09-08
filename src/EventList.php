<?php namespace Concrete\Package\Schedulizer\Src {

    use Package;
    use Loader;
    use DateTime;
    use DateTimeZone;
    use \Exception;
    use \Concrete\Package\Schedulizer\Src\Bin\EventListSerializeFormatter;

    /**
     * Class EventList. This goes completely around Doctrine and composes the database
     * query directly; no idea how to even begin building a query like this in an ORM.
     * @package Concrete\Package\Schedulizer\Src
     */
    class EventList {

        const DATE_FORMAT               = 'Y-m-d',
              DAYS_IN_FUTURE            = 45, // span 6 weeks for some calendar views
              DAYS_IN_FUTURE_MAX        = 365,
              LIMIT_PER_DAY_MAX         = 25,
              COLUMN_CAST_TIME          = 'time',
              COLUMN_CAST_TIME_UTC      = 'utc',
              COLUMN_CAST_TIME_LOCAL    = 'local',
              COLUMN_CAST_INT           = 'int',
              COLUMN_CAST_STRING        = 'str',
              COLUMN_CAST_BOOL          = 'bool';

        // used by the serializer/parser only
        protected $includeFilePathInResults = false;
        protected $includePagePathInResults = false;

        protected $attributesToFetch = array();
        protected $doEventGrouping = false; // Groups by eventID, showing only most recent
        protected $filterByIsActive = true;
        protected $startDTO; // set or calculated
        protected $endDTO; // set or calculated
        protected $limitPerDay  = null;
        protected $limitTotal   = null;
        protected $calendarIDs  = array();
        protected $eventIDs     = array();
        protected $categoryIDs  = array();
        protected $tagIDs       = array();
        // "collectionID" refers to a SCHEDULIZER collection, NOT C5 collection!
        protected $collectionID = null;
        // If a master collection is enabled (determine in logic below); this always override $collectionID
        protected $filterByMasterCollection = false;
        protected $queryDaySpan = self::DAYS_IN_FUTURE;
        protected $fullTextSearch = null;
        protected $fetchColumns = array(
            '_syntheticDate'                => array(false, self::COLUMN_CAST_TIME, self::COLUMN_CAST_TIME_UTC),
            'computedStartUTC'              => array(false, self::COLUMN_CAST_TIME, self::COLUMN_CAST_TIME_UTC),
            'computedStartLocal'            => array(true, self::COLUMN_CAST_TIME, self::COLUMN_CAST_TIME_LOCAL),
            'computedEndUTC'                => array(false, self::COLUMN_CAST_TIME, self::COLUMN_CAST_TIME_UTC),
            'computedEndLocal'              => array(false, self::COLUMN_CAST_TIME, self::COLUMN_CAST_TIME_LOCAL),
            'eventID'                       => array(true, self::COLUMN_CAST_INT),
            'versionID'                     => array(false, self::COLUMN_CAST_INT),
            'calendarID'                    => array(false, self::COLUMN_CAST_INT),
            'eventTimeID'                   => array(false, self::COLUMN_CAST_INT),
            'isActive'                      => array(false, self::COLUMN_CAST_BOOL),
            'title'                         => array(true, self::COLUMN_CAST_STRING),
            'calendarTitle'                 => array(false, self::COLUMN_CAST_STRING),
            'description'                   => array(false, self::COLUMN_CAST_STRING),
            'useCalendarTimezone'           => array(false, self::COLUMN_CAST_BOOL),
            'derivedTimezone'               => array(true, self::COLUMN_CAST_STRING),
            'eventColor'                    => array(false, self::COLUMN_CAST_STRING),
            'ownerID'                       => array(false, self::COLUMN_CAST_INT),
            'pageID'                        => array(false, self::COLUMN_CAST_INT),
            'fileID'                        => array(false, self::COLUMN_CAST_INT),
            'startUTC'                      => array(false, self::COLUMN_CAST_TIME, self::COLUMN_CAST_TIME_UTC),
            'endUTC'                        => array(false, self::COLUMN_CAST_TIME, self::COLUMN_CAST_TIME_UTC),
            'isOpenEnded'                   => array(false, self::COLUMN_CAST_BOOL),
            'isAllDay'                      => array(false, self::COLUMN_CAST_BOOL),
            'isRepeating'                   => array(false, self::COLUMN_CAST_BOOL),
            'repeatTypeHandle'              => array(false, self::COLUMN_CAST_STRING),
            'repeatEvery'                   => array(false, self::COLUMN_CAST_INT),
            'repeatIndefinite'              => array(false, self::COLUMN_CAST_BOOL),
            'repeatEndUTC'                  => array(false, self::COLUMN_CAST_TIME, self::COLUMN_CAST_TIME_UTC),
            'repeatMonthlyMethod'           => array(false, self::COLUMN_CAST_STRING),
            'repeatMonthlySpecificDay'      => array(false, self::COLUMN_CAST_INT),
            'repeatMonthlyOrdinalWeek'      => array(false, self::COLUMN_CAST_INT),
            'repeatMonthlyOrdinalWeekday'   => array(false, self::COLUMN_CAST_INT),
            'repeatWeeklyDay'               => array(false, self::COLUMN_CAST_INT),
            'isSynthetic'                   => array(false, self::COLUMN_CAST_BOOL),
            // THIS IS A SPECIAL COLUMN THAT IS ONLY AVAILABLE WHEN GROUPING HAPPENS.
            // IT'S AUTOMATICALLY SET TO FALSE BEFORE THE QUERY GETS BUILT IF GROUPING = DISABLED
            'occurrences'                   => array(false, self::COLUMN_CAST_INT)
        );

        /**
         * @param array $calendarIDs
         */
        public function __construct( array $calendarIDs = array() ){
            $this->setCalendarIDs($calendarIDs);
        }

        /**
         * Allows specifying the results to be returned. Just pass in a column
         * name (as documented by fetchColumns), and it'll automatically be
         * included in the result set.
         * @param array $columns
         */
        public function includeColumns( array $columns = array() ){
            $available = array_keys($this->fetchColumns);
            foreach($columns AS $columnName){
                // 'occurrences' can only be turned on internally by having Grouping enabled
                if( in_array($columnName, $available) && $columnName !== 'occurrences' ){
                    $this->fetchColumns[$columnName][0] = true;
                }
            }
        }

        /**
         * Search by keywords; sanitize the text when incoming.
         * @param $string
         */
        public function setFullTextSearch( $string ){
            $textHelper = Loader::helper('text');
            $this->fullTextSearch = preg_replace("/[^0-9a-zA-Z -]/", "", $textHelper->sanitize($string));
        }


        /**
         * Set the start date
         * @param DateTime $start
         * @return $this
         */
        public function setStartDate( \DateTime $start ){
            $this->startDTO = $start;
            return $this;
        }

        /**
         * Set the end date time to filter by.
         * @param DateTime $end
         * @return $this
         */
        public function setEndDate( \DateTime $end ){
            $this->endDTO = $end;
            return $this;
        }

        /**
         * Filter by tag IDs.
         * @param $tagIDs
         * @return $this
         */
        public function filterByTagIDs( $tagIDs ){
            if( is_array($tagIDs) ){
                $this->tagIDs = array_unique(array_merge($this->tagIDs, $tagIDs));
                return $this;
            }
            array_push($this->tagIDs, $tagIDs);
            $this->tagIDs = array_unique($this->tagIDs);
            return $this;
        }

        /**
         * Filter by categories.
         * @param $categoryIDs
         * @return $this
         */
        public function filterByCategoryIDs( $categoryIDs ){
            if( is_array($categoryIDs) ){
                $this->categoryIDs = array_unique(array_merge($this->categoryIDs, $categoryIDs));
                return $this;
            }
            array_push($this->categoryIDs, $categoryIDs);
            $this->categoryIDs = array_unique($this->categoryIDs);
            return $this;
        }


        /**
         * Schedulizer has the notion of "collections", which basically as a method of
         * de-coupled versioning (so different collections can approve different event
         * versions).
         * @param $collectionID
         */
        public function setSchedulizerCollectionID( $collectionID ){
            if( is_null($collectionID) || !((int)$collectionID >= 1) ){
                return;
            }
            $this->collectionID = (int)$collectionID;
        }


        /**
         * If master collection is enabled, this lets us declare that the list SHOULD
         * be filtered by it. We don't set the collectionID here in case setSchedulizerCollectionID
         * were to be called after, as this will *always* override.
         * @param bool $to
         */
        public function setFilterByMasterCollection( $to = false ){
            $this->filterByMasterCollection = (bool) $to;
        }


        /**
         * If using the API programmatically, this just makes it so you can pass a collection object in directly
         * and have it extract the ID.
         */
        public function filterByCollectionObject( \Concrete\Package\Schedulizer\Src\Collection $collectionObj ){
            $this->setSchedulizerCollectionID($collectionObj->getID());
        }


        /**
         * Add calendar id/idS to be filtered by.
         * @param $calendarIDs
         * @return $this
         */
        public function setCalendarIDs( $calendarIDs ){
            if( is_array($calendarIDs) ){
                $this->calendarIDs = array_unique(array_merge($this->calendarIDs, $calendarIDs));
                return $this;
            }
            array_push($this->calendarIDs, $calendarIDs);
            $this->calendarIDs = array_unique($this->calendarIDs);
            return $this;
        }

        /**
         * Add an id/idS to be filtered by.
         * @param $eventIDs
         * @return $this
         */
        public function setEventIDs( $eventIDs ){
            if( is_array($eventIDs) ){
                $this->eventIDs = array_unique(array_merge($this->eventIDs, $eventIDs));
                return $this;
            }
            array_push($this->eventIDs, $eventIDs);
            $this->eventIDs = array_unique($this->eventIDs);
            return $this;
        }

        /**
         * Use to restrict the number of results PER DAY that can
         * be returned.
         * @param $limit
         * @return $this
         */
        public function setLimitPerDay( $limit ){
            if( (int)$limit >= self::LIMIT_PER_DAY_MAX ){
                $limit = self::LIMIT_PER_DAY_MAX;
            }
            $this->limitPerDay = (int)$limit;
            return $this;
        }

        /**
         * Unlike limitPerDay, this sets the TOTAL limit that can be returned
         * from the result set.
         * @param $limit int
         * @return $this
         */
        public function setTotalLimit( $limit ){
            if( (int)$limit >= 1 ){
                $this->limitTotal = (int)$limit;
            }
            return $this;
        }

        /**
         * @todo: probably figure out a way to re-enable a maximum limit on the query
         * day span so we can't arbitrarily murder the database...
         * @param int $number
         * @return $this
         */
        public function setDaysIntoFuture( $number = self::DAYS_IN_FUTURE ){
            if( (int)$number >= self::DAYS_IN_FUTURE_MAX ){
                $number = self::DAYS_IN_FUTURE_MAX;
            }
            $this->queryDaySpan = (int)$number;
            return $this;
        }

        /**
         * We never do a join against the files table to get the path,
         * but this will indicate to the serializer that the filepath
         * should be included.
         * @param bool $to
         */
        public function setIncludeFilePathInResults( $to = true ){
            $this->includeFilePathInResults = $to;
            if( $to === true ){
                $this->includeColumns(array('fileID'));
            }
        }

        /**
         * Are we including the filepath?
         * @return bool
         */
        public function doIncludeFilePath(){
            return $this->includeFilePathInResults;
        }

        /**
         * If you only want to list events by the most recent (say from today forward),
         * but not have them listed as an occurrence on every day, this lets you do so.
         * @param bool $to
         */
        public function setEventGrouping( $to = true ){
            $this->doEventGrouping = $to;
            $this->fetchColumns['occurrences'][0] = true;
        }

        /**
         * We never do a join against the files table to get the path,
         * but this will indicate to the serializer that the pagepath
         * should be included.
         * @param bool $to
         */
        public function setIncludePagePathInResults( $to = true ){
            $this->includePagePathInResults = $to;
            if( $to === true ){
                $this->includeColumns(array('pageID'));
            }
        }

        /**
         * By default, the event list only looks for ACTIVE events. This
         * turns off that filtering (ie. for the dashboard...)
         */
        public function setIncludeInactiveEvents(){
            $this->filterByIsActive = false;
        }

        /**
         * Are we including the filepath?
         * @return bool
         */
        public function doIncludePagePath(){
            return $this->includePagePathInResults;
        }

        /**
         * Pass an array of attribute key handles to fetch the values.
         * @param array $attrKeyList
         */
        public function setAttributesToFetch( $attrKeyList = array() ){
            $this->attributesToFetch = $attrKeyList;
        }

        /**
         * Used by the resource outputter.
         * @return array
         */
        public function getAttributesToFetch(){
            return $this->attributesToFetch;
        }

        /**
         * Fetch the results.
         * @return mixed
         * @throws Exception
         */
        public function get(){
            return Loader::db()->GetAll($this->assembledQuery());
        }

        /**
         * Get a list of results but group 'em by day.
         * @todo: this could be dangerously overburdening to run on a large
         * unpaginated list... meaning, pagination needs to be implemented.
         * @return array
         */
        public function getGroupedByDay(){
            $grouped = array();
            foreach((array)$this->get() AS $row){
                $dateKey = substr($row['computedStartLocal'], 0, 10);
                if( ! $grouped[$dateKey] ){
                    $grouped[$dateKey] = array();
                }
                array_push($grouped[$dateKey], $row);
            }
            return $grouped;
        }


        /**
         * Return an instance of EventListSerializeFormatter, which implements
         * JsonSerializable and ensures all fields/results are cast to the proper
         * internal types.
         * @return EventListSerializeFormatter
         */
        public function getSerializable(){
            return new EventListSerializeFormatter($this);
        }


        /**
         * Gets the columns this query is configured to fetch.
         * @return array
         */
        public function getQueryColumnSettings(){
            if( $this->_queryColumnSettings === null ){
                $this->_queryColumnSettings = array_filter($this->fetchColumns, function( $definition ){
                    return $definition[0] === true;
                });
            }
            return $this->_queryColumnSettings;
        }


        /**
         * Prepare class data before generating the query.
         * @throws Exception
         * @return $this
         */
        protected function prepare(){
            // Ensure even if array itself is empty, it doesn't contain empty/null entries
            $this->calendarIDs = array_filter($this->calendarIDs, function( $calID ){
                return (int)$calID >= 1;
            });

            // Ensure eventIDs are numeric only
            $this->eventIDs = array_filter($this->eventIDs, function( $eventID ){
                return (int)$eventID >= 1;
            });

            // Ensure tagIDs are numeric only
            $this->tagIDs = array_filter($this->tagIDs, function( $tagID ){
                return (int)$tagID >= 1;
            });

            // Ensure categories are numeric only
            $this->categoryIDs = array_filter($this->categoryIDs, function( $categoryID ){
                return (int)$categoryID >= 1;
            });

            // If start hasn't been declared, set it to today but time 00:00:00
            if( !($this->startDTO instanceof DateTime) ){
                $this->startDTO = new DateTime('now', new DateTimeZone('UTC'));
                $this->startDTO->setTime(0,0,0);
            }

            // Conversely, if the endDTO *HAS* been set, automatically adjust
            // the queryDaySpan property to be the difference between start and end
            if( $this->endDTO instanceof DateTime ){
                $daySpan = $this->endDTO->diff($this->startDTO, true)->days + 1;
                $daySpan = ($daySpan >= self::DAYS_IN_FUTURE_MAX) ? self::DAYS_IN_FUTURE_MAX : $daySpan;
                $this->queryDaySpan = $daySpan;
            // endDTO is NOT set, but we know we always have a valid queryDaySpan
            // into the future, so derive the endDTO by adding startDate + daySpan
            }else{
                $this->endDTO = clone $this->startDTO;
                $this->endDTO->modify("+{$this->queryDaySpan} days");
            }

            // Are we trying to filter by a master collection? Note, because we're setting this
            // later, it'll override any other manually set collectionIDs to filter by (eg. master
            // always overrides user-defined)
            if( $this->filterByMasterCollection ){
                $packageObj     = Package::getByHandle('schedulizer');
                $masterCollID   = (int) $packageObj->configGet($packageObj::CONFIG_MASTER_COLLECTION_ID);
                $collectionObj  = \Concrete\Package\Schedulizer\Src\Collection::getByID($masterCollID);
                if( is_object($collectionObj) ){
                    $this->filterByCollectionObject($collectionObj);
                }
            }

            return $this;
        }


        /**
         * Parse the entire query string together.
         * @return string
         * @throws Exception
         */
        protected function assembledQuery(){
            //echo $this->prepare()->queryString(); exit;
            return $this->prepare()->queryString();
        }


        /**
         * Loads the _eventListQuery file and prepares the query string.
         * @return mixed
         */
        protected function queryString(){
            $queryData = (object) array(
                'selectableColumns' => $this->getQueryColumnSettings(),
                'calendarIDs'       => $this->calendarIDs,
                'eventIDs'          => $this->eventIDs,
                'tagIDs'            => $this->tagIDs,
                'categoryIDs'       => $this->categoryIDs,
                'startDTO'          => $this->startDTO,
                'endDTO'            => $this->endDTO,
                'queryDaySpan'      => (int)$this->queryDaySpan,
                'limitPerDay'       => (int)$this->limitPerDay,
                'limitTotal'        => (int)$this->limitTotal,
                'fullTextSearch'    => $this->fullTextSearch,
                'doEventGrouping'   => $this->doEventGrouping,
                'filterByIsActive'  => $this->filterByIsActive,
                'collectionID'      => $this->collectionID
            );
            return (require sprintf("%s/_eventListQuery.php", __DIR__));
        }

    }

}


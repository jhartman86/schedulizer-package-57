<?php namespace Concrete\Package\Schedulizer\Src {

    use Package;
    use Loader;
    use DateTime;
    use DateTimeZone;
    use \Exception;

    /**
     * Class EventList. This goes completely around Doctrine and composes the database
     * query directly; no idea how to even begin building a query like this in an ORM.
     * @package Concrete\Package\Schedulizer\Src
     */
    class CollectionEventList extends \Concrete\Package\Schedulizer\Src\EventList {

        const DAYS_IN_FUTURE            = 90, //45, // span 6 weeks for some calendar views
              DAYS_IN_FUTURE_MAX        = 365, //365,
              LIMIT_PER_DAY_MAX         = 25;

        protected $filterByDiscrepancies = false;
        protected $queryDaySpan = self::DAYS_IN_FUTURE;
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
            'occurrences'                   => array(false, self::COLUMN_CAST_INT),
            // When query for collection status in dashboard!,
            'collectionID' => array(true, self::COLUMN_CAST_INT),
            'approvedVersionID' => array(true, self::COLUMN_CAST_INT),
            'autoApprovable' => array(true, self::COLUMN_CAST_BOOL)
        );


        public function __construct( $collectionID ){
            parent::__construct();
            $this->setSchedulizerCollectionID($collectionID);
        }


        /**
         * Filter by collection approval discrepancies
         * @param bool $to
         */
        public function setFilterByDiscrepancies( $to = false ){
            $this->filterByDiscrepancies = $to;
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
                $collectionObj = \Concrete\Package\Schedulizer\Src\Collection::getMasterCollection();
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
                'selectableColumns'     => $this->getQueryColumnSettings(),
                'calendarIDs'           => $this->calendarIDs,
                'eventIDs'              => $this->eventIDs,
                'tagIDs'                => $this->tagIDs,
                'categoryIDs'           => $this->categoryIDs,
                'startDTO'              => $this->startDTO,
                'endDTO'                => $this->endDTO,
                'queryDaySpan'          => (int)$this->queryDaySpan,
                'limitPerDay'           => (int)$this->limitPerDay,
                'limitTotal'            => (int)$this->limitTotal,
                'fullTextSearch'        => $this->fullTextSearch,
                'doEventGrouping'       => $this->doEventGrouping,
                'filterByIsActive'      => $this->filterByIsActive,
                'collectionID'          => $this->collectionID,
                'filterByDiscrepancies' => $this->filterByDiscrepancies
            );
            return (require sprintf("%s/_collectionListQuery.php", __DIR__));
        }

    }

}


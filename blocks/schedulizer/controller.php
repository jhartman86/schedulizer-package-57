<?php namespace Concrete\Package\Schedulizer\Block\Schedulizer;

    use Concrete\Core\User\PrivateMessage\Event;
    use \DateTime;
    use \DateTimeZone;
    use Loader;
    use Concrete\Package\Schedulizer\Src\Calendar;
    use Concrete\Package\Schedulizer\Src\EventTag;
    use Concrete\Package\Schedulizer\Src\EventList;

    class Controller extends \Concrete\Core\Block\BlockController {

        protected $blockData;

        protected $btTable 									= 'btSchedulizer';
        protected $btInterfaceWidth 						= '585';
        protected $btInterfaceHeight						= '440';
        protected $btDefaultSet                             = 'artsy';
        protected $btCacheBlockRecord 						= false; // @todo: renable for production: true;
        protected $btCacheBlockOutput 						= false; // @todo: renable for production: true;
        protected $btCacheBlockOutputOnPost 				= false; // @todo: renable for production: true;
        protected $btCacheBlockOutputForRegisteredUsers 	= false;
        protected $btCacheBlockOutputLifetime 				= 0;


        /**
         * @return string
         */
        public function getBlockTypeName(){
            return t("Schedulizer");
        }


        /**
         * @return string
         */
        public function getBlockTypeDescription(){
            return t("Display Schedulizer");
        }


        public function view(){
            if( is_object($this->parsedRecord()) ){
                $eventListObj = new EventList((array)$this->parsedRecord()->calendarIDs);
                $eventListObj->setStartDate(new DateTime($this->parsedRecord()->startDate));
                $eventListObj->setEndDate(new DateTime($this->parsedRecord()->endDate));
                if( $this->parsedRecord()->limitPerDay ){
                    $eventListObj->setLimitPerDay($this->parsedRecord()->limitPerDay);
                }
                $eventListObj->includeColumns(array(
                    'fileID'
                ));
                $this->set('eventListObj', $eventListObj);
            }
        }


        public function add(){
            $this->edit();
        }


        public function composer(){
            $this->edit();
        }


        public function edit(){
            $this->requireAsset('select2');
            $this->set('calendarList', $this->calendarListResults());
            $this->set('tagList', $this->eventTagList());

            $selectedCalendars  = array();
            $selectedTags       = array();
            $startDate          = null;
            $endDate            = null;
            $limitPerDay        = null;

            if( is_object($this->parsedRecord()) ){
                $selectedCalendars  = (array)$this->parsedRecord()->calendarIDs;
                $selectedTags       = (array)$this->parsedRecord()->tagIDs;
                $startDate          = (new \DateTime($this->parsedRecord()->startDate))->format('m/d/Y');
                $endDate            = (new \DateTime($this->parsedRecord()->endDate))->format('m/d/Y');
                $limitPerDay        = (int)$this->parsedRecord()->limitPerDay;
            }

            $this->set('selectedCalendars', $selectedCalendars);
            $this->set('selectedTags', $selectedTags);
            $this->set('startDate', $startDate);
            $this->set('endDate', $endDate);
            $this->set('limitPerDay', $limitPerDay);
        }

        protected function parsedRecord(){
            if( $this->_parsedRecord === null ){
                $this->_parsedRecord = json_decode($this->blockData);
            }
            return $this->_parsedRecord;
        }

        protected function calendarListResults(){
            if( $this->_calendarList === null ){
                $this->_calendarList = Calendar::fetchAll();
            }
            return $this->_calendarList;
        }

        protected function eventTagList(){
            if( $this->_eventTagList === null ){
                $this->_eventTagList = EventTag::fetchAll();
            }
            return $this->_eventTagList;
        }


        /**
         * Called automatically by framework
         * @param array $args
         */
        public function save( $args ){
            parent::save(array('blockData' => json_encode((object)array(
                'calendarIDs'   => (array) $args['calendarIDs'],
                'startDate'     => empty($args['startDate']) ? null : (new DateTime($args['startDate'], new DateTimeZone('UTC')))->format('Y-m-d H:i:s'),
                'endDate'       => empty($args['endDate']) ? null : (new DateTime($args['endDate'], new DateTimeZone('UTC')))->format('Y-m-d H:i:s'),
                'tagIDs'        => (array) $args['eventTags'],
                'limitPerDay'   => $args['limitPerDay']
            ))));
        }

    }
<?php namespace Concrete\Package\Schedulizer\Block\SchedulizerEvent;

    use Loader;
    use Concrete\Package\Schedulizer\Src\Calendar;
    use Concrete\Package\Schedulizer\Src\Event;
    use Concrete\Package\Schedulizer\Src\EventList;

    class Controller extends \Concrete\Core\Block\BlockController {

        protected $btTable 									= 'btSchedulizerEvent';
        protected $btInterfaceWidth 						= '585';
        protected $btInterfaceHeight						= '440';
        protected $btDefaultSet                             = 'artsy';
        protected $btCacheBlockRecord 						= false; // @todo: renable for production: true;
        protected $btCacheBlockOutput 						= false; // @todo: renable for production: true;
        protected $btCacheBlockOutputOnPost 				= false; // @todo: renable for production: true;
        protected $btCacheBlockOutputForRegisteredUsers 	= false;
        protected $btCacheBlockOutputLifetime 				= 0;

        protected $eventID;

        /**
         * @return string
         */
        public function getBlockTypeName(){
            return t("Schedulizer Event");
        }


        /**
         * @return string
         */
        public function getBlockTypeDescription(){
            return t("Display Individual Schedulizer Event");
        }


        public function view(){
            $this->set('eventObj', $this->eventObj());
            if( $this->eventObj() instanceof Event ){
                $eventListObj = new EventList(array($this->eventObj()->getCalendarID()));
                $eventListObj->setEventIDs(array($this->eventObj()->getID()));
                $eventListObj->setDaysIntoFuture(365);
                $eventListObj->includeColumns(array(
                    'computedStartLocal'
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
            $this->set('calendarList', $this->calendarListResults());
            $eventObj = $this->eventObj();
            if( $eventObj instanceof Event ){
                $this->set('selectedCalendarID', $eventObj->getCalendarID());
                $this->set('selectedEventID', $eventObj->getID());
            }
        }

        /**
         * @return Event|void
         */
        protected function eventObj(){
            if( $this->_eventObj === null ){
                $this->_eventObj = Event::getByID($this->eventID);
            }
            return $this->_eventObj;
        }

        protected function calendarListResults(){
            if( $this->_calendarList === null ){
                $calendars  = Calendar::fetchAll();
                $selectList = array();
                foreach($calendars AS $calendarObj){
                    $selectList[$calendarObj->getID()] = $calendarObj->getTitle();
                }
                $this->_calendarList = $selectList;
            }
            return $this->_calendarList;
        }

        /**
         * Called automatically by framework
         * @param array $args
         */
        public function save( $args ){
            parent::save(array(
                'eventID' => (int) $args['eventID']
            ));
        }

    }
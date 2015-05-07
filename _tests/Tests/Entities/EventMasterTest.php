<?php //namespace Schedulizer\Tests\Entities {
//
//    use Concrete\Package\Schedulizer\Src\Calendar;
//    use Concrete\Package\Schedulizer\Src\Event;
//    use Concrete\Package\Schedulizer\Src\EventTime;
//    use Concrete\Package\Schedulizer\Src\EventTimeRepeat;
//
//    class EventMasterTest extends \Schedulizer\Tests\DatabaseTestCase {
//
//        protected $tableCalendar            = 'SchedulizerCalendar';
//        protected $tableEventMaster         = 'SchedulizerEvent';
//        protected $tableEvent               = 'SchedulizerEventTime';
//        protected $tableEventRepeat         = 'SchedulizerEventTimeRepeat';
//        //protected $tableEventRepeatNullify  = 'SchedulizerEventTimeRepeatNullify';
//        protected $calendarObj;
//
//        protected $eventData = array(
//            'title'         => 'EventMaster1',
//            'calendarID'    => 1,
//            'ownerID'       => 1
//        );
//
//        protected $eventTimeData = array(
//            'startUTC'      => '2015-04-17 09:00:00',
//            'endUTC'        => '2015-04-17 17:00:00',
//            'repeatEndUTC'  => '2015-04-17 17:00:00'
//        );
//
//        public function setUp(){
//            parent::setUp();
//            $this->calendarObj = Calendar::getByID(1);
//        }
//
//        public function testRunning(){
//
//        }
//
////        public function testCreateEvent(){
////            $event = Event::create($this->eventData);
////
////            $eventTime1 = EventTime::create(array_merge($this->eventTimeData, array(
////                'eventID' => $event->getID()
////            )));
////
////            $eventTime2 = EventTime::create(array_merge($this->eventTimeData, array(
////                'eventID'           => $event->getID(),
////                'isRepeating'       => EventTime::IS_REPEATING_TRUE,
////                'repeatTypeHandle'  => EventTime::REPEAT_TYPE_HANDLE_WEEKLY
////            )));
////
////            $eventTime2->setRepeaters((object) array(
////                'weekdayIndices' => array(3, 5, 8)
////            ));
////        }
////
////        public function testGetEvent(){
////            $event = Event::create($this->eventData);
////
////            EventTime::create(array_merge($this->eventTimeData, array(
////                'eventID' => $event->getID()
////            )));
////
////            $eventTime1 = EventTime::create(array_merge($this->eventTimeData, array(
////                'eventID'           => $event->getID(),
////                'isRepeating'       => EventTime::IS_REPEATING_TRUE,
////                'repeatTypeHandle'  => EventTime::REPEAT_TYPE_HANDLE_WEEKLY
////            )));
////
////            $eventTime1->setRepeaters((object) array(
////                'weekdayIndices' => array(3, 5, 8)
////            ));
////
////            $ev = Event::getByID(1);
////            $et = EventTime::fetchAllByEventID($ev->getID());
////
////            echo json_encode($event);
////        }
//
//    }
//
//}
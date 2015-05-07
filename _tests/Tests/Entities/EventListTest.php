<?php //namespace Schedulizer\Tests\Entities {
//
//    use Concrete\Package\Schedulizer\Src\EventList;
//
//    class EventListTest extends \PHPUnit_Framework_TestCase {
//
//        /** @var $eventListObj EventList */
//        protected $eventListObj;
//
//        public function setUp(){
//            $this->eventListObj = new EventList();
//        }
//
//        public function testBasic(){
//            $this->eventListObj->setCalendarIDs(array(1,2));
//            //$this->eventListObj->setEventIDs(3);
//            $this->eventListObj->setDaysIntoFuture(365);
//            $this->eventListObj->setColumnsToFetch(array(
//                'eventID',
//                'computedStartLocal'
//            ));
//            $res = $this->eventListObj->get();
//            print_r($res);
//        }
//
//    }
//
//}
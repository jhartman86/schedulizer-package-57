<?php //namespace Schedulizer\Tests\Entities {
//
//    use \Concrete\Package\Schedulizer\Src\Event;
//    use \Concrete\Package\Schedulizer\Src\EventTag;
//
//    class EventTagsTest extends \PHPUnit_Framework_TestCase {
//
//        public function testCreatingNewEventAndTag(){
//            $eventObj = Event::create((array(
//                'calendarID' => 13,
//                'startUTC' => '2015-02-19 14:30:00',
//                'endUTC' => '2015-02-19 17:15:00',
//                'title' => 'A new event',
//                'description' => 'this is the descr',
//                'useCalendarTimezone' => 0,
//                'timezoneName' => 'America/New_York',
//                'isAllDay' => false,
//                'isRepeating' => false,
//                'repeatIndefinite' => false,
//                'ownerID' => 14
//            )));
////            $eventObj->addTag(new EventTag('test'));
////            $eventObj->save();
//        }
////
////        public function testAddPlainOldTag(){
////            EventTag::create(array('tagName' => 'This is a tag'));
////            EventTag::create(array('tagName' => 'This is a second tag'));
////            EventTag::create(array('tagName' => 'This is a third tag'));
////        }
////
////        public function testUpdateEventWithTag(){
////            $eventObj = Event::getByID(1);
////            $eventObj->addTag(EventTag::getByID(2));
////            $eventObj->addTag(EventTag::getByID(3));
////            $eventObj->save();
////        }
//
////        public function testUpdatePreviouslyTaggedEvents(){
////            $eventObj = Event::getByID(1);
////            $tags = $eventObj->getEventTags();
////            print_r($tags);
////            exit;
////        }
//
//    }
//
//}
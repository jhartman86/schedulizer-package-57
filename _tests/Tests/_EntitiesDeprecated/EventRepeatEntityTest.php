<?php namespace Schedulizer\Tests\Entities {

    use Concrete\Package\Schedulizer\Src\EventRepeat;

    /**
     * Class EventRepeatEntityTest
     * @package Schedulizer\Tests\Entities
     * @group repeaters
     */
    class EventRepeatEntityTest extends \PHPUnit_Framework_TestCase {

        public function testEntityInstantiatedWithProperDefaults(){
            $repeatObj = new EventRepeat();
            $reflection = new \ReflectionObject($repeatObj);
            foreach($reflection->getProperties() AS $property){
                // Private properties we want to skip
                if( ! $property->isPrivate() ){
                    $property->setAccessible(true);
                    $this->assertEquals(null, $property->getValue($repeatObj));
                }
            }
        }

        public function testSetPropertiesViaConstructor(){
            $repeatObj = new EventRepeat(array(
                'eventID' => 2,
                'repeatWeek' => 3,
                'repeatDay' => 6,
                'repeatWeekday' => 4
            ));

            $reflection = new \ReflectionObject($repeatObj);

            $prop = $reflection->getProperty('eventID');
            $prop->setAccessible(true);
            $this->assertEquals(2, $prop->getValue($repeatObj));

            $prop = $reflection->getProperty('repeatWeek');
            $prop->setAccessible(true);
            $this->assertEquals(3, $prop->getValue($repeatObj));

            $prop = $reflection->getProperty('repeatDay');
            $prop->setAccessible(true);
            $this->assertEquals(6, $prop->getValue($repeatObj));

            $prop = $reflection->getProperty('repeatWeekday');
            $prop->setAccessible(true);
            $this->assertEquals(4, $prop->getValue($repeatObj));
        }

        public function testJsonSerialization(){
            $repeatObj = new EventRepeat(array(
                'eventID' => 2,
                'repeatWeek' => 3,
                'repeatDay' => 6,
                'repeatWeekday' => 4
            ));
            $result = json_decode(json_encode($repeatObj));
            $this->assertObjectHasAttribute('eventID', $result);
            $this->assertObjectHasAttribute('repeatWeek', $result);
            $this->assertObjectHasAttribute('repeatDay', $result);
            $this->assertObjectHasAttribute('repeatWeekday', $result);
        }

//        public function testCreatingNewEventWithRepeaters(){
//            $eventObj = new Event;
//            $eventObj->setPropertiesFromArray(array(
//                'calendarID' => 13,
//                'startUTC' => '2015-02-19 14:30:00',
//                'endUTC' => '2015-02-19 17:15:00',
//                'title' => 'Whoooaaaa',
//                'description' => 'this is the descr',
//                'useCalendarTimezone' => 0,
//                'timezoneName' => 'America/New_York',
//                'isAllDay' => false,
//                'isRepeating' => false,
//                'repeatIndefinite' => false,
//                'ownerID' => 14
//            ));
//            $eventObj->addRepeatSetting(new EventRepeat(array(
//                'repeatWeek'    => 2,
//                'repeatWeekday' => 5
//            )));
//            $eventObj->addRepeatSetting(new EventRepeat(array(
//                'repeatWeek'    => 1,
//                'repeatDay'     => 3
//            )));
//            $eventObj->save();
//
//            return $eventObj;
//        }
//
//        /**
//         * @depends testCreatingNewEventWithRepeaters
//         * @param Event $eventObj
//         * @return Event
//         */
//        public function testUpdating( Event $eventObj ){
//            $eventObj->update(array(
//                'title' => 'changed the name huh'
//            ));
//            $eventObj->addRepeatSetting(new EventRepeat(array(
//                'repeatWeek' => 1,
//                'repeatDay'  => 1,
//                'repeatWeekday' => 1
//            )));
//            $eventObj->save();
//
//            return $eventObj;
//        }
//
//        /**
//         * @depends testUpdating
//         * @param Event $eventObj
//         * @return Event
//         */
//        public function testReadingUpdateRecords( Event $eventObj ){
//            echo json_encode($eventObj) . "\n\n";
//
//            $records = $eventObj->getRepeatSettings();
//            foreach($records as $repeater){
//                echo json_encode($repeater);
//                echo "\n\n";
//            }
////            $records = $eventObj->getRepeatSettings()->toArray();
////            print_r($records);
////            exit;
//        }
//
//        public function testAddPlainOldTag(){
//            EventTag::create(array('tagName' => 'This is a tag'));
//            EventTag::create(array('tagName' => 'This is a second tag'));
//            EventTag::create(array('tagName' => 'This is a third tag'));
//        }
//
//        public function testUpdateEventWithTag(){
//            $eventObj = Event::getByID(1);
//            $eventObj->addTag(EventTag::getByID(2));
//            $eventObj->addTag(EventTag::getByID(3));
//            $eventObj->save();
//        }

//        public function testUpdatePreviouslyTaggedEvents(){
//            $eventObj = Event::getByID(1);
//            $tags = $eventObj->getEventTags();
//            print_r($tags);
//            exit;
//        }

    }

}
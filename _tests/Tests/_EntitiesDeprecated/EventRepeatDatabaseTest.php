<?php namespace Schedulizer\Tests\Entities {

    use Concrete\Package\Schedulizer\Src\Calendar;
    use Concrete\Package\Schedulizer\Src\Event;
    use Concrete\Package\Schedulizer\Src\EventRepeat;

    /**
     * Class CalendarDatabaseTest
     * @package Schedulizer\Tests\Calendar
     * @group repeaters
     */
    class EventRepeatDatabaseTest extends \Schedulizer\Tests\DatabaseTestCase {

        const TABLE_NAME_CALENDAR = 'SchedulizerCalendar';
        const TABLE_NAME_EVENT    = 'SchedulizerEvent';

        protected static $newEventSettingSample = array(
            'startUTC'              => '2015-02-19 14:30:00',
            'endUTC'                => '2015-02-19 17:15:00',
            'title'                 => 'A new event',
            'description'           => 'this is the descr',
            'useCalendarTimezone'   => Event::USE_CALENDAR_TIMEZONE_TRUE,
            'isAllDay'              => false,
            'isRepeating'           => false,
            'repeatIndefinite'      => false,
            'ownerID'               => 14
        );

        public function testEventOneHasAssociatedRepeatSettings(){
            /** @var $eventObj Event */
            $eventObj = Event::getByID(1);
            $this->assertEquals(2, $eventObj->getRepeatSettings()->count());
        }

        public function testEventTwoHasAssociatedRepeatSettings(){
            /** @var $eventObj Event */
            $eventObj = Event::getByID(2);
            $this->assertEquals(3, $eventObj->getRepeatSettings()->count());
        }

        public function testCreatingNewEventWithRepeater(){

        }

        public function testUpdatingExistingEventWithRepeaters(){
//            /** @var $cal Calendar */
//            $cal = Calendar::getByID(3);
//            $ev = $cal->getEvents()->first();
//            $ev->addRepeatSetting(new EventRepeat(array(
//                'repeatWeekday' => 22
//            )));
//            $ev->save();


//            Event::getByID(1)->update(array(
//                'title' => 'Doctrine sucks'
//            ));

//            $ev = Event::getByID(1);
//            $ev->setPropertiesFromArray(array(
//                'title' => 'dirty'
//            ));
//            $ev->addRepeatSetting(new EventRepeat(array(
//                'repeatWeek' => 3
//            )));
//            $ev->save();

//            $cal = Calendar::getByID(3);
//            $cal->addEvent(new Event(array(
//                'title' => 'fuck this'
//            )));
//            $cal->save();
//            $this->packageEntityManager()->clear();

//            /** @var $eventObj Event */
//            $eventObj = Event::getByID(1);
//            $eventObj->addRepeatSetting(new EventRepeat(array(
//                'repeatWeek' => 3
//            )));
//            $eventObj->addRepeatSetting(new EventRepeat(array(
//                'repeatDay' => 5,
//                'repeatWeekday' => 3
//            )));
//            $eventObj->save();
        }


        public function testNewEventWithRepeatSettingsOnPersistLifecycleGetsCalled(){
            $cal        = Calendar::getByID(3);
            $eventObj   = new Event(self::$newEventSettingSample);
//            $eventObj->addRepeatSetting(new EventRepeat(array(
//                'repeatWeek' => 27
//            )));
            $cal->addEvent($eventObj);
            $cal->save();
        }

    }

}
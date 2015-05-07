<?php namespace Schedulizer\Tests\Entities {

    use \Concrete\Package\Schedulizer\Src\Event;
    use \Concrete\Package\Schedulizer\Src\EventRepeat;

    class EventEntityTest extends \PHPUnit_Framework_TestCase {

        /** @var $eventObj Event */
        protected $eventObj;

        public function setUp(){
            $this->eventObj = new Event();
        }

        public function testEventInstantiatedWithProperDefaults(){
            $this->assertEquals(null, $this->eventObj->__toString());
            $this->assertEquals(null, $this->eventObj->getID(), "New'd Event should not have an ID");
            $this->assertEquals(null, $this->eventObj->getCreatedUTC(), "New'd Event should not have createdUTC");
            $this->assertEquals(null, $this->eventObj->getModifiedUTC(), "New'd Event should not have modifiedUTC");
            $this->assertEquals(null, $this->eventObj->getCalendar(), "New'd Event should not have a calendar entity");
            $this->assertEquals(null, $this->eventObj->getTitle(), "New'd Event should have a null title");
            $this->assertEquals(null, $this->eventObj->getDescription(), "New'd Event should have a null description");
            $this->assertEquals(null, $this->eventObj->getStartUTC(), "New'd Event should have null startUTC");
            $this->assertEquals(null, $this->eventObj->getEndUTC(), "New'd Event should have null endUTC");
            $this->assertEquals(false, $this->eventObj->getIsAllDay(), "New'd Event isAllDay should default to false");
            $this->assertEquals(true, $this->eventObj->getUseCalendarTimezone(), "New'd Event useCalendarTimezone should default to true");
            $this->assertEquals('UTC', $this->eventObj->getTimezoneName(), "New'd Event timezoneName should default to UTC");
            $this->assertStringStartsWith('#', $this->eventObj->getEventColor(), "New'd Event eventColor should be a HEX color code");
            $this->assertEquals(false, $this->eventObj->getIsRepeating(), "New'd Event isRepeating should default to false");
            $this->assertEquals(null, $this->eventObj->getRepeatTypeHandle(), "New'd Event repeatTypeHandle should be null");
            $this->assertEquals(null, $this->eventObj->getRepeatEvery(), "New'd Event repeatEvery should be null");
            $this->assertEquals(null, $this->eventObj->getRepeatIndefinite(), "New'd Event repeatIndefinite should be null");
            $this->assertEquals(null, $this->eventObj->getRepeatEndUTC(), "New'd Event repeatEndUTC should be null");
            $this->assertEquals(null, $this->eventObj->getRepeatMonthlyMethod(), "New'd Event repeatMonthlyMethod should be null");
            $this->assertEquals(null, $this->eventObj->getOwnerID(), "New'd Event should not have an ownerID");
            $this->assertEquals(null, $this->eventObj->getFileID(), "New'd Event should not have a fileID");
        }

        public function testEventSetPropertiesFromArray(){
            $this->eventObj->setPropertiesFromArray(array(
                'title'         => 'Bruce Wayne',
                'description'   => 'Watch BW mess people up',
                'startUTC'      => '2015-02-02 09:00:00',
                'openEnded'     => Event::OPEN_ENDED_TRUE,
                'timezoneName'  => 'America/New_York'
            ));
            $this->assertEquals('Bruce Wayne', $this->eventObj->getTitle());
            $this->assertEquals('Watch BW mess people up', $this->eventObj->getDescription());
            $this->assertEquals('2015-02-02 09:00:00', $this->eventObj->getStartUTC());
            $this->assertEquals(true, $this->eventObj->getOpenEnded());
            $this->assertEquals('America/New_York', $this->eventObj->getTimezoneName());
        }

        public function testEventSetPropertiesFromObject(){
            $this->eventObj->setPropertiesFromArray(array(
                'title'         => 'Bruce Wayne',
                'description'   => 'Watch BW mess people up',
                'startUTC'      => '2015-02-02 09:00:00',
                'openEnded'     => Event::OPEN_ENDED_TRUE,
                'timezoneName'  => 'America/New_York'
            ));
            $this->assertEquals('Bruce Wayne', $this->eventObj->getTitle());
            $this->assertEquals('Watch BW mess people up', $this->eventObj->getDescription());
            $this->assertEquals('2015-02-02 09:00:00', $this->eventObj->getStartUTC());
            $this->assertEquals(true, $this->eventObj->getOpenEnded());
            $this->assertEquals('America/New_York', $this->eventObj->getTimezoneName());
        }

//        public function testAddingEventRepeatEntities(){
//            $this->eventObj->addRepeatSetting(new EventRepeat(array('repeatWeek' => 2, 'repeatDay' => 6, 'repeatWeekday' => 19)));
//            $this->eventObj->addRepeatSetting(new EventRepeat(array('repeatWeek' => 3, 'repeatDay' => 13, 'repeatWeekday' => 5)));
//            $this->assertCount(2, $this->eventObj->getRepeatSettings());
//        }

    }

}
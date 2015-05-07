<?php namespace Schedulizer\Tests\Entities {

    use Concrete\Package\Schedulizer\Src\Calendar;
    use Concrete\Package\Schedulizer\Src\Event;

    /**
     * Class EventDatabaseTest
     * @package Schedulizer\Tests\Event
     */
    class EventDatabaseTest extends \Schedulizer\Tests\DatabaseTestCase {

        const TABLE_NAME_CALENDAR = 'SchedulizerCalendar';
        const TABLE_NAME_EVENT    = 'SchedulizerEvent';

        /** @var $calendarObj Calendar */
        protected $calendarObj;

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

        /**
         * Use Doctrine's destroy/create schema facilities to destroy and
         * create for each test.
         */
        public function setUp(){
            parent::setUp();
            $this->calendarObj = Calendar::getByID(1);
        }

        /**
         * @todo: test ALL properties
         */
        public function testEventGetInstanceByID(){
            $instance = Event::getByID(1);
            $this->assertEquals(1, $instance->getID());
            $this->assertEquals('First Event Name', $instance->getTitle());
            $this->assertEquals('Lorem ipsum dolor sit amet consect', $instance->getDescription());
            $this->assertInstanceOf('DateTime', $instance->getStartUTC());
            $this->assertInstanceOf('DateTime', $instance->getEndUTC());
            $this->assertInstanceOf('DateTime', $instance->getRepeatEndUTC());
            $this->assertInstanceOf('DateTime', $instance->getCreatedUTC());
            $this->assertInstanceOf('DateTime', $instance->getModifiedUTC());
        }

        /**
         * @expectedException \Exception
         */
        public function testCreateEventWithoutCalendarAssociationFails(){
            Event::create(self::$newEventSettingSample);
        }

        /**
         * Create an event with minimal info passed (whats required) and ensure
         * proper defaults are set.
         */
        public function testCreateEventHasProperDefaults(){
            /** @var $eventObj Event */
            $eventObj = Event::create(array(
                'title' => 'Something',
                'ownerID' => 12,
                'calendarInstance' => $this->calendarObj
            ));

            $this->assertEquals(1, $eventObj->getCalendar()->getID());
            $this->assertEquals(null, $eventObj->getDescription());
            $this->assertEquals(false, $eventObj->getOpenEnded());
            $this->assertEquals(false, $eventObj->getIsAllDay());
            $this->assertEquals(true, $eventObj->getUseCalendarTimezone());
            $this->assertEquals('America/New_York', $eventObj->getTimezoneName());
            $this->assertEquals(Event::EVENT_COLOR_DEFAULT, $eventObj->getEventColor());
            $this->assertEquals(false, $eventObj->getIsRepeating());
            $this->assertEquals(null, $eventObj->getRepeatTypeHandle());
            $this->assertEquals(null, $eventObj->getRepeatEvery());
        }

        /**
         * Test cascading persistence on the calendar for automatically creating a new
         * event.
         */
        public function testPersistingOneEventByCascadingCalendarSave(){
            $rowsBefore = $this->getConnection()->getRowCount(self::TABLE_NAME_EVENT);
            $this->calendarObj->addEvent(new Event(self::$newEventSettingSample));
            $this->calendarObj->save();
            $this->assertEquals(($rowsBefore + 1), $this->getConnection()->getRowCount(self::TABLE_NAME_EVENT));
        }

        /**
         * Test cascading persistence on the calendar for automatically creating multiple
         * events.
         */
        public function testPersistingMultipleEventsByCascadingCalendarSave(){
            $rowsBefore = $this->getConnection()->getRowCount(self::TABLE_NAME_EVENT);
            $this->calendarObj->addEvent(new Event(self::$newEventSettingSample));
            $this->calendarObj->addEvent(new Event(self::$newEventSettingSample));
            $this->calendarObj->addEvent(new Event(self::$newEventSettingSample));
            $this->calendarObj->save();
            $this->assertEquals(($rowsBefore + 3), $this->getConnection()->getRowCount(self::TABLE_NAME_EVENT));
        }

        /**
         * Test that creating a new event (via adding to the calendar events array collection)
         * is tracked via the Calendar's array collection.
         */
        public function testCalendarEventArrayCollectionSyncd(){
            $this->assertEquals(2, $this->calendarObj->getEvents()->count());
            $event1 = new Event(self::$newEventSettingSample);
            $this->calendarObj->addEvent($event1)->save();
            $this->assertEquals(3, $this->calendarObj->getEvents()->count());
            $this->assertEquals(true, $this->calendarObj->getEvents()->contains($event1));
        }

        /**
         * Test updating an existing event and then query the database directory to ensure
         * that it was captured.
         */
        public function testEventUpdate(){
            $eventObj = Event::getByID(3);

            $eventObj->update(array(
                'title'                 => 'MyNewTitle',
                'openEnded'             => Event::OPEN_ENDED_TRUE,
                'useCalendarTimezone'   => Event::USE_CALENDAR_TIMEZONE_FALSE,
                'timezoneName'          => 'America/New_York'
            ));

            $res = $this->getRawConnection()
                        ->query("SELECT * FROM ".self::TABLE_NAME_EVENT." WHERE id = 3")
                        ->fetch(\PDO::FETCH_OBJ);

            $this->assertEquals('MyNewTitle', $res->title);
            $this->assertEquals(1, $res->openEnded);
            $this->assertEquals(0, $res->useCalendarTimezone);
            $this->assertEquals('America/New_York', $res->timezoneName);
        }

        /**
         * Delete a single event and don't look at the state of anything else (just
         * the database)
         */
        public function testDeletingASingleEvent(){
            $rowsBefore = $this->getConnection()->getRowCount(self::TABLE_NAME_EVENT);
            Event::getByID(1)->delete();
            $this->assertEquals(($rowsBefore - 1), $this->getConnection()->getRowCount(self::TABLE_NAME_EVENT));
        }

        /**
         * After deleting an event, if we try to refetch it with getByID(), it should
         * be returned as Null and not cached by the entity manager.
         */
        public function testDeleteSingleEventEntityManagerInSync(){
            /** @var $eventObj Event */
            $eventObj = Event::getByID(1);
            $eventObj->delete();
            $this->assertEquals(null, Event::getByID(1));
        }

        /**
         * Even more complex than above - when we delete an event, we need to ensure
         * that the association via the ArrayCollection property of the calendar is updated.
         */
        public function testDeleteSingleEventThenCheckAssociationsFromCalendarSide(){
            /** @var $eventObj Event */
            $eventObj = Event::getByID(1);
            // Get the Calendar object
            $calendar = $eventObj->getCalendar();
            // Count that, BEFORE DELETION, the number of events matches 2
            $this->assertEquals(2, $calendar->getEvents()->count());
            // NOW delete the event
            $eventObj->delete();
            // Now, from the $calendar entity, check that the event count is correct (ie,
            // deleting the event entity is communicated to the ArrayCollection of the
            // the calendar)
            $this->assertEquals(1, $calendar->getEvents()->count());
        }

        /**
         * Test deleting a calendar cascades deletion to all associated events.
         */
        public function testDeletingACalendarCascadesDeletingAssociatedEvents(){
            $calendarID = $this->calendarObj->getID();

            $this->calendarObj->delete();

            // Count rows in SchedulizerCalendar table with ID of one just deleted
            $calendarRowCount = $this->getRawConnection()
                                     ->query("SELECT * FROM ".self::TABLE_NAME_CALENDAR." WHERE id = {$calendarID}")
                                     ->rowCount();

            // Count rows in SchedulizerEvent table with calendarID of one just deleted
            $eventRowCount = $this->getRawConnection()
                                  ->query("SELECT * FROM ".self::TABLE_NAME_EVENT." WHERE calendarID = {$calendarID}")
                                  ->rowCount();

            $this->assertEquals(0, $calendarRowCount);
            $this->assertEquals(0, $eventRowCount);
        }

    }
}
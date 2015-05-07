<?php //namespace Schedulizer\Tests\Entities {
//
//    use Concrete\Package\Schedulizer\Src\Calendar;
//    use Concrete\Package\Schedulizer\Src\Event;
//
//    /**
//     * Class EventTest
//     * @package Schedulizer\Tests\Entities
//     * @group entities
//     */
//    class EventTest extends \Schedulizer\Tests\DatabaseTestCase {
//
//        protected $tableNameCalendar = 'SchedulizerCalendar';
//        protected $tableNameEvent    = 'SchedulizerEvent';
//        /** @var $calendarObj Calendar */
//        protected $calendarObj;
//
//        protected static $newEventSettingSample = array(
//            'startUTC'              => '2015-02-19 14:30:00',
//            'endUTC'                => '2015-02-19 17:15:00',
//            'title'                 => 'A new event',
//            'description'           => 'this is the descr',
//            'ownerID'               => 14
//        );
//
//        public function setUp(){
//            parent::setUp();
//            $this->calendarObj = Calendar::getByID(1);
//        }
//
//        public function testEventInstantiatedWithProperDefaults(){
//            $event = new Event();
//            $this->assertEquals(null, $event->__toString());
//            $this->assertEquals(null, $event->getID(), "New'd Event should not have an ID");
//            $this->assertEquals(null, $event->getCreatedUTC(), "New'd Event should not have createdUTC");
//            $this->assertEquals(null, $event->getModifiedUTC(), "New'd Event should not have modifiedUTC");
//            $this->assertEquals(null, $event->getTitle(), "New'd Event should have a null title");
//            $this->assertEquals(null, $event->getDescription(), "New'd Event should have a null description");
//            $this->assertEquals(null, $event->getStartUTC(), "New'd Event should have null startUTC");
//            $this->assertEquals(null, $event->getEndUTC(), "New'd Event should have null endUTC");
//            $this->assertEquals(false, $event->getIsAllDay(), "New'd Event isAllDay should default to false");
//            $this->assertEquals(true, $event->getUseCalendarTimezone(), "New'd Event useCalendarTimezone should default to true");
//            $this->assertEquals('UTC', $event->getTimezoneName(), "New'd Event timezoneName should default to UTC");
//            $this->assertStringStartsWith('#', $event->getEventColor(), "New'd Event eventColor should be a HEX color code");
//            $this->assertEquals(false, $event->getIsRepeating(), "New'd Event isRepeating should default to false");
//            $this->assertEquals(null, $event->getRepeatTypeHandle(), "New'd Event repeatTypeHandle should be null");
//            $this->assertEquals(null, $event->getRepeatEvery(), "New'd Event repeatEvery should be null");
//            $this->assertEquals(null, $event->getRepeatIndefinite(), "New'd Event repeatIndefinite should be null");
//            $this->assertEquals(null, $event->getRepeatEndUTC(), "New'd Event repeatEndUTC should be null");
//            $this->assertEquals(null, $event->getRepeatMonthlyMethod(), "New'd Event repeatMonthlyMethod should be null");
//            $this->assertEquals(null, $event->getOwnerID(), "New'd Event should not have an ownerID");
//            $this->assertEquals(null, $event->getFileID(), "New'd Event should not have a fileID");
//
//            try {
//                $event->getCalendar();
//            }catch(\Exception $e){
//                $this->assertInstanceOf('Exception', $e);
//            }
//        }
//
//        /**
//         * Create an event with minimal info passed (whats required) and ensure
//         * proper defaults are set.
//         */
//        public function testCreateAndPersistingEventHasProperDefaults(){
//            /** @var $eventObj Event */
//            $eventObj = Event::create(array(
//                'title' => 'Something',
//                'ownerID' => 12,
//                'calendarID' => $this->calendarObj->getID()
//            ));
//
//            $this->assertEquals(1, $eventObj->getCalendar()->getID());
//            $this->assertEquals(null, $eventObj->getDescription());
//            $this->assertEquals(false, $eventObj->getIsOpenEnded());
//            $this->assertEquals(false, $eventObj->getIsAllDay());
//            $this->assertEquals(true, $eventObj->getUseCalendarTimezone());
//            $this->assertEquals(Event::EVENT_COLOR_DEFAULT, $eventObj->getEventColor());
//            $this->assertEquals(false, $eventObj->getIsRepeating());
//            $this->assertEquals(null, $eventObj->getRepeatTypeHandle());
//            $this->assertEquals(null, $eventObj->getRepeatEvery());
//
//            // Event should inherit the calendar timezone, so ensure that occurred
//            $this->assertEquals('America/New_York', $eventObj->getTimezoneName());
//        }
//
//        /**
//         * @todo: test ALL properties
//         */
//        public function testEventGetInstanceByID(){
//            $instance = Event::getByID(1);
//            $this->assertEquals(1, $instance->getID());
//            $this->assertEquals('First Event Name', $instance->getTitle());
//            $this->assertEquals('Lorem ipsum dolor sit amet consect', $instance->getDescription());
//            $this->assertInstanceOf('DateTime', $instance->getStartUTC());
//            $this->assertInstanceOf('DateTime', $instance->getEndUTC());
//            $this->assertInstanceOf('DateTime', $instance->getRepeatEndUTC());
//            $this->assertInstanceOf('DateTime', $instance->getCreatedUTC());
//            $this->assertInstanceOf('DateTime', $instance->getModifiedUTC());
//        }
//
//        /**
//         * @expectedException \Exception
//         */
//        public function testCreateEventWithoutCalendarAssociationFails(){
//            Event::create(self::$newEventSettingSample);
//        }
//
//        /**
//         * With foreign key constraints enabled, trying to add an event with an invalid (non-existent calendarID)
//         * should fail. NOTE: in order to have the event created WITHOUT the onBeforePersist callback
//         * try to find a calendar that is indeed invalid, we have to pass useCalendarTimezone as FALSE
//         * so it wont try to find the calendar and throw an exception if it doesn't exist.
//         *
//         * @expectedException \PDOException
//         * @group calendar_association
//         */
//        public function testEventWithInvalidCalendarAssociationFails(){
//            Event::create(array_merge(self::$newEventSettingSample, array(
//                'useCalendarTimezone' => Event::USE_CALENDAR_TIMEZONE_FALSE,
//                'calendarID' => 27
//            )));
//        }
//
//        /**
//         * @expectedException \Exception
//         */
//        public function testEventWithInvalidCalendarAssociationFailsWhenTryingToInheritTimezone(){
//            Event::create(array_merge(self::$newEventSettingSample, array(
//                'calendarID' => 27
//            )));
//        }
//
//        /**
//         * By default, events should inherit the calendar's timezone.
//         */
//        public function testCreateEventInheritsCalendarTimezone(){
//            $eventObj = Event::create(array_merge(self::$newEventSettingSample, array(
//                'calendarID' => $this->calendarObj->getID()
//            )));
//            $this->assertEquals($this->calendarObj->getDefaultTimezone(), $eventObj->getTimezoneName());
//        }
//
//        public function testEventUpdate(){
//            // Get the record state before doing anything else
//            $recordBefore = $this->getRawConnection()
//                ->query("SELECT * FROM {$this->tableNameEvent} WHERE id = 2")
//                ->fetch(\PDO::FETCH_OBJ);
//
//            // Execute update
//            $event = Event::getByID(2)->update(array(
//                'title'                 => 'Kerfuffle',
//                'isOpenEnded'           => Event::IS_OPEN_ENDED_TRUE,
//                'useCalendarTimezone'   => Event::USE_CALENDAR_TIMEZONE_FALSE,
//                'timezoneName'          => 'America/New_York',
//                'repeatEndUTC'          => '2015-09-01 13:00:00'
//            ));
//
//            // Get database record state after update
//            $recordAfter = $this->getRawConnection()
//                ->query("SELECT * FROM {$this->tableNameEvent} WHERE id = 2")
//                ->fetch(\PDO::FETCH_OBJ);
//
//            // Check the database record values
//            $this->assertEquals('Kerfuffle', $recordAfter->title);
//            $this->assertEquals(1, $recordAfter->isOpenEnded);
//            $this->assertEquals(0, $recordAfter->useCalendarTimezone);
//            $this->assertEquals('America/New_York', $recordAfter->timezoneName);
//            $this->assertEquals('2015-09-01 13:00:00', $recordAfter->repeatEndUTC);
//
//            // Check the $calendar instance values
//            $this->assertEquals('Kerfuffle', $event->getTitle());
//            $this->assertEquals(true, $event->getIsOpenEnded());
//            $this->assertEquals(false, $event->getUseCalendarTimezone());
//            $this->assertEquals('America/New_York', $event->getTimezoneName());
//            $this->assertInstanceOf('DateTime', $event->getRepeatEndUTC());
//
//
//            // Check the timestamps
//            $this->assertEquals($recordBefore->createdUTC, $recordAfter->createdUTC);
//            $this->assertNotEquals($recordBefore->modifiedUTC, $recordAfter->modifiedUTC);
//        }
//
//        public function testEventDelete(){
//            $rowsBefore = $this->getConnection()->getRowCount($this->tableNameEvent);
//            Event::getByID(1)->delete();
//            $this->assertEquals(($rowsBefore - 1), $this->getConnection()->getRowCount($this->tableNameEvent), 'Deleting Event Failed');
//        }
//
//        public function testJsonSerializationWithEmptyInstance(){
//            $result = json_decode(json_encode(new Event()));
//            $this->assertObjectNotHasAttribute('id', $result);
//            $this->assertObjectNotHasAttribute('createdUTC', $result);
//            $this->assertObjectNotHasAttribute('modifiedUTC', $result);
//            $this->assertObjectHasAttribute('title', $result);
//            $this->assertObjectHasAttribute('description', $result);
//            $this->assertObjectHasAttribute('startUTC', $result);
//            $this->assertObjectHasAttribute('endUTC', $result);
//            $this->assertObjectHasAttribute('isOpenEnded', $result);
//            $this->assertObjectHasAttribute('isAllDay', $result);
//            $this->assertObjectHasAttribute('useCalendarTimezone', $result);
//            $this->assertObjectHasAttribute('timezoneName', $result);
//            $this->assertObjectHasAttribute('eventColor', $result);
//            $this->assertObjectHasAttribute('isRepeating', $result);
//            $this->assertObjectHasAttribute('repeatTypeHandle', $result);
//            $this->assertObjectHasAttribute('repeatEvery', $result);
//            $this->assertObjectHasAttribute('repeatIndefinite', $result);
//            $this->assertObjectHasAttribute('repeatEndUTC', $result);
//            $this->assertObjectHasAttribute('repeatMonthlyMethod', $result);
//            $this->assertObjectHasAttribute('ownerID', $result);
//            $this->assertObjectHasAttribute('fileID', $result);
//        }
//
//        public function testJsonSerializationWithPopulatedInstance(){
//            $result = json_decode(json_encode(Event::getByID(1)));
//            $this->assertEquals('2015-02-01T12:00:00+00:00', $result->createdUTC);
//        }
//
//    }
//
//}
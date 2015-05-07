<?php //namespace Schedulizer\Tests\Entities {
//
//    use Concrete\Package\Schedulizer\Src\Calendar;
//
//    /**
//     * Class CalendarTest
//     * @package Schedulizer\Tests\Entities
//     * @group entities
//     */
//    class CalendarTest extends \Schedulizer\Tests\DatabaseTestCase {
//
//        protected $tableName = 'SchedulizerCalendar';
//
//        /**
//         * When creating a new instance make sure nothing is cattywhompus.
//         */
//        public function testCalendarInstantiatedWithProperDefaults(){
//            $calendar = new Calendar();
//            $this->assertEquals(null, $calendar->__toString());
//            $this->assertEquals(null, $calendar->getID());
//            $this->assertEquals(null, $calendar->getCreatedUTC());
//            $this->assertEquals(null, $calendar->getModifiedUTC());
//            $this->assertEquals(null, $calendar->getTitle());
//            $this->assertEquals(null, $calendar->getOwnerID());
//            $this->assertEquals('UTC', $calendar->getDefaultTimezone());
//        }
//
//        /**
//         * Get a calendar by ID and ensure all is swell.
//         */
//        public function testCalendarInstancePopulatedOnFetch(){
//            /** @var $instance Calendar */
//            $instance = Calendar::getByID(1);
//            $this->assertInstanceOf('Concrete\Package\Schedulizer\Src\Calendar', $instance);
//            $this->assertInternalType('int', $instance->getID());
//            $this->assertInstanceOf('DateTime', $instance->getCreatedUTC());
//            $this->assertInstanceOf('DateTime', $instance->getModifiedUTC());
//            $this->assertEquals('Title 1', $instance->getTitle());
//            $this->assertEquals('UTC', $instance->getDefaultTimezone());
//            $this->assertEquals(12, $instance->getOwnerID());
//        }
//
//        /**
//         * Create a new calendar instance by passing setters via constructor
//         * and then persist via calling ->save() directly.
//         */
//        public function testInstantiatingCalendarWithSetters(){
//            $rowsBefore = $this->getConnection()->getRowCount($this->tableName);
//            $calendar = new Calendar(array(
//                'title'     => 'asdf',
//                'ownerID'   => 13
//            ));
//            // First, inspect the instance (note, it should NOT be persisted yet!)
//            $this->assertEquals($rowsBefore, $this->getConnection()->getRowCount($this->tableName));
//            $this->assertEquals(false, $calendar->isPersisted());
//            $this->assertEquals('asdf', $calendar->getTitle());
//            $this->assertEquals(13, $calendar->getOwnerID());
//            // NOW persist the instance and re-check it
//            $calendar->save();
//            $this->assertEquals($rowsBefore + 1, $this->getConnection()->getRowCount($this->tableName));
//            $this->assertNotEquals(null, $calendar->getID());
//            $this->assertInstanceOf('DateTime', $calendar->getCreatedUTC());
//            $this->assertInstanceOf('DateTime', $calendar->getModifiedUTC());
//        }
//
//        /**
//         * Calendar is persisted on create
//         */
//        public function testCalendarCreate(){
//            $rowsBefore = $this->getConnection()->getRowCount($this->tableName);
//            $calendarObj = Calendar::create(array(
//                'title'             => 'My Title',
//                'ownerID'           => 22,
//                'defaultTimezone'   => 'America/Los_Angeles'
//            ));
//            $this->assertEquals(($rowsBefore + 1), $this->getConnection()->getRowCount($this->tableName), 'Inserting Calendar Failed');
//
//            // Inspect the returned instance from creating the Calendar
//            $this->assertNotEquals(null, $calendarObj->getID());
//            $this->assertInternalType('int', $calendarObj->getID());
//        }
//
//        /**
//         * Test updates are persisted AND that created/modified timestamps are correct.
//         */
//        public function testCalendarUpdate(){
//            // Get the record state before doing anything else
//            $recordBefore = $this->getRawConnection()
//                                 ->query("SELECT * FROM {$this->tableName} WHERE id = 1")
//                                 ->fetch(\PDO::FETCH_OBJ);
//
//            // Execute update
//            $calendar = Calendar::getByID(1)->update(array(
//                'title'             => 'FancyPants',
//                'defaultTimezone'   => 'Canada',
//                'ownerID'           => 1999
//            ));
//
//            // Get database record state after update
//            $recordAfter = $this->getRawConnection()
//                        ->query("SELECT * FROM {$this->tableName} WHERE id = 1")
//                        ->fetch(\PDO::FETCH_OBJ);
//
//            // Check the database record values
//            $this->assertEquals('FancyPants', $recordAfter->title);
//            $this->assertEquals('Canada', $recordAfter->defaultTimezone);
//            $this->assertEquals(1999, $recordAfter->ownerID);
//
//            // Check the $calendar instance values
//            $this->assertEquals('FancyPants', $calendar->getTitle());
//            $this->assertEquals('Canada', $calendar->getDefaultTimezone());
//            $this->assertEquals(1999, $calendar->getOwnerID());
//
//            // Check the timestamps
//            $this->assertEquals($recordBefore->createdUTC, $recordAfter->createdUTC);
//            $this->assertNotEquals($recordBefore->modifiedUTC, $recordAfter->modifiedUTC);
//        }
//
//        public function testCalendarPropertySetterOnExistingInstance(){
//            /** @var $calendar Calendar */
//            $calendar = Calendar::getByID(1);
//            $calendar->mergePropertiesFrom((object)array(
//                'title'     => 'Yolo',
//                'ownerID'   => 17
//            ));
//
//            // Check the $calendar instance values
//            $this->assertEquals('Yolo', $calendar->getTitle());
//            $this->assertEquals('UTC', $calendar->getDefaultTimezone());
//            $this->assertEquals(17, $calendar->getOwnerID());
//        }
//
//        /**
//         * Calendar record is actually removed from the db
//         */
//        public function testCalendarDeleteRemovesFromCalendarTable(){
//            $rowsBefore = $this->getConnection()->getRowCount($this->tableName);
//            Calendar::getByID(1)->delete();
//            $this->assertEquals(($rowsBefore - 1), $this->getConnection()->getRowCount($this->tableName), 'Deleting Calendar Failed');
//        }
//
//        /**
//         * Deleting a calendar should also delete all associated events.
//         */
//        public function testCalendarDeleteCascadesEventRemoval(){
//            $calendarID     = 3;
//            $rowsBefore     = $this->getConnection()->getRowCount('SchedulizerEvent');
//            $calendarEvents = (int) $this->getRawConnection()->query("SELECT count(*) FROM SchedulizerEvent WHERE calendarID = {$calendarID}")->fetchColumn(0);
//            Calendar::getByID($calendarID)->delete();
//            $this->assertEquals(($rowsBefore - $calendarEvents), $this->getConnection()->getRowCount('SchedulizerEvent'), 'Deleting Calendar did not cascade deletes to Events');
//        }
//
//        /**
//         * JSON serialization with empty instance (should be basically just a template)
//         */
//        public function testJsonSerializationWithEmptyInstance(){
//            $result = json_decode(json_encode(new Calendar()));
//            $this->assertObjectNotHasAttribute('id', $result);
//            $this->assertObjectNotHasAttribute('createdUTC', $result);
//            $this->assertObjectNotHasAttribute('modifiedUTC', $result);
//            $this->assertObjectHasAttribute('title', $result);
//            $this->assertObjectHasAttribute('ownerID', $result);
//            $this->assertObjectHasAttribute('defaultTimezone', $result);
//        }
//
//        /**
//         * JSON serialization with existing instance.
//         */
//        public function testJsonSerializationWithPopulatedInstance(){
//            $result = json_decode(json_encode(Calendar::getByID(1)));
//            $this->assertObjectHasAttribute('id', $result);
//            $this->assertObjectHasAttribute('createdUTC', $result);
//            $this->assertObjectHasAttribute('modifiedUTC', $result);
//            $this->assertObjectHasAttribute('title', $result);
//            $this->assertObjectHasAttribute('ownerID', $result);
//            $this->assertObjectHasAttribute('defaultTimezone', $result);
//            $this->assertEquals(1, $result->id);
//        }
//
//    }
//
//}
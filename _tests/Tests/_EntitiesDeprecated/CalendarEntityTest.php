<?php namespace Schedulizer\Tests\Entities {

    use \Concrete\Package\Schedulizer\Src\Calendar;

    class CalendarEntityTest extends \PHPUnit_Framework_TestCase {

        protected $calendarObj;

        public function setUp(){
            $this->calendarObj = new Calendar();
        }

        /**
         * Test that a new calendar instance, with nothing else, is in the
         * correct state.
         */
        public function testCalendarInstantiatedWithProperDefaults(){
            $this->assertEquals(null, $this->calendarObj->__toString());
            $this->assertEquals(null, $this->calendarObj->getID());
            $this->assertEquals(null, $this->calendarObj->getCreatedUTC());
            $this->assertEquals(null, $this->calendarObj->getModifiedUTC());
            $this->assertEquals(null, $this->calendarObj->getTitle());
            $this->assertEquals(null, $this->calendarObj->getOwnerID());
            $this->assertEquals('UTC', $this->calendarObj->getDefaultTimezone());
        }

        /**
         * Test that a new calendar instance returns correct values when properties
         * are passed as an array.
         */
        public function testCalendarSetPropertiesFromArray(){
            $this->calendarObj->setPropertiesFromArray(array(
                'title'             => 'MyTitle',
                'ownerID'           => 22,
                'defaultTimezone'   => 'America/Denver'
            ));

            $this->assertEquals('MyTitle', $this->calendarObj->getTitle());
            $this->assertEquals(22, $this->calendarObj->getOwnerID());
            $this->assertEquals('America/Denver', $this->calendarObj->getDefaultTimezone());
        }

        /**
         * Test that a new calendar instance returns correct values when properties
         * are passed as an object.
         */
        public function testCalendarSetPropertiesFromObject(){
            $this->calendarObj->setPropertiesFromObject((object)array(
                'title'             => 'MyTitle',
                'ownerID'           => 22,
                'defaultTimezone'   => 'America/Denver'
            ));

            $this->assertEquals('MyTitle', $this->calendarObj->getTitle());
            $this->assertEquals(22, $this->calendarObj->getOwnerID());
            $this->assertEquals('America/Denver', $this->calendarObj->getDefaultTimezone());
        }

        /**
         * When running json_encode on a new'd Calendar, specific properties shouldn't
         * be set (eg. id, createdUTC, modifiedUTC).
         */
        public function testJsonSerializationWithEmptyInstance(){
            $result = json_decode(json_encode($this->calendarObj));
            $this->assertObjectNotHasAttribute('id', $result);
            $this->assertObjectNotHasAttribute('createdUTC', $result);
            $this->assertObjectNotHasAttribute('modifiedUTC', $result);
            $this->assertObjectHasAttribute('title', $result);
            $this->assertObjectHasAttribute('ownerID', $result);
            $this->assertObjectHasAttribute('defaultTimezone', $result);
        }

    }

}
<?php namespace Schedulizer\Tests\Entities {

    use \PDO;
    use Concrete\Package\Schedulizer\Src\Calendar;
    use Concrete\Package\Schedulizer\Src\Event;
    use Concrete\Package\Schedulizer\Src\EventRepeat;

    /**
     * DOCTRINE BLOWS SO HARD WITH ITS ENTITY TRACKING SO WE HAVE TO TEST
     * THE SHIT OUT OF IT.
     * @package Schedulizer\Tests\Entities
     * @group associable
     */
    class AssociableEntitiesTest extends \Schedulizer\Tests\DatabaseTestCase {

        protected $tableCalendar = "SchedulizerCalendar";
        protected $tableEvents   = "SchedulizerEvent";

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
         * @return array
         */
        protected function getCalendarTableData(){
            return $this->getRawConnection()
                        ->query("SELECT * FROM {$this->tableCalendar}")
                        ->fetchAll(PDO::FETCH_OBJ);
        }

        /**
         * @return array
         */
        protected function getEventTableData(){
            return $this->getRawConnection()
                        ->query("SELECT * FROM {$this->tableEvents}")
                        ->fetchAll(PDO::FETCH_OBJ);
        }

        /** ------------ JUST CALENDAR ALONE ------------ */

        /**
         * @return Calendar
         */
        public function testCreatingBasicCalendar(){
            $calendar = Calendar::create(array(
                'title'     => 'Test',
                'ownerID'   => 2
            ));
            // Check entity is managed
            $this->assertEquals(true, $this->packageEntityManager()->contains($calendar));
            // Check record exists in DB
            $this->assertEquals(1, count($this->getCalendarTableData()));
            return $calendar;
        }

        /**
         * @param Calendar $calendar
         * @depends testCreatingBasicCalendar
         * @return Calendar
         */
        public function testEntityManagerStillContains( Calendar $calendar ){
            $this->assertEquals(true, $this->packageEntityManager()->contains($calendar));
            return $calendar;
        }

        /**
         * @param Calendar $calendar
         * @depends testEntityManagerStillContains
         * @returns Calendar
         */
        public function testEntityManangerNoLongerContainsAfterClear( Calendar $calendar ){
            $this->packageEntityManager()->clear();
            $this->assertEquals(false, $this->packageEntityManager()->contains($calendar));
            return $calendar;
        }

        /**
         * @param Calendar $calendar
         * @depends testEntityManangerNoLongerContainsAfterClear
         */
        public function testUpdateCalendarAfterPreviouslyClearedEntityManager( Calendar $calendar ){
            $calendar->update(array(
                'title' => 'changed'
            ));
            // Check the database
            $res = $this->getCalendarTableData();

            $this->assertEquals($res[0]->title, $calendar->getTitle());
            $this->assertEquals($res[0]->modifiedUTC, $calendar->getModifiedUTC()->format('Y-m-d H:i:s'));
        }

        /** ------------ THROWING EVENTS IN THE MIX ------------ */

        /**
         * 1) Create a calendar (persist it)
         * 2) Add an event that has not been persisted yet to the calendar
         * 3) Save the calendar
         */
        public function testCreateCalendarFirstThenAddEvents(){
            // Ensure entity manager is empty
            $this->packageEntityManager()->clear();
            $this->assertEquals(0, $this->packageEntityManager()->getUnitOfWork()->size());

            /** @var $calendar Calendar */
            $calendar = Calendar::create(array(
                'title'     => 'Calendar 2',
                'ownerID'   => 298
            ));

            $calendar->addEvent(new Event(self::$newEventSettingSample));

            // At this point, check database to see persistence state is expected (calendar
            // exists but event doesn't, yet)
            $this->assertEquals(1, count($this->getCalendarTableData()));
            $this->assertEquals(0, count($this->getEventTableData()));

            // Now persist the CALENDAR, and ensure persistence is propagated to event
            $calendar->save();
            $this->assertEquals(1, count($this->getCalendarTableData()));
            $this->assertEquals(1, count($this->getEventTableData()));
        }

        /**
         * Same steps as above but delete the calendar and ensure state.
         */
        public function testDeleteCalendarRemovesAssociatedEvents(){
            // Ensure entity manager is empty
            $this->packageEntityManager()->clear();
            $this->assertEquals(0, $this->packageEntityManager()->getUnitOfWork()->size());

            // Recreate a calendar with an event
            $calendar = Calendar::create(array(
                'title'     => 'Calendar 2',
                'ownerID'   => 298
            ));
            $calendar->addEvent(new Event(self::$newEventSettingSample));
            $calendar->save();
            $this->assertEquals(1, count($this->getCalendarTableData()));
            $this->assertEquals(1, count($this->getEventTableData()));

            $calendar->delete();
            $this->assertEquals(0, count($this->getCalendarTableData()));
            $this->assertEquals(0, count($this->getEventTableData()));

            // Check the entity manager
            $this->assertEquals(false, $this->packageEntityManager()->contains($calendar));
        }

        /**
         * Create a new event and use the setCalendarInstance directly before
         * persisting it, then ensure the Calendar entity ArrayCollection is in
         * the right state.
         */
        public function testCreateEventStandalone(){
            /** @var $calendar Calendar */
            $calendar = Calendar::create(array(
                'title'     => 'asdf',
                'ownerID'   => 13
            ));
            $this->assertEquals(0, $calendar->getEvents()->count());

            $event = new Event(self::$newEventSettingSample);
            $this->assertEquals(0, count($this->getEventTableData()));

            // Now add calendar to the event and save it
            $event->setCalendarInstance($calendar);
            $event->save();
            $this->assertEquals(1, count($this->getEventTableData()));
            $this->assertEquals(1, $this->getEventTableData()[0]->calendarID);

            // NOW THEN - we have to check that the Calendar's ArrayCollection of
            // events was updated and does indeed contain the event we just saved
            $this->assertEquals(1, $calendar->getEvents()->count());
            $this->assertEquals(true, $calendar->getEvents()->contains($event));
        }

        public function testDeletingEventRemovesItselfFromAssociatedCalendarArrayCollection(){
            /** @var $calendar Calendar */
            $calendar = Calendar::create(array(
                'title'     => 'Calendar 2',
                'ownerID'   => 298
            ));
            $calendar->addEvent(new Event(array_merge(self::$newEventSettingSample, array(
                'title' => 'Different Title'
            ))));
            $calendar->save();
            $this->assertEquals(1, count($this->getCalendarTableData()));
            $this->assertEquals(1, count($this->getEventTableData()));
            $this->assertEquals(1, $calendar->getEvents()->count());

            foreach($calendar->getEvents() AS $eventObj){
                $eventObj->delete();
            }
            echo $calendar->getEvents()->count();

            $calendar->removeEvent(Event::getByID(1));
            echo $calendar->getEvents()->count();

            //echo Calendar::getByID(1)->getEvents()->count();

            //$calendar->getEvents()->
//            Event::getByID(1)->delete();
//            echo 'now=' . $calendar->getEvents()->count();
//            $eventObj = Event::getByID(1);
//            $calendar->removeEvent($eventObj);
//            $calendar->save();

//            Event::getByID(1)->delete();
//            $this->assertEquals(0, count($this->getEventTableData()));
//
//            echo $calendar->getEvents()->count();
//            $cal = Calendar::getByID(1);
//            echo $cal->getEvents()->count();
//            exit;
//            $this->packageEntityManager()->refresh($calendar);
//            echo $calendar->getEvents()->count();
            //print_r($calendar->getEvents()->count());exit;
            //$this->assertEquals(0, $calendar->getEvents()->count());
        }

    }

}
<?php namespace Schedulizer\Tests\Entities {

    use Events;
    use \Concrete\Package\Schedulizer\Src\Calendar;
    use \Concrete\Package\Schedulizer\Src\Event;
    use \Concrete\Package\Schedulizer\Src\EventVersion;

    /**
     * Class EventVersionTest
     * @package Schedulizer\Tests\Entities
     * @group ev
     */
    class EventVersionTest extends \PHPUnit_Framework_TestCase {

        public function testEventDispatching(){
//            Events::addListener('schedulizer.event_save', function( $dispatchedEvent ){
//                print_r($dispatchedEvent->getData());
//            });

            Event::create(array(
                'title' => 'lorem ipsum',
                'calendarID' => 1
            ));
        }

//        public function testCreate(){
//            $eventObj = Event::create(array(
//                'title'      => 'An Event',
//                'calendarID' => 1,
//                'eventColor' => '#dadada'
//            ));
//
//            $i = 1;
//            while( $i < 5 ){
//                $i++;
//                $eventObj->update(array(
//                    'title' => 'changed',
//                    'eventColor' => '#1d1d1d',
//                    'timezoneName' => 'America/New_York'
//                ));
//            }
//
//            //print_r($eventObj);
//        }
//
//        public function testGet(){
//            $evObj = Event::getByID(2, 3);
//            print_r($evObj);
//            exit;
//        }

//        public function testOne(){
//            $calObj = Calendar::create(array(
//                'title' => 'A calendar'
//            ));
//
//            $evObj = Event::create(array(
//                'title' => 'lorem ipsum dolor',
//                'calendarID' => $calObj->getID()
//            ));
//
//            $evObj->update(array(
//                'title' => 'changed it! ' . rand(0,3)
//            ));
//
//            return $calObj;
//        }

//        /**
//         * @depends testOne
//         */
//        public function testTwo( $calObj ){
//            Event::create(array(
//                'title' => 'second event',
//                'calendarID' => $calObj->getID()
//            ));
//        }

//        public function testThree(){
//            $evObj = Event::getByID(1);
//            $evObj->update(array(
//                'title' => 'another new version eh'
//            ));
//            print_r($evObj);
//        }

    }

}
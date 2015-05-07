<?php namespace Schedulizer\Tests\Api {

    use \Concrete\Package\Schedulizer\Src\Calendar;

    /**
     * Class CalendarTest
     * @package Schedulizer\Tests\Api
     * @group calendar
     */
    class CalendarTest extends \PHPUnit_Framework_TestCase {

        const ROUTE_PRETTY = 'http://127.0.0.1/_schedulizer/calendar';
        const ROUTE_FUGLY  = 'http://127.0.0.1/index.php/_schedulizer/calendar';

        /** @var $httpClient \GuzzleHttp\Client */
        protected $httpClient;

        public function setUp(){
            $this->httpClient = new \GuzzleHttp\Client();
        }

//        public function testCreateNewCalendarViaPost(){
//            $client = $this->httpClient->post(self::ROUTE_PRETTY, array(
//                'json' => (object)array(
//                    'title'             => 'NewCalendarTest',
//                    'defaultTimezone'   => 'America/Denver'
//                )
//            ));
//            $response = $client->json(['object' => true]);
//            $this->assertEquals('NewCalendarTest', $response->title);
//        }
//
        public function testUpdateCalendarViaPut(){
            /** @var $calendarObj Calendar */
            $calendarObj = Calendar::getByID(1);
            $calendarObj->mergePropertiesFrom(array(
                'title' => 'newTitle!'
            ));

            $url = self::ROUTE_PRETTY . '/' . $calendarObj->getID();
            $client = $this->httpClient->put($url, [
                'json' => $calendarObj
            ]);
            echo $client;exit;
            $response = $client->json(['object' => true]);
            $this->assertEquals('newTitle!', $response->title);
        }

//        public function testCalendarDeleteViaDelete(){
//            $url = self::ROUTE_PRETTY . '/2';
//            $client = $this->httpClient->delete($url);
//            $response = $client->json(['object' => true]);
//            $this->assertEquals(true, $response->ok);
//        }

    }

}
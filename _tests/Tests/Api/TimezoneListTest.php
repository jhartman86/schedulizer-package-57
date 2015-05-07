<?php namespace Schedulizer\Tests\Api {

    use DateTimeZone;
    use \Symfony\Component\HttpFoundation\Response;

    class TimezoneListTest extends \PHPUnit_Framework_TestCase {

        const ROUTE_PRETTY = 'http://127.0.0.1/_schedulizer/timezones';
        const ROUTE_FUGLY  = 'http://127.0.0.1/index.php/_schedulizer/timezones';

        protected $httpClient;

        public function setUp(){
            $this->httpClient = new \GuzzleHttp\Client();
        }

        /**
         * Ensure URL resolves with pretty format
         */
        public function testResolvedWithRewriteUrls(){
            $client   = $this->httpClient->get(self::ROUTE_PRETTY);
            $response = $client->json();
            $this->assertAlways($client, $response, __FUNCTION__);
            $this->assertContains('America/Denver', $response);
        }

        /**
         * Ensure URL resolves with index.php
         */
        public function testResolvedWithFuglyUrls(){
            $client   = $this->httpClient->get(self::ROUTE_FUGLY);
            $response = $client->json();
            $this->assertAlways($client, $response, __FUNCTION__);
            $this->assertContains('America/Denver', $response);
        }

        /**
         * Ensure URL resolves with pretty format and trailing slash
         */
        public function testResolvedWithRewriteUrlsAndTrailingSlash(){
            $client   = $this->httpClient->get(self::ROUTE_PRETTY . '/');
            $response = $client->json();
            $this->assertAlways($client, $response, __FUNCTION__);
            $this->assertContains('America/Denver', $response);
        }

        /**
         * Ensure URL resolves with index.php AND a trailing slash
         */
        public function testResolvedWithFuglyUrlsAndTrailingSlash(){
            $client   = $this->httpClient->get(self::ROUTE_FUGLY . '/');
            $response = $client->json();
            $this->assertAlways($client, $response, __FUNCTION__);
            $this->assertContains('America/Denver', $response);
        }

        /**
         * List should be filtered to specific region
         */
        public function testResolvedWithRegionFilter(){
            $client   = $this->httpClient->get(self::ROUTE_PRETTY, [
                'query' => ['region' => 'pacific']
            ]);
            $response = $client->json();
            $this->assertAlways($client, $response, __FUNCTION__);
            foreach($response AS $zone){
                $this->assertRegExp('/^Pacific\/.+/', $zone, 'Timezone API region filter failed.');
            }
        }

        /**
         * List should be filtered to specific country
         */
        public function testResolvedWithCountryFilter(){
            $client   = $this->httpClient->get(self::ROUTE_PRETTY, [
                'query' => ['country' => 'US']
            ]);
            $response = $client->json();
            $this->assertAlways($client, $response, __FUNCTION__);
            foreach($response AS $zone){ // Whaddup Hawaii?!
                $this->assertRegExp('/^America\/|^Pacific\/.+/', $zone);
            }
        }

        /**
         * Try passing an invalid region; API should return 406 Not Acceptable
         * @expectedException \GuzzleHttp\Exception\RequestException
         */
        public function testFailGracefullyWithInvalidRegion(){
            try {
                $this->httpClient->get(self::ROUTE_PRETTY, [
                    'query' => ['region' => 'myinvalidregion']
                ]);
            }catch(\Exception $e){
                $this->assertEquals(Response::HTTP_NOT_ACCEPTABLE, $e->getResponse()->getStatusCode());
                throw $e;
            }
        }

        /**
         * Try passing an invalid country; API should return 406 Not Acceptable
         * @expectedException \GuzzleHttp\Exception\RequestException
         */
        public function testFailGracefullyWithInvalidCountry(){
            try {
                $this->httpClient->get(self::ROUTE_PRETTY, [
                    'query' => ['country' => 'myinvalidcountry']
                ]);
            }catch(\Exception $e){
                $this->assertEquals(Response::HTTP_NOT_ACCEPTABLE, $e->getResponse()->getStatusCode());
                throw $e;
            }
        }

        /**
         * Frequently used assertions
         * @param \GuzzleHttp\Message\Response $client
         * @param $response array
         * @param $from __FUNCTION__
         */
        protected function assertAlways( \GuzzleHttp\Message\Response $client, $response, $from ){
            $this->assertEquals(Response::HTTP_OK, $client->getStatusCode(), $from);
            $this->assertNotEmpty($response, $from);
        }

    }

}
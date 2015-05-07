<?php namespace Schedulizer\Tests\Bin {

    use DateTime;
    use DateTimeZone;
    use \Concrete\Package\Schedulizer\Src\Bin\TimeConversion;

    class TimeConversionTest extends \PHPUnit_Framework_TestCase {

        /**
         * Tests that passing in a DateTime object in timezone UTC will
         * be converted to the correct time in the given timezone.
         * @param $dateTimeUTC DateTime
         * @param $timezone DateTimeZone
         * @dataProvider providerDateTimeScenarios
         */
        public function testLocalizeCorrectly( DateTime $dateTimeUTC, DateTimeZone $timezone, $expected ){
            $this->assertEquals($expected, TimeConversion::localize($dateTimeUTC, $timezone)->format('c'));
        }

        /**
         * Same thing as above but tests formatting output.
         */
        public function testLocalizeWithFormatOK(){
            $datetime  = new DateTime('2020-01-02 09:00:00', new DateTimeZone('UTC'));
            $convertTo = new DateTimeZone('America/New_York');
            $this->assertEquals('2020-01-02 04:00:00', TimeConversion::localizeWithFormat($datetime,$convertTo,'Y-m-d H:i:s'));
        }

        /**
         * Give an array of test scenarios
         */
        public function providerDateTimeScenarios(){
            $utcTimezone = new DateTimeZone('UTC');
            return array(
                array(new DateTime('2013-02-17 02:00:00', $utcTimezone), new DateTimeZone('America/New_York'), '2013-02-16T21:00:00-05:00'),
                array(new DateTime('2016-07-22 13:00:30', $utcTimezone), new DateTimeZone('America/Los_Angeles'), '2016-07-22T06:00:30-07:00')
            );
        }

    }

}
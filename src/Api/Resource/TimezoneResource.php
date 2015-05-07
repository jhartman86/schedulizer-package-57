<?php namespace Concrete\Package\Schedulizer\Src\Api\Resource {

    use \DateTimeZone;
    use \Concrete\Package\Schedulizer\Src\Api\ApiException;

    class TimezoneResource extends \Concrete\Package\Schedulizer\Src\Api\ApiDispatcher {

        /**
         * http://{domain}/_schedulizer/timezones
         *  ?region=africa|america|antarctica|arctic|asia|atlantic|australia|europe|indian|pacific
         *  ?country=ISO_COUNTRY_CODE (2 letters)
         */
        protected function httpGet(){
            // If config_default is set; we're asking for the config value default timezone name
            if( isset($this->requestParams()->config_default) ){
                /** @var $packageObj \Concrete\Package\Schedulizer\Controller */
                $packageObj = \Package::getByHandle('schedulizer');
                $this->setResponseData((object)array(
                    'name' => $packageObj->configGet($packageObj::CONFIG_DEFAULT_TIMEZONE)
                ));
                return;
            }

            // If country code is set, honor ONLY that (ignore region) and return
            if( isset($this->requestParams()->country) ){
                $this->filterByCountry();
                return;
            }

            // If region is set, do filter
            if( isset($this->requestParams()->region) ){
                $this->filterByRegion();
                return;
            }

            $this->setResponseData(DateTimeZone::listIdentifiers());
        }

        /**
         * Filtering by country code?
         * @throws ApiException
         * @return void
         */
        protected function filterByCountry(){
            $countryCode  = strtoupper($this->requestParams()->country);
            $timezoneList = DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY, $countryCode);

            // If requestParams()->country is a VALID country, a list should be available
            if( !empty($timezoneList) ){
                $this->setResponseData($timezoneList);
                return;
            }

            // If the $timezoneList was empty, an invalid country was passed
            throw ApiException::generic('Invalid country code.');
        }


        /**
         * Determine what to send back and set on the injected $response.
         * @throws ApiException
         * @return void
         */
        protected function filterByRegion(){
            $region = null;

            switch( strtolower($this->requestParams()->region) ){
                case 'africa': $region = DateTimeZone::AFRICA; break;
                case 'america': $region = DateTimeZone::AMERICA; break;
                case 'antarctica': $region = DateTimeZone::ANTARCTICA; break;
                case 'arctic': $region = DateTimeZone::ARCTIC; break;
                case 'asia': $region = DateTimeZone::ASIA; break;
                case 'atlantic': $region = DateTimeZone::ATLANTIC; break;
                case 'australia': $region = DateTimeZone::AUSTRALIA; break;
                case 'europe': $region = DateTimeZone::EUROPE; break;
                case 'indian': $region = DateTimeZone::INDIAN; break;
                case 'pacific': $region = DateTimeZone::PACIFIC; break;
            }

            // Is $region NOT null anymore? We can get a list...
            if( ! is_null($region) ){
                $this->setResponseData(DateTimeZone::listIdentifiers($region));
                return;
            }

            // Region was still null (thus invalid)
            throw ApiException::generic('Invalid timezone region.');
        }

    }

}
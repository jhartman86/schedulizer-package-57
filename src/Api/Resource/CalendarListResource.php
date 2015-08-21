<?php namespace Concrete\Package\Schedulizer\Src\Api\Resource {

    use Permissions;
    use \Concrete\Package\Schedulizer\Src\Calendar;
    use \Concrete\Package\Schedulizer\Src\Event;
    use \Concrete\Package\Schedulizer\Src\Api\ApiException;
    use \Symfony\Component\HttpFoundation\Response;

    class CalendarListResource extends \Concrete\Package\Schedulizer\Src\Api\ApiDispatcher {

        /**
         * Get a calendar by its ID OR pass /events as parameter after id and
         * optionally a third as 'verbose' to get a verbose list of args
         * @todo: permissions!
         * @throws ApiException
         * @throws \Exception
         */
        protected function httpGet(){
                $this->setResponseData(Calendar::fetchAll());
                return;
        }
    }

}

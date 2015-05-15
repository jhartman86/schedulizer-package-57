<?php namespace Concrete\Package\Schedulizer\Src\Api\Resource {

    use \Concrete\Package\Schedulizer\Src\EventCategory;

    class EventCategoriesResource extends \Concrete\Package\Schedulizer\Src\Api\ApiDispatcher {

        protected function httpGet( $id ){
            $this->setResponseData(EventCategory::fetchAll());
        }

    }

}
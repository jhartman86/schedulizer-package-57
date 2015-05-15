<?php namespace Concrete\Package\Schedulizer\Src\Api\Resource {

    use \Concrete\Package\Schedulizer\Src\EventCategory;
    use \Concrete\Package\Schedulizer\Src\Api\ApiException;
    use \Symfony\Component\HttpFoundation\Response;

    class EventCategoriesResource extends \Concrete\Package\Schedulizer\Src\Api\ApiDispatcher {

        protected function httpGet( $id ){
            $this->setResponseData(EventCategory::fetchAll());
        }

        /**
         * Create a new category
         * @todo: permissions, pass user (api key determines?), and timezone options
         */
        protected function httpPost(){
            $data = $this->scrubbedPostData();
            $categoryObj = EventCategory::createOrGetExisting($data);
            $this->setResponseData($categoryObj);
            $this->setResponseCode(Response::HTTP_CREATED);
        }

        /**
         * @param $id
         */
        protected function httpPut( $id ){
            $categoryObj = EventCategory::getByID($id);
            $categoryObj->update($this->scrubbedPostData());
            $this->setResponseData($categoryObj);
            $this->setResponseCode(Response::HTTP_ACCEPTED);
        }

        /**
         * @param $id
         * @todo: permissions, error handling
         */
        protected function httpDelete( $id ){
            EventCategory::getByID($id)->delete();
        }

    }

}
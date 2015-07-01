<?php namespace Concrete\Package\Schedulizer\Src\Api\Resource {

    use \Concrete\Package\Schedulizer\Src\EventTag;
    use \Concrete\Package\Schedulizer\Src\Api\ApiException;
    use \Symfony\Component\HttpFoundation\Response;

    class EventTagsResource extends \Concrete\Package\Schedulizer\Src\Api\ApiDispatcher {

        protected function httpGet( $id ){
            $this->setResponseData(EventTag::fetchAll());
        }

        /**
         * Create a new tag
         * @todo: permissions
         * @return void
         */
        protected function httpPost(){
            $data = $this->scrubbedPostData();
            $tagObj = EventTag::createOrGetExisting($data);
            $this->setResponseData($tagObj);
            $this->setResponseCode(Response::HTTP_CREATED);
        }

        /**
         * @param $id
         */
        protected function httpPut( $id ){
            $tagObj = EventTag::getByID($id);
            $tagObj->update($this->scrubbedPostData());
            $this->setResponseData($tagObj);
            $this->setResponseCode(Response::HTTP_ACCEPTED);
        }

        /**
         * @param $id
         * @todo: permissions, error handling
         */
        protected function httpDelete( $id ){
            EventTag::getByID($id)->delete();
        }

    }

}

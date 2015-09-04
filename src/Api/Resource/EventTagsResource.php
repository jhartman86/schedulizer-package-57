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
         * @throws ApiException
         * @return void
         */
        protected function httpPost(){
            // Permission check
            if( ! $this->getGenericTaskPermissionObj()->canCreateTag() ){
                throw ApiException::permissionInvalid("You do not have permission to create tags.");
            }
            // Get passed data
            $data = $this->scrubbedPostData();
            // Is displayText property set?
            if( empty($data->displayText) ){
                throw ApiException::generic("displayText property must be set");
            }
            // All good
            $tagObj = EventTag::createOrGetExisting($data);
            $this->setResponseData($tagObj);
            $this->setResponseCode(Response::HTTP_CREATED);
        }

        /**
         * @param $id
         * @throws ApiException
         * @return void
         */
        protected function httpPut( $id ){
            // Permission check
            if( ! $this->getGenericTaskPermissionObj()->canCreateTag() ){
                throw ApiException::permissionInvalid("You do not have permission to create tags.");
            }
            // Get passed data
            $data = $this->scrubbedPostData();
            // Is displayText property set?
            if( empty($data->displayText) ){
                throw ApiException::generic("displayText property must be set");
            }

            $tagObj = EventTag::getByID($id);
            $tagObj->update($data);
            $this->setResponseData($tagObj);
            $this->setResponseCode(Response::HTTP_ACCEPTED);
        }

        /**
         * @param $id
         * @throws ApiException
         * @return void
         */
        protected function httpDelete( $id ){
            // Permission check
            if( ! $this->getGenericTaskPermissionObj()->canCreateTag() ){
                throw ApiException::permissionInvalid("You do not have permission to create tags.");
            }
            EventTag::getByID($id)->delete();
        }

    }

}

<?php namespace Concrete\Package\Schedulizer\Src\Api\Resource {

    use Permissions;
    use \Concrete\Package\Schedulizer\Src\Collection;
    use \Concrete\Package\Schedulizer\Src\Api\ApiException;
    use \Symfony\Component\HttpFoundation\Response;

    class CollectionResource extends \Concrete\Package\Schedulizer\Src\Api\ApiDispatcher {

        /**
         * @todo: permissioning, error handling if collection not found
         * @param $collectionID
         * @param null $subAction
         */
        protected function httpGet( $collectionID, $subAction = null ){
            $collectionObj = Collection::getByID($collectionID);
            $this->setResponseData($collectionObj);
        }


        /**
         * @todo: permissioning?; fail if no calendars set
         */
        protected function httpPost(){
            $data = $this->scrubbedPostData();
            $data->ownerID = 1;
//            if( empty($data->ownerID) ){
//                $data->ownerID = 1;
//            }
            $collectionObj = Collection::create($data);
            $this->setResponseData($collectionObj);
            $this->setResponseCode(Response::HTTP_CREATED);
        }


        /**
         * @todo: ownerID! (see how its done w/ calendars)
         * @param $id
         * @throws ApiException
         */
        protected function httpPut( $id ){
            $data = $this->scrubbedPostData();
            if( empty($data->collectionCalendars) ){
                throw ApiException::generic('Collections must have at least one calendar');
            }
            $data->ownerID = 1;
            /** @var $collectionObj Collection */
            $collectionObj = Collection::getByID($id);
            $collectionObj->update($data);
            $this->setResponseData($collectionObj);
        }


        protected function httpDelete( $collectionID ){
            $collectionObj = Collection::getByID((int)$collectionID);
            if( $collectionObj ){
                $collectionObj->delete();
                $this->setResponseCode(Response::HTTP_NO_CONTENT);
                return;
            }
            throw ApiException::generic('Collection no longer exists.');
        }

    }

}


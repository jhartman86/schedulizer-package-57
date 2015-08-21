<?php namespace Concrete\Package\Schedulizer\Src\Api\Resource {

    use Permissions;
    use \Concrete\Package\Schedulizer\Src\Collection;
    use \Concrete\Package\Schedulizer\Src\Api\ApiException;
    use \Symfony\Component\HttpFoundation\Response;

    class CollectionEventResource extends \Concrete\Package\Schedulizer\Src\Api\ApiDispatcher {

        const SUBACTION_GET_VERSION_LIST           = 'version_list',
              SUBACTION_GET_APPROVED_EVENT_VERSION = 'approved_version';

        protected function httpGet( $subAction = null ){
            switch($subAction){
                case self::SUBACTION_GET_VERSION_LIST:
                    $eventID = (int) $_REQUEST['eventID'];
                    $this->setResponseData(Collection::fetchEventVersionList( $eventID ));
                    break;

                case self::SUBACTION_GET_APPROVED_EVENT_VERSION:
                    $eventID      = (int) $_REQUEST['eventID'];
                    $collectionID = (int) $_REQUEST['collectionID'];
                    $this->setResponseData(Collection::fetchApprovedEventVersionID( $collectionID, $eventID ));
                    break;

                default:
                    $this->setResponseData(array(
                        'error' => true,
                        'msg' => 'No subaction specified.'
                    ));
            }
        }

        protected function httpPost(){
            $data = $this->scrubbedPostData();
            $collectionObj = Collection::getByID($data->collectionID);
            $collectionObj->approveEventVersion($data->eventID, $data->approvedVersionID);
            $this->setResponseCode(200);
            $this->setResponseData($data);
        }

    }

}
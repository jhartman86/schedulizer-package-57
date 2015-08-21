<?php namespace Concrete\Package\Schedulizer\Src\Api\Resource {

    use Permissions;
    use \Concrete\Package\Schedulizer\Src\Collection;
    use \Concrete\Package\Schedulizer\Src\Api\ApiException;
    use \Symfony\Component\HttpFoundation\Response;

    class CollectionResource extends \Concrete\Package\Schedulizer\Src\Api\ApiDispatcher {

        const SUBACTION_GET_ALL_EVENTS_LIST = 'all_events_list';

        protected function httpGet( $collectionID, $subAction = null ){
            $collectionObj = Collection::getByID($collectionID);

            switch($subAction){
                case self::SUBACTION_GET_ALL_EVENTS_LIST:
                    $castedTypes = array();
                    $eventsList  = $collectionObj->fetchAllAvailableEvents();
                    foreach($eventsList AS $row){
                        array_push($castedTypes, (object) array(
                            'approvedVersionID' => $row->approvedVersionID ? (int) $row->approvedVersionID : null,
                            'calendarTitle'     => $row->calendarTitle,
                            'eventID'           => (int) $row->eventID,
                            'eventTitle'        => $row->eventTitle,
                            'isActive'          => (bool) (int) $row->isActive,
                            'versionID'         => (int) $row->versionID
                        ));
                    }
                    $this->setResponseData($castedTypes);
                    break;

                default:
                    $this->setResponseData(array(
                        'error' => true
                    ));
            }
        }

        /**
         * @todo: permissioning?; fail if no calendars set
         */
        protected function httpPost(){
            $data = $this->scrubbedPostData();
            if( empty($data->ownerID) ){
                $data->ownerID = 1;
            }
            $collectionObj = Collection::create($data);
            $this->setResponseData($collectionObj);
            $this->setResponseCode(Response::HTTP_CREATED);
        }

        protected function httpPut(){
            $this->setResponseData(array('ok' => 'mkkkk'));
        }

        protected function httpDelete(){
            $this->setResponseData(array('ok' => 'mkkkk'));
        }

    }

}


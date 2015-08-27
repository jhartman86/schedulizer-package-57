<?php namespace Concrete\Package\Schedulizer\Src\Api\Resource {

    use Permissions;
    use \Concrete\Package\Schedulizer\Src\Collection;
    use \Concrete\Package\Schedulizer\Src\Api\ApiException;
    use \Symfony\Component\HttpFoundation\Response;

    class CollectionResource extends \Concrete\Package\Schedulizer\Src\Api\ApiDispatcher {

        const SUBACTION_GET_ALL_EVENTS_LIST = 'all_events_list';

        protected function httpGet( $collectionID, $subAction = null ){
            $collectionObj = Collection::getByID($collectionID);

            switch($subAction):
                // List all events
                case self::SUBACTION_GET_ALL_EVENTS_LIST:
                    $castedTypes = array();
                    $calendarID  = ((int)$_REQUEST['calendarID'] >= 1) ? (int)$_REQUEST['calendarID'] : null;
                    $eventsList  = $collectionObj->fetchAllAvailableEvents($calendarID);
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

                // Get one event by ID
                default:
                    $this->setResponseData(Collection::getByID($collectionID));
            endswitch;
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

        protected function httpPut( $id ){
            $data = $this->scrubbedPostData();
            if( empty($data->collectionCalendars) ){
                throw ApiException::generic('Collections must have at least one calendar');
            }
            /** @var $collectionObj Collection */
            $collectionObj = Collection::getByID($id);
            $collectionObj->update($data);
            $this->setResponseData($collectionObj);
        }

        protected function httpDelete(){
            $this->setResponseData(array('ok' => 'mkkkk'));
        }

    }

}


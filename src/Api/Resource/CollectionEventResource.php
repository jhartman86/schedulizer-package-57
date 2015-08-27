<?php namespace Concrete\Package\Schedulizer\Src\Api\Resource {

    use Permissions;
    use \Concrete\Package\Schedulizer\Src\Collection;
    use \Concrete\Package\Schedulizer\Src\Api\ApiException;
    use \Symfony\Component\HttpFoundation\Response;

    class CollectionEventResource extends \Concrete\Package\Schedulizer\Src\Api\ApiDispatcher {

        const SUBACTION_GET_ALL_EVENTS_LIST = 'all_events_list',
              SUBACTION_GET_VERSION_LIST = 'version_list',
              SUBACTION_GET_APPROVED_EVENT_VERSION = 'approved_version',
              SUBACTION_POST_APPROVE_LATEST_VERSIONS = 'approve_latest_versions',
              SUBACTION_PUT_APPROVE_MULTI_AUTOAPPROVABLE = 'multi_auto_approve';

        protected function httpGet($subAction = null) {
            switch ($subAction):
                case self::SUBACTION_GET_ALL_EVENTS_LIST:
                    $castedTypes = array();
                    /** @var $collectionObj Collection */
                    $collectionObj = Collection::getByID((int)$_REQUEST['collectionID']);
                    $calendarID = ((int)$_REQUEST['calendarID'] >= 1) ? (int)$_REQUEST['calendarID'] : null;
                    $eventsList = $collectionObj->fetchAllAvailableEvents($calendarID);
                    foreach ($eventsList AS $row) {
                        array_push($castedTypes, (object)array(
                            'approvedVersionID' => $row->approvedVersionID ? (int)$row->approvedVersionID : null,
                            'calendarTitle' => $row->calendarTitle,
                            'eventID' => (int)$row->eventID,
                            // If event hasn't been approved (ie, no record exists) this will be null!
                            'collectionID' => $row->collectionID ? (int)$row->collectionID : null,
                            'eventTitle' => $row->eventTitle,
                            'isActive' => (bool)(int)$row->isActive,
                            'versionID' => (int)$row->versionID,
                            'autoApprovable' => (bool)(int)$row->autoApprovable
                        ));
                    }
                    $this->setResponseData($castedTypes);
                    break;

                // Get all versions of the given event
                case self::SUBACTION_GET_VERSION_LIST:
                    $eventID = (int)$_REQUEST['eventID'];
                    $this->setResponseData(Collection::fetchEventVersionList($eventID));
                    break;

                // What is the APPROVED event version for the given collection?
                case self::SUBACTION_GET_APPROVED_EVENT_VERSION:
                    $eventID = (int)$_REQUEST['eventID'];
                    $collectionID = (int)$_REQUEST['collectionID'];
                    $record = Collection::fetchApprovedEventVersionRecord($collectionID, $eventID);
                    // If no event version has been approved (ie. this is unapproved)
                    if (!$record) {
                        goto defaultNoRecord;
                    }
                    // Otherwise, send back approved version data
                    $this->setResponseData((object)array(
                        'approvedVersionID' => (int)$record->approvedVersionID,
                        'collectionID' => (int)$record->collectionID,
                        'eventID' => (int)$record->eventID
                    ));
                    break;

                // By default, we send No-Content: means request didn't fail, just... there's
                // nothing to send back
                default:
                    defaultNoRecord:
                    $this->setResponseCode(Response::HTTP_NO_CONTENT);
            endswitch;
        }

        /**
         * Called when approving an event, or event(S), version.
         * @param null $subAction
         */
        protected function httpPost($subAction = null) {
            switch ($subAction) {
                // Approve the latest versions for the given eventIDs in the collection
                case self::SUBACTION_POST_APPROVE_LATEST_VERSIONS:
                    $collectionObj = Collection::getByID($this->scrubbedPostData()->collectionID);
                    $collectionObj->approveEventsAtLatestVersion($this->scrubbedPostData()->events);
                    $this->setResponseCode(Response::HTTP_NO_CONTENT);
                    break;

                // Approve a single, specific event version
                default:
                    $data = $this->scrubbedPostData();
                    /** @var $collectionObj Collection */
                    $collectionObj = Collection::getByID($data->collectionID);
                    $collectionObj->approveEventVersion($data->eventID, $data->approvedVersionID);
                    $this->setResponseCode(Response::HTTP_NO_CONTENT);
            }
        }

        /**
         * This should only be used by the "Approval" drop-down menu component, where we
         * are setting the autoApprovable directly on the event record itself.
         */
        protected function httpPut( $subAction = null ) {
            /** @var $collectionObj Collection */
            $data = $this->scrubbedPostData();
            $collectionObj = Collection::getByID($data->collectionID);

            switch($subAction){
                case self::SUBACTION_PUT_APPROVE_MULTI_AUTOAPPROVABLE:
                    foreach((array)$data->events AS $eventID){
                        $collectionObj->markEventAutoApprovable((int)$eventID, true);
                    }
                    $this->setResponseCode(Response::HTTP_NO_CONTENT);
                    break;

                default:
                    $collectionObj->markEventAutoApprovable($data->eventID, $data->autoApprovable);
                    $this->setResponseCode(Response::HTTP_NO_CONTENT);
            }
        }

        /**
         * Unfortunately we can't send a body via delete method (so no JSON post).
         * Instead, we comma-separate the list of IDs and then parse them back to
         * an array
         */
        protected function httpDelete() {
            $collectionObj = Collection::getByID((int)$_REQUEST['collectionID']);
            $collectionObj->unapproveCollectionEvents(explode(',', $_REQUEST['events']));
            $this->setResponseCode(Response::HTTP_ACCEPTED);
        }

    }

}
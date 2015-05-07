<?php namespace Concrete\Package\Schedulizer\Controller\Permission\Category {

    /** @see concrete/tools/permissions/categories/user file */
    use Loader;
    use PermissionAccess;
    use \Concrete\Core\Permission\Access\Entity\Entity as PermissionAccessEntity;
    use \Concrete\Core\Permission\Duration as PermissionDuration;
    use \Concrete\Package\Schedulizer\Src\Calendar;
    use \Concrete\Package\Schedulizer\Src\Permission\Key\SchedulizerCalendarKey;
    use \Concrete\Core\Workflow\Workflow as Workflow;

    class SchedulizerCalendar extends \Concrete\Core\Controller\Controller {

        protected $tokenIsValid = false;

        public function __construct(){
            parent::__construct();
            $this->tokenIsValid = Loader::helper('validation/token')->validate($_REQUEST['task']);
        }

        public function view(){
            $task = isset($_REQUEST['task']) ? $_REQUEST['task'] : null;
            if( ! $task ){ return; }

            $pkID       = isset($_REQUEST['pkID']) ? (int)$_REQUEST['pkID'] : null;
            $paID       = isset($_REQUEST['paID']) ? (int)$_REQUEST['paID'] : null;
            $peID       = isset($_REQUEST['peID']) ? (int)$_REQUEST['peID'] : null;
            $pdID       = isset($_REQUEST['pdID']) ? (int)$_REQUEST['pdID'] : null;
            $calendarID = isset($_REQUEST['calendarID']) ? (int)$_REQUEST['calendarID'] : null;
            $accessType = isset($_REQUEST['accessType']) ? $_REQUEST['accessType'] : null;

            $calendarObj = Calendar::getByID($calendarID);

            switch($task){
                case 'display_list':
                    Loader::packageElement('permission/lists/schedulizer_calendar', 'schedulizer', array(
                        'calendarObj'       => $calendarObj,
                        'permissionKeyList' => SchedulizerCalendarKey::getList()
                    ));
                    break;

                case 'add_access_entity':
                    $pkObj = SchedulizerCalendarKey::getByID($pkID);
                    $paObj = PermissionAccess::getByID($paID, $pkObj);
                    $peObj = PermissionAccessEntity::getByID($peID);
                    $pdObj = PermissionDuration::getByID($pdID);
                    $paObj->addListItem($peObj, $pdObj, $accessType);
                    break;

                case 'remove_access_entity':
                    $pkObj = SchedulizerCalendarKey::getByID($pkID);
                    $paObj = PermissionAccess::getByID($paID, $pkObj);
                    $peObj = PermissionAccessEntity::getByID($peID);
                    $paObj->removeListItem($peObj);
                    break;

                case 'display_access_cell':
                    $pkObj = SchedulizerCalendarKey::getByID($pkID);
                    $paObj = PermissionAccess::getByID($paID, $pkObj);
                    Loader::element('permission/labels', array('pk' => $pkObj, 'pa' => $paObj));
                    break;

                case 'save_permission':
                    $pkObj = SchedulizerCalendarKey::getByID($pkID);
                    $paObj = PermissionAccess::getByID($paID, $pkObj);
                    $paObj->save($_POST);
                    break;

                case 'save_permission_assignments':
                    foreach(SchedulizerCalendarKey::getList() AS $permissionKey){
                        $paID = $_POST['pkID'][$permissionKey->getPermissionKeyID()];
                        $permissionKey->setPermissionObject($calendarObj);
                        $pt = $permissionKey->getPermissionAssignmentObject();
                        $pt->clearPermissionAssignment();
                        if( $paID > 0 ){
                            $paObj = PermissionAccess::getByID($paID, $permissionKey);
                            if( is_object($paObj) ){
                                $pt->assignPermissionAccess($paObj);
                            }
                        }
                    }
                    break;

                case 'save_workflows':
                    $pkObj = SchedulizerCalendarKey::getByID($pkID);
                    $pkObj->clearWorkflows();
                    foreach($_POST['wfID'] AS $workflowID){
                        $workflowObj = Workflow::getByID($workflowID);
                        if( is_object($workflowObj) ){
                            $pkObj->attachWorkflow($workflowObj);
                        }
                    }
                    break;
            }
        }

    }

}
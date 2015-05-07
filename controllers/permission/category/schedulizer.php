<?php namespace Concrete\Package\Schedulizer\Controller\Permission\Category {

    /** @see concrete/tools/permissions/categories/user file */
    use Loader;
    use PermissionAccess;
    use \Concrete\Core\Permission\Access\Entity\Entity as PermissionAccessEntity;
    use \Concrete\Core\Permission\Duration as PermissionDuration;
    use \Concrete\Package\Schedulizer\Src\Permission\Key\SchedulizerKey;
    use \Concrete\Core\Workflow\Workflow as Workflow;

    class Schedulizer extends \Concrete\Core\Controller\Controller {

        protected $tokenIsValid = false;

        public function __construct(){
            parent::__construct();
            $this->tokenIsValid = Loader::helper('validation/token')->validate($_REQUEST['task']);
        }

        public function view(){
            $task = isset($_REQUEST['task']) ? $_REQUEST['task'] : null;
            if( ! $task || ! $this->tokenIsValid ){ return; }

            $pkID = isset($_REQUEST['pkID']) ? (int)$_REQUEST['pkID'] : null;
            $paID = isset($_REQUEST['paID']) ? (int)$_REQUEST['paID'] : null;
            $peID = isset($_REQUEST['peID']) ? (int)$_REQUEST['peID'] : null;
            $pdID = isset($_REQUEST['pdID']) ? (int)$_REQUEST['pdID'] : null;
            //$dtID = isset($_REQUEST['dtID']) ? (int)$_REQUEST['dtID'] : null;
            $accessType = isset($_REQUEST['accessType']) ? $_REQUEST['accessType'] : null;

            switch($task){
//                case 'display_list':
//                    break;
                case 'add_access_entity':
                    $pkObj = SchedulizerKey::getByID($pkID);
                    $paObj = PermissionAccess::getByID($paID, $pkObj);
                    $peObj = PermissionAccessEntity::getByID($peID);
                    $pdObj = PermissionDuration::getByID($pdID);
                    $paObj->addListItem($peObj, $pdObj, $accessType);
                    break;

                case 'remove_access_entity':
                    $pkObj = SchedulizerKey::getByID($pkID);
                    $paObj = PermissionAccess::getByID($paID, $pkObj);
                    $peObj = PermissionAccessEntity::getByID($peID);
                    $paObj->removeListItem($peObj);
                    break;

                case 'display_access_cell':
                    $pkObj = SchedulizerKey::getByID($pkID);
                    $paObj = PermissionAccess::getByID($paID, $pkObj);
                    Loader::element('permission/labels', array('pk' => $pkObj, 'pa' => $paObj));
                    break;

                case 'save_permission':
                    $pkObj = SchedulizerKey::getByID($pkID);
                    $paObj = PermissionAccess::getByID($paID, $pkObj);
                    $paObj->save($_POST);
                    break;

                case 'save_workflows':
                    $pkObj = SchedulizerKey::getByID($pkID);
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
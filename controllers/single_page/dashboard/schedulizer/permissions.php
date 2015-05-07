<?php namespace Concrete\Package\Schedulizer\Controller\SinglePage\Dashboard\Schedulizer {

    use Loader;
    use PermissionAccess;
    use \Concrete\Package\Schedulizer\Src\Permission\Key\SchedulizerKey;
    use \Concrete\Package\Schedulizer\Controller\DashboardController;

    class Permissions extends DashboardController {

        protected $tokenHelper;

        public function on_start(){
            parent::on_start();
            $this->tokenHelper = Loader::helper('validation/token');
        }

        public function view(){
            $this->set('permissionKeyList', SchedulizerKey::getList());
        }

        public function save(){
            if( $this->tokenHelper->validate('save_permissions') ){
                // @todo: $permission->"CanAccessThisPage"
                $permissions = SchedulizerKey::getList();
                foreach($permissions AS $permissionKey){
                    $paID = $_POST['pkID'][$permissionKey->getPermissionKeyID()];
                    $pt   = $permissionKey->getPermissionAssignmentObject();
                    $pt->clearPermissionAssignment();
                    if( $paID > 0 ){
                        $paObj = PermissionAccess::getByID($paID, $permissionKey);
                        if( is_object($paObj) ){
                            print_r($paObj);
                            $pt->assignPermissionAccess($paObj);
                        }
                    }
                }
                $this->redirect('/dashboard/schedulizer/permissions', 'updated');
            }else{
                $this->error->add($this->tokenHelper->getErrorMessage());
                $this->view();
            }
        }

        public function updated(){
            $this->set('success', t('Permissions saved.'));
            $this->view();
        }

    }

}
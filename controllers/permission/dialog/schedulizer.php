<?php namespace Concrete\Package\Schedulizer\Controller\Permission\Dialog {

    use Loader, Permissions, PermissionKey;

    class Schedulizer extends \Concrete\Core\Controller\Controller {

        protected $viewPath = 'permission/dialog/schedulizer';

        public function view(){
            $this->set('permissions', new Permissions());
        }

    }

}
<?php namespace Concrete\Package\Schedulizer\Controller\Permission\Dialog {

    use Loader, Permissions, PermissionKey, Page;

    class Schedulizer extends \Concrete\Core\Controller\Controller {

        protected $viewPath = 'permission/dialog/schedulizer';

        public function view(){
            $p = Page::getByPath('/dashboard/schedulizer/permissions');
            $this->set('permissions', new Permissions($p));
        }

    }

}
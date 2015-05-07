<?php namespace Concrete\Package\Schedulizer\Controller\Permission\Dialog {

    use Loader, Permissions;

    class SchedulizerCalendar extends \Concrete\Core\Controller\Controller {

        protected $viewPath = 'permission/dialog/schedulizer_calendar';

        public function view(){
            $this->set('permissions', new Permissions());
        }

    }

}
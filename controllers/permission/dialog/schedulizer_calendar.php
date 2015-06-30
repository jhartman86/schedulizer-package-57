<?php namespace Concrete\Package\Schedulizer\Controller\Permission\Dialog {

    use Loader,
        Permissions,
        PermissionKey,
        \Concrete\Package\Schedulizer\Src\Calendar;

    class SchedulizerCalendar extends \Concrete\Core\Controller\Controller {

        protected $viewPath = 'permission/dialog/schedulizer_calendar';

        public function view(){
            $calendarObj = Calendar::getByID($_REQUEST['calendarID']);
            if( is_object($calendarObj) ){
                $this->set('permissions', new Permissions($calendarObj));
            }else{
                $this->set('permissions', new Permissions());
            }
        }

    }

}

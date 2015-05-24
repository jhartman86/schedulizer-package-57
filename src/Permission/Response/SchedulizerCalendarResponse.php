<?php namespace Concrete\Package\Schedulizer\Src\Permission\Response {

    use \Concrete\Package\Schedulizer\Src\Permission\Key\SchedulizerKey;
    use \Concrete\Core\Permission\Response\Response;

    class SchedulizerCalendarResponse extends Response {

        public function canManageCalendarPermissions(){
            $taskPermission = SchedulizerKey::getByHandle('manage_calendar_permissions');
            return $taskPermission->can();
        }

    }
}
<?php namespace Concrete\Package\Schedulizer\Src\Permission\Response {

    use PermissionKey;
    use \Concrete\Core\Permission\Response\Response;

    class SchedulizerCalendarResponse extends Response {

        public function canManageCalendarPermissions(){
            $pk = PermissionKey::getByHandle('manage_calendar_permissions');
            $pk->setPermissionObject($this->object);
            return $pk->validate();
        }

    }
}
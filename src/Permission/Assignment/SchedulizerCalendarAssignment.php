<?php namespace Concrete\Package\Schedulizer\Src\Permission\Assignment {

    use Loader;
    use Router;
    use \Concrete\Core\Permission\Assignment\Assignment;
    use \Concrete\Core\Permission\Access\Access AS PermissionAccess;
    use \Concrete\Package\Schedulizer\Src\Permission\Access\SchedulizerCalendarAccess;

    class SchedulizerCalendarAssignment extends Assignment {

        const PERMISSION_CATEGORY_HANDLE = 'schedulizer_calendar';
        protected $permAssignTable       = 'SchedulizerCalendarPermissionAssignments';

        public function getPermissionAccessObject(){
            $paeID = Loader::db()->GetOne("SELECT paID FROM {$this->permAssignTable} WHERE calendarID = ? AND pkID = ?", array(
                $this->getPermissionObject()->getID(),
                $this->pk->getPermissionKeyID()
            ));
            return SchedulizerCalendarAccess::getByID( $paeID, $this->pk );
        }

        public function clearPermissionAssignment(){
            Loader::db()->Execute("UPDATE {$this->permAssignTable} SET paID = 0 WHERE calendarID = ? AND pkID = ?", array(
                $this->getPermissionObject()->getID(), $this->pk->getPermissionKeyID()
            ));
        }

        public function assignPermissionAccess( PermissionAccess $pa ){
            Loader::db()->Replace($this->permAssignTable, array(
                'calendarID' => $this->getPermissionObject()->getID(),
                'paID'       => $pa->getPermissionAccessID(),
                'pkID'       => $this->pk->getPermissionKeyID()
            ), array('calendarID', 'pkID'), true);
            $pa->markAsInUse();
        }

        /**
         * Override the parent method as it still uses tools files, which no
         * longer route correctly via packages.
         * @param bool $task
         * @return mixed
         */
        public function getPermissionKeyToolsURL( $task = false ){
            if( ! $task ){
                $task = 'save_permission';
            }
            $token = Loader::helper('validation/token')->getParameter($task);
            $query = http_build_query(array(
                'task'      => $task,
                'pkID'      => $this->pk->getPermissionKeyID()
            )) . "&{$token}";
            return Router::route(array('permission/category/schedulizer?'.$query, 'schedulizer'));
        }

    }

}
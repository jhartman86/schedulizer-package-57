<?php namespace Concrete\Package\Schedulizer\Src\Permission\Key {

    use \Concrete\Package\Schedulizer\Src\Permission\Assignment\SchedulizerAssignment;
    use PermissionKey; /** @see \Concrete\Core\Permission\Key\Key */

    /**
     * Note - the $permissions->can{WhateverCamelCasedHere} gets dynamically converted into
     * handle format (eg. canCreateTag -> "create_tag") and then the PermissionKey is loaded
     * from that:
     *
     *  $p = new Permissions();
     *  $p->canCreateTag();
     *  $p->canCreateCalendar();
     *  $p->canManageCalendarPermissions();
     *
     * Class SchedulizerKey
     * @package Concrete\Package\Schedulizer\Src\Permission\Key
     * @extends \Concrete\Core\Permission\Key\Key
     */
    class SchedulizerKey extends PermissionKey {

        public static function getList(){
            return parent::getList('schedulizer');
        }

        public function getPermissionAssignmentObject(){
            if (is_object($this->permissionObject)) {
                $className = $this->permissionObject->getPermissionAssignmentClassName();
                $targ = \Core::make($className);
                $targ->setPermissionObject($this->permissionObject);
            } else {
                $targ = new SchedulizerAssignment();
            }
            $targ->setPermissionKeyObject($this);
            return $targ;
        }

    }

}
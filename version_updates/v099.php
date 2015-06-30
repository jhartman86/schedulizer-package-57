<?php namespace Concrete\Package\Schedulizer\VersionUpdates {

    use \Concrete\Core\Permission\Access\Access AS PermissionAccess;
    use \Concrete\Core\User\Group\Group;
    use \Concrete\Package\Schedulizer\Src\Calendar;
    use \Concrete\Package\Schedulizer\Src\Permission\Key\SchedulizerCalendarKey;
    use \Concrete\Core\Permission\Access\Entity\GroupEntity as GroupPermissionAccessEntity;
    use \Concrete\Package\Schedulizer\Src\Permission\Access\Entity\CalendarOwnerEntity AS CalendarOwnerAccessEntity;

    /**
     * This update ensures that all calendars have Admins and Calendar Owners
     * set to edit/delete events.
     */
    class V099 {

        public function run(){
            $calendars = Calendar::fetchAll();
            if( !empty($calendars) ){
                foreach($calendars AS $calendarObj){
                    // Edit Events
                    $pkEditEvents = SchedulizerCalendarKey::getByHandle('edit_events');
                    $pkEditEvents->setPermissionObject($calendarObj);
                    $paEdit = $pkEditEvents->getPermissionAccessObject();
                    if( !is_object($paObj) ){
                        $paEdit = PermissionAccess::create($pkEditEvents);
                    }
                    $peOwner = CalendarOwnerAccessEntity::getOrCreate();
                    $paEdit->addListItem($peOwner);
                    $peAdmin = GroupPermissionAccessEntity::getOrCreate(Group::getByID(ADMIN_GROUP_ID));
                    $paEdit->addListItem($peAdmin);
                    $pkEditEvents->getPermissionAssignmentObject()->assignPermissionAccess($paEdit);

                    // Delete Events
                    $pkDeleteEvents = SchedulizerCalendarKey::getByHandle('delete_events');
                    $pkDeleteEvents->setPermissionObject($calendarObj);
                    $paDelete = $pkDeleteEvents->getPermissionAccessObject();
                    if( !is_object($paDelete) ){
                        $paDelete = PermissionAccess::create($pkDeleteEvents);
                    }
                    $peOwner = CalendarOwnerAccessEntity::getOrCreate();
                    $paDelete->addListItem($peOwner);
                    $peAdmin = GroupPermissionAccessEntity::getOrCreate(Group::getByID(ADMIN_GROUP_ID));
                    $paDelete->addListItem($peAdmin);
                    $pkDeleteEvents->getPermissionAssignmentObject()->assignPermissionAccess($paDelete);
                }
            }
        }

    }

}

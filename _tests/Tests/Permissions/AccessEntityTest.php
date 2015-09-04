<?php namespace Schedulizer\Tests\Package {

//    use Cache;
//    use \Concrete\Core\Permission\Access\Access AS PermissionAccess;
//    use \Concrete\Core\User\Group\Group;
//    use \Concrete\Core\Permission\Access\Entity\GroupEntity as GroupPermissionAccessEntity;
    use \Concrete\Package\Schedulizer\Src\Permission\Access\Entity\CalendarOwnerEntity AS CalendarOwnerAccessEntity;
//    use \Concrete\Core\Permission\Category AS PermissionKeyCategory;
//    use \Concrete\Package\Schedulizer\Src\Permission\Key\SchedulizerKey AS SchedulizerPermKey;

    /**
     * When the package gets installed, we need to setup default "task"
     * permissions (eg. create tags, add/delete calendars, manage calendar
     * permissions) for Administrators and, if applicable, Calendar Owners.
     * @todo: actually run validations/tests :)
     * @group permissions
     * @package Schedulizer\Tests\Package
     */
    class AccessEntityTest extends \PHPUnit_Framework_TestCase {

        public function setUp(){
            Cache::disableAll();
        }

        /**
         * Create "Administrators" create_tag permission
         */
        public function testSetupAdministratorsCreateTagPermission(){
            $pkCreateTag = SchedulizerPermKey::getByHandle('create_tag');
            $paCreateTag = $pkCreateTag->getPermissionAccessObject();
            if( ! is_object($paCreateTag) ){
                // Permission Access
                $paCreateTag = PermissionAccess::create($pkCreateTag);
                // Permission Access "Entity"
                $peAdmins = GroupPermissionAccessEntity::getOrCreate(Group::getByID(ADMIN_GROUP_ID));
                // Add peAdmins Entity to paCreateTag PermissionAccess
                $paCreateTag->addListItem($peAdmins);
                // Assign the built out $paCreateTag PermissionAccess object
                $pkCreateTag->getPermissionAssignmentObject()->assignPermissionAccess($paCreateTag);
            }
        }

        /**
         * Create "Administrators" create_calendar permission
         */
        public function testSetupAdministratorsCreateCalendarPermission(){
            $pkCreateCalendar = SchedulizerPermKey::getByHandle('create_calendar');
            $paCreateCalendar = $pkCreateCalendar->getPermissionAccessObject();
            if( ! is_object($paCreateCalendar) ){
                // Permission Access
                $paCreateCalendar = PermissionAccess::create($pkCreateCalendar);
                // Permission Access "Entity"
                $peAdmins = GroupPermissionAccessEntity::getOrCreate(Group::getByID(ADMIN_GROUP_ID));
                // Add peAdmins Entity to paCreateTag PermissionAccess
                $paCreateCalendar->addListItem($peAdmins);
                // Assign the built out $paCreateTag PermissionAccess object
                $pkCreateCalendar->getPermissionAssignmentObject()->assignPermissionAccess($paCreateCalendar);
            }
        }

        /**
         * Create "Administrators" edit_calendar permission
         */
        public function testSetupAdministratorsEditCalendarPermission(){
            $pkEditCalendar = SchedulizerPermKey::getByHandle('edit_calendar');
            $paEditCalendar = $pkEditCalendar->getPermissionAccessObject();
            if( ! is_object($paEditCalendar) ){
                // Permission Access
                $paEditCalendar = PermissionAccess::create($pkEditCalendar);
                // Permission Access "Entity"
                $peAdmins = GroupPermissionAccessEntity::getOrCreate(Group::getByID(ADMIN_GROUP_ID));
                // Add peAdmins Entity to paCreateTag PermissionAccess
                $paEditCalendar->addListItem($peAdmins);
                // Assign the built out $paCreateTag PermissionAccess object
                $pkEditCalendar->getPermissionAssignmentObject()->assignPermissionAccess($paEditCalendar);
            }
        }

        /**
         * Create "Administrators" delete_calendar permission
         */
        public function testSetupAdministratorsDeleteCalendarPermission(){
            $pkDeleteCalendar = SchedulizerPermKey::getByHandle('delete_calendar');
            $paDeleteCalendar = $pkDeleteCalendar->getPermissionAccessObject();
            if( ! is_object($paDeleteCalendar) ){
                // Permission Access
                $paDeleteCalendar = PermissionAccess::create($pkDeleteCalendar);
                // Permission Access "Entity"
                $peAdmins = GroupPermissionAccessEntity::getOrCreate(Group::getByID(ADMIN_GROUP_ID));
                // Add peAdmins Entity to paCreateTag PermissionAccess
                $paDeleteCalendar->addListItem($peAdmins);
                // Assign the built out $paCreateTag PermissionAccess object
                $pkDeleteCalendar->getPermissionAssignmentObject()->assignPermissionAccess($paDeleteCalendar);
            }
        }

        /**
         * Create "Administrators" manage_calendar_permissions permission
         */
        public function testSetupAdministratorsManageCalendarPermission(){
            $pkManageCalendar = SchedulizerPermKey::getByHandle('manage_calendar_permissions');
            $paManageCalendar = $pkManageCalendar->getPermissionAccessObject();
            if( ! is_object($paManageCalendar) ){
                // Permission Access
                $paManageCalendar = PermissionAccess::create($pkManageCalendar);
                // Permission Access "Entity"
                $peAdmins = GroupPermissionAccessEntity::getOrCreate(Group::getByID(ADMIN_GROUP_ID));
                // Add peAdmins Entity to paCreateTag PermissionAccess
                $paManageCalendar->addListItem($peAdmins);
                // Assign the built out $paCreateTag PermissionAccess object
                $pkManageCalendar->getPermissionAssignmentObject()->assignPermissionAccess($paManageCalendar);
            }
        }

        /**
         * Create "CalendarOwner" manage_calendar_permissions permission
         */
        public function testSetupCalendarOwnerManageCalendarPermission(){
            $pkManageCalendar = SchedulizerPermKey::getByHandle('manage_calendar_permissions');
            $paManageCalendar = $pkManageCalendar->getPermissionAccessObject();
            // Permission Access Object doesn't exist (but since we added Administrators already,
            // it does) - so the if statement shows how to do it if already exists.
            if( ! is_object($paManageCalendar) ){
                // Permission Access
                $paManageCalendar = PermissionAccess::create($pkManageCalendar);
                // Permission Access "Entity"
                $peCalendarOwner = CalendarOwnerAccessEntity::getOrCreate();
                // Add peAdmins Entity to paCreateTag PermissionAccess
                $paManageCalendar->addListItem($peCalendarOwner);
                // Assign the built out $paCreateTag PermissionAccess object
                $pkManageCalendar->getPermissionAssignmentObject()->assignPermissionAccess($paManageCalendar);
            }else{
                // Permission Access "Entity"
                $peCalendarOwner = CalendarOwnerAccessEntity::getOrCreate();
                // Add peAdmins Entity to paCreateTag PermissionAccess
                $paManageCalendar->addListItem($peCalendarOwner);
                // Assign the built out $paCreateTag PermissionAccess object
                $pkManageCalendar->getPermissionAssignmentObject()->assignPermissionAccess($paManageCalendar);
            }
        }

        /**
         * Create "CalendarOwner" edit_calendar permission
         */
        public function testSetupCalendarOwnerEditCalendarPermission(){
            $pkEditCalendar = SchedulizerPermKey::getByHandle('edit_calendar');
            $paEditCalendar = $pkEditCalendar->getPermissionAccessObject();
            // Permission Access Object doesn't exist (but since we added Administrators already,
            // it does) - so the if statement shows how to do it if already exists.
            if( ! is_object($paEditCalendar) ){
                // Permission Access
                $paEditCalendar = PermissionAccess::create($pkEditCalendar);
                // Permission Access "Entity"
                $peCalendarOwner = CalendarOwnerAccessEntity::getOrCreate();
                // Add peAdmins Entity to paCreateTag PermissionAccess
                $paEditCalendar->addListItem($peCalendarOwner);
                // Assign the built out $paCreateTag PermissionAccess object
                $pkEditCalendar->getPermissionAssignmentObject()->assignPermissionAccess($paEditCalendar);
            }else{
                // Permission Access "Entity"
                $peCalendarOwner = CalendarOwnerAccessEntity::getOrCreate();
                // Add peAdmins Entity to paCreateTag PermissionAccess
                $paEditCalendar->addListItem($peCalendarOwner);
                // Assign the built out $paCreateTag PermissionAccess object
                $pkEditCalendar->getPermissionAssignmentObject()->assignPermissionAccess($paEditCalendar);
            }
        }

    }

}
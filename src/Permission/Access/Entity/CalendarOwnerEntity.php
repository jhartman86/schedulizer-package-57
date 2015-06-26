<?php namespace Concrete\Package\Schedulizer\Src\Permission\Access\Entity {

    use \Concrete\Core\Permission\Access\Entity\Entity;
    use PermissionAccess; /** @see \Concrete\Core\Permissions\Access\Access */
    use UserInfo;
    use Loader;
    use User;
    use \Concrete\Package\Schedulizer\Src\Calendar;
    use \Concrete\Package\Schedulizer\Src\Permission\Access\SchedulizerAccess;
    use \Concrete\Package\Schedulizer\Src\Permission\Access\SchedulizerCalendarAccess;

    class CalendarOwnerEntity extends Entity {

        public function getAccessEntityUsers( PermissionAccess $permissionAccessObj ){
            $calObj = $permissionAccessObj->getPermissionObjectToCheck();
            if( is_null($calObj) ){
                $calObj = $permissionAccessObj->getPermissionObject();
            }
            if( is_object($calObj) && ($calObj instanceof Calendar) ){
                return UserInfo::getByID($calObj->getOwnerID());
            }
        }

        /**
         * @todo make this work...
         * @param PermissionAccess $permissionAccessObj
         * @return bool
         */
        public function validate( PermissionAccess $permissionAccessObj ){
            // If we get a SchedulizerAccess class, we're checking if the user
            // has been granted access via the Schedulizer task permissions. As in,
            // the Schedulizer permissions are generic and not usually assigned on a
            // per-object (calendar) basis, but we can grant a permission to a calendar
            // owner that will be relevant for all calendars they own...
            if( $permissionAccessObj instanceof SchedulizerAccess ){
                $user = $this->getAccessEntityUsers($permissionAccessObj);
                if( is_object($user) ){
                    $currentUser = new User();
                    return (int)$user->getUserID() === (int)$currentUser->getUserID();
                }
            }

            // With this, we know the permissionAccessObj has a permissionObj to check
            // against directly...
            if( $permissionAccessObj instanceof SchedulizerCalendarAccess ){
                $calObj = $permissionAccessObj->getPermissionObjectToCheck();
                if( $calObj instanceof Calendar ){
                    $currentUser = new User();
                    return (int)$currentUser->getUserID() === (int)$calObj->getOwnerID();
                }
            }

            return false;
        }

        public function getAccessEntityTypeLinkHTML(){
            return sprintf('<a href="javascript:void(0)" onclick="choosePermissionAccessEntityPageOwner()">%s</a>', tc('PermissionAccessEntityTypeName', 'Calendar Owner'));
        }

        public static function getAccessEntitiesForUser( $user ){
            $entities = array();
            $db = Loader::db();
            if ($user->isRegistered()) {
                $pae = static::getOrCreate();
                $r = $db->GetOne('select id from SchedulizerCalendar where ownerID = ?', array($user->getUserID()));
                if ($r > 0) {
                    $entities[] = $pae;
                }
            }
            return $entities;
        }

        public static function getOrCreate(){
            $db = Loader::db();
            $petID = $db->GetOne('select petID from PermissionAccessEntityTypes where petHandle = \'calendar_owner\'');
            $peID = $db->GetOne('select peID from PermissionAccessEntities where petID = ?',
                array($petID));
            if (!$peID) {
                $db->Execute("insert into PermissionAccessEntities (petID) values(?)", array($petID));
                $peID = $db->Insert_ID();
                \Config::save('concrete.misc.access_entity_updated', time());
            }
            return \Concrete\Core\Permission\Access\Entity\Entity::getByID($peID);
        }

        public function load(){
            $this->label = t('Calendar Owner');
        }

    }

}
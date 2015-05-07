<?php namespace Concrete\Package\Schedulizer\Src\Permission\Access\Entity {

    use \Concrete\Core\Permission\Access\Entity\Entity;
    use PermissionAccess; /** @see \Concrete\Core\Permissions\Access\Access */
    use UserInfo;
    use Loader;
    use \Concrete\Package\Schedulizer\Src\Calendar;

    class CalendarOwnerEntity extends Entity {

        public function getAccessEntityUsers( PermissionAccess $permissionAccessObj ){
            $calObj = $permissionAccessObj->getPermissionObject();
            if( is_object($calObj) && ($calObj instanceof Calendar) ){
                return UserInfo::getByID($calObj->getOwnerID());
            }
        }

        public function validate( PermissionAccess $permissionAccessObj ){
            print_r($permissionAccessObj);exit;
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
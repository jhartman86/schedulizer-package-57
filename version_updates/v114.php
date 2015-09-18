<?php namespace Concrete\Package\Schedulizer\VersionUpdates {

    use Group;
    use Concrete\Core\Package\Package;
    use \Concrete\Core\Permission\Access\Access AS PermissionAccess;
    use \Concrete\Core\Permission\Access\Entity\GroupEntity as GroupPermissionAccessEntity;
    use \Concrete\Package\Schedulizer\Src\Permission\Key\SchedulizerKey AS SchedulizerPermissionKey;

    /**
     * We added a new "task" permission, 'manage_collections'. Need to setup
     * Administrators as access entities.
     */
    class V114 {

        public function run(){

            $packageObj = Package::getByHandle('schedulizer');

            // Ensure permission key exists...
            if( ! SchedulizerPermissionKey::getByHandle('manage_collections') ){
                SchedulizerPermissionKey::add(
                    'schedulizer',
                    'manage_collections',
                    t('Manage Collections'),
                    t('Can Manage Collections'),
                    0, 0, $packageObj
                );
            }

            $pkObj = SchedulizerPermissionKey::getByHandle('manage_collections');
            $paObj = $pkObj->getPermissionAccessObject();

            if( !is_object($paObj) ){
                // Permission Access Record
                $paObj = PermissionAccess::create($pkObj);
                // Permissionable Access Entity
                $peAdmins = GroupPermissionAccessEntity::getOrCreate(Group::getByID(ADMIN_GROUP_ID));
                // Add $peAdmins Entity to $paObj
                $paObj->addListItem($peAdmins);
                // Save Assignment
                $pkObj->getPermissionAssignmentObject()->assignPermissionAccess($paObj);
            }

        }

    }

}

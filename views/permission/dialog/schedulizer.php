<?php defined('C5_EXECUTE') or die("Access Denied.");
/** https://github.com/mkly/Data/blob/master/src/data/tools/permissions/dialogs/data_type.php */

if( $permissions->canAccessTaskPermissions() ){
    Loader::packageElement('permission/details/schedulizer', 'schedulizer');
}
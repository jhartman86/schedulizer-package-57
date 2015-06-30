<?php defined('C5_EXECUTE') or die("Access Denied.");
/** https://github.com/mkly/Data/blob/master/src/data/tools/permissions/dialogs/data_type.php */

if( is_object($permissions) && $permissions->canManageCalendarPermissions() ){
    Loader::packageElement('permission/details/schedulizer_calendar', 'schedulizer');
}else{
    echo 'Permission denied.';
}

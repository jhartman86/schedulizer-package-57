<?php defined('C5_EXECUTE') or die("Access Denied.");
/** https://github.com/mkly/Data/blob/master/src/data/tools/permissions/dialogs/data_type.php */
use Loader;
use Permissions; /** @see \Concrete\Core\Permission\Checker */

$p = new Permissions();
if( $p->canAccessTaskPermissions() ){
    Loader::packageElement('permission/details/schedulizer_calendar', 'schedulizer');
}
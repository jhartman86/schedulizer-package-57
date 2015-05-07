<?php defined('C5_EXECUTE') or die("Access Denied.");

    use PermissionKey;
    Loader::element('permission/detail', array('permissionKey' => PermissionKey::getByID((int)$_REQUEST['pkID'])));

?>
<script type="text/javascript">
    var ccm_permissionDialogURL = '<?php echo Router::route(array('permission/dialog/schedulizer', 'schedulizer')); ?>';
</script>
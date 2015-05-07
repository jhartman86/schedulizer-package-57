<form method="post" action="<?php echo $this->action('save'); ?>" role="form">
    <table class="ccm-permission-grid table table-striped">
        <?php foreach($permissionKeyList AS $pkObj): ?>
            <tr>
                <td class="ccm-permission-grid-name" id="ccm-permission-grid-name-<?php echo $pkObj->getPermissionKeyID(); ?>">
                    <strong><a dialog-title="<?php echo $pkObj->getPermissionKeyDisplayName(); ?>" data-pkID="<?php echo $pkObj->getPermissionKeyID(); ?>" data-paID="<?php echo $pkObj->getPermissionAccessID()?>" onclick="ccm_permissionLaunchDialog(this)" href="javascript:void(0)"><?php echo $pkObj->getPermissionKeyDisplayName(); ?></a></strong>
                </td>
                <td id="ccm-permission-grid-cell-<?php echo $pkObj->getPermissionKeyID(); ?>" class="ccm-permission-grid-cell">
                    <?php Loader::element('permission/labels', array('pk' => $pkObj)); ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <input type="submit" value="<?=t('Save')?>" class="btn btn-success pull-right"><?=t('Save')?> <i class="icon-ok-sign icon-white"></i></button>
        </div>
    </div>
    <?php Loader::helper('validation/token')->output('save_permissions'); ?>
</form>

<?php Loader::helper('concrete/dashboard')->getDashboardPaneFooterWrapper(false)?>

<script type="text/javascript">
    ccm_permissionLaunchDialog = function( link ){
        var $link   = $(link),
            // not sure when duplicate is ever used? just copying from core files as example...
            dupe    = $link.attr('data-duplicate');
        if( dupe != 1 ){ dupe = 0; }
        var params = jQuery.param({
            duplicate: dupe,
            pkID: $link.attr('data-pkID'),
            paID: $link.attr('data-paID')
        });
        jQuery.fn.dialog.open({
            title: $link.attr('dialog-title'),
            href: '<?php echo Router::route(array('permission/dialog/schedulizer', 'schedulizer')); ?>' + '?' + params,
            modal: false,
            width: 500,
            height: 380
        });
    };
</script>

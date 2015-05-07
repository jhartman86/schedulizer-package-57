<?php defined('C5_EXECUTE') or die('Access Denied.');
    /** @var $calendarObj \Concrete\Package\Schedulizer\Src\Calendar */
?>
<div class="ccm-ui">
    <form id="ccm-permission-list-form" method="POST" action="<?php echo $calendarObj->getPermissionCategoryToolsUrlShim('save_permission_assignments'); ?>">
        <table class="ccm-permission-grid table table-striped">
            <?php foreach($permissionKeyList AS $pkObj): /** @var $pkObj \Concrete\Package\Schedulizer\Src\Permission\Key\SchedulizerCalendarKey */
                $pkObj->setPermissionObject($calendarObj);
                ?>
                <tr>
                    <td class="ccm-permission-grid-name" id="ccm-permission-grid-name-<?php echo $pkObj->getPermissionKeyID(); ?>">
                        <strong>
                            <a class="data-permission-key-edit-button" dialog-title="<?php echo $pkObj->getPermissionKeyDisplayName(); ?>" data-pkID="<?php echo $pkObj->getPermissionKeyID(); ?>" data-paID="<?php echo $pkObj->getPermissionAccessID()?>" data-calendarID="<?php echo $calendarObj->getID(); ?>">
                                <?php echo $pkObj->getPermissionKeyDisplayName(); ?>
                            </a>
                        </strong>
                    </td>
                    <td id="ccm-permission-grid-cell-<?php echo $pkObj->getPermissionKeyID(); ?>" class="ccm-permission-grid-cell">
                        <?php Loader::element('permission/labels', array('pk' => $pkObj)); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </form>

    <div class="dialog-buttons">
        <a href="javascript:void(0)" onclick="jQuery.fn.dialog.closeTop()" class="btn"><?php echo t('Cancel'); ?></a>
        <button send-permission-form class="btn primary ccm-button-right"><?php echo t('Save'); ?> <i class="icon-ok-sign icon-white"></i></button>
    </div>
</div>

<script type="text/javascript">
    (function(){
        var $form = $('#ccm-permission-list-form');

        $('[send-permission-form]').on('click', function(){ $form.submit(); });

        $form.ajaxForm({
            beforeSubmit: function(){
                jQuery.fn.dialog.showLoader();
            },
            success: function(){
                jQuery.fn.dialog.hideLoader();
                jQuery.fn.dialog.closeTop();
            }
        });

        $('.data-permission-key-edit-button', $form).on('click', function(){
            var $this = $(this),
                dupe  = $this.attr('data-duplicate');
            if( dupe != 1 ){ dupe = 0; }
            var params = jQuery.param({
                duplicate: dupe,
                pkID: $this.attr('data-pkID'),
                paID: $this.attr('data-paID'),
                calendarID: $this.attr('data-calendarID')
            });
            jQuery.fn.dialog.open({
                title: $this.attr('dialog-title'),
                href: '<?php echo Router::route(array('permission/dialog/schedulizer_calendar', 'schedulizer')) ?>' + '?' + params,
                modal: false,
                width: 500,
                height: 380
            });
        });
    })();
</script>
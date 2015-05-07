<? defined('C5_EXECUTE') or die("Access Denied.");
/**
 * Note: see other entity type definition files in core elements directory. Normally
 * you can just do $type->getAccessEntityTypeToolsURL(), but since tools are deprecated
 * and do not route correctly to packages, we have to hack around this. Further, extending/overriding
 * class methods in the core permissions stuff is so effed up its pointless to try and override.
 * @var $type \Concrete\Core\Permission\Access\Entity\Type
 */
$url = Router::route(array('permission/access/entity/types/calendar_owner', 'schedulizer'));
$url .= '?' . Loader::helper('validation/token')->getParameter('process');
?>
<script type="text/javascript">
    choosePermissionAccessEntityPageOwner = function(){
        $('#ccm-permissions-access-entity-form .btn-group').removeClass('open');
        $.getJSON('<?php echo $url; ?>', function(r) {
            $('#ccm-permissions-access-entity-form input[name=peID]').val(r.peID);
            $('#ccm-permissions-access-entity-label').html('<div class="alert alert-info">' + r.label + '</div>');
        });
    }
</script>

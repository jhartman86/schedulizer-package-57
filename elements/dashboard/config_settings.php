<?php
$formHelper     = Loader::helper('form');
$sitemapHelper  = Loader::helper('form/page_selector');
/** @var $packageObj \Concrete\Package\Schedulizer\Controller */
$packageObj = \Package::getClass('schedulizer');
// ^ We have to use ::getClass instead of ::getByHandle as the package
// may not be installed yet, thus getByHandle would return null
$pageTypesList = \Concrete\Core\Page\Type\Type::getList();
$pageTypesSelectList = array();
foreach($pageTypesList AS $pageTypeObj){ /** @var $pageTypeObj \Concrete\Core\Page\Type\Type */
    $pageTypesSelectList[ $pageTypeObj->getPageTypeID() ] = $pageTypeObj->getPageTypeName();
}
?>

<style type="text/css">
    .config-table td.config-label {white-space:nowrap;border-right:1px dotted #ccc;}
    .config-table span.checkbox {margin-top:0;padding-left:20px;}
    .config-table table.inner-table {width:100%;background:transparent;}
    .config-table table.inner-table td {width:50%;padding:8px;background:transparent;}
    .config-table table.inner-table tr.inner-table-label td {padding-top:0;border-bottom:1px dotted #ccc;}
    .config-table .ccm-page-selector {margin:0;}
</style>

<div class="row">
    <div class="col-sm-12">
        <table class="config-table table table-striped">
            <tbody>
                <tr>
                    <td class="config-label"><label>Event Pages</label></td>
                    <td style="width:99%;">
                        <span class="checkbox">
                            <?php echo $formHelper->checkbox($packageObj::CONFIG_EVENT_AUTOGENERATE_PAGES, 1, (int)$packageObj->configGet($packageObj::CONFIG_EVENT_AUTOGENERATE_PAGES)); ?>
                            Automatically Generate Event Pages
                        </span>
                        <table class="inner-table">
                            <tr class="inner-table-label">
                                <td>Beneath Page</td>
                                <td>Page Type</td>
                            </tr>
                            <tr>
                                <td>
                                    <?php echo $sitemapHelper->selectPage($packageObj::CONFIG_EVENT_PAGE_PARENT, (int)$packageObj->configGet($packageObj::CONFIG_EVENT_PAGE_PARENT)); ?>
                                </td>
                                <td>
                                    <?php echo $formHelper->select($packageObj::CONFIG_EVENT_PAGE_TYPE, $pageTypesSelectList, (int)$packageObj->configGet($packageObj::CONFIG_EVENT_PAGE_TYPE)); ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="config-label"><label>Timezone</label></td>
                    <td><?php echo $formHelper->select($packageObj::CONFIG_DEFAULT_TIMEZONE, array_combine(DateTimeZone::listIdentifiers(), DateTimeZone::listIdentifiers()), $packageObj->configGet($packageObj::CONFIG_DEFAULT_TIMEZONE)); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
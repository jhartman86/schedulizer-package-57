<!-- Templates -->
<script type="text/ng-template" id="/collection_form">
    <?php Loader::packageElement('templates/collection_form', 'schedulizer'); ?>
</script>

<div class="schedulizer-app" ng-controller="CtrlCollectionSearchPage">
    <div class="ccm-dashboard-content-full search-page" ng-class="{'search-open':searchOpen}">
        <div class="not-stupid-header-style">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="pull-left">
                            <h3><?php echo $pageTitle; ?></h3>
                        </div>
                        <div class="pull-right">
                            <button class="btn btn-primary" modalize="/collection_form"><?php echo t('Create Collection'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="app-wrap">
            <table border="0" cellspacing="0" cellpadding="0" class="ccm-search-results-table">
                <thead>
                <tr>
                    <th><a>Title</a></th>
                    <th><a>Calendar</a></th>
                    <th><a>Owner</a></th>
                </tr>
                </thead>
                <tbody>
                <?php if(!empty($collections)): foreach($collections AS $collectionObj): ?>
                    <tr>
                        <td><a href="<?php echo View::url('/dashboard/schedulizer/calendars/collections/manage', $collectionObj->getID()); ?>"><?php echo $collectionObj; ?></a></td>
                        <td></td>
                        <td></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php Loader::packageElement('browser_unsupported', 'schedulizer');
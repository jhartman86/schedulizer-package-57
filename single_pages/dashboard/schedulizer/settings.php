<style type="text/css">
    .schedulizer-app ul.nav-tabs a {cursor:pointer;}
    .schedulizer-app .tab-pane {padding-top:1rem;}
    .schedulizer-app table.table td {vertical-align:middle !important;}
    .schedulizer-app table.table tr:first-of-type td {border-top:0 !important;}
    .schedulizer-app table.table-list .form-group {margin-bottom:0;position:relative;}
    .schedulizer-app table.table-list .saver {position:absolute;top:2px;right:2px;z-index:1;}
    #ccm-dashboard-result-message.ccm-ui {max-width:678px;margin:0 auto !important;}
</style>

<div class="schedulizer-system-page-wrap schedulizer-app" ng-controller="CtrlSettingsPage">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <ul class="nav nav-tabs">
                    <li class="active" ng-click="activateTab(0)" ng-class="{active:(activeTab === 0)}"><a>Basic</a></li>
                    <li ng-click="activateTab(1)" ng-class="{active:(activeTab === 1)}"><a>Categories</a></li>
                    <li ng-click="activateTab(2)" ng-class="{active:(activeTab === 2)}"><a>Tags</a></li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane active" ng-class="{active:(activeTab === 0)}">
                        <form method="post" action="<?php echo $this->action('save'); ?>">
                            <?php Loader::packageElement('dashboard/config_settings', 'schedulizer'); ?>
                            <button type="submit" class="btn btn-success btn-block">Save</button>
                        </form>
                    </div>

                    <div class="tab-pane" ng-class="{active:(activeTab === 1)}">
                        <table class="table table-striped table-list" ng-cloak>
                            <tbody>
                                <tr ng-repeat="category in categoriesList">
                                    <td>
                                        <form class="form-group" ng-submit="persist(categoriesList, $index)">
                                            <input type="text" class="form-control" ng-model="category.displayText" ng-change="category.dirty = true" />
                                            <button type="submit" class="btn btn-success btn-sm saver" ng-show="category.dirty">Save</button>
                                        </form>
                                    </td>
                                    <td style="width:1%;">
                                        <a class="btn btn-danger btn-sm" ng-click="remove(categoriesList, $index)"><i class="icon-trash"></i></a>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2">
                                        <button type="button" class="btn btn-success btn-block" ng-click="addCategory()">Add Category</button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="tab-pane" ng-class="{active:(activeTab === 2)}">
                        <table class="table table-striped table-list" ng-cloak>
                            <tbody>
                                <tr ng-repeat="tag in tagsList">
                                    <td>
                                        <form class="form-group" ng-submit="persist(tagsList, $index)">
                                            <input type="text" class="form-control" ng-model="tag.displayText" ng-change="tag.dirty = true" />
                                            <button type="submit" class="btn btn-success btn-sm saver" ng-show="tag.dirty">Save</button>
                                        </form>
                                    </td>
                                    <td style="width:1%;">
                                        <a class="btn btn-danger btn-sm" ng-click="remove(tagsList,$index)"><i class="icon-trash"></i></a>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2">
                                        <button type="button" class="btn btn-success btn-block" ng-click="addTag()">Add Tag</button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<form method="post" class="container-fluid" action="<?php echo $this->action('save'); ?>">
    <div class="row">
        <div class="col-sm-12">
            <div class="pull-left">
                <h4 class="lead">Event Publishing</h4>
            </div>
            <div class="pull-right">
                <button type="submit" class="btn btn-success">Save</button>
            </div>
        </div>
    </div>

    <?php Loader::packageElement('dashboard/config_settings', 'schedulizer'); ?>
</form>

<div ng-controller="CtrlManageCategories">
    <div class="row">
        <div class="col-sm-12">
            <div class="pull-left">
                <h4 class="lead">Event Categories <small>Note: changes are made immediately</small></h4>
            </div>
            <div class="pull-right">
                <button type="submit" class="btn btn-success" ng-click="addCategory()">Add Category</button>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <table class="table table-condensed" ng-cloak>
                <thead>
                <tr>
                    <th></th>
                    <th>Display Value</th>
                </tr>
                </thead>
                <tbody>
                <tr ng-repeat="category in categoriesList">
                    <td>
                        <a class="btn btn-danger btn-sm" ng-click="remove($index)">Remove</a>
                    </td>
                    <td>
                        <input type="text" name="" ng-model="category.displayText" ng-change="category.dirty = true" />
                    </td>
                    <td style="width:99%;">
                        <a class="btn btn-success btn-sm" ng-show="category.dirty" ng-click="persist($index)">Save</a>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
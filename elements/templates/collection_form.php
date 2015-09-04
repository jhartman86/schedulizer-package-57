<?php $currentUser = new User(); ?>
<form name="frmCollection" class="calendar-collections container-fluid" ng-controller="CtrlCollectionForm" ng-submit="submitHandler()">
    <?php Loader::packageElement('templates/loading', 'schedulizer'); ?>

    <div ng-show="_ready">
        <ul class="nav nav-tabs">
            <li class="active"><a>Collection Info</a></li>
            <li class="pull-right">
                <button type="submit" class="btn btn-success save-entity">
                    <span ng-hide="_requesting">Save</span>
                    <img ng-show="_requesting" src="<?php echo SCHEDULIZER_IMAGE_PATH; ?>spinner.svg" />
                </button>
            </li>
            <li class="pull-right delete-entity" ng-show="entity.id">
                <button type="button" class="btn btn-warning" ng-click="confirmDelete = !confirmDelete" ng-hide="confirmDelete">
                    Delete Collection
                </button>
                <div ng-show="confirmDelete">
                    <button type="button" class="btn btn-danger" ng-click="deleteCollection()">
                        <strong>Delete It</strong>
                    </button>
                    <button type="button" class="btn btn-info" ng-click="confirmDelete = !confirmDelete">
                        Nevermind!
                    </button>
                </div>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane active">
                <!-- title -->
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label for="">Title</label>
                            <input name="title" type="text" class="form-control input-title" placeholder="Title" ng-model="entity.title" />
                        </div>
                    </div>
                </div>

                <table class="table table-striped table-condensed">
                    <thead>
                        <tr>
                            <th><input type="checkbox" ng-model="checkToggleAll" ng-change="toggleAllCheckboxes()" /></th>
                            <th>Calendar Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr ng-repeat="calendarObj in calendarList">
                            <td>
                                <input type="checkbox" ng-model="selectedCals[calendarObj.id]" ng-init="selectedCals[calendarObj.id] = false" />
                            </td>
                            <td class="col-sm-11">
                                {{ calendarObj.title }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</form>
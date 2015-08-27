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

                <!-- collection owner -->
                <div class="row">
                    <div class="col-sm-12">
                        <label>Collection Owner</label>
                        <div class="form-group white">
                            <a data-calendar-owner-picker data-default-owner-id="<?php echo $currentUser->getUserID(); ?>" dialog-append-buttons="true" dialog-width="90%" dialog-height="70%" dialog-modal="false" dialog-title="Choose User" href="/ccm/system/dialogs/user/search">
                                <?php
                                if( is_object($calendarObj) ){
                                    $ownerUserInfoObj = $calendarObj->getCalendarOwnerUserInfoObj();
                                    if( is_object($ownerUserInfoObj) ){
                                        echo $ownerUserInfoObj->getUserName();
                                    }else{
                                        echo 'Unassigned';
                                    }
                                }else{
                                    echo $currentUser->getUserName();
                                }
                                ?>
                            </a>
                        </div>
                    </div>
                </div>

                <table class="table table-striped table-condensed">
                    <thead>
                        <tr>
                            <th><input type="checkbox" /></th>
                            <th>Calendar Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr ng-repeat="calendarObj in calendarList">
                            <td>
                                <input name="selectedCalendars[]" type="checkbox" ng-model="selectedCals[calendarObj.id]" />
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
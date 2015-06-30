<?php
$currentUser = new User();
?>
<form class="calendar container-fluid" ng-controller="CtrlCalendarForm" ng-submit="submitHandler()">
    <?php Loader::packageElement('templates/loading', 'schedulizer'); ?>

    <div ng-show="_ready">
        <!-- note: tabs are just for consistency in the layout; would need
        to add tab functionality to the controller to switch between them -->
        <ul class="nav nav-tabs">
            <li class="active"><a>Calendar Info</a></li>
            <li class="pull-right">
                <button type="submit" class="btn btn-success save-entity">
                    <span ng-hide="_requesting">Save</span>
                    <img ng-show="_requesting" src="<?php echo SCHEDULIZER_IMAGE_PATH; ?>spinner.svg" />
                </button>
            </li>
            <li class="pull-right delete-entity" ng-show="entity.id">
                <button type="button" class="btn btn-warning" ng-click="confirmDelete = !confirmDelete" ng-hide="confirmDelete">
                    Delete Calendar
                </button>
                <div ng-show="confirmDelete">
                    <button type="button" class="btn btn-danger" ng-click="deleteEvent()">
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
                            <input type="text" class="form-control input-title" placeholder="Title" ng-model="entity.title" />
                        </div>
                    </div>
                </div>

                <!-- timezone -->
                <div class="row">
                    <div class="col-sm-12">
                        <label>Calendar Timezone</label>
                        <div class="form-group white">
                            <span select-wrap class="block"><select class="form-control" ng-options="opt for opt in timezoneOptions" ng-model="entity.defaultTimezone"></select></span>
                        </div>
                    </div>
                </div>

                <!-- calendar owner -->
                <div class="row">
                    <div class="col-sm-12">
                        <label>Calendar Owner</label>
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
            </div>
        </div>
    </div>
</form>

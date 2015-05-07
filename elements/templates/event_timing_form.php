<div class="row" ng-class="{'is-all-day':_timeEntity.isAllDay,'is-open-ended':_timeEntity.isOpenEnded}">
    <div class="col-sm-6 start-dt">
        <div class="row">
            <div class="col-sm-12">
                <label>Starts</label>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 time-widgets">
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Start" bs-datepicker ng-model="_timeEntity.startUTC" data-autoclose="1" data-min-date="today" data-template="/tpl-datepicker" data-icon-left="icon-angle-left" data-icon-right="icon-angle-right" />
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Time" bs-timepicker ng-model="_timeEntity.startUTC" data-autoclose="1" data-template="/tpl-timepicker" data-icon-up="icon-angle-up" data-icon-down="icon-angle-down" data-time-format="hh:mm a" />
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 end-dt">
        <div class="row">
            <div class="col-sm-12">
                <label>Ends</label>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 time-widgets">
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="End" bs-datepicker ng-model="_timeEntity.endUTC" data-autoclose="1" data-min-date="{{calendarEndMinDate}}" data-template="/tpl-datepicker" data-icon-left="icon-angle-left" data-icon-right="icon-angle-right" />
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Time" bs-timepicker ng-model="_timeEntity.endUTC" data-autoclose="1" data-template="/tpl-timepicker" data-icon-up="icon-angle-up" data-icon-down="icon-angle-down" data-time-format="hh:mm a" />
                </div>
            </div>
        </div>
    </div>
</div>

<!-- all day, repeating, timezone -->
<div class="row">
    <div class="col-sm-12">
        <div class="form-group">
            <label class="checkbox-inline">
                <input type="checkbox" ng-model="_timeEntity.isOpenEnded" /> Open Ended
            </label>
            <label class="checkbox-inline">
                <input type="checkbox" ng-model="_timeEntity.isAllDay" /> All Day Event
            </label>
            <label class="checkbox-inline">
                <input type="checkbox" ng-model="_timeEntity.isRepeating" /> Repeat
            </label>
        </div>
    </div>
</div>

<!-- repeat how? -->
<div ng-show="_timeEntity.isRepeating">
    <div class="row">
        <div class="col-sm-12">
            <div class="form-group form-inline">
                Every <span select-wrap><select class="form-control" ng-options="opt as opt for opt in repeatEveryOptions" ng-model="_timeEntity.repeatEvery"></select></span>
                <span select-wrap><select class="form-control" ng-options="opt.value as opt.label for opt in repeatTypeHandleOptions" ng-model="_timeEntity.repeatTypeHandle"></select></span>
                <span select-wrap><select class="form-control" ng-options="opt.value as opt.label for opt in repeatIndefiniteOptions" ng-model="_timeEntity.repeatIndefinite"></select></span>
                <input type="text" class="form-control" placeholder="Repeating End Date" ng-show="_timeEntity.repeatIndefinite == repeatIndefiniteOptions[1].value" bs-datepicker ng-model="_timeEntity.repeatEndUTC" data-autoclose="1" data-min-date="{{_timeEntity.startUTC}}" data-template="/tpl-datepicker" data-icon-left="icon-angle-left" data-icon-right="icon-angle-right" />
                <a class="has-nullifiers" ng-show="hasNullifiers" ng-click="showNullifiers = !showNullifiers">Hidden Dates</a>
            </div>
        </div>
    </div>

    <div class="row nullifiers-list" ng-show="showNullifiers">
        <div class="col-sm-12">
            <div class="form-group">
                <a class="btn btn-sm btn-info" ng-repeat="resource in configuredNullifiers" ng-click="cancelNullifier(resource)" ng-show="resource.id">
                    {{ resource._moment.format('ddd MMMM Do YYYY') }} <i class="icon-close-circle"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- repeat weekly options -->
    <div class="row" ng-show="_timeEntity.repeatTypeHandle == repeatTypeHandleOptions[1].value">
        <div class="col-sm-12">
            <div class="form-group">
                <div class="form-inline">
                    Weekdays &nbsp;
                    <div class="btn-group" role="group">
                        <label class="btn btn-default" ng-repeat="opt in weekdayRepeatOptions" ng-class="{active:opt.checked}">
                            {{opt.label}} <input type="checkbox" ng-model="opt.checked" ng-change="selectedWeekdays()" />
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- repeat monthly options -->
    <div class="row" ng-show="_timeEntity.repeatTypeHandle == repeatTypeHandleOptions[2].value">
        <div class="col-sm-12">
            <div class="form-group">
                <div class="form-inline">
                    <label>
                        On the &nbsp;
                        <input type="radio" ng-model="_timeEntity.repeatMonthlyMethod" ng-value="repeatMonthlyMethodOptions.specific" />
                        <span select-wrap><select class="form-control" ng-options="opt as opt for opt in repeatMonthlySpecificDayOptions" ng-model="_timeEntity.repeatMonthlySpecificDay"></select></span>
                        {{ _timeEntity.repeatMonthlySpecificDay|numberContraction }} of the month,
                    </label>
                    <label>
                        or the &nbsp;
                        <input type="radio" ng-model="_timeEntity.repeatMonthlyMethod" ng-value="repeatMonthlyMethodOptions.dynamic" />
                        <span select-wrap><select class="form-control" ng-options="opt.value as opt.label for opt in repeatMonthlyDynamicWeekOptions" ng-model="_timeEntity.repeatMonthlyOrdinalWeek"></select></span>
                        <span select-wrap><select class="form-control" ng-options="opt.value as opt.label for opt in repeatMonthlyDynamicWeekdayOptions" ng-model="_timeEntity.repeatMonthlyOrdinalWeekday"></select></span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
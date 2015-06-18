<!-- Templates -->
<script type="text/ng-template" id="/tpl-datepicker">
    <?php Loader::packageElement('templates/datepicker', 'schedulizer'); ?>
</script>
<script type="text/ng-template" id="/tpl-timepicker">
    <?php Loader::packageElement('templates/timepicker', 'schedulizer'); ?>
</script>
<script type="text/ng-template" id="/event_form">
    <?php Loader::packageElement('templates/event_form', 'schedulizer'); ?>
</script>
<script type="text/ng-template" id="/event_timing_form">
    <?php Loader::packageElement('templates/event_timing_form', 'schedulizer'); ?>
</script>

<div class="schedulizer-app" ng-controller="CtrlSearchPage">
    <div class="ccm-dashboard-content-full search-page" ng-class="{'search-open':searchOpen}">
        <div class="not-stupid-header-style">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="pull-left">
                            <h3><?php echo $pageTitle; ?></h3>
                        </div>
                        <div class="pull-right">
                            <span select-wrap><select class="form-control" ng-change="sendSearch()" ng-options="opt.value as opt.label for opt in isActiveOptions" ng-model="showAllEvents"></select></span>
                            <span select-wrap><select class="form-control" ng-change="sendSearch()" ng-options="opt.value as opt.label for opt in groupingOptions" ng-model="doGrouping"></select></span>
                            <div class="time-widgets inline">
                                <input type="text" class="form-control date-selector" placeholder="Start" ng-change="sendSearch()"  bs-datepicker ng-model="searchStart" data-autoclose="1" data-template="/tpl-datepicker" data-icon-left="icon-angle-left" data-icon-right="icon-angle-right" data-placement="bottom-right" />
                                <input type="text" class="form-control date-selector" placeholder="End" ng-change="sendSearch()"  bs-datepicker ng-model="searchEnd" data-autoclose="1" data-min-date="{{searchFields.start}}" data-template="/tpl-datepicker" data-icon-left="icon-angle-left" data-icon-right="icon-angle-right" data-placement="bottom-right" />
                            </div>
                            <button type="button" class="btn btn-default" ng-click="toggleSearch()" ng-class="{'btn-success':searchFiltersSet}"><i class="icon-search"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="app-wrap">
            <form class="calendar-event-search">
                <a class="btn btn-sm clear-fields" ng-click="clearSearchFields()">Clear</a>
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>Keyword Search</label>
                                <input type="text" class="form-control" ng-model="searchFields.keywords" placeholder="eg. Aunt Gretta's Cookies" />
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <label>Tags</label>
                            <div class="form-group ui-select-widget">
                                <ui-select multiple ng-model="searchFields.tags" theme="bootstrap" title="Tags">
                                    <ui-select-match placeholder="Tags">{{ $item.displayText }}</ui-select-match>
                                    <ui-select-choices repeat="tag in eventTagList | propsFilter: {displayText: $select.search}">
                                        <div ng-bind-html="tag.displayText | highlight: $select.search"></div>
                                    </ui-select-choices>
                                </ui-select>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <label>Categories</label>
                            <div class="form-group ui-select-widget">
                                <ui-select multiple ng-model="searchFields.categories" theme="bootstrap" title="Categories">
                                    <ui-select-match placeholder="Categories">{{ $item.displayText }}</ui-select-match>
                                    <ui-select-choices repeat="cat in eventCategoryList | propsFilter: {displayText: $select.search}">
                                        <div ng-bind-html="cat.displayText | highlight: $select.search"></div>
                                    </ui-select-choices>
                                </ui-select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <button type="submit" class="btn btn-block btn-primary" ng-click="sendSearch()">Apply Search Filters</button>
                        </div>
                    </div>
                </div>
            </form>

            <table border="0" cellspacing="0" cellpadding="0" class="ccm-search-results-table event-search-table">
                <thead>
                <tr>
                    <th><a>Title</a></th>
                    <th><a>Calendar</a></th>
                    <th><a>Start Date</a></th>
                    <th><a>Active</a></th>
                    <th ng-show="doGrouping"><a>Occurrences</a></th>
                </tr>
                </thead>
                <tbody>
                    <tr ng-repeat="eventObj in resultData" ng-cloak>
                        <td><a class="text-wrap" modalize="/event_form" data-using="{eventObj:eventObj}">{{ eventObj.title }}</a></td>
                        <td><a ng-href="/dashboard/schedulizer/calendars/manage/{{ eventObj.calendarID }}">{{ eventObj.calendarTitle }}</a></td>
                        <td>{{ momentJS(eventObj.computedStartLocal).format('MMM DD, YYYY / h:mm a') }}</td>
                        <td>{{ eventObj.isActive ? 'Yes' : 'No' }}</td>
                        <td ng-show="doGrouping">{{ eventObj.occurrences }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
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
<script type="text/ng-template" id="/calendar_form">
<?php Loader::packageElement('templates/calendar_form', 'schedulizer', array('calendarObj' => $calendarObj)); ?>
</script>
<script type="text/ng-template" id="/calendry">
<?php Loader::packageElement('templates/calendry', 'schedulizer'); ?>
</script>
<script type="text/ng-template" id="/tpl-tooltip">
    <?php Loader::packageElement('templates/tooltip', 'schedulizer'); ?>
</script>

<!-- Page view -->
<div class="schedulizer-app" ng-controller="CtrlCalendarPage" ng-init="calendarID = <?php echo $calendarObj->getID(); ?>">
    <div class="ccm-dashboard-content-full" ng-class="{'search-open':searchOpen}">

        <div class="not-stupid-header-style">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="pull-left">
                            <?php if($calendarObj->getPermissions()->canEditCalendar()): ?>
                                <h3 modalize="/calendar_form" data-using="{calendarID:<?php echo $calendarObj->getID(); ?>}">
                                    <?php echo $pageTitle; ?><i class="icon-config"></i>
                                </h3>
                            <?php else: ?>
                                <h3 ng-click="warnNoPermission()">
                                    <?php echo $pageTitle; ?><i class="icon-config"></i>
                                </h3>
                            <?php endif; ?>
                        </div>
                        <div class="pull-right">
                            <?php if($calendarObj->getPermissions()->canEditEvents()): ?>
                            <button class="btn btn-primary" modalize="/event_form" data-using="{eventObj:{calendarID:<?php echo $calendarObj->getID(); ?>}}"><?php echo t("Add Event"); ?></button>
                            <?php endif; ?>
                            <?php if($calendarObj->getPermissions()->canManageCalendarPermissions()): ?>
                                <button class="btn btn-default" ng-click="permissionModal('<?php echo $calendarObj->getPermissionCategoryToolsUrlShim('display_list'); ?>')"><?php echo t("Permissions"); ?></button>
                            <?php endif; ?>
                            <button type="button" class="btn btn-default" ng-click="toggleSearch()" ng-class="{'btn-success':searchFiltersSet}"><i class="icon-search"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="app-wrap">
            <form class="search-form">
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

            <div class="calendar-wrap" ng-class="{'updating':updateInProgress}">
                <!-- Note: transclusion of items *inside* calendry represents the EVENT objects on the day cells. -->
                <div calendry="instance" ng-cloak>
                    <?php if($calendarObj->getPermissions()->canEditEvents()): ?>
                        <a class="event-cell" modalize="/event_form" data-using="{eventObj:eventObj}" ng-class="{'is-active':eventObj.isActive,'is-inactive':!eventObj.isActive}" ng-style="{background:eventObj.eventColor,color:helpers.eventFontColor(eventObj.eventColor)}">
                            <span class="dt">{{ eventObj.isAllDay ? 'All Day' : eventObj._moment.format('h:mm a')}}</span> {{eventObj.title}}
                        </a>
                    <?php else: // YIKES, calling $parent.$parent to access two-scopes up is bad... This is in the CtrlCalendar controller though for reference ?>
                        <a ng-click="$parent.$parent.warnNoPermission()" class="event-cell" ng-class="{'is-active':eventObj.isActive,'is-inactive':!eventObj.isActive}" ng-style="{background:eventObj.eventColor,color:helpers.eventFontColor(eventObj.eventColor)}">
                            <span class="dt">{{ eventObj.isAllDay ? 'All Day' : eventObj._moment.format('h:mm a')}}</span> {{eventObj.title}}
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php Loader::packageElement('browser_unsupported', 'schedulizer');
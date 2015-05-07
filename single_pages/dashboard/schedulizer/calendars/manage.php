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

<!-- Page view -->
<div class="schedulizer-app" ng-controller="CtrlCalendarPage" ng-init="calendarID = <?php echo $calendarObj->getID(); ?>">
    <div class="ccm-dashboard-content-full" ng-class="{'search-open':searchOpen}">

        <div class="not-stupid-header-style">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="pull-left">
                            <h3 modalize="/calendar_form" data-using="{calendarID:<?php echo $calendarObj->getID(); ?>}">
                                <?php echo $pageTitle; ?><i class="icon-config"></i>
                            </h3>
                        </div>
                        <div class="pull-right">
                            <?php if($calendarObj->getPermissions()->canAddEvents()): ?>
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
            <form class="calendar-event-search">
                <a class="btn btn-sm clear-fields" ng-click="clearSearchFields()">Clear</a>
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Keyword Search</label>
                                <input type="text" class="form-control" ng-model="searchFields.keywords" placeholder="eg. Aunt Gretta's Cookies" />
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label>Filter By Tags</label>
                            <div class="form-group ui-select-widget">
                                <ui-select multiple ng-model="searchFields.tags" theme="bootstrap" title="Tags">
                                    <ui-select-match placeholder="Tags">{{ $item.displayText }}</ui-select-match>
                                    <ui-select-choices repeat="tag in eventTagList | propsFilter: {displayText: $select.search}">
                                        <div ng-bind-html="tag.displayText | highlight: $select.search"></div>
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
                    <a class="event-cell" modalize="/event_form" data-using="{eventObj:eventObj}" ng-style="{background:eventObj.eventColor,color:helpers.eventFontColor(eventObj.eventColor)}">
                        <span class="dt">{{ eventObj.isAllDay ? 'All Day' : eventObj._moment.format('h:mm a')}}</span> {{eventObj.title}}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

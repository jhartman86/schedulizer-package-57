<script type="text/ng-template" id="/calendar_form">
<?php Loader::packageElement('templates/calendar_form', 'schedulizer'); ?>
</script>

<div class="schedulizer-app">
    <div class="ccm-dashboard-content-full">
        <div class="not-stupid-header-style">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="pull-left">
                            <h3><?php echo $pageTitle; ?></h3>
                        </div>
                        <?php if($permissionsObj->canCreateCalendar()): ?>
                        <div class="pull-right">
                            <button class="btn btn-primary" modalize="/calendar_form"><?php echo t("Create Calendar"); ?></button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <table border="0" cellspacing="0" cellpadding="0" class="ccm-search-results-table">
            <thead>
            <tr>
                <th><span class="ccm-search-results-checkbox"><input type="checkbox" data-search-checkbox="select-all" class="ccm-flat-checkbox" /></span></th>
                <th><a>Calendar</a></th>
                <th><a>Timezone</a></th>
                <th><a>Created</a></th>
                <th><a>Modified</a></th>
                <th><a>Owner</a></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach($calendars AS $calendarObj): ?>
                <tr>
                    <td><span class="ccm-search-results-checkbox"><input type="checkbox" class="ccm-flat-checkbox" /></span></td>
                    <td><a href="<?php echo View::url('/dashboard/schedulizer/calendars/manage/', $calendarObj->getID()); ?>"><?php echo $calendarObj; ?></a></td>
                    <td><?php echo $calendarObj->getDefaultTimezone(); ?></td>
                    <td><?php echo $conversionHelper->localizeWithFormat($calendarObj->getCreatedUTC(), $calendarObj->getCalendarTimezoneObj(), 'M d, Y H:i:s'); ?></td>
                    <td><?php echo $conversionHelper->localizeWithFormat($calendarObj->getCreatedUTC(), $calendarObj->getCalendarTimezoneObj(), 'M d, Y H:i:s'); ?></td>
                    <td><?php echo 'todo'; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
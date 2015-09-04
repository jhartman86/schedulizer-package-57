<script type="text/ng-template" id="/calendar_form">
<?php Loader::packageElement('templates/calendar_form', 'schedulizer'); ?>
</script>

<div class="schedulizer-app">
    <div class="ccm-dashboard-content-full search-page">
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

        <div class="app-wrap">
            <table border="0" cellspacing="0" cellpadding="0" class="ccm-search-results-table search-table">
                <thead>
                <tr>
                    <th><span>Calendar</span></th>
                    <th><span>Timezone</span></th>
                    <th><span>Created</span></th>
                    <th><span>Modified</span></th>
                    <th><span>Owner</span></th>
                </tr>
                </thead>
                <tbody>
                <?php if(!empty($calendars)): foreach($calendars AS $calendarObj): ?>
                    <tr>
                        <td><a href="<?php echo View::url('/dashboard/schedulizer/calendars/manage/', $calendarObj->getID()); ?>"><?php echo $calendarObj; ?></a></td>
                        <td><?php echo $calendarObj->getDefaultTimezone(); ?></td>
                        <td><?php echo $conversionHelper->localizeWithFormat($calendarObj->getCreatedUTC(), $calendarObj->getCalendarTimezoneObj(), 'M d, Y H:i:s'); ?></td>
                        <td><?php echo $conversionHelper->localizeWithFormat($calendarObj->getCreatedUTC(), $calendarObj->getCalendarTimezoneObj(), 'M d, Y H:i:s'); ?></td>
                        <td>
                            <?php
                            $ownerUIObj = $calendarObj->getCalendarOwnerUserInfoObj();
                            if( is_object($ownerUIObj) ){
                                echo $ownerUIObj->getUserName();
                            }else{
                                echo 'Unassigned';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr>
                        <td colspan="5" class="text-center">
                            <?php if($permissionsObj->canCreateCalendar()){ ?>
                                <a class="lead" modalize="/calendar_form"><?php echo t('Create Your First Calendar'); ?></a>
                            <?php }else{ ?>
                                <h3><?php echo t('No calendars created yet'); ?></h3>
                            <?php } ?>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

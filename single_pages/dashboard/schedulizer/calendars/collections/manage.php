<!-- Templates -->
<script type="text/ng-template" id="/collection_form">
    <?php Loader::packageElement('templates/collection_form', 'schedulizer'); ?>
</script>
<script type="text/ng-template" id="/collection_event_form">
    <?php Loader::packageElement('templates/collection_event_form', 'schedulizer'); ?>
</script>
<script type="text/ng-template" id="/tpl-tooltip">
    <?php Loader::packageElement('templates/tooltip', 'schedulizer'); ?>
</script>

<div class="schedulizer-app" ng-controller="CtrlCollectionPage" ng-init="collectionID = <?php echo $collectionObj->getID(); ?>">
    <div class="ccm-dashboard-content-full search-page">
        <div class="not-stupid-header-style">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="pull-left">
                            <?php if( $isMasterCollection ): // If IS a master collection, shouldn't be able to edit it! ?>
                                <h3><?php echo $pageTitle; ?><i class="icon-config"></i></h3>
                            <?php else: ?>
                                <h3 modalize="/collection_form" data-using="{collectionID:<?php echo $collectionObj->getID(); ?>}">
                                    <?php echo $pageTitle; ?><i class="icon-config"></i>
                                </h3>
                            <?php endif; ?>
                        </div>
                        <div class="pull-right" ng-cloak>
                            <div ng-show="boxesAreChecked">
                                <button type="button" class="btn btn-default" ng-click="approveLatest()" bs-tooltip="'Approve all selected at their latest versions'" data-template="/tpl-tooltip" data-placement="bottom">Approve Latest</button>
                                <button type="button" class="btn btn-default" ng-click="unapprove()" bs-tooltip="'Unapprove all selected events'" data-template="/tpl-tooltip" data-placement="bottom">Unapprove</button>
                                <button type="button" class="btn btn-success" ng-click="makeAutoApprovable()" bs-tooltip="'When auto-approvable, saving an event updates the approved version immediately. This immediately approves all latest event versions.'" data-template="/tpl-tooltip" data-placement="bottom">Make Auto-Approvable</button>
                            </div>
                            <div ng-hide="boxesAreChecked">
                                <span select-wrap><select class="form-control" ng-change="refreshEventList()" ng-options="opt.id as opt.title for opt in calendarList" ng-model="filters.calendarID"></select></span>
                                <button type="button" class="btn btn-default" ng-class="{'btn-success':filters.discrepancies}" ng-click="toggleDiscrepanciesFilter()" bs-tooltip="'Filter to only show events where the Approved version does not equal the Latest'" data-template="/tpl-tooltip" data-placement="bottom">Approval Discrepancies</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="app-wrap">
            <table ng-cloak border="0" cellspacing="0" cellpadding="0" class="ccm-search-results-table search-table tbl-collection-event-list">
                <thead>
                <tr>
                    <th rowspan="2" checkbox-wrap><input type="checkbox" ng-model="checkToggleAll" ng-change="toggleAllCheckboxes()" /></th>
                    <th rowspan="2"><span>Event</span></th>
                    <th rowspan="2"><span>Calendar</span></th>
                    <th rowspan="2" class="text-center"><span>Active</span></th>
                    <th colspan="2" class="split-row divided"><span>Version</span></th>
                    <th rowspan="2" class="text-center"><span>Approval</span></th>
                </tr>
                <tr style="white-space:nowrap;">
                    <th class="split-row"><span>Latest</span></th>
                    <th class="split-row"><span>Approved</span></th>
                </tr>
                </thead>
                <tbody ng-show="eventList.length">
                    <tr ng-repeat="event in eventList">
                        <td checkbox-wrap><input type="checkbox" ng-model="checkboxes[event.eventID]" ng-init="checkboxes[event.eventID] = false" /></td>
                        <td class="event-title"><a modalize="/collection_event_form" data-using="{collectionID:collectionID,eventID:event.eventID}">{{ event.eventTitle }}</a></td>
                        <td><span>{{ event.calendarTitle }}</span></td>
                        <td class="text-center"><span class="active-status" ng-class="{'active':event.isActive,'inactive':!event.isActive}"></span></td>
                        <td class="text-center"><span>{{ event.versionID }}</span></td>
                        <td class="text-center"><span>{{ event.approvedVersionID || '--' }}</span></td>
                        <td class="text-center">
                            <span select-wrap>
                                <select class="form-control" ng-change="updateEventApproval(event)" ng-options="opt.value as opt.label for opt in approvalList" ng-model="event.autoApprovable"></select>
                            </span>
                        </td>
                    </tr>
                </tbody>
                <tbody ng-hide="eventList.length">
                    <tr>
                        <td colspan="7"><p class="lead text-center">No Results</p></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
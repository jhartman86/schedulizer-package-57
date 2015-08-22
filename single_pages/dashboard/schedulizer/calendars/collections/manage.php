<!-- Templates -->
<script type="text/ng-template" id="/collection_event_form">
    <?php Loader::packageElement('templates/collection_event_form', 'schedulizer'); ?>
</script>

<div class="schedulizer-app" ng-controller="CtrlCollectionPage" ng-init="collectionID = <?php echo $collectionObj->getID(); ?>">
    <div class="ccm-dashboard-content-full">
        <div class="not-stupid-header-style">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="pull-left">
                            <h3 modalize="/calendar_form" data-using="{calendarID:<?php echo $collectionObj->getID(); ?>}">
                                <?php echo $pageTitle; ?><i class="icon-config"></i>
                            </h3>
                        </div>
                        <div class="pull-right">
                            <div ng-show="boxesAreChecked">
                                With Checked:
                                <button type="button" class="btn btn-default" ng-click="approveLatest()">Approve Latest</button>
                                <button type="button" class="btn btn-default" ng-click="unapprove()">Unapprove</button>
                            </div>
                            <div ng-hide="boxesAreChecked">
                                <button type="button" class="btn btn-default" ng-click="toggleSearch()" ng-class="{'btn-success':searchFiltersSet}"><i class="icon-search"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <table ng-cloak border="0" cellspacing="0" cellpadding="0" class="ccm-search-results-table tbl-collection-list">
            <thead>
                <tr>
                    <th rowspan="2"><span><input type="checkbox" ng-model="checkToggleAll" ng-change="toggleAllCheckboxes()" /></span></th>
                    <th rowspan="2"><span>Event</span></th>
                    <th rowspan="2"><span>Calendar</span></th>
                    <th colspan="2" class="split-row divided"><span>Version</span></th>
                    <th rowspan="2"><span>Active</span></th>
                </tr>
                <tr style="white-space:nowrap;">
                    <th class="split-row"><span>Latest</span></th>
                    <th class="split-row"><span>Approved</span></th>
                </tr>
            </thead>
            <tbody>
                <tr ng-repeat="event in eventList">
                    <td><span><input type="checkbox" ng-model="checkboxes[event.eventID]" ng-init="checkboxes[event.eventID] = false" /></span></td>
                    <td><a modalize="/collection_event_form" data-using="{collectionID:collectionID,eventID:event.eventID}">{{ event.eventTitle }}</a></td>
                    <td><span>{{ event.calendarTitle }}</span></td>
                    <td class="text-center"><span>{{ event.versionID }}</span></td>
                    <td class="text-center"><span>{{ event.approvedVersionID }}</span></td>
                    <td class="text-center"><span class="active-status" ng-class="{'active':event.isActive,'inactive':!event.isActive}"></span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
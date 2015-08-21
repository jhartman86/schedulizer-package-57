<form name="frmCollectionEvent" class="collection-event-form" ng-controller="CtrlCollectionEventForm">
    <div class="col-left-bar">
        <ul class="list-unstyled">
            <li ng-repeat="eventVersionObj in versionList" ng-class="{active:(eventVersionObj.versionID == approvedVersion.approvedVersionID)}">
                <a ng-click="viewVersion(eventVersionObj)">Version {{ eventVersionObj.versionID }}</a>
            </li>
        </ul>
    </div>
    <div class="col-right-main">
        <div class="version-stat">
            <button type="button" class="btn btn-success" ng-click="approveVersion()">Approve Version {{ viewingVersion.versionID }}</button>
        </div>

        {{ viewingVersion }}
    </div>
</form>
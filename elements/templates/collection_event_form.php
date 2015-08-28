<form name="frmCollectionEvent" class="collection-event-form" ng-controller="CtrlCollectionEventForm">
    <div class="col-left-bar">
        <ul class="list-unstyled">
            <li ng-repeat="eventVersionObj in versionList" ng-class="{active:(eventVersionObj.versionID == approvedVersion.approvedVersionID),viewing:(eventVersionObj.versionID == viewingVersion.versionID)}">
                <a ng-click="viewVersion(eventVersionObj)">Version {{ eventVersionObj.versionID }}</a>
            </li>
        </ul>
    </div>
    <div class="col-right-main" ng-cloak>
        <div ng-hide="viewingVersion && approvedVersion">
            <h5 class="lead text-center">No versions have been approved yet :(</h5>
        </div>

        <div ng-show="viewingVersion && approvedVersion">
            <div class="version-stat">
                <button type="button" class="btn btn-success" ng-click="approveVersion(viewingVersion)">Approve Version {{ viewingVersion.versionID }}</button>
            </div>

            <h6 class="lead">{{ viewingVersion.title }}</h6>

            <h6 class="lead">Description</h6>
            <div ng-bind-html="viewingVersion.description"></div>

            <h6 class="lead">Photo</h6>
            <div ng-show="versionThumbnail">
                <img class="img-responsive" ng-src="{{ versionThumbnail }}" />
            </div>
            <div ng-show="!versionThumbnail">
                <h5>No thumbnail image available</h5>
            </div>
        </div>
    </div>
</form>
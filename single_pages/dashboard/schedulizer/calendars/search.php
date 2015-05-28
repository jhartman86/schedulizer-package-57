<div class="schedulizer-app" ng-controller="CtrlSearchPage">
    <div class="cmm-dashboard-content-full" ng-class="{'search-open':searchOpen}">
        <div class="not-stupid-header-style">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="pull-left">
                            <h3><?php echo $pageTitle; ?></h3>
                        </div>
                        <div class="pull-right">
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

<!--            <table border="0" cellspacing="0" cellpadding="0" class="ccm-search-results-table">-->
<!--                <thead>-->
<!--                <tr>-->
<!--                    <th><a>Title</a></th>-->
<!--                    <th><a>Calendar</a></th>-->
<!--                    <th><a>Owner</a></th>-->
<!--                    <th><a>Start Date</a></th>-->
<!--                    <th><a>Repeating</a></th>-->
<!--                </tr>-->
<!--                </thead>-->
<!--                <tbody>-->
<!---->
<!--                </tbody>-->
<!--            </table>-->
        </div>
    </div>
</div>
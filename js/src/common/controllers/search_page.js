angular.module('schedulizer.app').

    controller('CtrlSearchPage', ['$rootScope', '$scope', '$http', '$cacheFactory', 'API', '_moment',
        function( $rootScope, $scope, $http, $cacheFactory, API, _moment ){

            var _cache  = $cacheFactory('searchCalendarData'),
                _fields = [ // Tell the API what fields we want back
                    'eventID', 'eventTimeID', 'calendarID', 'calendarTitle', 'title',
                    'eventColor', 'isAllDay', 'isSynthetic', 'computedStartUTC',
                    'computedStartLocal', 'isActive'
                ];

            // Pass MomentJS to the view via scope
            $scope.momentJS = _moment;

            $scope.resultData           = [];
            $scope.updateInProgress     = false;
            $scope.searchOpen           = false;
            $scope.eventTagList         = [];
            $scope.eventCategoryList    = [];
            $scope.searchFiltersSet     = false;
            $scope.isActiveOptions      = [{label:'Include Inactive', value: true}, {label:'Active Only', value: false}];
            $scope.showAllEvents        = true;
            $scope.groupingOptions      = [{label:'Group Events', value: true}, {label:'Show Repeating', value: false}];
            $scope.doGrouping           = true;
            $scope.searchStart          = _moment();
            $scope.searchEnd            = _moment().add(1, 'month');
            $scope.searchFields         = {keywords:null, tags:[], categories:[], calendar:null};

            $scope.toggleSearch = function(){
                $scope.searchOpen = !$scope.searchOpen;
            };

            API.eventTags.query().$promise.then(function( results ){
                $scope.eventTagList = results;
            });

            API.eventCategories.query().$promise.then(function( results ){
                $scope.eventCategoryList = results;
            });

            API.calendarList.get().$promise.then(function( results ){
                results.unshift({id:null, title:'Filter By Calendar'});
                $scope.calendarList = results;
            });

            /**
             * Turn the search button green if any search fields are filled in to indicate
             * to the user that search filters are being applied.
             */
            $scope.$watch('searchFields', function(val){
                var filtersSet = false;
                if( val.keywords ){filtersSet = true;}
                if( val.tags.length !== 0 ){filtersSet = true;}
                if( val.categories.length !== 0 ){filtersSet = true;}
                if( +(val.calendar) >= 1 ){filtersSet = true;}
                $scope.searchFiltersSet = filtersSet;
            }, true);

            /**
             * In the paramaterizedSearchFields below, we have to use a .map function
             * with a callback that just returns the ID. Instead of writing it multiple
             * times, just use this since it always returns the same .id property.
             * @param obj
             * @returns {obj.id|*}
             * @private
             */
            function _mapCallbackReturnID( obj ){
                return obj.id;
            }

            /**
             * We need to pre-process the $scope.searchFields and format them for
             * querying; this does so. Also, this determines whether to add 'includeinactives'
             * to the query string or not.
             * @returns {{keywords: null, tags: *}}
             */
            function parameterizedSearchFields(){
                return angular.extend({
                    calendars:  $scope.searchFields.calendar,
                    start:      _moment($scope.searchStart).format('YYYY-MM-DD'),
                    end:        _moment($scope.searchEnd).format('YYYY-MM-DD'),
                    fields:     _fields.join(','),
                    keywords:   $scope.searchFields.keywords,
                    tags:       $scope.searchFields.tags.map(_mapCallbackReturnID).join(','),
                    categories: $scope.searchFields.categories.map(_mapCallbackReturnID).join(',')
                },
                    // Set these this way because it only matters if they're PRESENT, not their actual value
                    ($scope.showAllEvents ? {includeinactives:true} : {}),
                    ($scope.doGrouping ? {grouping:true} : {})
                );
            }

            /**
             * Fetch updates
             * @returns {HttpPromise}
             * @private
             */
            function _fetch(){
                return $http.get(API._routes.generate('api.eventList'), {
                    cache:  _cache,
                    params: parameterizedSearchFields()
                });
            }

            /**
             * Trigger refreshing the calendar.
             * @private
             */
            function _updateResults(){
                $scope.updateInProgress = true;
                _cache.removeAll();
                _fetch().success(function( resp ){
                    $scope.resultData = resp;
                    $scope.updateInProgress = false;
                }).error(function( data, status, headers, config ){
                    $scope.updateInProgress = false;
                    console.warn(status, 'Failed fetching data');
                });
                $scope.searchOpen = false;
            }

            /**
             * Clear the search fields and update calendar.
             */
            $scope.clearSearchFields = function(){
                $scope.searchFields = {
                    keywords: null,
                    tags: [],
                    categories: []
                };
                _updateResults();
            };

            /**
             * Method to trigger calendar refresh callable from the scope.
             * @type {_updateResults}
             */
            $scope.sendSearch = _updateResults;

            /**
             * When an event is saved, it emits this event. So watch it
             * and update if necessary.
             */
            $rootScope.$on('calendar.refresh', _updateResults);

            // Once everything is wired up, now do first-run load...
            _updateResults();
        }
    ]);
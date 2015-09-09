angular.module('schedulizer.app').

    controller('CtrlCollectionPage', ['$rootScope', '$scope', '$q', '$http', 'API', '_moment',
        function( $rootScope, $scope, $q, $http, API, _moment ){

            $scope.collectionID     = null; // set via ng-init
            $scope.checkToggleAll   = false;
            $scope.checkboxes       = {};
            $scope.eventList        = [];
            $scope.searchStart      = _moment();
            $scope.searchEnd        = _moment().add(1, 'month');
            $scope.filters          = {
                grouping:           true,
                includeinactives:   true,
                calendars:          null,
                discrepancies:      null,
                fields:             'eventID,versionID,title,calendarTitle,isActive'
            };

            function checkedEventIDs(){
                var _checked = [];
                for(var key in $scope.checkboxes){
                    if($scope.checkboxes[key] === true){
                        _checked.push(key);
                    }
                }
                return _checked;
            }

            $scope.toggleDiscrepanciesFilter = function(){
                $scope.filters.discrepancies = $scope.filters.discrepancies === true ? null : true;
                $scope.refreshEventList();
            };

            $scope.$watchCollection('checkboxes', function(){
                var ids = checkedEventIDs();
                $scope.boxesAreChecked = (ids.length >= 1);
            });

            $scope.toggleAllCheckboxes = function(){
                for(var k in $scope.checkboxes){
                    $scope.checkboxes[k] = $scope.checkToggleAll;
                }
            };

            $scope.refreshEventList = function(){
                $http.get(API._routes.generate('api.eventList'), {
                    cache:false,
                    params: angular.extend({
                        start:  _moment($scope.searchStart).format('YYYY-MM-DD'),
                        end:    _moment($scope.searchEnd).format('YYYY-MM-DD'),
                        dashboard_collection_search: $scope.collectionID
                    }, $scope.filters)
                }).then(function( resp ){
                    $scope.checkboxes = {};
                    $scope.eventList = resp.data;
                    $scope.checkToggleAll = false;
                });
            };

            /**
             * Acts "statically"; doesn't work on a stateful resource, but instead
             * take existing stateful info from the UI and build the calls on the fly.
             */
            $scope.approveLatest = function(){
                API.collectionEvent.approveLatestVersions({
                    collectionID: $scope.collectionID,
                    events: checkedEventIDs()
                }, function(){
                    $scope.refreshEventList();
                });

            };

            /**
             * Acts "statically"; doesn't work on a stateful resource, but instead
             * take existing stateful info from the UI and build the calls on the fly.
             */
            $scope.unapprove = function(){
                API.collectionEvent.unapprove({
                    collectionID: $scope.collectionID,
                    events: checkedEventIDs().join(',')
                }, function(){
                    $scope.refreshEventList();
                });
            };

            /**
             * Since ng-init doesn't set the value of collectionID on the scope BEFORE
             * everything gets instantiated, we have to do this ugly watch and unbind
             * timing stuff.
             * @type {*|function()}
             */
            var unbindAfterOneCycle = $scope.$watch('collectionID', function( val ){
                if( val ){
                    unbindAfterOneCycle();

                    $q.all([
                        API.collection.get({id:val}).$promise,
                        API.calendarList.get().$promise
                    ]).then(function( responses ){
                        // Set collection object on the scope
                        $scope.collectionObj = responses[0];
                        var calendarList = responses[1].filter(function( calObj ){
                            return $scope.collectionObj.collectionCalendars.indexOf(calObj.id) !== -1;
                        });
                        calendarList.unshift({id:null, title:'Filter By Calendar'});
                        $scope.calendarList = calendarList;
                        $scope.refreshEventList();
                    });
                }
            });

            /**
             * When emitted on rootScope, refresh the event list
             */
            $rootScope.$on('collection:refreshEventList', $scope.refreshEventList);


            $scope.approvalList = [
                {value:false, label:'Required'},
                {value:true, label:'Auto'}
            ];

            /**
             * Change the autoApprovable setting for a SINGLE event. Note, the event argument
             * is NOT a resource, since we're using the standard event_list query via standard
             * $http call.
             * @param eventResource
             */
            $scope.updateEventApproval = function( event ){
                API.collectionEvent.saveSingleAutoApprovable(event, function(){
                    // Since the response from the server is just an HTTP header code, the resource
                    // in this callback isn't an "updated" version. But to fake an update to the user,
                    // we can just set approvedVersionID to the versionID property (which should be
                    // accurate anyways)
                    event.approvedVersionID = event.versionID;
                });
            };

            /**
             * Change the autoApprovable settings for multiple events
             */
            $scope.makeAutoApprovable = function(){
                API.collectionEvent.saveMultiAutoApprovable({
                    collectionID: +($scope.collectionID),
                    events: checkedEventIDs()
                }, $scope.refreshEventList);
            };
        }
    ]);

angular.module('schedulizer.app').

    controller('CtrlCollectionPage', ['$rootScope', '$scope', '$q', 'API',
        function( $rootScope, $scope, $q, API ){

            $scope.collectionID     = null; // set via ng-init
            $scope.checkToggleAll   = false;
            $scope.checkboxes       = {};
            $scope.eventList        = [];
            $scope.filters          = {
                calendarID: null,
                discrepancies: null
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
                API.collectionEvent.allEventsList(angular.extend({
                    collectionID : $scope.collectionID
                }, $scope.filters), function( resp ){
                    $scope.checkboxes = {};
                    $scope.eventList = resp;
                    $scope.checkToggleAll = false;
                });
            };

            $scope.approveLatest = function(){
                API.collectionEvent.approveLatestVersions({
                    collectionID: $scope.collectionID,
                    events: checkedEventIDs()
                }, function(){
                    $scope.refreshEventList();
                });

            };

            $scope.unapprove = function(){
                API.collectionEvent.unapprove({
                    collectionID: $scope.collectionID,
                    events: checkedEventIDs().join(',')
                }, function(){
                    $scope.refreshEventList();
                });
            };

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

            $rootScope.$on('collection:refreshEventList', $scope.refreshEventList);


            $scope.approvalList = [
                {value:false, label:'Required'},
                {value:true, label:'Auto'}
            ];

            /**
             * Change the autoApprovable setting for a SINGLE event
             * @param eventResource
             */
            $scope.updateEventApproval = function( eventResource ){
                eventResource.$saveSingleAutoApprovable(function(resource){
                    // Since the response from the server is just an HTTP header code, the resource
                    // in this callback isn't an "updated" version. But to fake an update to the user,
                    // we can just set approvedVersionID to the versionID property (which should be
                    // accurate anyways)
                    resource.approvedVersionID = resource.versionID;
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

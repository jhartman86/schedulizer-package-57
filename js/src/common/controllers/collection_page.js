angular.module('schedulizer.app').

    controller('CtrlCollectionPage', ['$rootScope', '$scope', '$q', 'API',
        function( $rootScope, $scope, $q, API ){

            $scope.collectionID     = null; // set via ng-init
            $scope.checkToggleAll   = false;
            $scope.checkboxes       = {};
            $scope.eventList        = [];
            $scope.filterByCalendar = null;

            function checkedEventIDs(){
                var _checked = [];
                for(var key in $scope.checkboxes){
                    if($scope.checkboxes[key] === true){
                        _checked.push(key);
                    }
                }
                return _checked;
            }

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
                API.collection.allEventsList({
                    id          : $scope.collectionID,
                    calendarID  : $scope.filterByCalendar
                }, function( resp ){
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
                    $scope.refreshEventList();

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
                    });
                }
            });

            $rootScope.$on('collection:refreshEventList', $scope.refreshEventList);
        }
    ]);

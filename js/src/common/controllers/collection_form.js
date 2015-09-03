angular.module('schedulizer.app').

    controller('CtrlCollectionForm', ['$window', '$rootScope', '$scope', 'API', 'ModalManager', 'Alerter',
        function( $window, $rootScope, $scope, API, ModalManager, Alerter ){

            // Show loading message
            $scope._ready           = true;
            $scope._requesting      = false;
            $scope.selectedCals     = {};
            $scope.checkToggleAll   = false;

            // Load list of available calendars
            API.calendarList.get().$promise.then(function( calendarList ){
                $scope.calendarList = calendarList;

                if( ModalManager.data.collectionID ){
                    API.collection.get({id: ModalManager.data.collectionID}, function( resp ){
                        $scope.entity = resp;
                        $scope.entity.collectionCalendars.forEach(function( calID ){
                            $scope.selectedCals[calID] = true;
                        });
                    });
                }else{
                    $scope.entity = new API.collection({
                        collectionCalendars: []
                    });
                }
            });

            $scope.toggleAllCheckboxes = function(){
                for(var k in $scope.selectedCals){
                    $scope.selectedCals[k] = $scope.checkToggleAll;
                }
            };

            $scope.submitHandler = function(){
                $scope._requesting = true;

                $scope.entity.collectionCalendars = Object.keys($scope.selectedCals).filter(function( calID ){
                    return $scope.selectedCals[calID];
                }).map(function(v){ return +(v); });

                ($scope.entity.id ? $scope.entity.$update() : $scope.entity.$save()).then(
                    function( resp ){
                        $scope._requesting = false;
                        $window.location.href = API._routes.generate('dashboard', ['calendars', 'collections', 'manage', resp.id]);
                    }
                );
            };

            $scope.deleteCollection = function(){
                $scope.entity.$delete(function(){
                    // If this function gets called, server responded w/ header code 20{x}, meaning "OK"
                    Alerter.add({msg:'Collection Removed!', success: true});
                    $window.location.href = API._routes.generate('dashboard', ['calendars', 'collections']);
                });
            };
        }
    ]);

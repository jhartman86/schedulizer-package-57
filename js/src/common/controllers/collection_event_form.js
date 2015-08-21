angular.module('schedulizer.app').

    controller('CtrlCollectionEventForm', ['$rootScope', '$scope', '$q', 'ModalManager', 'API',
        function( $rootScope, $scope, $q, ModalManager, API ){

            var _requests = [
                API.collectionEvent.versionList({
                    eventID: ModalManager.data.eventID
                }).$promise,

                API.collectionEvent.approvedVersion({
                    eventID: ModalManager.data.eventID,
                    collectionID: ModalManager.data.collectionID
                }).$promise
            ];

            $q.all(_requests).then(function( results ){
                $scope.versionList      = results[0];
                $scope.approvedVersion  = results[1];

                if( angular.isObject($scope.approvedVersion) ){
                    $scope.versionList.forEach(function(obj, key){
                        if( +(obj.versionID) === +($scope.approvedVersion.approvedVersionID) ){
                            $scope.viewVersion(obj);
                        }
                    });
                }
            });

            $scope.viewVersion = function( version ){
                $scope.viewingVersion = version;
            };

            $scope.approveVersion = function(){
                var $cev = new API.collectionEvent({
                    collectionID: ModalManager.data.collectionID,
                    eventID: ModalManager.data.eventID,
                    approvedVersionID: $scope.viewingVersion.versionID
                });

                $cev.$save(function( resp ){
                    $scope.approvedVersion = resp;
                    $rootScope.$emit('collection:refreshEventList');
                    ModalManager.classes.open = false;
                });
            };

        }
    ]);
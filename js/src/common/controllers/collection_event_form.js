angular.module('schedulizer.app').

    controller('CtrlCollectionEventForm', ['$rootScope', '$scope', '$q', 'ModalManager', 'API',
        function( $rootScope, $scope, $q, ModalManager, API ){

            $scope.versionThumbnail = null;

            /**
             * Queue requests for
             * 1) Getting the list of ALL versions of the event
             * 2) Get the approved version record (*if* it is approved)
             * @type {*[]}
             * @private
             */
            var _requests = [
                API.collectionEvent.versionList({
                    eventID: ModalManager.data.eventID
                }).$promise,

                API.collectionEvent.approvedVersion({
                    eventID: ModalManager.data.eventID,
                    collectionID: ModalManager.data.collectionID
                }).$promise
            ];

            /**
             * When requests are completed, then move on...
             */
            $q.all(_requests).then(function( results ){
                $scope.versionList      = results[0];
                $scope.approvedVersion  = results[1] || null;

                if( angular.isObject($scope.approvedVersion) ){
                    $scope.versionList.forEach(function( versionObj ){
                        if( +(versionObj.versionID) === +($scope.approvedVersion.approvedVersionID) ){
                            $scope.viewVersion(versionObj);
                        }
                    });
                }
            });

            /**
             * View details for a specific version.
             * @param version
             */
            $scope.viewVersion = function( version ){
                $scope.viewingVersion = version;
            };

            $scope.$watch('viewingVersion', function( v ){
                //console.log(v);
                if( v ){
                    API.event.image_path({id: +(v.eventID)}, function( resp ){
                        if( angular.isObject(resp) ){
                            $scope.versionThumbnail = resp.url;
                        }else{
                            $scope.versionThumbnail = null;
                        }
                    });
                }
            });

            /**
             * Approve a version
             */
            $scope.approveVersion = function( versionToApprove ){
                (new API.collectionEvent({
                    collectionID        : +(ModalManager.data.collectionID),
                    eventID             : +(ModalManager.data.eventID),
                    approvedVersionID   : +(versionToApprove.versionID)
                })).$save().then(function( resp ){
                    $scope.approvedVersion = resp;
                    $rootScope.$emit('collection:refreshEventList');
                    ModalManager.classes.open = false;
                });
            };

        }
    ]);
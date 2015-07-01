angular.module('schedulizer.app').

    controller('CtrlSettingsPage', ['$scope', 'API',
        function( $scope, API ){

            $scope.activeTab = 0;

            $scope.activateTab = function( index ){
                $scope.activeTab = index;
            };

            $scope.categoriesList = [];
            $scope.tagsList       = [];

            // Load categories
            API.eventCategories.query().$promise.then(function( resp ){
                $scope.categoriesList = resp;
            });

            // Load tags
            API.eventTags.query().$promise.then(function( resp ){
                $scope.tagsList = resp;
            });

            $scope.remove = function( listType, $index ){
                listType[$index].$delete().then(function(){
                    listType.splice($index,1);
                });
            };

            $scope.persist = function( listType, $index ){
                if( listType[$index].id ){
                    listType[$index].$update();
                }else{
                    listType[$index].$save();
                }
            };

            $scope.addCategory = function(){
                $scope.categoriesList.push( new API.eventCategories() );
            };

            $scope.addTag = function(){
                $scope.tagsList.push( new API.eventTags() );
            };
        }
    ]);

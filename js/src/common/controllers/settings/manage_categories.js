angular.module('schedulizer.app').

    controller('CtrlManageCategories', ['$scope', 'API',
        function( $scope, API ){

            $scope.categoriesList = [];

            API.eventCategories.query().$promise.then(function( resp ){
                $scope.categoriesList = resp;
            });

            $scope.remove = function( $index ){
                $scope.categoriesList[$index].$delete().then(function(){
                    $scope.categoriesList.splice($index,1);
                });
            };

            $scope.persist = function( $index ){
                ($scope.categoriesList[$index].id ? $scope.categoriesList[$index].$update() : $scope.categoriesList[$index].$save()).then(function( resp ){
                    console.log(resp);
                });
            };

            $scope.addCategory = function(){
                $scope.categoriesList.push( new API.eventCategories());
            };

        }
    ]);
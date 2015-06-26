angular.module('schedulizer.app').

    /**
     * Will automatically initialize modalWindow directive; and we don't have to worry about
     * leaving this in HTML somewhere.
     */
    run([function(){
        angular.element(document.body).append('<div alerter ng-class="{open:alerts.length}"><div class="alert-item" ng-repeat="alert in alerts" ng-class="{\'type-danger\':alert.danger,\'type-success\':alert.success}">{{ alert.msg }}<span alert-closer ng-click="removeAlert($index)">&#10005;</span></div></div>');
    }]).

    factory('Alerter', ['$q', '$timeout', function( $q, $timeout ){
        var service = {
            stack: []
        };

        service.add = function( alert ){
            // Push onto queue
            service.stack.push(alert);
            // Rig up the timeout to auto-remove it from the queue
            $timeout(function(){
                service.stack.splice(service.stack.indexOf(alert), 1);
            }, alert.duration || 3000);
        };

        service.removeByIndex = function( $index ){
            service.stack.splice($index, 1);
        };

        return service;
    }]).

    directive('alerter', [function(){

        function _link( scope, $element, attrs ){
            // Everything is bound via controller scopes...
        }

        return {
            link: _link,
            scope: true,
            controller: ['$scope', 'Alerter', function( $scope, Alerter ){
                $scope.service      = Alerter;
                $scope.alerts       = [];
                $scope.removeAlert  = $scope.service.removeByIndex;

                $scope.$watch('service', function(){
                    $scope.alerts = $scope.service.stack;
                }, true);
            }]
        };
    }]);
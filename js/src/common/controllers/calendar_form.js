angular.module('schedulizer.app').

    controller('CtrlCalendarForm', ['$scope', '$q', '$window', 'ModalManager', 'API',
        function( $scope, $q, $window, ModalManager, API ){

            // Show loading message
            $scope._ready       = false;
            $scope._requesting  = false;

            // Create requests promise queue, always loading available timezones list
            var _requests = [
                API.timezones.get().$promise, // full timezones list
                API.timezones.defaultTimezone().$promise  // default timezone (config setting)
            ];

            // If calendarID is available; try to load it, and push to the requests queue
            if( ModalManager.data.calendarID ){
                _requests.push(API.calendar.get({id:ModalManager.data.calendarID}).$promise);
            }

            // When all requests are finished; 'returned' is an array of
            // promises containing the query data whereas:
            // returned[0] = array of all timezones available
            // returned[1] = object with default timezone from config settings
            // returned[2] = the calendar, OR null
            $q.all(_requests).then(function( returned ){
                var ownerPickerNode = document.querySelector('[data-calendar-owner-picker]'),
                    ownerPickedID   = null;

                // The ownerPickerNode is definitely not always guaranteed to exist since some
                // users won't have access_user_search (thus we don't render the user picker in the UI)
                if( ownerPickerNode ){
                    ownerPickedID = +(ownerPickerNode.getAttribute('data-default-owner-id') || 1);
                }

                $scope.timezoneOptions = returned[0];
                $scope.entity = returned[2] || new API.calendar({
                    defaultTimezone: $scope.timezoneOptions[$scope.timezoneOptions.indexOf(returned[1].name)],
                    ownerID: ownerPickedID
                });
                $scope._ready = true;

                /**
                 * Concrete5-specific stuff...
                 */
                if( ownerPickerNode ){
                    jQuery(ownerPickerNode).dialog().on('click', function(){
                        var $picker = jQuery(this);
                        $window['ConcreteEvent'].unsubscribe('UserSearchDialogSelectUser.core');
                        $window['ConcreteEvent'].unsubscribe('UserSearchDialogAfterSelectUser.core');
                        $window['ConcreteEvent'].subscribe('UserSearchDialogSelectUser.core', function(e, data){
                            $picker.text(data.uName);
                            $scope.$apply(function(){
                                $scope.entity.ownerID = data.uID;
                            });
                        });
                        $window['ConcreteEvent'].subscribe('UserSearchDialogAfterSelectUser.core', function(e) {
                            jQuery.fn.dialog.closeTop();
                        });
                    });
                }
            }, function( resp ){ // Failure; @todo: proper handling!
                console.log(resp);
            });

            // Save the resource
            $scope.submitHandler = function(){
                $scope._requesting = true;
                // If entity already has ID, $update, otherwise $save (create), and bind callback
                ($scope.entity.id ? $scope.entity.$update() : $scope.entity.$save()).then(
                    function( resp ){
                        $scope._requesting = false;
                        $window.location.href = API._routes.generate('dashboard',['calendars','manage',resp.id]);
                    },
                    function(){
                        $scope._requesting = false;
                    }
                );
            };

            /**
             * Delete the entity.
             */
            $scope.confirmDelete = false;
            $scope.deleteCalendar = function(){
                $scope.entity.$delete().then(function( resp ){
                    if( resp.ok ){
                        $window.location.href = API._routes.generate('dashboard', ['calendars']);
                    }
                });
            };
        }
    ]);
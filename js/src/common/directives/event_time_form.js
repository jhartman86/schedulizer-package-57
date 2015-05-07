angular.module('schedulizer.app').

    directive('eventTimeForm', [function(){

        function _link( scope, $elem, attrs, Controller ){
            // Nothing done here, everything via the controller
        }

        return {
            restrict:       'A',
            templateUrl:    '/event_timing_form',
            scope:          {_timeEntity:'=eventTimeForm'},
            link:           _link,
            controller: ['$rootScope', '$scope', '$filter', 'API', 'Helpers', '_moment',
                function( $rootScope, $scope, $filter, API, Helpers, _moment ){
                    // Option setters
                    $scope.repeatTypeHandleOptions              = Helpers.repeatTypeHandleOptions();
                    $scope.repeatIndefiniteOptions              = Helpers.repeatIndefiniteOptions();
                    $scope.weekdayRepeatOptions                 = Helpers.weekdayRepeatOptions();
                    $scope.repeatMonthlyMethodOptions           = Helpers.repeatMonthlyMethodOptions();
                    $scope.repeatMonthlySpecificDayOptions      = Helpers.range(1,31);
                    $scope.repeatMonthlyDynamicWeekdayOptions   = Helpers.repeatMonthlyDynamicWeekdayOptions();
                    $scope.repeatMonthlyDynamicWeekOptions      = Helpers.repeatMonthlyDynamicWeekOptions();

                    /**
                     * Weekday selection is tracked in a different object on the $scope, so we
                     * use that to determine what to put into entity.weeklyDays.
                     */
                    $scope.selectedWeekdays = function(){
                        var selected = $filter('filter')($scope.weekdayRepeatOptions, {checked: true});
                        $scope._timeEntity.weeklyDays = selected.map(function( obj ){
                            return obj.value;
                        });
                    };

                    /**
                     * If weeklyDays has values, set selected values in the scope tracker.
                     */
                    if( angular.isArray($scope._timeEntity.weeklyDays) && $scope._timeEntity.weeklyDays.length >= 1 ){
                        angular.forEach($scope.weekdayRepeatOptions, function( obj ){
                            obj.checked = $scope._timeEntity.weeklyDays.indexOf(obj.value) > -1;
                        });
                    }

                    /**
                     * These setters will only run if the user clicks "repeat" and all the
                     * current repeat settings are null.
                     */
                    function onChangeRepeatMethodAdjustValuesIfNull(){
                        // Set repeatEvery frequency
                        if( $scope._timeEntity.repeatEvery === null ){
                            $scope._timeEntity.repeatEvery = $scope.repeatEveryOptions[0];
                        }
                        // Set repeatIndefinite values
                        if( $scope._timeEntity.repeatIndefinite === null ){
                            $scope._timeEntity.repeatIndefinite = $scope.repeatIndefiniteOptions[0].value;
                        }
                        // If repeat type is set to monthly and the monthly settings are null...
                        if( $scope._timeEntity.repeatTypeHandle === $scope.repeatTypeHandleOptions[2].value ){
                            if( $scope._timeEntity.repeatMonthlyMethod === null ){
                                $scope._timeEntity.repeatMonthlyMethod = $scope.repeatMonthlyMethodOptions.specific;
                            }
                            if( $scope._timeEntity.repeatMonthlySpecificDay === null ){
                                $scope._timeEntity.repeatMonthlySpecificDay = $scope.repeatMonthlySpecificDayOptions[0];
                            }
                            if( $scope._timeEntity.repeatMonthlyOrdinalWeek === null ){
                                $scope._timeEntity.repeatMonthlyOrdinalWeek = $scope.repeatMonthlyDynamicWeekOptions[0].value;
                            }
                            if( $scope._timeEntity.repeatMonthlyOrdinalWeekday === null ){
                                $scope._timeEntity.repeatMonthlyOrdinalWeekday = $scope.repeatMonthlyDynamicWeekdayOptions[0].value;
                            }
                        }
                    }

                    /**
                     * Nullify monthly repeat settings.
                     */
                    function nullifyMonthlySettings(){
                        $scope._timeEntity.repeatMonthlyMethod = null;
                        $scope._timeEntity.repeatMonthlyOrdinalWeek = null;
                        $scope._timeEntity.repeatMonthlyOrdinalWeekday = null;
                        $scope._timeEntity.repeatMonthlySpecificDay = null;
                    }

                    /**
                     * Nullify weekly repeat settings.
                     */
                    function nullifyWeeklySettings(){
                        $scope._timeEntity.weeklyDays = [];
                        angular.forEach($scope.weekdayRepeatOptions, function( obj ){
                            obj.checked = false;
                        });
                    }

                    /**
                     * Nullify all repeat settings.
                     */
                    function nullifyAllRepeatSettings(){
                        nullifyMonthlySettings();
                        nullifyWeeklySettings();
                        $scope._timeEntity.repeatEndUTC = null;
                        $scope._timeEntity.repeatEvery = null;
                        $scope._timeEntity.repeatIndefinite = null;
                        $scope._timeEntity.repeatTypeHandle = null;
                    }

                    /**
                     * When the repeat type handle is switched, set default values
                     * if some are existing, and nullify others.
                     */
                    $scope.$watch('_timeEntity.repeatTypeHandle', function( val ){
                        switch(val){
                            case $scope.repeatTypeHandleOptions[0].value: // daily
                                $scope.repeatEveryOptions = Helpers.range(1,31);
                                nullifyMonthlySettings();
                                nullifyWeeklySettings();
                                break;
                            case $scope.repeatTypeHandleOptions[1].value: // weekly
                                $scope.repeatEveryOptions = Helpers.range(1,30);
                                nullifyMonthlySettings();
                                break;
                            case $scope.repeatTypeHandleOptions[2].value: // monthly
                                $scope.repeatEveryOptions = Helpers.range(1,11);
                                nullifyWeeklySettings();
                                break;
                            case $scope.repeatTypeHandleOptions[3].value: // yearly
                                $scope.repeatEveryOptions = Helpers.range(1,5);
                                nullifyMonthlySettings();
                                nullifyWeeklySettings();
                                break;
                        }
                        if( $scope._timeEntity.repeatTypeHandle !== null ){
                            onChangeRepeatMethodAdjustValuesIfNull();
                        }
                    });

                    /**
                     * If set to repeat indefinitely, nullify repeatEndUTC.
                     */
                    $scope.$watch('_timeEntity.repeatIndefinite', function( value ){
                        if( value === true ){
                            $scope._timeEntity.repeatEndUTC = null;
                        }
                    });

                    /**
                     * Update the endUTC when startUTC is adjusted.
                     */
                    $scope.$watch('_timeEntity.startUTC', function( dateObj ){
                        if( dateObj ){
                            $scope.calendarEndMinDate = _moment(dateObj).subtract(1, 'day');
                            if( _moment($scope._timeEntity.endUTC).isBefore(_moment($scope._timeEntity.startUTC)) ){
                                $scope._timeEntity.endUTC = _moment($scope._timeEntity.startUTC);
                            }
                        }
                    });

                    /**
                     * This takes care of syncronizing repeat settings, including when
                     * the time form is initialized.
                     */
                    $scope.$watch('_timeEntity.isRepeating', function( value ){
                        if( value === true && $scope._timeEntity.repeatTypeHandle === null ){
                            $scope._timeEntity.repeatTypeHandle = $scope.repeatTypeHandleOptions[0].value;
                        }
                        if( value === false ){
                            nullifyAllRepeatSettings();
                        }
                    });

                    /**
                     * Nullifiers
                     */
                    $scope.showNullifiers = false;
                    API.eventNullify.query({eventTimeID:$scope._timeEntity.id}, function( resp ){
                        $scope.hasNullifiers = resp.length >= 1;
                        angular.forEach(resp, function( resource ){
                            resource._moment = _moment.utc(resource.hideOnDate);
                        });
                        $scope.configuredNullifiers = resp;
                    });

                    /**
                     * Delete an existing nullifer record.
                     * @param resource
                     */
                    $scope.cancelNullifier = function( resource ){
                        resource.$delete(function( resp ){
                            $rootScope.$emit('calendar.refresh');
                        });
                    };
                }
            ]
        };
    }]);
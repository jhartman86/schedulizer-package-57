/* global jQuery */
angular.module('schedulizer.app').

    controller('CtrlEventForm', ['$rootScope', '$scope', '$q', '$filter', 'Helpers', 'ModalManager', 'API', '_moment',
        function( $rootScope, $scope, $q, $filter, Helpers, ModalManager, API, _moment ){

            $scope.activeMasterTab = {
                1: true
            };

            $scope.setMasterTabActive = function( index ){
                $scope.activeMasterTab = {};
                $scope.activeMasterTab[index] = true;
            };

            /**
             * Template for a new time entity.
             * @param _populator
             * @returns {*}
             */
            function newEventTimeEntity( _populator ){
                return angular.extend({
                    startUTC:                       _moment(),
                    endUTC:                         _moment(),
                    isOpenEnded:                    false,
                    isAllDay:                       false,
                    isRepeating:                    false,
                    repeatTypeHandle:               null,
                    repeatEvery:                    null,
                    repeatIndefinite:               null,
                    repeatEndUTC:                   null,
                    repeatMonthlyMethod:            null,
                    repeatMonthlySpecificDay:       null,
                    repeatMonthlyOrdinalWeek:       null,
                    repeatMonthlyOrdinalWeekday:    null,
                    weeklyDays:                     []
                }, _populator || {});
            }

            // Set default scope variables
            $scope._ready               = false;
            $scope._requesting          = false;
            $scope.eventColorOptions    = Helpers.eventColorOptions();
            $scope.timingTabs           = [];
            $scope.eventTagList         = [];
            // Did the user click to edit an event that's an alias?
            $scope.warnAliased          = ModalManager.data.eventObj.isSynthetic || false;

            // If aliased, show the message
            if( $scope.warnAliased ){
                $scope._ready = true;
            }

            /**
             * Before doing anything else, get timezone list (which is cache-able),
             * the calendar object, and the list of available tags.
             * @type {*[]}
             * @private
             */
            var _requests = [
                API.timezones.get().$promise,
                API.calendar.get({id:ModalManager.data.eventObj.calendarID}).$promise,
                API.eventTags.query().$promise
            ];

            /**
             * After all dependencies are loaded via the queue, THEN proceed...
             */
            $q.all(_requests).then(function( results ){
                // Set timezone options on scope
                $scope.timezoneOptions = results[0];
                // Set calendar on scope
                $scope.calendarObj = results[1];
                // Set event tags on scope
                $scope.eventTagList = results[2];

                // If eventObj passed by the modal manager DOES NOT have an ID, we're
                // creating a new entity
                if( ! ModalManager.data.eventObj.eventID ){
                    // Set entity on scope
                    $scope.entity = new API.event({
                        calendarID:             $scope.calendarObj.id,
                        title:                  '',
                        description:            '',
                        useCalendarTimezone:    true,
                        timezoneName:           $scope.calendarObj.defaultTimezone,
                        eventColor:             $scope.eventColorOptions[0].value,
                        _timeEntities:          [newEventTimeEntity()]
                    });
                    jQuery('[data-file-selector="fileID"]').concreteFileSelector({
                        'inputName': 'fileID',
                        'filters': [{"field":"type","type":1}]
                    });
                    $scope._ready = true;
                }
            });

            /**
             * If modal manager passed an eventID, then add another request (to get the
             * full event info) to the queue and wait for it to resolve, then proceed.
             */
            if( ModalManager.data.eventObj.eventID ){
                // Push a new request onto the promise chain...
                _requests.push(API.event.get({id:ModalManager.data.eventObj.eventID}).$promise);
                // When resolved (first two should be done immediately, this just chains onto the queue),
                // and the last request is index 2
                $q.all(_requests).then(function( results ){
                    // Map existing time entity results before setting entity on scope
                    results[3]._timeEntities.map(function( record ){
                        return newEventTimeEntity(record);
                    });

                    // Set the entity
                    $scope.entity = results[3];

                    jQuery('[data-file-selector="fileID"]').concreteFileSelector({
                        'inputName': 'fileID',
                        'fID': $scope.entity.fileID,
                        'filters': [{"field":"type","type":1}]
                    });

                    $scope._ready = true;
                });
            }

            // Load the attributes form as a seperate include, passing eventID if applicable
            $scope.attributeForm = API._routes.generate('ajax', [
                'event_attributes_form', ModalManager.data.eventObj.eventID, ('?bustCache=' + Math.random().toString(36).substring(7) + Math.floor(Math.random() * 10000) + 1)
            ]);

            // Tag selection function (when creating new tags on the fly, this gets called)
            $scope.tagTransform = function( newTagText ){
                return {
                    displayText: newTagText
                };
            };

            /**
             * Set a specific time entity tab to active
             * @param index
             */
            $scope.setTimingTabActive = function( index ){
                angular.forEach($scope.timingTabs, function( obj ){
                    obj.active = false;
                });
                $scope.timingTabs[index].active = true;
            };

            /**
             * Add a new time entity by pushing onto the _timeEntities stack.
             */
            $scope.addTimeEntity = function(){
                $scope.entity._timeEntities.push(newEventTimeEntity());
            };

            /**
             * Remove a time entity.
             * @param index
             */
            $scope.removeTimeEntity = function( index ){
                $scope.entity._timeEntities.splice(index,1);
            };

            /**
             * Watch time entities and create/remove tabs appropriately.
             */
            $scope.$watchCollection('entity._timeEntities', function( timeEntities ){
                if( angular.isArray(timeEntities) ){
                    $scope.timingTabs = Helpers.range(1, timeEntities.length).map(function(val, index){
                        return {label:'Time ' + val, active:(index === (timeEntities.length - 1))};
                    });
                }
            });

            /**
             * Timezone configuration
             */
            $scope.$watch('calendarObj', function( obj ){
                if( angular.isObject(obj) ){
                    $scope.useCalendarTimezoneOptions = [
                        {label:'Use Calendar Timezone ('+$scope.calendarObj.defaultTimezone+')', value:true},
                        {label:'Event Uses Custom Timezone', value:false}
                    ];
                }
            });

            /**
             * If use calendar timezone is set to true, or changes to be set to true,
             * set the timezoneName on the event accordingly.
             */
            $scope.$watch('entity.useCalendarTimezone', function( val ){
                if( val === true ){
                    $scope.entity.timezoneName = $scope.calendarObj.defaultTimezone;
                }
            });

            /**
             * Persist the entity. THIS HAPPENS WITH TWO CALLS: first, we persist
             * the event object itself. Then when that returns, we make ANOTHER call
             * posting to _schedulizer/event/attributes/1 with JUST the values encapsulated
             * in the <div custom-attributes></div> section. We have to dumb down to using
             * just jQuery here in order to serialize the contents and treat it all as
             * an array :(.
             */
            $scope.submitHandler = function(){
                // Show the spinner...
                $scope._requesting = true;

                // Step 1 - submit primary event
                var step1 = $q(function( resolve, reject ){
                    // Set the primary fileID from the C5 file selector on the entity before submitting
                    $scope.entity.fileID = parseInt(jQuery('input[type="hidden"]', '.ccm-file-selector').val()) || null;

                    //If entity already has ID, $update, otherwise $save (create), and bind callback
                    ($scope.entity.id ? $scope.entity.$update() : $scope.entity.$save()).then(
                        function( resp ){
                            // Resolves the outer promise (step1) so we know to move on to step2
                            resolve(resp);
                        }
                    );
                });

                // Step 2 - serialize attributes and send (always goes to post handler in API)
                step1.then(function( eventObj ){
                    var _route = API._routes.generate('api.event', ['attributes', eventObj.id]),
                        // Serializes all the attributes within [custom-attributes] div
                        _attrs = jQuery('input,select,textarea', '[custom-attributes]').serialize();

                    jQuery.post(_route, _attrs).always(function( resp ){
                        if( resp.ok ){
                            $scope._requesting = false;
                            $rootScope.$emit('calendar.refresh');
                            ModalManager.classes.open = false;
                        }
                    });
                });
            };

            /**
             * Delete the entity.
             */
            $scope.confirmDelete = false;
            $scope.deleteEvent = function(){
                $scope.entity.$delete().then(function( resp ){
                    if( resp.ok ){
                        $rootScope.$emit('calendar.refresh');
                        ModalManager.classes.open = false;
                    }
                });
            };

            /**
             * This is a synthetic event being passed by the calendar results;
             * therefore the user sees a warning window and can nullify this
             * event day in the series.
             */
            $scope.nullifyInSeries = function(){
                var nullifier = new API.eventNullify({
                    eventTimeID: ModalManager.data.eventObj.eventTimeID,
                    hideOnDate: ModalManager.data.eventObj.computedStartUTC
                });
                nullifier.$save().then(function( resp ){
                    $rootScope.$emit('calendar.refresh');
                    ModalManager.classes.open = false;
                });
            };
        }
    ]);
/* global jQuery */
/* gloabl ConcreteFileManager */
angular.module('schedulizer.app').

    controller('CtrlEventForm', ['$window', '$rootScope', '$scope', '$q', '$filter', '$http', 'Helpers', 'ModalManager', 'Alerter', 'API', '_moment', '$compile',
        function( $window, $rootScope, $scope, $q, $filter, $http, Helpers, ModalManager, Alerter, API, _moment, $compile ){

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
            $scope._requestingApproval  = false;
            $scope.eventColorOptions    = Helpers.eventColorOptions();
            $scope.timingTabs           = [];
            $scope.eventTagList         = [];
            $scope.eventCategoryList    = [];
            $scope.isActiveOptions      = Helpers.isActiveOptions();
            // Did the user click to edit an event that's an alias?
            $scope.warnAliased          = ModalManager.data.eventObj.isSynthetic || false;

            // If aliased, show the message
            if( $scope.warnAliased ){
                $scope._ready = true;
            }

            /**
             * Before doing anything else, get timezone list (which is cache-able),
             * the calendar object, and the lists of available tags/categories.
             * @type {*[]}
             * @private
             */
            var _requests = [
                API.timezones.get().$promise,
                API.calendar.get({id:ModalManager.data.eventObj.calendarID}).$promise,
                API.eventTags.query().$promise,
                API.eventCategories.query().$promise
            ];

            /**
             * Workaround C5's horrendous error handling. The concreteFileSelector
             * call w/in this $http call hits the SAME path, but if (and there frequently will be)
             * an error gets thrown because the file no longer exists and C5 doesn't catch
             * that error, the interface explodes. So we call the route first and see if it actually
             * works, then we basically let concreteFileSelector call the same thing, again, right
             * away, but knowing that its valid. Also, we're using jQuery here to duplicate (exactly)
             * the request as its made by the core file manager.
             * @param  {int} fileID [description]
             * @return void
             */
            function setupFilePicker( fileID ){
                var _always = {
                    'inputName': 'fileID',
                    'filters': [{"field":"type","type":1}]
                };

                // If fileID is non-existent
                if( (+(fileID) >= 1) === false || angular.isDefined(fileID) === false ){
                    jQuery('[data-file-selector="fileID"]').concreteFileSelector(_always);
                    return;
                }

                // If fileID DOES exist, this is where we have to do the insanely stupid
                // pre-test to make sure it actually exists in the system.
                jQuery.ajax({
                    type: 'post',
                    dataType: 'json',
                    url: $window['CCM_DISPATCHER_FILENAME'] + '/ccm/system/file/get_json',
                    data: {'fID':$scope.entity.fileID},
                    error: function(r){
                        jQuery('[data-file-selector="fileID"]').concreteFileSelector(_always);
                    },
                    success: function(r){
                        jQuery('[data-file-selector="fileID"]').concreteFileSelector(
                            angular.extend(_always,{fID:fileID})
                        );
                    }
                });
            }

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
                // Set event categories
                $scope.eventCategoryList = results[3];

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
                        isActive:               $scope.isActiveOptions[0].value,
                        _timeEntities:          [newEventTimeEntity()]
                    });
                    setupFilePicker();
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
                    results[4]._timeEntities.map(function( record ){
                        return newEventTimeEntity(record);
                    });

                    // Set the entity
                    $scope.entity = results[4];

                    // Setup the file picker
                    setupFilePicker($scope.entity.fileID);

                    // Notify scope ready
                    $scope._ready = true;
                });
            }

            // Load the attributes form as a seperate include, passing eventID if applicable
            $scope.attributeForm = API._routes.generate('ajax', [
                'event_attributes_form', ModalManager.data.eventObj.eventID, ('?bustCache=' + Math.random().toString(36).substring(7) + Math.floor(Math.random() * 10000) + 1)
            ]);

            /**
             * Hackish: in order to work w/ C5's attribute system, we have to include the
             * attribute form via an include call, and the <input>s that get rendered aren't
             * bound to the $scope's model watchers. Since we aren't allowing the user to click
             * save unless the form has changed, we need to bind the inputs loaded via the
             * include.
             * Note: even though we store the attribute values in entity._attributes, nothing
             * is done with that data - its just for change detection in the UI.
             */
            $scope.decorateAttributes = function(){
                var customAttrs = document.querySelector('[custom-attributes]');

                if( customAttrs ){
                    var fields = customAttrs.querySelectorAll('input,select,textarea');

                    $scope.entity._attributes = {};

                    Array.prototype.slice.call(fields).forEach(function( node, index ){
                        var _node = angular.element(node);

                        // Special handling for checkboxes
                        if( _node.get(0).type === 'checkbox' ){
                            $scope.entity._attributes[index] = _node.attr('checked') ? 1 : null;
                            _node.attr('ng-model', 'entity._attributes['+index+']');
                            _node.attr('ng-true-value', 1);
                            _node.attr('ng-false-value', null);
                            $compile(_node)($scope);
                            return;
                        }

                        // Normal binding process
                        $scope.entity._attributes[index] = _node.val();
                        _node.attr('ng-model', 'entity._attributes['+index+']');
                        $compile(_node)($scope);
                    });
                }
            };

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
             * Watch the isActive value, and if its changed, send request and close the edit
             * window immediately: every time an event gets saved in full, it creates a new version
             * along with all associated records. This way we use a special route to change JUST
             * the active status of the event.
             */
            $scope.$watch('entity.isActive', function( newVal, oldVal ){
                // We do this if check b/c: upon initialization, entity.isActive will be undefined,
                // then when entity.isActive becomes initialized, we get what the current value is;
                // but we only want if its *changed* - meaning after 1) undefined, then 2) initial
                // value has been set. Further, we check if entity.id exists - because it only makes
                // sense to update active status on an *existing* event.
                if( typeof(newVal) === 'boolean' && typeof(oldVal) === 'boolean' && $scope.entity.id ){
                    // this causes the save button be disabled so user can't click 'Save' while the
                    // call is taking place... if the user were a really fast clicker
                    $scope.frmEventData.$setPristine();
                    // Show spinner
                    $scope._requesting = true;
                    // Send
                    $scope.entity.$updateActiveStatus(function(){
                        $rootScope.$emit('calendar.refresh');
                        ModalManager.classes.open = false;
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
                var step1 = $q(function( resolve ){
                    // Set the primary fileID from the C5 file selector on the entity before submitting
                    $scope.entity.fileID = parseInt(jQuery('input[type="hidden"]', '.ccm-file-selector').val()) || null;

                    //If entity already has ID, $update, otherwise $save (create), and bind callback
                    ($scope.entity.id ? $scope.entity.$update() : $scope.entity.$save()).then(
                        function( resp ){
                            // Resolves the outer promise (step1) so we know to move on to step2
                            resolve(resp);
                        },
                        function(){ // failure...
                            $scope._requesting = false;
                        }
                    );
                });

                // Step 2 - serialize attributes and send (always goes to post handler in API)
                step1.then(function( eventObj ){
                    var _route = API._routes.generate('api.event', ['attributes', eventObj.id]),
                        // Serializes all the attributes within [custom-attributes] div
                        _attrs = jQuery('input,select,textarea', '[custom-attributes]').serialize();

                    jQuery.post(_route, _attrs)
                        .done(function(){
                            // Need to $apply because we have to use effing jQuery for the post
                            // and this happens in the callback!
                            $scope.$apply(function(){
                                $scope._requesting = false;
                                $rootScope.$emit('calendar.refresh');
                                ModalManager.classes.open = false;
                            });
                        })
                        .fail(function( resp ){
                            Alerter.add({msg:resp.responseJSON.error,danger:true});
                        });
                });
            };

            /**
             * For versioning: users without 'manage_collections' permission level
             * are given a submit for approval button, in which case we just add to
             * the event data then run the normal submit method.
             */
            $scope.submitForApprovalHandler = function(){
                // Set special property that denotes to the API the user wants this
                // submitted to the approval queue.
                $scope.entity.__requestApproval = true;
                // UI
                $scope._requestingApproval      = true;
                // Submit that biznass
                $scope.submitHandler();
            };

            /**
             * Delete the entity.
             */
            $scope.confirmDelete = false;
            $scope.deleteEvent = function(){
                $scope.entity.$delete().then(
                    function( resp ){
                        $rootScope.$emit('calendar.refresh');
                        ModalManager.classes.open = false;
                    }
                );
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

/* global FastClick */
;(function( window, angular, undefined ){ 'use strict';

    angular.module('schedulizer', [
        'ngResource', 'schedulizer.app', 'mgcrea.ngStrap.datepicker', 'mgcrea.ngStrap.timepicker',
        'calendry', 'ui.select', 'ngSanitize'
    ]).

    /**
     * @description App configuration
     * @param $provide
     * @param $locationProvider
     */
    config(['$provide', '$locationProvider',
        function( $provide, $locationProvider ){
            // Disable Angular's HTML5 mode stuff
            $locationProvider.html5Mode(false);

            var routeBase = window['__schedulizer'];

            // Provide API route helpers
            $provide.factory('Routes', function(){
                var _routes = {
                    api: {
                        calendar:       routeBase.api + '/calendar',
                        event:          routeBase.api + '/event',
                        eventList:      routeBase.api + '/event_list',
                        eventNullify:   routeBase.api + '/event_time_nullify',
                        eventTags:      routeBase.api + '/event_tags',
                        timezones:      routeBase.api + '/timezones'
                    },
                    dashboard: routeBase.dashboard,
                    ajax: routeBase.ajax
                };

                return {
                    routeList: _routes,
                    generate: function( _route, _routeParams ){
                        var route = _route.split('.').reduce(function(obj, mapTo){
                            return obj[mapTo];
                        }, _routes);
                        return (_routeParams || []).length ? (route + '/' + _routeParams.join('/')) : route;
                    }
                };
            });
        }
    ]).

    factory('API', ['$resource', 'Routes',
       function( $resource, Routes ){
           function _methods(){
               return {
                   update: {method:'PUT', params:{_method:'PUT'}}
               };
           }

           return {
               calendar: $resource(Routes.generate('api.calendar', [':id']), {id:'@id'}, angular.extend(_methods(), {
                   // more custom methods here
               })),
               event: $resource(Routes.generate('api.event', [':id']), {id:'@id'}, angular.extend(_methods(), {
                   // more custom methods here
               })),
               eventNullify: $resource(Routes.generate('api.eventNullify', [':eventTimeID', ':id']), {eventTimeID:'@eventTimeID',id:'@id'}, angular.extend(_methods(), {
                   // more custom methods
               })),
               eventTags: $resource(Routes.generate('api.eventTags', [':id']), {id:'@id'}, angular.extend(_methods(), {

               })),
               timezones: $resource(Routes.generate('api.timezones'), {}, {
                   get: {isArray:true, cache:true},
                   defaultTimezone: {method:'GET', cache:true, params:{config_default:true}}
               }),
               // Append the Routes factory result into the API for easier access
               _routes: Routes
           };
       }
    ]);


    /**
     * Manually bootstrap the document
     */
    angular.element(document).ready(function(){
        if( !(window['__schedulizer']) ){
            alert('Schedulizer is missing a configuration to run and has aborted.');
            return;
        }

        angular.bootstrap(document, ['schedulizer']);
    });

})(window, window.angular);
angular.module('calendry', []);

angular.module('schedulizer.app', []);
;(function( window, angular, undefined ){
    'use strict';

    angular.module('calendry').


    /**
     * Wrap 'moment' from the global scope for angular DI, or set to false if unavailable.
     */
    factory('MomentJS', ['$window', '$log', function( $window, $log ){
        return $window['moment'] ||
            ($log.warn('Moment.JS not available in global scope, Calendry will be unavailable.'), false);
    }]).

    /**
     * Calendry directive
     */
    directive('calendry', ['$cacheFactory', '$document', '$log', '$q', 'MomentJS',
        function factory( $cacheFactory, $document, $log, $q, momentJS ){

            // If momentJS is not available, don't initialize the directive!
            if( ! momentJS ){
                $log.warn('Calendry not instantiated due to missing momentJS library');
                return;
            }


            var _document       = $document[0],
                _monthMapCache  = $cacheFactory('monthMap'),
                _docFragsCache  = $cacheFactory('docFrags'),
                // Cache keys
                _monthMapKey    = 'YYYY_MM',
                _eventMapKey    = 'YYYY_MM_DD',
                // Default settings
                _defaults       = {
                    forceListView   : false,
                    daysOfWeek      : momentJS.weekdaysShort(),
                    currentMonth    : momentJS(),
                    dayCellClass    : 'day-node',
                    parseDateField  : 'startDate',
                    onMonthChange   : function(){},
                    onDropEnd       : function(){}
                };


            /**
             * Instantiable method for creating month maps.
             * @param monthStartMoment
             * @constructor
             */
            function MonthMap( monthStartMoment ){
                this.monthStart         = monthStartMoment;
                this.monthEnd           = momentJS(this.monthStart).endOf('month');
                this.calendarStart      = momentJS(this.monthStart).subtract(this.monthStart.day(), 'day');
                this.calendarEnd        = momentJS(this.monthEnd).add((6 - this.monthEnd.day()), 'day');
                this.calendarDayCount   = Math.abs(this.calendarEnd.diff(this.calendarStart, 'days'));
                this.calendarDays       = (function( daysInCalendar, calendarStart, _array ){
                    for( var _i = 0; _i <= daysInCalendar; _i++ ){
                        _array.push(momentJS(calendarStart).add('days', _i));
                    }
                    return _array;
                })( this.calendarDayCount, this.calendarStart, []);
            }


            /**
             * Generate a list of moment objects, grouped by weeks visible on the calendar.
             * @param MomentJS _month : Pass in a moment object to derive the month, or the current month will be
             * used automatically.
             * @returns {Array}
             */
            function getMonthMap( _month ){
                var monthStart = momentJS.isMoment(_month) ? momentJS(_month).startOf('month') : momentJS({day:1}),
                    _cacheKey  = monthStart.format(_monthMapKey);

                // In cache?
                if( _monthMapCache.get(_cacheKey) ){
                    return _monthMapCache.get(_cacheKey);
                }

                // Hasn't been created yet, do so now.
                _monthMapCache.put(_cacheKey, new MonthMap(monthStart));

                // Return the cache item
                return _monthMapCache.get(_cacheKey);
            }


            /**
             * Get the id attribute for a day cell.
             * @param MomentJS | MomentObj
             * @returns {string}
             */
            function getDayCellID( MomentObj ){
                return _defaults.dayCellClass + '-' + MomentObj.format('YYYY_MM_DD');
            }


            /**
             * Passing in a monthMapObj, this will return a document fragment of the
             * composed calendar DOM elements.
             * @note: This caches documentFragments the first time they're generated, and
             * returns CLONED elements each time thereafter.
             * @param MonthMap | monthMapObj
             * @returns {DocumentFragment|Object|*}
             */
            function getCalendarFragment( monthMapObj ){
                var momentNow   = momentJS(),
                    cacheKey    = monthMapObj.monthStart.format('YYYY_MM');

                // If already exists in the cache, just return a cloned instance immediately
                if( _docFragsCache.get(cacheKey) ){
                    return _docFragsCache.get(cacheKey).cloneNode(true);
                }

                // Hasn't been created yet, do so now.
                var docFragment = _document.createDocumentFragment();

                for( var _i = 0, _len = monthMapObj.calendarDays.length; _i < _len; _i++ ){
                    var cell    = _document.createElement('div'),
                        inMonth = monthMapObj.calendarDays[_i].isSame(monthMapObj.monthStart, 'month') ? 'month-incl' : 'month-excl',
                        isToday = monthMapObj.calendarDays[_i].isSame(momentNow, 'day') ? 'is-today' : '';

                    cell.setAttribute('id', getDayCellID(monthMapObj.calendarDays[_i]));
                    cell.className = _defaults.dayCellClass + ' ' + inMonth + ' ' + isToday;
                    cell.innerHTML = '<span class="date-num">'+monthMapObj.calendarDays[_i].format('DD')+'<small>'+monthMapObj.calendarDays[_i].format('MMM')+'</small></span>';

                    docFragment.appendChild(cell);
                }

                _docFragsCache.put(cacheKey, docFragment);

                // Return a CLONED instance of the document fragment
                return _docFragsCache.get(cacheKey).cloneNode(true);
            }


            /**
             * Hex to RGB conversion utility
             * @param hex
             * @returns {{r: number, g: number, b: number}}
             */
            function hexToRgb(hex) {
                // Expand shorthand form (e.g. "03F") to full form (e.g. "0033FF")
                var shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
                hex = hex.replace(shorthandRegex, function(m, r, g, b) {
                    return r + r + g + g + b + b;
                });

                var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
                return result ? {
                    r: parseInt(result[1], 16),
                    g: parseInt(result[2], 16),
                    b: parseInt(result[3], 16)
                } : null;
            }


            function _link( $scope, $element, attrs, Controller, transcludeFn ){
                /**
                 * Pass in the directive element and the monthMap we use to generate the
                 * calendar DOM elements.
                 * @param $element
                 * @param monthMap
                 * @returns null
                 */
                function renderCalendarLayout( monthMap ){
                    // Rebuild the calendar layout (no events attached, just days)
                    var $renderTo = angular.element($element[0].querySelector('.calendar-render')),
                        weekRows  = Math.ceil( monthMap.calendarDayCount / 7 );

                    // Set row classes on calendar-body
                    angular.element($element[0].querySelector('.calendry-body'))
                        .removeClass('week-rows-4 week-rows-5 week-rows-6')
                        .addClass('week-rows-' + weekRows);

                    // Render the calendar body
                    //$renderTo.empty().append( getCalendarFragment(monthMap) );
                    var fragment = getCalendarFragment(monthMap);

                    // DECORATE EVERY DAY ELEMENT WITH A _moment PROPERTY VIA .data()
                    Array.prototype.slice.call(fragment.childNodes).forEach(function(node, index){
                        fragment.childNodes[index] = angular.element(node).data('_moment', monthMap.calendarDays[index]);
                    });

                    $renderTo.empty().append(fragment);
                }


                /**
                 * Receive an event list as an array, and update the UI.
                 * @param eventList array
                 */
                function renderEvents( eventList ){
                    // Clear all previously rendered events
                    angular.element($element[0].querySelectorAll('.event-cell')).remove();

                    // Variables
                    var mapped = {};

                    // Loop through every event object and create _moment property, and
                    // append to mapped
                    eventList.forEach(function(eventObj){
                        eventObj._moment = momentJS(eventObj[$scope.instance.parseDateField], momentJS.ISO_8601);
                        var mappedKey    = eventObj._moment.format(_eventMapKey);
                        if( ! mapped[mappedKey] ){
                            mapped[mappedKey] = [];
                        }
                        mapped[eventObj._moment.format(_eventMapKey)].push(eventObj);
                    });

                    /**
                     * Transclude function callback; note the $cloned element is implicitly
                     * set by the transcludeFn, and below we use .bind() to pass in the $dayNode
                     * @param $dayNode
                     * @param $cloned
                     * @private
                     */
                    function _transcluder( $dayNode, $cloned ){
                        $dayNode.append($cloned);
                    }

                    /**
                     * Loop through every day in the calendar and look for events to
                     * render.
                     * @note: the transcluder function in the loop, by default, passes in
                     * $cloned as the first argument. but since we're using .bind(), it
                     * re-orders the arguments so that $dayNode is the first arg, THEN
                     * $cloned
                     */
                    $scope.instance.monthMap.calendarDays.forEach(function( dayMoment ){
                        var eventsForDay = mapped[dayMoment.format(_eventMapKey)];
                        if( eventsForDay ){
                            var $dayNode = angular.element($element[0].querySelector('#' + getDayCellID(dayMoment)));
                            if( $dayNode ){
                                for(var _i = 0, _len = eventsForDay.length; _i < _len; _i++){
                                    var $newScope       = $scope.$new(/*true*/);
                                    $newScope.eventObj  = eventsForDay[_i];
                                    transcludeFn($newScope, _transcluder.bind(null, $dayNode));
                                }
                            }
                        }
                    });
                }


                // Any time the monthMap model changes, re-render.
                $scope.$watch('instance.monthMap', function( monthMapObj ){
                    if( monthMapObj ){
                        renderCalendarLayout(monthMapObj);
                    }
                });


                // Watch for changes to events property
                $scope.$watch('events', function(eventList){
                    if( angular.isArray(eventList) ){
                        renderEvents(eventList);
                    }
                });

                // Event click handler
//                angular.element($element[0].querySelector('.calendry-body')).on('click', function(event){
//                    // Ghetto delegation from the parent
//                    var delegator = this,
//                        target    = (function( _target ){
//                            while( ! _target.classList.contains('event-cell') ){
//                                if(_target === delegator){_target = null; break;}
//                                _target = _target.parentNode;
//                            }
//                            return _target;
//                        })(event.target);
//
//                    //console.log(target);
//                });

            }


            return {
                restrict: 'A',
                scope: {
                    instance: '=calendry'
                },
                replace: true,
                templateUrl: '/calendry',
                transclude: true,
                link: _link,
                controller: ['$scope', function( $scope ){

                    var Controller = this;

                    $scope.instance = angular.extend(Controller, _defaults, ($scope.instance || {}));

                    this.goToCurrentMonth = $scope.goToCurrentMonth = function(){
                        $scope.instance.currentMonth = momentJS();
                    };

                    this.goToPrevMonth = $scope.goToPrevMonth = function(){
                        $scope.instance.currentMonth = momentJS($scope.instance.currentMonth).subtract({months:1});
                    };

                    this.goToNextMonth = $scope.goToNextMonth = function(){
                        $scope.instance.currentMonth = momentJS($scope.instance.currentMonth).add({months:1});
                    };

                    this.toggleListView = $scope.toggleListView = function(){
                        $scope.instance.forceListView = !$scope.instance.forceListView;
                    };

                    $scope.$watch('instance.currentMonth', function( monthMoment ){
                        if( monthMoment ){
                            $scope.instance.monthMap = getMonthMap(monthMoment);
                            // Dispatch callback
                            $scope.instance.onMonthChange.apply(Controller, [$scope.instance.monthMap]);
                        }
                    });

                    $scope.$watch('instance.events', function( events ){
                        if( events ){
                            $scope.events = events;
                        }
                    });

                    $scope.helpers = {
                        eventFontColor: function( color ){
                            var rgb = hexToRgb(color),
                                val = Math.round(((rgb.r * 299) + (rgb.g * 587) + (rgb.b * 114)) / 1000);
                            return (val > 125) ? '#000000' : '#FFFFFF';
                        }
                    };
                }]
            };
        }
    ]);

})( window, window.angular );
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
angular.module('schedulizer.app').

    /**
     * Will automatically initialize modalWindow directive; and we don't have to worry about
     * leaving this in HTML somewhere.
     */
    run([function(){
        angular.element(document.querySelector('body')).append('<div modal-window class="schedulizer-app" ng-class="manager.classes"><a class="icon-close default-closer" modal-close></a><div class="modal-inner" ng-include="manager.data.source"></div></div>');
    }]).

    /**
     * ModalManager
     */
    factory('ModalManager', [function(){
        return {
            classes : {open: false},
            data    : {source: null}
        };
    }]).

    /**
     * Elements that should trigger opening a modal window
     * @returns {{restrict: string, scope: boolean, link: Function, controller: Array}}
     */
    directive('modalize', [function(){

            /**
             * @param scope
             * @param $element
             * @param attrs
             * @private
             */
            function _link( scope, $element, attrs ){
                $element.on('click', function(){
                    scope.$apply(function(){
                        scope.manager.data = angular.extend({
                            source: attrs.modalize
                        }, scope.using);
                    });
                });
            }

            return {
                restrict:   'A',
                scope:      {using: '=using'},
                link:       _link,
                controller: ['$scope', 'ModalManager', function( $scope, ModalManager ){
                    $scope.manager = ModalManager;
                }]
            };
        }
    ]).

    /**
     * Close the modal window
     */
    directive('modalClose', ['ModalManager', function( ModalManager ){

        function _link( scope, $elem, attrs ){
            $elem.on('click', function(){
                scope.$apply(function(){
                    ModalManager.classes.open = false;
                    ModalManager.data = null;
                });
            });
        }

        return {
            restrict: 'A',
            link: _link
        };
    }]).

    /**
     * Actual ModalWindow directive handler
     * @param Tween
     * @returns {{restrict: string, scope: boolean, link: Function, controller: Array}}
     */
    directive('modalWindow', [function(){

        /**
         * Link function with ModalManager service bound to the scope
         * @param scope
         * @param $elem
         * @param attrs
         * @private
         */
        function _link( scope, $elem, attrs ){
            scope.$watch('manager.classes.open', function(_val){
                angular.element(document.documentElement).toggleClass('schedulizer-modal', _val);
                if( ! _val ){
                    scope.manager.data = null;
                }
            });
        }

        return {
            restrict:   'A',
            scope:      true,
            link:       _link,
            controller: ['$scope', 'ModalManager', function( $scope, ModalManager ){
                $scope.manager = ModalManager;

                $scope.$on('$includeContentLoaded', function(){
                    $scope.manager.classes.open = true;
                });
            }]
        };
        }
    ]);

angular.module('schedulizer.app').

    directive('redactorized', [function(){

        /**
         * Redactor settings, pulled from Concrete5 defaults
         * @type {{minHeight: number, concrete5: {filemanager: boolean, sitemap: boolean, lightbox: boolean}, plugins: Array}}
         */
        var settings = {
            minHeight: 200,
            concrete5: {
                filemanager: true,
                sitemap: true,
                lightbox: true
            },
            plugins: ['fontcolor', 'concrete5','underline']
        };

        /**
         * @param scope
         * @param $element
         * @param attrs
         * @param Controller ngModel controller
         * @private
         */
        function _link( scope, $elem, attrs, Controller ){
            // ngModel's $render function
            Controller.$render = function(){
                // Set the initial value, if any
                $elem.val(Controller.$viewValue);

                // Initialize redactor, binding change callback
                $elem.redactor(angular.extend(settings, {
                    changeCallback: function(){
                        Controller.$setViewValue(this.get());
                        //scope.$apply(Controller.$setViewValue(this.get()));
                    }
                }));

                if( Controller.$viewValue ){
                    $elem.redactor('set', Controller.$viewValue);
                }
            };
        }

        return {
            priority:   0,
            require:    '?ngModel',
            restrict:   'A',
            link:       _link
        };
    }]);
angular.module('schedulizer.app').

    factory('Helpers', ['_moment', function factory(_moment){

        this.range = function( start, end ){
            var arr = [];
            for(var i = start; i <= end; i++){
                arr.push(i);
            }
            return arr;
        };

        this.repeatTypeHandleOptions = function(){
            return [
                {label: 'Days', value: 'daily'},
                {label: 'Weeks', value: 'weekly'},
                {label: 'Months', value: 'monthly'},
                {label: 'Years', value: 'yearly'}
            ];
        };

        this.repeatIndefiniteOptions = function(){
            return [
                {label: 'Forever', value: true},
                {label: 'Until', value: false}
            ];
        };

        this.weekdayRepeatOptions = function(){
            return [
                {label: 'Sun', value: 1},
                {label: 'Mon', value: 2},
                {label: 'Tue', value: 3},
                {label: 'Wed', value: 4},
                {label: 'Thu', value: 5},
                {label: 'Fri', value: 6},
                {label: 'Sat', value: 7}
            ];
        };

        this.repeatMonthlyMethodOptions = function(){
            return {
                specific    : 'specific',
                dynamic     : 'ordinal'
            };
        };

        this.repeatMonthlyDynamicWeekOptions = function(){
            return [
                {label: 'First', value: 1},
                {label: 'Second', value: 2},
                {label: 'Third', value: 3},
                {label: 'Fourth', value: 4},
                {label: 'Last', value: 5}
            ];
        };

        this.repeatMonthlyDynamicWeekdayOptions = function(){
            return [
                {label: 'Sunday', value: 1},
                {label: 'Monday', value: 2},
                {label: 'Tuesday', value: 3},
                {label: 'Wednesday', value: 4},
                {label: 'Thursday', value: 5},
                {label: 'Friday', value: 6},
                {label: 'Saturday', value: 7}
            ];
        };

        this.eventColorOptions = function(){
            return [
                {value: '#A3D900'},
                {value: '#3A87AD'},
                {value: '#DE4E56'},
                {value: '#BFBFFF'},
                {value: '#FFFF73'},
                {value: '#FFA64D'},
                {value: '#CCCCCC'},
                {value: '#00B7FF'},
                {value: '#222222'}
            ];
        };

        return this;
    }]);
angular.module('schedulizer.app').

    filter('numberContraction', function($filter) {

        var suffixes = ["th", "st", "nd", "rd"];

        return function(input) {
            var relevant = (input < 20) ? input : input % (Math.floor(input / 10) * 10);
            var suffix   = (relevant <= 3) ? suffixes[relevant] : suffixes[0];
            return suffix;
        };
    });
angular.module('schedulizer.app').

    /**
     * AngularJS default filter with the following expression:
     * "person in people | filter: {name: $select.search, age: $select.search}"
     * performs a AND between 'name: $select.search' and 'age: $select.search'.
     * We want to perform a OR.
     * @link: https://github.com/angular-ui/ui-select/blob/master/examples/demo.js#L134
     */
    filter('propsFilter', function() {
        return function(items, props) {
            var out = [];

            if (angular.isArray(items)) {
                items.forEach(function(item) {
                    var itemMatches = false;

                    var keys = Object.keys(props);
                    for (var i = 0; i < keys.length; i++) {
                        var prop = keys[i];
                        var text = props[prop].toLowerCase();
                        if (item[prop].toString().toLowerCase().indexOf(text) !== -1) {
                            itemMatches = true;
                            break;
                        }
                    }

                    if (itemMatches) {
                        out.push(item);
                    }
                });
            } else {
                // Let the output be the input untouched
                out = items;
            }

            return out;
        };
    });
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
                $scope.timezoneOptions = returned[0];
                $scope.entity = returned[2] || new API.calendar({
                    defaultTimezone: $scope.timezoneOptions[$scope.timezoneOptions.indexOf(returned[1].name)]
                });
                $scope._ready = true;
            }, function( resp ){
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
                    }
                );
            };
        }
    ]);
angular.module('schedulizer.app').

    controller('CtrlCalendarPage', ['$rootScope', '$scope', '$http', '$cacheFactory', 'API',
        function( $rootScope, $scope, $http, $cacheFactory, API ){

            $scope.updateInProgress = false;
            $scope.searchOpen       = false;
            $scope.eventTagList     = [];
            $scope.searchFiltersSet = false;
            $scope.searchFields     = {
                keywords: null,
                tags: []
            };

            $scope.toggleSearch = function(){
                $scope.searchOpen = !$scope.searchOpen;
            };

            API.eventTags.query().$promise.then(function( results ){
                $scope.eventTagList = results;
            });

            // $scope.calendarID is ng-init'd from the view!
            var _cache = $cacheFactory('calendarData');

            // Tell the API what fields we want back
            var _fields = [
                'eventID', 'eventTimeID', 'calendarID', 'title',
                'eventColor', 'isAllDay', 'isSynthetic', 'computedStartUTC',
                'computedStartLocal'
            ];

            /**
             * Turn the search button green if any search fields are filled in to indicate
             * to the user that search filters are being applied.
             */
            $scope.$watch('searchFields', function(val){
                var filtersSet = false;
                if( val.keywords ){filtersSet = true;}
                if( val.tags.length !== 0 ){filtersSet = true;}
                $scope.searchFiltersSet = filtersSet;
            }, true);

            /**
             * We need to pre-process the $scope.searchFields and format them for
             * querying; this does so.
             * @returns {{keywords: null, tags: *}}
             */
            function parameterizedSearchFields(){
                return {
                    keywords: $scope.searchFields.keywords,
                    tags: $scope.searchFields.tags.map(function( tag ){
                        return tag.id;
                    }).join(',')
                };
            }

            /**
             * Receive a month map object from calendry and setup the request as
             * you see fit.
             * @param monthMapObj
             * @returns {HttpPromise}
             * @private
             */
            function _fetch( monthMapObj ){
                return $http.get(API._routes.generate('api.eventList', [$scope.calendarID]), {
                    cache: _cache,
                    params: angular.extend({
                        start: monthMapObj.calendarStart.format('YYYY-MM-DD'),
                        end: monthMapObj.calendarEnd.format('YYYY-MM-DD'),
                        fields: _fields.join(',')
                    }, parameterizedSearchFields())
                });
            }

            /**
             * Trigger refreshing the calendar.
             * @private
             */
            function _updateCalendar(){
                $scope.updateInProgress = true;
                _cache.removeAll();
                _fetch($scope.instance.monthMap, true).success(function( resp ){
                    $scope.instance.events = resp;
                    $scope.updateInProgress = false;
                }).error(function( data, status, headers, config ){
                    $scope.updateInProgress = false;
                    console.warn(status, 'Failed fetching calendar data.');
                });
                $scope.searchOpen = false;
            }

            /**
             * Clear the search fields and update calendar.
             */
            $scope.clearSearchFields = function(){
                $scope.searchFields = {
                    keywords: null,
                    tags: []
                };
                _updateCalendar();
            };

            /**
             * Method to trigger calendar refresh callable from the scope.
             * @type {_updateCalendar}
             */
            $scope.sendSearch = function(){
                _updateCalendar();
            };

            /**
             * Handlers for calendry stuff.
             * @type {{onMonthChange: Function, onDropEnd: Function}}
             */
            $scope.instance = {
                parseDateField: 'computedStartLocal',
                onMonthChange: function( monthMap ){
                    _updateCalendar();
                },
                onDropEnd: function( landingMoment, eventObj ){
                    console.log(landingMoment, eventObj);
                }
            };

            /**
             * calendar.refresh IS NOT issued by the calendry directive; it comes
             * from other things in the app.
             */
            $rootScope.$on('calendar.refresh', _updateCalendar);

            // Launch C5's default modal stuff
            $scope.permissionModal = function( _href ){
                jQuery.fn.dialog.open({
                    title:  'Calendar Permissions',
                    href:   _href,
                    modal:  false,
                    width:  500,
                    height: 380
                });
            };

        }
    ]);
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
angular.module('schedulizer.app').

    /**
     * @description MomentJS provider
     * @param $window
     * @param $log
     * @returns Moment | false
     */
    provider('_moment', function(){
        this.$get = ['$window', '$log',
            function( $window, $log ){
                return $window['moment'] || ($log.warn('MomentJS unavailable!'), false);
            }
        ];
    });
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
    config(['$provide', '$locationProvider', '$httpProvider',
        function( $provide, $locationProvider, $httpProvider ){
            // Disable Angular's HTML5 mode stuff
            $locationProvider.html5Mode(false);

            var routeBase = window['__schedulizer'];

            // Provide API route helpers
            $provide.factory('Routes', function(){
                var _routes = {
                    api: {
                        calendar:           routeBase.api + '/calendar',
                        calendarList:       routeBase.api + '/calendar_list',
                        collection:         routeBase.api + '/collection',
                        collectionEvent:    routeBase.api + '/collection_event',
                        event:              routeBase.api + '/event',
                        eventList:          routeBase.api + '/event_list',
                        eventNullify:       routeBase.api + '/event_time_nullify',
                        eventTags:          routeBase.api + '/event_tags',
                        eventCategories:    routeBase.api + '/event_categories',
                        timezones:          routeBase.api + '/timezones'
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

            // "Global" ajax error handlers
            $httpProvider.interceptors.push(['$q', 'Alerter', 'ModalManager', function( $q, Alerter, ModalManager ){
                return {
                    responseError: function( rejection ){
                        var message = 'An error occurred; your request was not completed.';
                        if( rejection.data && rejection.data.error ){
                            message = rejection.data.error;
                        }
                        Alerter.add({msg:message, danger:true});
                        ModalManager.classes.open = false;
                        return $q.reject(rejection);
                    }
                };
            }]);
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
               calendarList: $resource(Routes.generate('api.calendarList'), {}, {
                   get: {isArray:true, cache:true}
               }),
               collection: $resource(Routes.generate('api.collection', [':id', ':subAction']), {id:'@id'}, angular.extend(_methods(), {
                   //allEventsList: {method:'get', isArray:true, cache:false, params:{subAction:'all_events_list'}}
               })),
               collectionEvent: $resource(Routes.generate('api.collectionEvent', [':subAction']), {}, angular.extend(_methods(), {
                   allEventsList: {method:'get', isArray:true, cache:false, params:{subAction:'all_events_list'}},
                   versionList: {method:'get', isArray:true, cache:false, params:{subAction:'version_list'}},
                   approvedVersion: {method:'get', cache:false, params:{subAction:'approved_version'}},
                   approveLatestVersions: {method:'post', params:{subAction:'approve_latest_versions'}},
                   unapprove: {method:'delete'},
                   saveSingleAutoApprovable: {method:'put', params:{_method:'PUT'}, transformRequest:function( data ){
                       return angular.toJson({
                           eventID: data.eventID,
                           versionID: data.versionID,
                           collectionID: data.collectionID,
                           autoApprovable: data.autoApprovable
                       });
                   }},
                   saveMultiAutoApprovable: {method:'put', params:{_method:'PUT',subAction:'multi_auto_approve'}}
               })),
               event: $resource(Routes.generate('api.event', [':id', ':subAction']), {id:'@id'}, angular.extend(_methods(), {
                   image_path: {method:'get', cache:false, params:{subAction:'image_path'}}
               })),
               eventNullify: $resource(Routes.generate('api.eventNullify', [':eventTimeID', ':id']), {eventTimeID:'@eventTimeID',id:'@id'}, angular.extend(_methods(), {
                   // more custom methods
               })),
               eventTags: $resource(Routes.generate('api.eventTags', [':id']), {id:'@id'}, angular.extend(_methods(), {

               })),
               eventCategories: $resource(Routes.generate('api.eventCategories', [':id']), {id:'@id'}, angular.extend(_methods(), {

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

!function(e,t){"use strict";t.module("schedulizer",["ngResource","schedulizer.app","mgcrea.ngStrap.datepicker","mgcrea.ngStrap.timepicker","calendry","ui.select","ngSanitize"]).config(["$provide","$locationProvider",function(t,n){n.html5Mode(!1);var a=e.__schedulizer;t.factory("Routes",function(){var e={api:{calendar:a.api+"/calendar",event:a.api+"/event",eventList:a.api+"/event_list",eventNullify:a.api+"/event_time_nullify",eventTags:a.api+"/event_tags",eventCategories:a.api+"/event_categories",timezones:a.api+"/timezones"},dashboard:a.dashboard,ajax:a.ajax};return{routeList:e,generate:function(t,n){var a=t.split(".").reduce(function(e,t){return e[t]},e);return(n||[]).length?a+"/"+n.join("/"):a}}})}]).factory("API",["$resource","Routes",function(e,n){function a(){return{update:{method:"PUT",params:{_method:"PUT"}}}}return{calendar:e(n.generate("api.calendar",[":id"]),{id:"@id"},t.extend(a(),{})),event:e(n.generate("api.event",[":id"]),{id:"@id"},t.extend(a(),{})),eventNullify:e(n.generate("api.eventNullify",[":eventTimeID",":id"]),{eventTimeID:"@eventTimeID",id:"@id"},t.extend(a(),{})),eventTags:e(n.generate("api.eventTags",[":id"]),{id:"@id"},t.extend(a(),{})),eventCategories:e(n.generate("api.eventCategories",[":id"]),{id:"@id"},t.extend(a(),{})),timezones:e(n.generate("api.timezones"),{},{get:{isArray:!0,cache:!0},defaultTimezone:{method:"GET",cache:!0,params:{config_default:!0}}}),_routes:n}}]),t.element(document).ready(function(){return e.__schedulizer?(t.bootstrap(document,["schedulizer"]),void 0):(alert("Schedulizer is missing a configuration to run and has aborted."),void 0)})}(window,window.angular),angular.module("calendry",[]),angular.module("schedulizer.app",[]),angular.module("schedulizer.app").controller("CtrlCalendarForm",["$scope","$q","$window","ModalManager","API",function(e,t,n,a,i){e._ready=!1,e._requesting=!1;var r=[i.timezones.get().$promise,i.timezones.defaultTimezone().$promise];a.data.calendarID&&r.push(i.calendar.get({id:a.data.calendarID}).$promise),t.all(r).then(function(t){e.timezoneOptions=t[0],e.entity=t[2]||new i.calendar({defaultTimezone:e.timezoneOptions[e.timezoneOptions.indexOf(t[1].name)]}),e._ready=!0},function(e){console.log(e)}),e.submitHandler=function(){e._requesting=!0,(e.entity.id?e.entity.$update():e.entity.$save()).then(function(t){e._requesting=!1,n.location.href=i._routes.generate("dashboard",["calendars","manage",t.id])})},e.confirmDelete=!1,e.deleteEvent=function(){e.entity.$delete().then(function(e){e.ok&&(n.location.href=i._routes.generate("dashboard",["calendars"]))})}}]),angular.module("schedulizer.app").controller("CtrlCalendarPage",["$rootScope","$scope","$http","$cacheFactory","API",function(e,t,n,a,i){function r(){return{includeinactives:!0,keywords:t.searchFields.keywords,tags:t.searchFields.tags.map(function(e){return e.id}).join(","),categories:t.searchFields.categories.map(function(e){return e.id}).join(",")}}function o(e){return n.get(i._routes.generate("api.eventList",[t.calendarID]),{cache:s,params:angular.extend({start:e.calendarStart.format("YYYY-MM-DD"),end:e.calendarEnd.format("YYYY-MM-DD"),fields:c.join(",")},r())})}function l(){t.updateInProgress=!0,s.removeAll(),o(t.instance.monthMap).success(function(e){t.instance.events=e,t.updateInProgress=!1}).error(function(e,n){t.updateInProgress=!1,console.warn(n,"Failed fetching calendar data.")}),t.searchOpen=!1}t.updateInProgress=!1,t.searchOpen=!1,t.eventTagList=[],t.eventCategoryList=[],t.searchFiltersSet=!1,t.searchFields={keywords:null,tags:[],categories:[]},t.toggleSearch=function(){t.searchOpen=!t.searchOpen},i.eventTags.query().$promise.then(function(e){t.eventTagList=e}),i.eventCategories.query().$promise.then(function(e){t.eventCategoryList=e});var s=a("calendarData"),c=["eventID","eventTimeID","calendarID","title","isActive","eventColor","isAllDay","isSynthetic","computedStartUTC","computedStartLocal"];t.$watch("searchFields",function(e){var n=!1;e.keywords&&(n=!0),0!==e.tags.length&&(n=!0),0!==e.categories.length&&(n=!0),t.searchFiltersSet=n},!0),t.clearSearchFields=function(){t.searchFields={keywords:null,tags:[],categories:[]},l()},t.sendSearch=function(){l()},t.instance={parseDateField:"computedStartLocal",onMonthChange:function(){l()},onDropEnd:function(e,t){console.log(e,t)}},e.$on("calendar.refresh",l),t.permissionModal=function(e){jQuery.fn.dialog.open({title:"Calendar Permissions",href:e,modal:!1,width:500,height:380})}}]),angular.module("schedulizer.app").controller("CtrlEventForm",["$rootScope","$scope","$q","$filter","$http","Helpers","ModalManager","API","_moment",function(e,t,n,a,i,r,o,l,s){function c(e){return angular.extend({startUTC:s(),endUTC:s(),isOpenEnded:!1,isAllDay:!1,isRepeating:!1,repeatTypeHandle:null,repeatEvery:null,repeatIndefinite:null,repeatEndUTC:null,repeatMonthlyMethod:null,repeatMonthlySpecificDay:null,repeatMonthlyOrdinalWeek:null,repeatMonthlyOrdinalWeekday:null,weeklyDays:[]},e||{})}t.activeMasterTab={1:!0},t.setMasterTabActive=function(e){t.activeMasterTab={},t.activeMasterTab[e]=!0},t._ready=!1,t._requesting=!1,t.eventColorOptions=r.eventColorOptions(),t.timingTabs=[],t.eventTagList=[],t.eventCategoryList=[],t.isActiveOptions=r.isActiveOptions(),t.warnAliased=o.data.eventObj.isSynthetic||!1,t.warnAliased&&(t._ready=!0);var u=[l.timezones.get().$promise,l.calendar.get({id:o.data.eventObj.calendarID}).$promise,l.eventTags.query().$promise,l.eventCategories.query().$promise];n.all(u).then(function(e){t.timezoneOptions=e[0],t.calendarObj=e[1],t.eventTagList=e[2],t.eventCategoryList=e[3],o.data.eventObj.eventID||(t.entity=new l.event({calendarID:t.calendarObj.id,title:"",description:"",useCalendarTimezone:!0,timezoneName:t.calendarObj.defaultTimezone,eventColor:t.eventColorOptions[0].value,isActive:t.isActiveOptions[0].value,_timeEntities:[c()]}),jQuery('[data-file-selector="fileID"]').concreteFileSelector({inputName:"fileID",filters:[{field:"type",type:1}]}),t._ready=!0)}),o.data.eventObj.eventID&&(u.push(l.event.get({id:o.data.eventObj.eventID}).$promise),n.all(u).then(function(e){e[4]._timeEntities.map(function(e){return c(e)}),t.entity=e[4],i({method:"POST",url:"/ccm/system/file/get_json",headers:{"Content-Type":"application/x-www-form-urlencoded"},transformRequest:function(e){var t=[];for(var n in e)return t.push(encodeURIComponent(n)+"="+encodeURIComponent(e[n])),t.join("&")},data:{fID:t.entity.fileID}}).then(function(){jQuery('[data-file-selector="fileID"]').concreteFileSelector({inputName:"fileID",fID:t.entity.fileID,filters:[{field:"type",type:1}]})},function(){jQuery('[data-file-selector="fileID"]').concreteFileSelector({inputName:"fileID",filters:[{field:"type",type:1}]}),console.log("No file object assigned to event or it no longer exists")}),t._ready=!0})),t.attributeForm=l._routes.generate("ajax",["event_attributes_form",o.data.eventObj.eventID,"?bustCache="+Math.random().toString(36).substring(7)+Math.floor(1e4*Math.random())+1]),t.tagTransform=function(e){return{displayText:e}},t.setTimingTabActive=function(e){angular.forEach(t.timingTabs,function(e){e.active=!1}),t.timingTabs[e].active=!0},t.addTimeEntity=function(){t.entity._timeEntities.push(c())},t.removeTimeEntity=function(e){t.entity._timeEntities.splice(e,1)},t.$watchCollection("entity._timeEntities",function(e){angular.isArray(e)&&(t.timingTabs=r.range(1,e.length).map(function(t,n){return{label:"Time "+t,active:n===e.length-1}}))}),t.$watch("calendarObj",function(e){angular.isObject(e)&&(t.useCalendarTimezoneOptions=[{label:"Use Calendar Timezone ("+t.calendarObj.defaultTimezone+")",value:!0},{label:"Event Uses Custom Timezone",value:!1}])}),t.$watch("entity.useCalendarTimezone",function(e){e===!0&&(t.entity.timezoneName=t.calendarObj.defaultTimezone)}),t.submitHandler=function(){t._requesting=!0;var a=n(function(e){t.entity.fileID=parseInt(jQuery('input[type="hidden"]',".ccm-file-selector").val())||null,(t.entity.id?t.entity.$update():t.entity.$save()).then(function(t){e(t)})});a.then(function(n){var a=l._routes.generate("api.event",["attributes",n.id]),i=jQuery("input,select,textarea","[custom-attributes]").serialize();jQuery.post(a,i).always(function(n){n.ok&&t.$apply(function(){t._requesting=!1,e.$emit("calendar.refresh"),o.classes.open=!1})})})},t.confirmDelete=!1,t.deleteEvent=function(){t.entity.$delete().then(function(t){t.ok&&(e.$emit("calendar.refresh"),o.classes.open=!1)})},t.nullifyInSeries=function(){var t=new l.eventNullify({eventTimeID:o.data.eventObj.eventTimeID,hideOnDate:o.data.eventObj.computedStartUTC});t.$save().then(function(){e.$emit("calendar.refresh"),o.classes.open=!1})}}]),angular.module("schedulizer.app").controller("CtrlSearchPage",["$rootScope","$scope","$http","$cacheFactory","API","_moment",function(e,t,n,a,i,r){function o(e){return e.id}function l(){return angular.extend({start:r(t.searchStart).format("YYYY-MM-DD"),end:r(t.searchEnd).format("YYYY-MM-DD"),fields:d.join(","),keywords:t.searchFields.keywords,tags:t.searchFields.tags.map(o).join(","),categories:t.searchFields.categories.map(o).join(",")},t.showAllEvents?{includeinactives:!0}:{},t.doGrouping?{grouping:!0}:{})}function s(){return n.get(i._routes.generate("api.eventList"),{cache:u,params:l()})}function c(){t.updateInProgress=!0,u.removeAll(),s().success(function(e){t.resultData=e,t.updateInProgress=!1}).error(function(e,n){t.updateInProgress=!1,console.warn(n,"Failed fetching data")}),t.searchOpen=!1}var u=a("searchCalendarData"),d=["eventID","eventTimeID","calendarID","calendarTitle","title","eventColor","isAllDay","isSynthetic","computedStartUTC","computedStartLocal","isActive"];t.momentJS=r,t.resultData=[],t.updateInProgress=!1,t.searchOpen=!1,t.eventTagList=[],t.eventCategoryList=[],t.searchFiltersSet=!1,t.isActiveOptions=[{label:"Include Inactive",value:!0},{label:"Active Only",value:!1}],t.showAllEvents=!0,t.groupingOptions=[{label:"Group Events",value:!0},{label:"Show Repeating",value:!1}],t.doGrouping=!0,t.searchStart=r(),t.searchEnd=r().add(1,"month"),t.searchFields={keywords:null,tags:[],categories:[]},t.toggleSearch=function(){t.searchOpen=!t.searchOpen},i.eventTags.query().$promise.then(function(e){t.eventTagList=e}),i.eventCategories.query().$promise.then(function(e){t.eventCategoryList=e}),t.$watch("searchFields",function(e){var n=!1;e.keywords&&(n=!0),0!==e.tags.length&&(n=!0),0!==e.categories.length&&(n=!0),t.searchFiltersSet=n},!0),t.clearSearchFields=function(){t.searchFields={keywords:null,tags:[],categories:[]},c()},t.sendSearch=c,e.$on("calendar.refresh",c),c()}]),angular.module("schedulizer.app").filter("numberContraction",function(){var e=["th","st","nd","rd"];return function(t){var n=20>t?t:t%(10*Math.floor(t/10)),a=3>=n?e[n]:e[0];return a}}),angular.module("schedulizer.app").filter("propsFilter",function(){return function(e,t){var n=[];return angular.isArray(e)?e.forEach(function(e){for(var a=!1,i=Object.keys(t),r=0;r<i.length;r++){var o=i[r],l=t[o].toLowerCase();if(-1!==e[o].toString().toLowerCase().indexOf(l)){a=!0;break}}a&&n.push(e)}):n=e,n}}),angular.module("schedulizer.app").directive("eventTimeForm",[function(){function e(){}return{restrict:"A",templateUrl:"/event_timing_form",scope:{_timeEntity:"=eventTimeForm"},link:e,controller:["$rootScope","$scope","$filter","API","Helpers","_moment",function(e,t,n,a,i,r){function o(){null===t._timeEntity.repeatEvery&&(t._timeEntity.repeatEvery=t.repeatEveryOptions[0]),null===t._timeEntity.repeatIndefinite&&(t._timeEntity.repeatIndefinite=t.repeatIndefiniteOptions[0].value),t._timeEntity.repeatTypeHandle===t.repeatTypeHandleOptions[2].value&&(null===t._timeEntity.repeatMonthlyMethod&&(t._timeEntity.repeatMonthlyMethod=t.repeatMonthlyMethodOptions.specific),null===t._timeEntity.repeatMonthlySpecificDay&&(t._timeEntity.repeatMonthlySpecificDay=t.repeatMonthlySpecificDayOptions[0]),null===t._timeEntity.repeatMonthlyOrdinalWeek&&(t._timeEntity.repeatMonthlyOrdinalWeek=t.repeatMonthlyDynamicWeekOptions[0].value),null===t._timeEntity.repeatMonthlyOrdinalWeekday&&(t._timeEntity.repeatMonthlyOrdinalWeekday=t.repeatMonthlyDynamicWeekdayOptions[0].value))}function l(){t._timeEntity.repeatMonthlyMethod=null,t._timeEntity.repeatMonthlyOrdinalWeek=null,t._timeEntity.repeatMonthlyOrdinalWeekday=null,t._timeEntity.repeatMonthlySpecificDay=null}function s(){t._timeEntity.weeklyDays=[],angular.forEach(t.weekdayRepeatOptions,function(e){e.checked=!1})}function c(){l(),s(),t._timeEntity.repeatEndUTC=null,t._timeEntity.repeatEvery=null,t._timeEntity.repeatIndefinite=null,t._timeEntity.repeatTypeHandle=null}t.repeatTypeHandleOptions=i.repeatTypeHandleOptions(),t.repeatIndefiniteOptions=i.repeatIndefiniteOptions(),t.weekdayRepeatOptions=i.weekdayRepeatOptions(),t.repeatMonthlyMethodOptions=i.repeatMonthlyMethodOptions(),t.repeatMonthlySpecificDayOptions=i.range(1,31),t.repeatMonthlyDynamicWeekdayOptions=i.repeatMonthlyDynamicWeekdayOptions(),t.repeatMonthlyDynamicWeekOptions=i.repeatMonthlyDynamicWeekOptions(),t.selectedWeekdays=function(){var e=n("filter")(t.weekdayRepeatOptions,{checked:!0});t._timeEntity.weeklyDays=e.map(function(e){return e.value})},angular.isArray(t._timeEntity.weeklyDays)&&t._timeEntity.weeklyDays.length>=1&&angular.forEach(t.weekdayRepeatOptions,function(e){e.checked=t._timeEntity.weeklyDays.indexOf(e.value)>-1}),t.$watch("_timeEntity.repeatTypeHandle",function(e){switch(e){case t.repeatTypeHandleOptions[0].value:t.repeatEveryOptions=i.range(1,31),l(),s();break;case t.repeatTypeHandleOptions[1].value:t.repeatEveryOptions=i.range(1,30),l();break;case t.repeatTypeHandleOptions[2].value:t.repeatEveryOptions=i.range(1,11),s();break;case t.repeatTypeHandleOptions[3].value:t.repeatEveryOptions=i.range(1,5),l(),s()}null!==t._timeEntity.repeatTypeHandle&&o()}),t.$watch("_timeEntity.repeatIndefinite",function(e){e===!0&&(t._timeEntity.repeatEndUTC=null)}),t.$watch("_timeEntity.startUTC",function(e){e&&(t.calendarEndMinDate=r(e).subtract(1,"day"),r(t._timeEntity.endUTC).isBefore(r(t._timeEntity.startUTC))&&(t._timeEntity.endUTC=r(t._timeEntity.startUTC)))}),t.$watch("_timeEntity.isRepeating",function(e){e===!0&&null===t._timeEntity.repeatTypeHandle&&(t._timeEntity.repeatTypeHandle=t.repeatTypeHandleOptions[0].value),e===!1&&c()}),t.showNullifiers=!1,a.eventNullify.query({eventTimeID:t._timeEntity.id},function(e){t.hasNullifiers=e.length>=1,angular.forEach(e,function(e){e._moment=r.utc(e.hideOnDate)}),t.configuredNullifiers=e}),t.cancelNullifier=function(t){t.$delete(function(){e.$emit("calendar.refresh")})}}]}}]),angular.module("schedulizer.app").run([function(){angular.element(document.querySelector("body")).append('<div modal-window class="schedulizer-app" ng-class="manager.classes"><a class="icon-close default-closer" modal-close></a><div class="modal-inner" ng-include="manager.data.source"></div></div>')}]).factory("ModalManager",[function(){return{classes:{open:!1},data:{source:null}}}]).directive("modalize",[function(){function e(e,t,n){t.on("click",function(){e.$apply(function(){e.manager.data=angular.extend({source:n.modalize},e.using)})})}return{restrict:"A",scope:{using:"=using"},link:e,controller:["$scope","ModalManager",function(e,t){e.manager=t}]}}]).directive("modalClose",["ModalManager",function(e){function t(t,n){n.on("click",function(){t.$apply(function(){e.classes.open=!1,e.data=null})})}return{restrict:"A",link:t}}]).directive("modalWindow",[function(){function e(e){e.$watch("manager.classes.open",function(t){angular.element(document.documentElement).toggleClass("schedulizer-modal",t),t||(e.manager.data=null)})}return{restrict:"A",scope:!0,link:e,controller:["$scope","ModalManager",function(e,t){e.manager=t,e.$on("$includeContentLoaded",function(){e.manager.classes.open=!0})}]}}]),angular.module("schedulizer.app").directive("redactorized",["$q",function(){function e(e,n,a,i){var r=!1;i.$render=function(){return r?(angular.isDefined(i.$viewValue)&&n.redactor("code.set",i.$viewValue),void 0):(n.redactor(angular.extend(t,{initCallback:function(){r=!0,angular.isDefined(i.$viewValue)&&this.code.set(i.$viewValue)},changeCallback:function(){i.$setViewValue(this.code.get())}})),void 0)}}var t={minHeight:200,concrete5:{filemanager:!0,sitemap:!0},plugins:["concrete5lightbox","undoredo","specialcharacters","table","concrete5magic"]};return{priority:0,require:"?ngModel",restrict:"A",link:e}}]),angular.module("schedulizer.app").provider("_moment",function(){this.$get=["$window","$log",function(e,t){return e.moment||(t.warn("MomentJS unavailable!"),!1)}]}),angular.module("schedulizer.app").factory("Helpers",["_moment",function(){return this.range=function(e,t){for(var n=[],a=e;t>=a;a++)n.push(a);return n},this.isActiveOptions=function(){return[{label:"Active",value:!0},{label:"Inactive",value:!1}]},this.repeatTypeHandleOptions=function(){return[{label:"Days",value:"daily"},{label:"Weeks",value:"weekly"},{label:"Months",value:"monthly"},{label:"Years",value:"yearly"}]},this.repeatIndefiniteOptions=function(){return[{label:"Forever",value:!0},{label:"Until",value:!1}]},this.weekdayRepeatOptions=function(){return[{label:"Sun",value:1},{label:"Mon",value:2},{label:"Tue",value:3},{label:"Wed",value:4},{label:"Thu",value:5},{label:"Fri",value:6},{label:"Sat",value:7}]},this.repeatMonthlyMethodOptions=function(){return{specific:"specific",dynamic:"ordinal"}},this.repeatMonthlyDynamicWeekOptions=function(){return[{label:"First",value:1},{label:"Second",value:2},{label:"Third",value:3},{label:"Fourth",value:4},{label:"Last",value:5}]},this.repeatMonthlyDynamicWeekdayOptions=function(){return[{label:"Sunday",value:1},{label:"Monday",value:2},{label:"Tuesday",value:3},{label:"Wednesday",value:4},{label:"Thursday",value:5},{label:"Friday",value:6},{label:"Saturday",value:7}]},this.eventColorOptions=function(){return[{value:"#A3D900"},{value:"#3A87AD"},{value:"#DE4E56"},{value:"#BFBFFF"},{value:"#FFFF73"},{value:"#FFA64D"},{value:"#CCCCCC"},{value:"#00B7FF"},{value:"#222222"}]},this}]),function(e,t){"use strict";t.module("calendry").factory("MomentJS",["$window","$log",function(e,t){return e.moment||(t.warn("Moment.JS not available in global scope, Calendry will be unavailable."),!1)}]).directive("calendry",["$cacheFactory","$document","$log","$q","MomentJS",function(e,n,a,i,r){function o(e){this.monthStart=e,this.monthEnd=r(this.monthStart).endOf("month"),this.calendarStart=r(this.monthStart).subtract(this.monthStart.day(),"day"),this.calendarEnd=r(this.monthEnd).add(6-this.monthEnd.day(),"day"),this.calendarDayCount=Math.abs(this.calendarEnd.diff(this.calendarStart,"days")),this.calendarDays=function(e,t,n){for(var a=0;e>=a;a++)n.push(r(t).add("days",a));return n}(this.calendarDayCount,this.calendarStart,[])}function l(e){var t=r.isMoment(e)?r(e).startOf("month"):r({day:1}),n=t.format(h);return f.get(n)?f.get(n):(f.put(n,new o(t)),f.get(n))}function s(e){return v.dayCellClass+"-"+e.format("YYYY_MM_DD")}function c(e){var t=r(),n=e.monthStart.format("YYYY_MM");if(m.get(n))return m.get(n).cloneNode(!0);for(var a=p.createDocumentFragment(),i=0,o=e.calendarDays.length;o>i;i++){var l=p.createElement("div"),c=e.calendarDays[i].isSame(e.monthStart,"month")?"month-incl":"month-excl",u=e.calendarDays[i].isSame(t,"day")?"is-today":"";l.setAttribute("id",s(e.calendarDays[i])),l.className=v.dayCellClass+" "+c+" "+u,l.innerHTML='<span class="date-num">'+e.calendarDays[i].format("DD")+"<small>"+e.calendarDays[i].format("MMM")+"</small></span>",a.appendChild(l)}return m.put(n,a),m.get(n).cloneNode(!0)}function u(e){var t=/^#?([a-f\d])([a-f\d])([a-f\d])$/i;e=e.replace(t,function(e,t,n,a){return t+t+n+n+a+a});var n=/^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(e);return n?{r:parseInt(n[1],16),g:parseInt(n[2],16),b:parseInt(n[3],16)}:null}function d(e,n,a,i,o){function l(){for(var e;e=p.pop();)e.remove();for(var t;t=f.pop();)t.$destroy()}function u(e){l();var a=t.element(n[0].querySelector(".calendar-render")),i=Math.ceil(e.calendarDayCount/7);t.element(n[0].querySelector(".calendry-body")).removeClass("week-rows-4 week-rows-5 week-rows-6").addClass("week-rows-"+i);var r=c(e);Array.prototype.slice.call(r.childNodes).forEach(function(n,a){r.childNodes[a]=t.element(n).data("_moment",e.calendarDays[a])}),a.empty().append(r)}function d(a){function i(e,t,n){e.append(t),p.push(t),f.push(n)}t.element(n[0].querySelectorAll(".event-cell")).remove();var l={};a.forEach(function(t){t._moment=r(t[e.instance.parseDateField],r.ISO_8601);var n=t._moment.format(y);l[n]||(l[n]=[]),l[t._moment.format(y)].push(t)}),e.instance.monthMap.calendarDays.forEach(function(a){var r=l[a.format(y)];if(r){var c=t.element(n[0].querySelector("#"+s(a)));if(c)for(var u=0,d=r.length;d>u;u++){var p=e.$new();p.eventObj=r[u],o(p,i.bind(null,c))}}})}var p=[],f=[];e.$watch("instance.monthMap",function(e){e&&u(e)}),e.$watch("events",function(e){t.isArray(e)&&d(e)})}if(!r)return a.warn("Calendry not instantiated due to missing momentJS library"),void 0;var p=n[0],f=e("monthMap"),m=e("docFrags"),h="YYYY_MM",y="YYYY_MM_DD",v={forceListView:!1,daysOfWeek:r.weekdaysShort(),currentMonth:r(),dayCellClass:"day-node",parseDateField:"startDate",onMonthChange:function(){},onDropEnd:function(){}};return{restrict:"A",scope:{instance:"=calendry"},replace:!0,templateUrl:"/calendry",transclude:!0,link:d,controller:["$scope",function(e){var n=this;e.instance=t.extend(n,v,e.instance||{}),this.goToCurrentMonth=e.goToCurrentMonth=function(){e.instance.currentMonth=r()},this.goToPrevMonth=e.goToPrevMonth=function(){e.instance.currentMonth=r(e.instance.currentMonth).subtract({months:1})},this.goToNextMonth=e.goToNextMonth=function(){e.instance.currentMonth=r(e.instance.currentMonth).add({months:1})},this.toggleListView=e.toggleListView=function(){e.instance.forceListView=!e.instance.forceListView},e.$watch("instance.currentMonth",function(t){t&&(e.instance.monthMap=l(t),e.instance.onMonthChange.apply(n,[e.instance.monthMap]))}),e.$watch("instance.events",function(t){t&&(e.events=t)}),e.helpers={eventFontColor:function(e){var t=u(e),n=Math.round((299*t.r+587*t.g+114*t.b)/1e3);return n>125?"#000000":"#FFFFFF"}}}]}}])}(window,window.angular),angular.module("schedulizer.app").controller("CtrlManageCategories",["$scope","API",function(e,t){e.categoriesList=[],t.eventCategories.query().$promise.then(function(t){e.categoriesList=t}),e.remove=function(t){e.categoriesList[t].$delete().then(function(){e.categoriesList.splice(t,1)})},e.persist=function(t){(e.categoriesList[t].id?e.categoriesList[t].$update():e.categoriesList[t].$save()).then(function(e){console.log(e)})},e.addCategory=function(){e.categoriesList.push(new t.eventCategories)}}]);
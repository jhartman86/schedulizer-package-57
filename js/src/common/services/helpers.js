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
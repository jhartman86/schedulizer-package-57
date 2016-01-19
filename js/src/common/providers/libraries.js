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
    }).

    provider('_modernizr', function(){
        this.$get = ['$window', '$log',
            function( $window, $log ){
                return $window['Modernizr'] || ($log.warn('Modernizr unavailable!'), false);
            }
        ];
    });
angular.module('schedulizer.app').

    directive('redactorized', ['$q', function( $q ){

        /**
         * Redactor settings, pulled from Concrete5 defaults
         * @type {{minHeight: number, concrete5: {filemanager: boolean, sitemap: boolean, lightbox: boolean}, plugins: Array}}
         */
        var settings = {
            minHeight: 200,
            concrete5: {
                filemanager: true,
                sitemap: true
                //,lightbox: true
            },
            //plugins: ['fontcolor', 'concrete5','underline', 'undoredo', 'concrete5magic']
            plugins: ["concrete5lightbox","undoredo","specialcharacters","table","concrete5magic"]
        };

        /**
         * @param scope
         * @param $element
         * @param attrs
         * @param Controller ngModel controller
         * @private
         */
        function _link( scope, $elem, attrs, ngModelController ){
            var initialized = false;

            ngModelController.$render = function(){
                // Init if not done so yet
                if( ! initialized ){
                    $elem.redactor(angular.extend(settings, {
                        initCallback: function(){
                            initialized = true;
                            if( angular.isDefined(ngModelController.$viewValue) ){
                                this.code.set(ngModelController.$viewValue);
                            }
                        },
                        changeCallback: function(){
                            ngModelController.$setViewValue(this.code.get());
                        }
                    }));
                    return;
                }

                // If view value is defined, set it
                if( angular.isDefined(ngModelController.$viewValue) ){
                    $elem.redactor('code.set', ngModelController.$viewValue);
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
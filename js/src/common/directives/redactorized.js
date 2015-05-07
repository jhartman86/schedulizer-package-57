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
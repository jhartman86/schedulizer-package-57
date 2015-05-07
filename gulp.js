module.exports = function( gulp ){

    // Get the name of the parent directory so we can use it to "namespace" tasks
    var directoryName = require('path').basename(__dirname);

    /** Return full absolute filesystem path to something in this directory */
    function _pathTo(_path){
        return __dirname + '/' + _path;
    }

    /** Prepends a task name with the parent directory for uniqueness. */
    function _taskName( taskName ){
        return directoryName + ':' + taskName;
    }

    /**
     * Include required libraries, and declare source paths.
     */
    var utils   = require('gulp-util'),
        concat  = require('gulp-concat'),
        uglify  = require('gulp-uglify'),
        sass    = require('gulp-ruby-sass'),
        jshint  = require('gulp-jshint'),
        sources = {
            scss: {
                app: _pathTo('css/src/app.scss')
            },
            js: {
                core: [
                    _pathTo('bower_components/moment/moment.js'),
                    _pathTo('bower_components/angular/angular.js'),
                    _pathTo('bower_components/angular-resource/angular-resource.js'),
                    _pathTo('bower_components/angular-sanitize/angular-sanitize.js'),
                    // Angular-strap (Bootstrap) Modules
                    _pathTo('bower_components/angular-strap/dist/modules/dimensions.js'),
                    _pathTo('bower_components/angular-strap/dist/modules/date-parser.js'),
                    _pathTo('bower_components/angular-strap/dist/modules/date-formatter.js'),
                    _pathTo('bower_components/angular-strap/dist/modules/tooltip.js'),
                    _pathTo('bower_components/angular-strap/dist/modules/datepicker.js'),
                    _pathTo('bower_components/angular-strap/dist/modules/timepicker.js'),
                    // Angular-ui select module
                    _pathTo('bower_components/angular-ui-select/dist/select.js')
                ],
                app: [
                    _pathTo('js/src/**/*.js')
                ]
            }
        };

    /**
     * Sass compilation
     * @param _style
     * @returns {*|pipe|pipe}
     */
    function runSass( files, _style ){
        return gulp.src(files)
            .pipe(sass({compass:true, style:(_style || 'nested')}))
            .on('error', function( err ){
                utils.log(utils.colors.red(err.toString()));
                this.emit('end');
            })
            .pipe(gulp.dest(_pathTo('css/')));
    }

    /**
     * Javascript builds (concat, optionally minify)
     * @param files
     * @param fileName
     * @param minify
     * @returns {*|pipe|pipe}
     */
    function runJs( files, fileName, minify ){
        return gulp.src(files)
            .pipe(concat(fileName))
            .pipe(minify === true ? uglify() : utils.noop())
            .pipe(gulp.dest(_pathTo('js/')));
    }

    /**
     * Run JSHint
     * @param files
     * @returns {*|pipe|pipe}
     */
    function runJsHint( files ){
        return gulp.src(files)
            .pipe(jshint(_pathTo('.jshintrc')))
            .pipe(jshint.reporter('jshint-stylish'));
    }

    /** Register individual tasks */
    gulp.task(_taskName('jshint'), function(){ return runJsHint(sources.js.app); });
    gulp.task(_taskName('sass:app:dev'), function(){ return runSass(sources.scss.app); });
    gulp.task(_taskName('sass:app:prod'), function(){ return runSass(sources.scss.app, 'compressed'); });
    gulp.task(_taskName('js:core:dev'), function(){ return runJs(sources.js.core, 'core.js', false) });
    gulp.task(_taskName('js:core:prod'), function(){ return runJs(sources.js.core, 'core.js', true) });
    gulp.task(_taskName('js:app:dev'), [_taskName('jshint')], function(){ return runJs(sources.js.app, 'app.js', false) });
    gulp.task(_taskName('js:app:prod'), [_taskName('jshint')], function(){ return runJs(sources.js.app, 'app.js', true) });

    /** Run all dev tasks */
    gulp.task(_taskName('build:dev'), [
        _taskName('sass:app:dev'),
        _taskName('js:core:dev'),
        _taskName('js:app:dev')
    ], function(){ utils.log(utils.colors.bgGreen('Dev build OK')); });

    /** Run all prod tasks */
    gulp.task(_taskName('build:prod'), [
        _taskName('sass:app:prod'),
        _taskName('js:core:prod'),
        _taskName('js:app:prod')
    ], function(){ utils.log(utils.colors.bgGreen('Prod build OK')); });

    /** Watches */
    gulp.task(_taskName('watches'), function(){
        gulp.watch(_pathTo('css/src/**/*.scss'), {interval:1000}, [_taskName('sass:app:dev')]);
        gulp.watch(_pathTo('js/src/**/*.js'), {interval:1000}, [_taskName('js:app:dev')]);
    });

};
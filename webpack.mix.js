let mix  = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

var vendors = 'resources/assets/vendors/';

var resourcesAssets = 'resources/assets/';
var srcCss = resourcesAssets + 'css/';
var srcJs = resourcesAssets + 'js/';

var dest = 'public/';
var destFonts = dest + 'fonts/';
var destCss = dest + 'css/';
var destJs = dest + 'js/';

var paths = {
    'jquery': vendors + 'jquery/dist',
    'jqueryUi': vendors + 'jquery-ui',
    'moment': vendors + 'moment',
    'bootstrap': vendors + 'bootstrap/dist',
    'dataTables': vendors + 'datatables/media',
    'fontawesome': vendors + 'font-awesome',
    'animate': vendors + 'animate.css',
    'underscore': vendors + 'underscore',
    'tether': vendors + 'tether/dist',
    'jQueryStorageAPI': vendors + 'jQuery-Storage-API',
    'pace': vendors + 'pace',
    'lazyload': vendors + 'lazyload',
    'screenfull': vendors + 'screenfull/dist',
    'select2': vendors + 'select2/dist',
    'eonasdanBootstrapDatetimepicker': vendors + 'eonasdan-bootstrap-datetimepicker/build',
    'fullcalendar': vendors + 'fullcalendar/dist',
    'summernote': vendors + 'summernote/dist',
    'morris': vendors + 'morris.js',
    'raphael': vendors + 'raphael',
    'pusher': vendors + 'pusher/dist/web',
    'icheck': vendors + 'iCheck',
    'jasnyBootstrap': vendors + 'jasny-bootstrap/dist',
    'toastr': vendors + 'toastr',
    'dropzone': vendors + 'dropzone/dist',
    'bootstrapValidator': vendors + 'bootstrapvalidator/dist',
    'select2BootstrapTheme': vendors + 'select2-bootstrap-theme/dist',
    'c3': vendors + '/c3',
    'slimscroll': vendors + 'jquery-slimscroll/',
    'bootstrap_tagsinput': vendors + 'bootstrap-tagsinput/dist/',
};

//Custom Styles
mix.combine(
    [
        srcCss + 'crm_bootstrap.css',
        // srcCss + 'metisMenu.min.css',
        srcCss + 'crm.css',
        srcCss + 'mail.css'
    ], destCss + 'secure.css');

//Custom Javascript
mix.js(srcJs + 'app.js', destJs + 'secure.js');
mix.copy(srcJs + 'stripe', destJs + 'stripe');
mix.copy(srcCss + 'stripe', destCss + 'stripe');

mix.copy('resources/assets/vendors/bootstrap/dist/css/bootstrap.min.css', 'public/css');


mix.copy('resources/assets/js/metisMenu.min.js', 'public/js');
mix.copy('resources/assets/js/crm_app.js', 'public/js');
mix.copy('resources/assets/js/todolist.js', 'public/js');

// Copy fonts straight to public
mix.copy('resources/assets/vendors/bootstrap/fonts', destFonts);
mix.copy('resources/assets/vendors/bootstrap/fonts', destFonts+'bootstrap/');
mix.copy('resources/assets/vendors/font-awesome/fonts', destFonts);
mix.copy('resources/assets/css/material-design-icons/iconfont', destFonts);

// Copy images straight to public
mix.copy('resources/assets/vendors/jquery-ui/themes/base/images', 'public/img');
mix.copy('resources/assets/vendors/datatables/media/images', 'public/img');

mix.copy('resources/assets/img', 'public/img',false);
mix.copy('resources/assets/images', 'public/images',false);
mix.copy('resources/assets/img/logo.png', 'public/uploads/site');
mix.copy('resources/assets/img/street.jpg', 'public/img');
mix.copy('resources/assets/img/fav.ico', 'public/uploads/site');
mix.copy('resources/assets/img/user.png', 'public/uploads/avatar');

// copy js files ( we don't need to combine all files into single js)
mix.copy('resources/assets/vendors/screenfull/dist/screenfull.min.js', 'public/js');

//c3&d3 chart css and js files
mix.copy('resources/assets/vendors/c3/c3.min.css', 'public/css');
mix.copy('resources/assets/vendors/c3/c3.min.js', 'public/js');
mix.copy('resources/assets/vendors/d3/d3.min.js', 'public/js');
mix.copy('resources/assets/js/d3.v3.min.js', 'public/js');

//jvector map files
mix.copy('resources/assets/vendors/bower-jvectormap/jquery-jvectormap-1.2.2.min.js', 'public/js');
mix.copy('resources/assets/css/jquery-jvectormap.css', 'public/css');
mix.copy('resources/assets/vendors/bower-jvectormap/jquery-jvectormap-world-mill-en.js', 'public/js');
mix.copy('resources/assets/js/jquery-jvectormap-us-aea-en.js', 'public/js');

// install
mix.copy('resources/assets/css/custom_install.css', 'public/css');

//icheck
mix.copy('resources/assets/css/icheck.css', 'public/css');
mix.copy('resources/assets/vendors/iCheck/icheck.min.js', 'public/js');

//countUp
mix.copy('resources/assets/vendors/countUp.js/dist/countUp.min.js', 'public/js');
mix.copy('resources/assets/css/login_register.css', 'public/css');

//slimscroll
mix.copy('resources/assets/vendors/jquery-slimscroll/jquery.slimscroll.js', 'public/js');

//bootstrap_tagsinput
mix.copy(paths.bootstrap_tagsinput + 'bootstrap-tagsinput.css', 'public/css');
mix.copy(paths.bootstrap_tagsinput + 'bootstrap-tagsinput.js', 'public/js');

//bootstrapValidator
mix.copy(paths.bootstrapValidator + '/js/language/zh_CN.js', 'public/js');

//CSS Libraries
mix.combine(
    [
        paths.fontawesome + "/css/font-awesome.min.css",
        paths.animate + "/animate.min.css",
        srcCss + "material-design-icons/material-design-icons.css",
        paths.select2 + "/css/select2.min.css",
        paths.eonasdanBootstrapDatetimepicker + '/css/bootstrap-datetimepicker.css',
        srcCss + 'dataTables.bootstrap.css',
        paths.fullcalendar + '/fullcalendar.css',
        paths.summernote + '/summernote.css',
        paths.summernote + '/summernote-bs3.css',
        paths.morris + '/morris.css',
        paths.bootstrapValidator + '/css/bootstrapValidator.min.css',
        paths.dropzone + '/dropzone.css',
        paths.jasnyBootstrap + "/css/jasny-bootstrap.min.css",
        paths.toastr + '/toastr.css',
        paths.select2BootstrapTheme + "/select2-bootstrap.min.css"
    ], destCss + 'libs.css')
    .version();


//JS Libraries
mix.combine(
    [
        paths.jquery + "/jquery.js",
        paths.jqueryUi + "/jquery-ui.min.js",
        paths.tether + "/js/tether.min.js",
        paths.bootstrap + "/js/bootstrap.min.js",
        paths.dataTables + "/js/jquery.dataTables.min.js",
        paths.dataTables + "/js/dataTables.bootstrap.js",
        paths.pace + '/pace.min.js',
        paths.underscore + "/underscore-min.js",
        paths.select2 + "/js/select2.min.js",
        paths.moment + '/moment.js',
        paths.moment + '/locale/zh-cn.js',
        paths.eonasdanBootstrapDatetimepicker + '/js/bootstrap-datetimepicker.min.js',
        paths.fullcalendar + '/fullcalendar.js',
        paths.fullcalendar + '/locale/zh-cn.js',
        paths.summernote + '/summernote.js',
        paths.morris + '/morris.js',
        paths.raphael + '/raphael.js',
        paths.toastr + '/toastr.min.js',
        paths.bootstrapValidator + '/js/bootstrapValidator.min.js',
        paths.jasnyBootstrap + "/js/jasny-bootstrap.min.js",
        srcJs + "palette.js"
    ], destJs + 'libs.js')
    .version();

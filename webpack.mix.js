const mix = require('laravel-mix');

mix.js(
    'resources/js/app.js', 
    'public/assets/dist/bundle.min.js'
).vue();

mix.babel([
    'public/assets/vendor/jquery.js',
    'public/assets/vendor/jquery-ui.min.js',
    'public/assets/vendor/bootstrap/js/bootstrap.js',
    'public/assets/vendor/contextmenu/bootstrap-contextmenu.js',

    'public/assets/vendor/jquery.colorpicker.js',

    'public/assets/vendor/jquery.table2excel.js',

    'public/assets/vendor/viewerjs/viewer.js',

    'public/assets/vendor/toastr/toastr.js',

    'public/assets/vendor/jquery.paging.js',

    'public/assets/vendor/select2/select2.js',
    'public/assets/vendor/select2/zh-CN.js',

    'public/assets/js/aggrid/celleditor/dropdown.js',
    'public/assets/js/aggrid/celleditor/suggest.js',
    'public/assets/js/aggrid/form.js',
    'public/assets/js/aggrid.js',

    'public/assets/vendor/pcas.js',
    'public/assets/vendor/template.min.js',

    'public/assets/js/gdoo.js',
    'public/assets/js/select2.js',
    'public/assets/js/gdoo.dialog.input.js',
    'public/assets/js/search.js',
    'public/assets/js/dialog.js',
    'public/assets/js/listview.js',
    'public/assets/js/model.js',
    'public/assets/js/support.js',

],'public/assets/dist/app.min.js');

mix.combine([
    'public/assets/vendor/bootstrap/css/font-awesome.min.css',
    'public/assets/vendor/bootstrap/css/animate.css',
    'public/assets/vendor/bootstrap/css/bootstrap.css',
    'public/assets/vendor/bootstrap/css/glyphicon.css',
    'public/assets/vendor/toastr/toastr.css',

    'public/assets/vendor/viewerjs/viewer.css',
    'public/assets/vendor/select2/select2.css',

    'public/assets/css/reset.css',
    'public/assets/css/gdoo.css',

    'public/assets/css/aggrid.css',

],'public/assets/dist/app.min.css')

mix.combine([
    'public/assets/vendor/ag-grid/ag-grid.css',
    'public/assets/vendor/ag-grid/ag-theme-balham.css',
],'public/assets/vendor/ag-grid/ag-grid.min.css')

mix.babel([
    'public/assets/libs/modernizr.min.js',
    'public/assets/vendor/jquery.js',
    'public/assets/vendor/jquery-ui.min.js',
    'public/assets/vendor/bootstrap/js/bootstrap.js',
    'public/assets/vendor/toastr/toastr.js',
    'public/assets/vendor/template.min.js',
    'public/assets/js/dialog.js',
    'public/assets/js/menu.js',
    'public/assets/js/support.js',
    'public/assets/vendor/addtabs/bootstrap.addtabs.js',
    
],'public/assets/dist/index.min.js')

mix.combine([
    'public/assets/vendor/bootstrap/css/font-awesome.min.css',
    'public/assets/vendor/bootstrap/css/animate.css',
    'public/assets/vendor/bootstrap/css/bootstrap.css',
    'public/assets/vendor/bootstrap/css/glyphicon.css',
    'public/assets/vendor/toastr/toastr.css',

    'public/assets/css/reset.css',
    'public/assets/css/gdoo.css',
    'public/assets/css/menu.css',

],'public/assets/dist/index.min.css');

if (mix.inProduction()) {
    mix.version();
}

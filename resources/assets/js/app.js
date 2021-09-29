global.$ = window.jQuery = require('../../../node_modules/jquery/dist/jquery');

require('../../../node_modules/jquery-ui-dist/jquery-ui.min');
require('../../../node_modules/bootstrap-sass/assets/javascripts/bootstrap');
require('../../../node_modules/datatables.net/js/jquery.dataTables');
require('../../../node_modules/bootstrap-switch/dist/js/bootstrap-switch.min');
require('../../../node_modules/@fengyuanchen/datepicker/dist/datepicker');
require('../../../node_modules/@fengyuanchen/datepicker/i18n/datepicker.en-US');
require('../../../node_modules/timepicker/jquery.timepicker.min');
require('../../../node_modules/datepair.js/dist/datepair');
require('../../../node_modules/datepair.js/dist/jquery.datepair.min');
require('../../../node_modules/chart.js/dist/Chart.min');
require('../../../node_modules/pivottable/dist/pivot.min');




$( document ).ready(function() {
    $( document ).ajaxStop(function() {
        $("#ajaxloader").removeClass("active");
    });

    $( document ).ajaxStart(function(e) {
        if(noAjaxLoading){
            return true;
        }
        $("#ajaxloader").addClass("active");
    });

    $('body').delegate('.tooltip-im-activator','mouseenter', function(event) {
        $(".tooltip").addClass("tooltip-im-active");
        $("#tooltip-hover").addClass("active");
    });

    $('body').delegate('.tooltip-im-activator','mouseleave', function(event) {
        $("#tooltip-hover").removeClass("active");
    });
});

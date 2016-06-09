/**
 * Created by mconyango on 7/19/15.
 */
(function ($) {
    'use strict';

    var HIGHCHART = function (graphOptions, filterOptions) {
        this.chart = {};
        this.filterOptions = filterOptions;
        this.graphOptions = graphOptions;
        if (this.filterOptions.showFilter) {
            setGraphFilter.call(this);
        }
    };

    var setGraphFilter = function () {
        var $this = this
            , date_range_selector = '#' + $this.filterOptions.dateRangeFilterId;
        //set date range picker
        $(date_range_selector).daterangepicker({
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
                startDate: $this.filterOptions.dateRangeFrom,
                endDate: $this.filterOptions.dateRangeTo,
                format: 'MMM D, YYYY',
            },
            function (start, end) {
                var date = start.format('MMM D, YYYY') + ' - ' + end.format('MMM D, YYYY');
                $(date_range_selector + ' span').html(date);
                $(date_range_selector).find('input[type="hidden"]').val(date);
                $this.reloadGraph();
            }
        );

        //graph_type events
        $('#' + $this.filterOptions.graphTypeFilterId).on('change.myapp.plugin.highchart', function () {
            $this.reloadGraph();
        });
    };

    HIGHCHART.prototype.create = function () {
        var $this = this;
        //destroy any existing chart b4 creating another one
        if (!MyApp.utils.empty($this.chart)) {
            $this.chart.destroy();
        }

        $this.chart = new Highcharts.Chart($this.graphOptions);
    };

    HIGHCHART.prototype.reloadGraph = function () {
        var $this = this
            , form = $('#' + $this.filterOptions.filterFormId)
            , url = form.attr('action')
            , data = form.serialize();

        $.ajax({
            type: 'get',
            url: url,
            data: data,
            dataType: 'json',
            success: function (data) {
                // $this.data = data;
                // $this.create();
            },
            beforeSend: function () {
                MyApp.utils.startBlockUI('Please wait...');
            },
            complete: function () {
                MyApp.utils.stopBlockUI();
            },
            error: function (XHR) {
                if (MyApp.DEBUG_MODE) {
                    var message = XHR.responseText;
                    MyApp.utils.showAlertMessage(message, 'error');
                }
            }
        });
    };

    var PLUGIN = function (graphOptions, filterOptions) {
        var obj = new HIGHCHART(graphOptions, filterOptions);
        obj.create();
    };

    MyApp.plugin.highCharts = PLUGIN;
}(jQuery));

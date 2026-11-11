(function ($) {

    var $table = $("#turbosmtp-messages-list-table");
    var $tbody = $table.find("tbody");
    var $theaders = $table.find("thead, tfoot");

    var list = {

        display: function (data) {

            // WordPress core (common.js) already toggles .is-expanded on
            // .toggle-row clicks: binding a second toggle here cancelled it
            // out. Only mirror the state for assistive technologies.
            $tbody.on("click", ".toggle-row", function () {
                var $button = $(this);
                window.setTimeout(function () {
                    $button.attr("aria-expanded", $button.closest("tr").hasClass("is-expanded") ? "true" : "false");
                }, 0);
            });

            data = $.extend({
                turbosmtp_get_stats_history_nonce: $('#turbosmtp_get_stats_history_nonce').val(),
                action: 'turbosmtp_get_stats_history',
                filter: 'all'
            }, data);

            list.init(data);

        },

        init: function (data) {

            var timer;
            var delay = 500;
            var origin_data = data;

            $('.tablenav-pages a, .manage-column.sortable a, .manage-column.sorted a').on('click', function (e) {
                e.preventDefault();
                var query = this.search.substring(1);

                var data = $.extend(origin_data, {
                    paged: list.__query(query, 'paged') || '1',

                });

                var orderby = list.__query(query, 'orderby');
                var order = list.__query(query, 'order');

                if (orderby){
                    data['orderby'] = orderby;
                }

                if (order){
                    data['order'] = order;
                }

                list.update(data);
            });

            $('input[name=paged]').on('keyup', function (e) {

                if (13 == e.which)
                    e.preventDefault();

                var data = $.extend(origin_data, {
                    paged: parseInt($('input[name=paged]').val()) || '1',
                });

                window.clearTimeout(timer);
                timer = window.setTimeout(function () {
                    list.update(data);
                }, delay);
            });

            $('#email-sent-list').on('submit', function (e) {
                e.preventDefault();
            });

            $("tbody tr").prop("title", "");

            $tbody.find(".toggle-row").each(function () {
                $(this).attr("aria-expanded", $(this).closest("tr").hasClass("is-expanded") ? "true" : "false");
            });

            $(".ts-loading").hide();
            $(".history-step").show();

        },

        update: function (data) {

            $tbody.addClass("table-loading");
            $(".ts-history-table-loading").show();

            $theaders.removeClass().addClass(data.filter);

            $.ajax({

                url: ajaxurl,
                data: $.extend(
                    {
                        turbosmtp_get_stats_history_nonce: $('#turbosmtp_get_stats_history_nonce').val(),
                        action: 'turbosmtp_get_stats_history'
                    },
                    data
                ),
                success: function (response) {

                    response = $.parseJSON(response);

                    if (response.rows.length)
                        $tbody.html(response.rows);
                    if (response.column_headers.length)
                        $theaders.find("tr").html(response.column_headers);
                    if (response.pagination.bottom.length)
                        $('.tablenav.top .tablenav-pages').html($(response.pagination.top).html());
                    if (response.pagination.top.length)
                        $('.tablenav.bottom .tablenav-pages').html($(response.pagination.bottom).html());


                    $tbody.removeClass("table-loading");
                    $(".ts-history-table-loading").hide();

                    list.init(data);
                },
                error: function(){
                    alert(ts.i18n.connection_request_error)
                    $tbody.removeClass("table-loading");
                    $(".ts-history-table-loading").hide();
                }
            });
        },

        __query: function (query, variable) {

            var vars = query.split("&");
            for (var i = 0; i < vars.length; i++) {
                var pair = vars[i].split("=");
                if (pair[0] === variable)
                    return pair[1];
            }
            return false;
        }
    };

    var chartData = null;

    $(function () {

        var $tooltip = $(".error-tooltip");

        $(document).on('mouseenter', '#ts-history-table tbody tr', function(e) {
            var $row = $(this);
            var $errorCell = $row.find('.column-error');
            var errorText = $errorCell.text().trim();

            if (errorText !== '') {
                $tooltip.text(errorText);

                var offset = $row.offset();
                $tooltip.css({
                    top: offset.top + $row.outerHeight(),
                    left: e.pageX,
                    display: 'block'
                });
            }
        });

        $(document).on('mouseleave', '#ts-history-table tbody tr', function() {
            $tooltip.hide();
        });

        // The API key icon has its own tooltip: hide the error one meanwhile.
        $(document).on('mouseenter', '#ts-history-table .tswp-api-key-wrap', function() {
            $tooltip.hide();
        });

        // WCAG 1.4.13: the API key tooltip can be dismissed with Escape.
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                $('.tswp-api-key-wrap').addClass('is-dismissed');
            }
        });
        $(document).on('mouseleave focusout', '#ts-history-table .tswp-api-key-wrap', function() {
            $(this).removeClass('is-dismissed');
        });

        $(document).on('mousemove', '#ts-history-table tbody tr', function(e) {
            $tooltip.css({
                left: e.pageX + 10,
                top: e.pageY + 10
            });
        });

        $(".other-infos-toggle button").on("click", function () {
            var $button = $(this);
            var expanded = $button.attr("aria-expanded") === "true";

            $("#turbo-stat-chart-wrap").toggle(!expanded);
            $button
                .attr("aria-expanded", expanded ? "false" : "true")
                .attr("aria-label", expanded ? ts.i18n.show_chart : ts.i18n.hide_chart);
            $button.find("span")
                .toggleClass("icon-arrow-down", !expanded)
                .toggleClass("icon-arrow-left", expanded);
        });

        var from_date = $("input[name='from_date']");
        var start = moment().subtract(6, 'days').startOf('day').toDate();
        var end = moment().startOf('day').toDate();

        $(from_date).daterangepicker({
            startDate: start,
            endDate: end,
            locale: {
                format: 'DD/M/YY',
                cancelLabel: ts.i18n.drp_preset['cancel'],
                applyLabel: ts.i18n.drp_preset['apply'],
                customRangeLabel: ts.i18n.drp_preset['customrange']
            },
            ranges: {
                [ts.i18n.drp_preset['today']]: [moment(), moment()],
                [ts.i18n.drp_preset['yesterday']]: [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                [ts.i18n.drp_preset['last7days']]: [moment().subtract(6, 'days'), moment()],
                [ts.i18n.drp_preset['last30days']]: [moment().subtract(29, 'days'), moment()],
                [ts.i18n.drp_preset['thismonth']]: [moment().startOf('month'), moment().endOf('month')],
                [ts.i18n.drp_preset['lastmonth']]: [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }).on("change", function () {
            GetChartData(1);
        });



        function respondCanvas() {

            if (window.chart != null)
                window.chart.destroy();

            var ctx = $('#turbo-stat-chart').get(0).getContext("2d");

            // The height comes from .tswp-chart-wrap (CSS), so the chart stays
            // readable on narrow screens instead of keeping a fixed ratio.
            window.chart = new Chart(ctx, {
                type: 'line',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        position: 'bottom'
                    },
                    scales: {
                        xAxes: [{
                            ticks: {
                                maxRotation: 0,
                                autoSkip: true,
                                // ~90px per YYYY-MM-DD label, so they never overlap on phones
                                maxTicksLimit: Math.max(2, Math.min(10, Math.floor($('#turbo-stat-chart-wrap').width() / 90)))
                            }
                        }]
                    }
                }
            });

        }

        function getType(key) {
            return ts.i18n[key];
        }

        function getColor(key, value) {

            switch (key) {
                case "queued":
                    return "rgba(181,181,190," + value + ")";
                case "delivered":
                    return "rgba(0,144,194," + value + ")";
                case "bounce":
                    return "rgba(232,193,79," + value + ")";
                case "opens":
                    return "rgba(0,82,147," + value + ")";
                case "clicks":
                    return "rgba(104,159,56," + value + ")";
                case "unsubscribes":
                    return "rgba(235,91,135," + value + ")";
                case "spam":
                    return "rgba(198,40,40," + value + ")";
                case "drop":
                    return "rgba(133,133,141," + value + ")";
                default:
                    return "rgba(72,72,79," + value + ")";

            }

        }

        var fillBox = function (key, values, tot, all) {

            var total = 0;
            var pct = 0;

            for (var i = 0; i < values.length; i++)
                total += values[i];

            pct = (tot === 0 ?  0 : Number((total / tot) * 100)).toFixed(2);

            var pct_part = pct.toString().split('.');

            if (all) {
                $("." + key).find("h4").html(pct_part[0] + ".<span>" + pct_part[1] + "%</span>");
                $("." + key).find("p").text(total);
            }

            else
                $("." + key).find("p").text(pct + "%");


        }

        var GetChartData = function (update) {

            $(".other-infos").hide();
            $(".other-infos-columns").hide();
            $(".other-infos-loading").show();
            $(".other-infos-noresults").hide();
            $(".ts-loading").show();
            $(".history-step").hide();

            var picker = $(from_date).data('daterangepicker');

            $.ajax({
                url: ajaxurl,
                method: 'POST',
                dataType: 'json',
                data: {
                    'action': 'turbosmtp_get_stats_chart',
                    'turbosmtp_get_stats_history_nonce': $('#turbosmtp_get_stats_history_nonce').val(),
                    'start_date': picker.startDate.format("YYYY-MM-DD"),
                    'end_date': picker.endDate.format("YYYY-MM-DD")
                },
                success: function (d) {

                    if (d.data.stats.aggs && d.data.stats.aggs.length == 0) {
                        $(".other-infos-noresults").show();
                        return;
                    }

                    $("#paid-client").show();

                    $(".total-email").find("h4").text(d.data.stats.count);
                    $(".chartjs-hidden-iframe").remove();

                    chartData = window.tsAggsChartJs.dataToChartJs(
                        d.data.stats.aggs,
                        picker.startDate.format("YYYY-MM-DD"),
                        picker.endDate.format("YYYY-MM-DD"),
                        'd',
                        function(dataset, key){

                            if (key == "delivered" || key == "opens" || key == "clicks" || key == 'bounce')
                                fillBox(key, dataset.data, d.data.stats.count, true);

                            else if (key != "all")
                                fillBox(key, dataset.data, d.data.stats.count, false);

                            return {
                                ...dataset,
                                label: getType(key),
                                backgroundColor: getColor(key, 0.2),
                                borderColor: getColor(key, 1),
                                pointBorderColor: getColor(key, 1),
                                pointBackgroundColor: "#000000",
                                lineTension: 0
                            }
                        }
                    );

                    if (chartData){
                        chartData.labels = chartData.labels.map(
                            label => moment
                                .unix(label)
                                .format("YYYY-MM-DD")
                        )
                    }

                    $(".other-infos").show();
                    $(".other-infos-columns").css("display", "flex");
                    $(".other-infos-loading").hide();

                    respondCanvas();

                    if (update == 0) {

                        list.display({
                            'begin': picker.startDate.format("YYYY-MM-DD"),
                            'end': picker.endDate.format("YYYY-MM-DD")
                        });

                    }

                    else {
                        // The table is reset to all events: keep the filter state in sync.
                        $("div[data-ts-filter]").removeClass("active").attr("aria-pressed", "false");
                        $("div[data-ts-filter='all']").addClass("active").attr("aria-pressed", "true");

                        list.update({
                            'filter': 'all',
                            'begin': picker.startDate.format("YYYY-MM-DD"),
                            'end': picker.endDate.format("YYYY-MM-DD")
                        });
                    }

                },
                error: function(){
                    alert(ts.i18n.connection_request_error);
                }
            });
        };

        $("div[data-ts-filter]").on("keydown", function (e) {
            if (e.key === "Enter" || e.key === " ") {
                e.preventDefault();
                $(this).trigger("click");
            }
        });

        $("div[data-ts-filter]").on("click", function () {
            var picker = $(from_date).data('daterangepicker');
            $("div[data-ts-filter]").removeClass("active").attr("aria-pressed", "false");
            $(this).addClass("active").attr("aria-pressed", "true");
            list.update({
                'filter': $(this).data("ts-filter"),
                'begin': picker.startDate.format("YYYY-MM-DD"),
                'end': picker.endDate.format("YYYY-MM-DD")
            });
        });

        GetChartData(0);

    });

})(jQuery);

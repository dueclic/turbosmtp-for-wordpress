(function ($) {

    var list = {

        display: function (data) {

            $("tbody").on("click", ".toggle-row", function (e) {
                e.preventDefault();
                $(this).closest("tr").toggleClass("is-expanded")
            });

            data = $.extend({
                _ajax_custom_list_nonce: $('#_ajax_custom_list_nonce').val(),
                action: '_ajax_fetch_ts_history',
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

                console.log(data);

                list.update(data);
            });

            $('input[name=paged]').on('keyup', function (e) {

                if (13 == e.which)
                    e.preventDefault();

                var data = $.extend(origin_data, {
                    paged: parseInt($('input[name=paged]').val()) || '1',
                });

                console.log(data);

                window.clearTimeout(timer);
                timer = window.setTimeout(function () {
                    list.update(data);
                }, delay);
            });

            $('#email-sent-list').on('submit', function (e) {
                e.preventDefault();
            });

            $("tbody tr").prop("title", "");

            /*$("tbody:not(.table-loading) tr:not(.no-items)").tooltip({
                content: function () {

                    var subject_comp = $(this).find(".column-subject_comp").text();
                    var error = $(this).find(".column-error").text()

                    var tooltip = '<p><strong>' + ts.i18n['subject'] + '</strong>: ' + subject_comp + '</p>';

                    if (error.length > 0)

                        tooltip += '<p><strong style="color: #ff0000;">' + ts.i18n['description_error'] + '</strong>: ' + error + '</p>';

                    return tooltip;

                },
                track: true
            });*/

            $(".ts-loading").hide();
            $(".history-step").show();

        },

        update: function (data) {

            $("#the-list").addClass("table-loading");
            $(".ts-history-table-loading").show();

            $("#ts-history-table").find("thead").removeClass().addClass(data.filter);

            $.ajax({

                url: ajaxurl,
                data: $.extend(
                    {
                        _ajax_custom_list_nonce: $('#_ajax_custom_list_nonce').val(),
                        action: '_ajax_fetch_ts_history'
                    },
                    data
                ),
                success: function (response) {

                    response = $.parseJSON(response);

                    if (response.rows.length)
                        $('#the-list').html(response.rows);
                    if (response.column_headers.length)
                        $('thead tr, tfoot tr').html(response.column_headers);
                    if (response.pagination.bottom.length)
                        $('.tablenav.top .tablenav-pages').html($(response.pagination.top).html());
                    if (response.pagination.top.length)
                        $('.tablenav.bottom .tablenav-pages').html($(response.pagination.bottom).html());


                    $("#the-list").removeClass("table-loading");
                    $(".ts-history-table-loading").hide();

                    list.init(data);
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

        $(".other-infos-toggle a").on("click", function (evt) {
            evt.preventDefault();

            if ($(this).find("span").hasClass("icon-arrow-down")) {
                $("#turbo-stat-chart").hide();
                $(this).find("span").removeClass("icon-arrow-down").addClass("icon-arrow-left");
            }
            else {
                $("#turbo-stat-chart").show();
                $(this).find("span").removeClass("icon-arrow-left").addClass("icon-arrow-down");
            }

        });

        var from_date = $("input[name='from_date']");
        var start = moment().subtract(7, 'days').startOf('day').toDate();
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

            var c = $('#turbo-stat-chart');
            var ctx = c.get(0).getContext("2d");
            var container = c.parent();

            var $container = $(container);

            c.attr('width', $container.width()); //max width

            c.attr('height', $container.height() / 1.5); //max height

            //Call a function to redraw other content (texts, images etc)

            window.chart = new Chart(ctx, {
                type: 'line',
                data: chartData,
                options: {
                    responsive: true,
                    legend: {
                        position: 'bottom'
                    },
                    scales: {
                        xAxes: [{
                            ticks: {
                                maxRotation: 0,
                                autoSkip: true,
                                maxTicksLimit: 10
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
                url: ts.chart_ajax_url,
                method: 'POST',
                dataType: 'json',
                data: {
                    'start_date': picker.startDate.format("YYYY-MM-DD"),
                    'end_date': picker.endDate.format("YYYY-MM-DD")
                },
                success: function (d) {

                    console.log("D = ", d);

                    if (d.is_free == 1){
                        $("#demo-client").show();
                        $("#paid-client").hide();
                        $(".other-infos-loading").hide();
                        return;
                    }

                    if (d.data && d.data.length == 0) {
                        $(".other-infos-noresults").show();
                        return;
                    }

                    $("#demo-client").hide();
                    $("#paid-client").show();

                    $(".total-email").find("h4").text(d.total);
                    $(".chartjs-hidden-iframe").remove();

                    var ts_datasets = [];

                    $.each(d.data, function (key, values) {

                        ts_datasets.push({
                            label: getType(key),
                            backgroundColor: getColor(key, 0.2),
                            borderColor: getColor(key, 1),
                            pointBorderColor: getColor(key, 1),
                            pointBackgroundColor: "#000000",
                            data: d.data[key],
                            lineTension: 0
                        });

                        if (key == "delivered" || key == "opens" || key == "clicks" || key == 'bounce')
                            fillBox(key, values, d.total, true);

                        else if (key != "all")
                            fillBox(key, values, d.total, false);

                    });

                    $(".other-infos").show();
                    $(".other-infos-columns").css("display", "flex");
                    $(".other-infos-loading").hide();

                    chartData = {
                        labels: d.axis,
                        datasets: ts_datasets
                    };

                    respondCanvas();

                    if (update == 0) {

                        list.display({
                            'begin': picker.startDate.format("YYYY-MM-DD"),
                            'end': picker.endDate.format("YYYY-MM-DD")
                        });

                    }

                    else {
                        list.update({
                            'filter': 'all',
                            'begin': picker.startDate.format("YYYY-MM-DD"),
                            'end': picker.endDate.format("YYYY-MM-DD")
                        });
                    }

                }
            });
        };

        $("div[data-ts-filter]").on("click", function () {
            var picker = $(from_date).data('daterangepicker');
            $("div[data-ts-filter]").removeClass("active");
            $(this).addClass("active");
            list.update({
                'filter': $(this).data("ts-filter"),
                'begin': picker.startDate.format("YYYY-MM-DD"),
                'end': picker.endDate.format("YYYY-MM-DD")
            });
        });

        GetChartData(0);

    });

})(jQuery);

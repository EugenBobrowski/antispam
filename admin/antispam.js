/**
 * Created by eugen on 5/31/16.
 */


(function ($) {

    var antispamchart = {};

    antispamchart.chart = function () {

        var points1 = antispamchart.preparedata(antispam_data.data);

        var chart = new CanvasJS.Chart("chartContainer",
            {
                animationEnabled: true,
                title: {
                    text: "Antispam stistic"
                },
                data: [
                    {
                        legendText: "Spam Rejected",
                        type: "spline", //change type to bar, line, area, pie, etc
                        showInLegend: true,
                        dataPoints: points1
                    }
                    // ,
                    // {
                    //     type: "spline",
                    //     showInLegend: true,
                    //     dataPoints: [
                    //         // { label: '1 may', y: 31 },
                    //         // { label: '2 may', y: 25},
                    //         // { label: '3 may', y: 80 },
                    //         // { label: '4 may', y: 52 },
                    //         // { label: '5 may', y: 65 },
                    //         // { label: '6 may', y: 56 },
                    //         // { label: '7 may', y: 34 },
                    //         {label: '8 may', y: 0},
                    //         {label: '9 may', y: 0}
                    //     ]
                    // }
                ],
                legend: {
                    cursor: "pointer",
                    itemclick: function (e) {
                        if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                            e.dataSeries.visible = false;
                        } else {
                            e.dataSeries.visible = true;
                        }
                        chart.render();
                    }
                }
            });

        chart.render();
    };

    antispamchart.preparedata = function (a) {
        var first = {};
        first.el = a.shift();
        first.date = Date.parse(first.el.s_date);
        var last = {};
        last.el = a.pop();
        last.date = Date.parse(last.el.s_date);
        var timeline = [];
        var current = {};

        current.date = new Date(first.date);

        timeline.push(antispamchart.point(current.date, first.el.s_count));

        antispamchart.getPoints(a);

        current.timestamp = first.date;
        while (current.timestamp < last.date) {
            current.timestamp += 3600 * 24 * 1000;

            if (undefined == antispamchart.points[current.timestamp]) current.val = 0;
            else current.val = antispamchart.points[current.timestamp];

            timeline.push(antispamchart.point(current.timestamp, current.val));
        }

        return timeline;

    };

    antispamchart.point = function (timestamp, value) {
        var date = new Date(timestamp);

        return {
            label: date.toLocaleString("en-US", {
                month: 'short',
                day: 'numeric'
            }), y: parseInt(value)
        }
    };

    antispamchart.getPoints = function (a) {
        antispamchart.points = {};
        a.forEach(function(item, i, arr) {
            antispamchart.points[Date.parse(item.s_date)] = parseInt(item.s_count);
        });
    };

    window.onload = antispamchart.chart;

})(jQuery);
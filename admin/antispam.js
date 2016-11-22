/**
 * Created by eugen on 5/31/16.
 */


(function ($) {


    window.onload = function () {

        var points1 = preparedata(antispam_data.data);

        var chart = new CanvasJS.Chart("chartContainer",
            {
                animationEnabled: true,
                title:{
                    text: "Antispam stistic"
                },
                data: [
                    {
                        type: "spline", //change type to bar, line, area, pie, etc
                        showInLegend: true,
                        dataPoints: points1
                    }
                    ,
                    {
                        type: "spline",
                        showInLegend: true,
                        dataPoints: [
                            // { label: '1 may', y: 31 },
                            // { label: '2 may', y: 25},
                            // { label: '3 may', y: 80 },
                            // { label: '4 may', y: 52 },
                            // { label: '5 may', y: 65 },
                            // { label: '6 may', y: 56 },
                            // { label: '7 may', y: 34 },
                            { label: '8 may', y: 0 },
                            { label: '9 may', y: 0 }
                        ]
                    }
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

    var chart = {};

    var preparedata = function (a) {

        var first = {};
        first.el = a.shift();
        first.date = Date.parse(first.el.s_date);
        var last = {};
        last.el = a.pop();
        last.date = Date.parse(last.el.s_date);
        var timeline = [];
        var current = {};
        console.log(first.date, last.date, a);

        current.date = new Date (first.date);

        timeline.push({label: current.date.toLocaleString("en-US", {
            month: 'short',
            day: 'numeric'
        }), y: parseInt(first.el.s_count)});

        current.timestamp = first.date;
        while (current.timestamp < last.date) {
            current.timestamp += 3600 * 24 * 1000;
            current.date = new Date (current.timestamp);
            timeline.push({
                label: current.date.toLocaleString("en-US", {
                month: 'short',
                day: 'numeric'
            }), y: 0});

        }

        console.log(timeline);

        return timeline;

    }


})(jQuery);
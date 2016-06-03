/**
 * Created by eugen on 5/31/16.
 */
window.onload = function () {
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
                    dataPoints: [
                        // { label: '1 may', y: 51 },
                        // { label: '2 may', y: 45},
                        // { label: '3 may', y: 50 },
                        // { label: '4 may', y: 62 },
                        // { label: '5 may', y: 95 },
                        // { label: '6 may', y: 66 },
                        // { label: '7 may', y: 24 },
                        { label: '8 may', y: 32 },
                        { label: '9 may', y: 16 }
                    ]
                },
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
}
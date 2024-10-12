
$(function () {
  $('#graph').highcharts({
      chart: {
          type: 'spline',
          zoomType: 'x',
          resetZoomButton: {
            position: {
              x: 0,
              y: -30,
            },
          },
      },
      title: {
          text: graphIn.name,
      },
      xAxis: {
          type: 'linear',
          labels: {
              overflow: 'justify'
          },
          min: graphIn.min,
          max: graphIn.max,
          tickPixelInterval: 50,
      },
      yAxis: {
          title: {
              text: graphIn.name
          },
          minorGridLineWidth: 0,
          gridLineWidth: 0,
          alternateGridColor: null,
          tickPixelInterval: 36,
          gridLineColor: '#666',
          gridLineWidth: 1,
      },
      plotOptions: {
          spline: {
              lineWidth: 2,
              marker: {
                  enabled: false
              },
              pointInterval: 1,
          }
      },
      series: [{
          name: graphIn.name,
          data: graphIn.points,
      }],
      navigation: {
          menuItemStyle: {
              fontSize: '10px'
          }
      }
  });
});

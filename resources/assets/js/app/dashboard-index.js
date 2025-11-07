'use strict';

$(function () {


  let cardColor, headingColor, labelColor, borderColor, legendColor;

  if (isDarkStyle) {
    cardColor = config.colors_dark.cardColor;
    headingColor = config.colors_dark.headingColor;
    labelColor = config.colors_dark.textMuted;
    legendColor = config.colors_dark.bodyColor;
    borderColor = config.colors_dark.borderColor;
  } else {
    cardColor = config.colors.cardColor;
    headingColor = config.colors.headingColor;
    labelColor = config.colors.textMuted;
    legendColor = config.colors.bodyColor;
    borderColor = config.colors.borderColor;
  }
  // Color constant
  const chartColors = {
    column: {
      series1: '#826af9',
      series2: '#d2b0ff',
      bg: '#f8d3ff'
    },
    donut: {
      series1: '#fee802',
      series2: '#F1F0F2',
      series3: '#826bf8',
      series4: '#3fd0bd'
    },
    area: {
      series1: '#29dac7',
      series2: '#60f2ca',
      series3: '#a5f8cd'
    },
    bar: {
      bg: '#1D9FF2'
    }
  };

  $.ajax({
    url: '/getDepartmentPerformanceAjax',
    type: 'GET',
    success: function (response) {
      console.log(response);

      var data = response.data;

      // Extract department data
      var departmentNames = data.map(dept => dept.code);
      var presentEmployees = data.map(dept => dept.totalPresentEmployees);
      var absentEmployees = data.map(dept => dept.totalAbsentEmployees);

      // Initialize ApexCharts with vertical column chart design
      var options = {
        chart: {
          type: 'bar',
          height: 400,
          toolbar: {
            show: false
          }
        },
        title: {
          text: 'Department Attendance Overview',
          align: 'left',
          style: {
            fontSize: '18px',
            fontWeight: 'regular',
            color: '#333'
          }
        },
        series: [
          {
            name: 'Present Employees',
            data: presentEmployees
          },
          {
            name: 'Absent Employees',
            data: absentEmployees
          }
        ],
        xaxis: {
          categories: departmentNames,
          title: {
            text: 'Departments'
          },
          labels: {
            style: {
              fontSize: '12px',
              fontWeight: 'bold'
            }
          }
        },
        yaxis: {
          title: {
            text: 'Number of Employees'
          }
        },
        colors: ['#2ECC71', '#E74C3C'], // Green for Present, Red for Absent
        plotOptions: {
          bar: {
            horizontal: false,
            columnWidth: '50%',
            borderRadius: 32
          }
        },
        dataLabels: {
          enabled: true,
          style: {
            fontSize: '10px',
            fontWeight: 'bold'
          }
        },
        tooltip: {
          shared: true,
          intersect: false,
          theme: 'light'
        },
        legend: {
          position: 'top',
          horizontalAlign: 'center',
          fontSize: '12px',
          markers: {
            width: 12,
            height: 12,
            radius: 4
          }
        },
        grid: {
          borderColor: '#e0e0e0',
          strokeDashArray: 4
        }
      };

      var chart = new ApexCharts(document.querySelector('#topDepartmentsChart'), options);
      chart.render();
    },
    error: function (response) {
      console.error('Error:', response);
      $('#topDepartmentsChart').html('<p class="text-danger">Failed to load data.</p>');
    }
  });


  //Attendance Overview Chart
  const donutChartEl = document.querySelector('#donutChart'),
    donutChartConfig = {
      chart: {
        height: 390,
        type: 'donut'
      },
      labels: ['Present', 'Absent', 'On Leave'],
      series: [42, 7, 25],
      colors: [
        chartColors.donut.series1,
        chartColors.donut.series4,
        chartColors.donut.series3,
        chartColors.donut.series2
      ],
      stroke: {
        show: false,
        curve: 'straight'
      },
      dataLabels: {
        enabled: true,
        formatter: function (val, opt) {
          return parseInt(val, 10) + '%';
        }
      },
      legend: {
        show: true,
        position: 'bottom',
        markers: {offsetX: -3},
        itemMargin: {
          vertical: 3,
          horizontal: 10
        },
        labels: {
          colors: legendColor,
          useSeriesColors: false
        }
      },
      plotOptions: {
        pie: {
          donut: {
            labels: {
              show: true,
              name: {
                fontSize: '2rem',
                fontFamily: 'Public Sans'
              },
              value: {
                fontSize: '1.2rem',
                color: legendColor,
                fontFamily: 'Public Sans',
                formatter: function (val) {
                  return parseInt(val, 10) + '%';
                }
              },
              total: {
                show: true,
                fontSize: '1.5rem',
                color: headingColor,
                label: 'Present',
                formatter: function (w) {
                  return '42%';
                }
              }
            }
          }
        }
      },
      responsive: [
        {
          breakpoint: 992,
          options: {
            chart: {
              height: 380
            },
            legend: {
              position: 'bottom',
              labels: {
                colors: legendColor,
                useSeriesColors: false
              }
            }
          }
        },
        {
          breakpoint: 576,
          options: {
            chart: {
              height: 320
            },
            plotOptions: {
              pie: {
                donut: {
                  labels: {
                    show: true,
                    name: {
                      fontSize: '1.5rem'
                    },
                    value: {
                      fontSize: '1rem'
                    },
                    total: {
                      fontSize: '1.5rem'
                    }
                  }
                }
              }
            },
            legend: {
              position: 'bottom',
              labels: {
                colors: legendColor,
                useSeriesColors: false
              }
            }
          }
        },
        {
          breakpoint: 420,
          options: {
            chart: {
              height: 280
            },
            legend: {
              show: false
            }
          }
        },
        {
          breakpoint: 360,
          options: {
            chart: {
              height: 250
            },
            legend: {
              show: false
            }
          }
        }
      ]
    };
  if (typeof donutChartEl !== undefined && donutChartEl !== null) {
    const donutChart = new ApexCharts(donutChartEl, donutChartConfig);
    donutChart.render();
  }


  $.ajax({
    url: '/getRecentActivities',
    type: 'GET',
    success: function (response) {      // Recent Activities
      const recentActivities = document.querySelector('#activityList');

      if (recentActivities && response.data.length > 0) {
        response.data.forEach(activity => {
          const user = activity.user || {};

          recentActivities.innerHTML += `
      <li class="list-group-item border-0 d-flex align-items-start py-3">
        <div class="timeline-item w-100">
          <span class="timeline-point timeline-point-primary"></span>
          <div class="timeline-event">
            <div class="timeline-header d-flex justify-content-between align-items-center mb-2">
              <h6 class="mb-0">${activity.type} by <strong>${user.name || 'Unknown User'}</strong></h6>
              <small class="text-muted">${activity.created_at_human}</small>
            </div>
              </div>
              <div class="d-flex flex-column">
                 <p class="mb-1"><strong></strong> ${activity.title}</p>
              </div>
            </div>

            <!-- Activity Title -->

          </div>
        </div>
      </li>
    `;
        });
      } else {
        recentActivities.innerHTML = `
    <li class="list-group-item text-center text-muted">No recent activities found.</li>
  `;
      }
    },
    error: function (response) {
      console.error(response);
    }
  });
});

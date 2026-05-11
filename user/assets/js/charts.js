// ApexCharts dashboard bootstrap (safe no-op until dashboard widgets exist)
(function () {
    function buildBaseOptions() {
        return {
            chart: {
                fontFamily: 'Inter, system-ui, sans-serif',
                toolbar: { show: false }
            },
            dataLabels: { enabled: false },
            grid: { borderColor: 'rgba(15, 23, 42, 0.08)' },
            tooltip: { theme: 'light' }
        };
    }

    function initApexDashboardCharts() {
        if (typeof ApexCharts === 'undefined') return;

        const attendanceEl = document.querySelector('#attendanceApexChart');
        if (attendanceEl) {
            const attendanceChart = new ApexCharts(attendanceEl, {
                ...buildBaseOptions(),
                chart: {
                    ...buildBaseOptions().chart,
                    type: 'area',
                    height: 270,
                    zoom: { enabled: false }
                },
                series: [
                    {
                        name: 'Attendance %',
                        data: [92, 95, 90, 94, 93, 88, 91]
                    }
                ],
                xaxis: {
                    categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
                },
                yaxis: {
                    min: 0,
                    max: 100
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                colors: ['#6C4CF1'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.25,
                        opacityTo: 0.05,
                        stops: [0, 90, 100]
                    }
                }
            });

            attendanceChart.render();
        }

        const mixEl = document.querySelector('#attendanceMixApexChart');
        if (mixEl) {
            const mixChart = new ApexCharts(mixEl, {
                ...buildBaseOptions(),
                chart: {
                    ...buildBaseOptions().chart,
                    type: 'donut',
                    height: 270
                },
                labels: ['On Time', 'Late', 'Half Day', 'Absent'],
                series: [78, 12, 6, 4],
                colors: ['#22c55e', '#f59e0b', '#0ea5e9', '#ef4444'],
                legend: {
                    position: 'bottom',
                    fontSize: '12px',
                    labels: { colors: '#64748b' }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '68%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'On Time',
                                    formatter: function () {
                                        return '78%';
                                    }
                                }
                            }
                        }
                    }
                }
            });

            mixChart.render();
        }

        const leaveUsageEl = document.querySelector('#leaveUsageApexChart');
        if (leaveUsageEl) {
            const leaveUsageChart = new ApexCharts(leaveUsageEl, {
                ...buildBaseOptions(),
                chart: {
                    ...buildBaseOptions().chart,
                    type: 'bar',
                    height: 270
                },
                series: [
                    { name: 'Annual', data: [1, 0, 2, 1, 0, 2] },
                    { name: 'Sick', data: [0, 1, 0, 1, 1, 0] },
                    { name: 'Casual', data: [1, 1, 0, 0, 1, 1] }
                ],
                xaxis: {
                    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']
                },
                colors: ['#6C4CF1', '#f59e0b', '#0ea5e9'],
                plotOptions: {
                    bar: {
                        borderRadius: 6,
                        columnWidth: '42%'
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'left',
                    fontSize: '12px',
                    labels: { colors: '#64748b' }
                }
            });

            leaveUsageChart.render();
        }

        const workHoursEl = document.querySelector('#workHoursApexChart');
        if (workHoursEl) {
            const workHoursChart = new ApexCharts(workHoursEl, {
                ...buildBaseOptions(),
                chart: {
                    ...buildBaseOptions().chart,
                    type: 'line',
                    height: 270
                },
                series: [
                    { name: 'Worked Hours', data: [7.2, 8.1, 7.8, 8.4, 7.5, 6.8, 7.9] },
                    { name: 'Target Hours', data: [8, 8, 8, 8, 8, 8, 8] }
                ],
                xaxis: {
                    categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
                },
                yaxis: {
                    min: 0,
                    max: 10,
                    tickAmount: 5
                },
                stroke: {
                    curve: 'smooth',
                    width: [3, 2],
                    dashArray: [0, 6]
                },
                colors: ['#10b981', '#94a3b8'],
                markers: { size: 4 },
                legend: {
                    position: 'top',
                    horizontalAlign: 'left',
                    fontSize: '12px',
                    labels: { colors: '#64748b' }
                }
            });

            workHoursChart.render();
        }
    }

    document.addEventListener('DOMContentLoaded', initApexDashboardCharts);
})();

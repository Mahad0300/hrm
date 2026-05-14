// ApexCharts dashboard bootstrap - Dynamic Data Integration
(function () {
    function buildBaseOptions() {
        return {
            chart: {
                fontFamily: 'Outfit, system-ui, sans-serif',
                toolbar: { show: false }
            },
            dataLabels: { enabled: false },
            grid: { borderColor: 'rgba(15, 23, 42, 0.08)' },
            tooltip: { theme: 'light' }
        };
    }

    async function initApexDashboardCharts() {
        if (typeof ApexCharts === 'undefined') return;

        try {
            const response = await fetch('../includes/api/stats_handler.php?action=get_chart_data');
            const res = await response.json();
            if (res.status !== 'success') return;
            const d = res.data;

            // 1. Weekly Attendance (Mon - Fri)
            const attendanceEl = document.querySelector('#attendanceApexChart');
            if (attendanceEl) {
                new ApexCharts(attendanceEl, {
                    ...buildBaseOptions(),
                    chart: { ...buildBaseOptions().chart, type: 'area', height: 270, zoom: { enabled: false } },
                    series: [{ name: 'Status', data: d.weekly_attendance }],
                    xaxis: { categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'] },
                    yaxis: { min: 0, max: 100, show: false },
                    tooltip: {
                        y: {
                            formatter: function(val, { dataPointIndex }) {
                                return d.weekly_statuses[dataPointIndex];
                            },
                            title: {
                                formatter: () => 'Status:'
                            }
                        }
                    },
                    stroke: { curve: 'smooth', width: 3 },
                    colors: ['#6C4CF1'],
                    fill: {
                        type: 'gradient',
                        gradient: { shadeIntensity: 1, opacityFrom: 0.25, opacityTo: 0.05, stops: [0, 90, 100] }
                    }
                }).render();
            }

            // 2. Monthly Mix (Donut)
            const mixEl = document.querySelector('#attendanceMixApexChart');
            if (mixEl) {
                const total = d.monthly_mix.reduce((a, b) => a + b, 0);
                const onTimePercent = total > 0 ? Math.round((d.monthly_mix[0] / total) * 100) : 0;

                new ApexCharts(mixEl, {
                    ...buildBaseOptions(),
                    chart: { ...buildBaseOptions().chart, type: 'donut', height: 270 },
                    labels: ['On Time', 'Late', 'Half Day', 'Absent'],
                    series: d.monthly_mix,
                    colors: ['#22c55e', '#f59e0b', '#0ea5e9', '#ef4444'],
                    legend: { position: 'bottom', fontSize: '12px', labels: { colors: '#64748b' } },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '68%',
                                labels: {
                                    show: true,
                                    total: {
                                        show: true,
                                        label: 'On Time',
                                        formatter: () => onTimePercent + '%'
                                    }
                                }
                            }
                        }
                    }
                }).render();
            }

            // 3. Work Hours (Last 7 Days)
            const workHoursEl = document.querySelector('#workHoursApexChart');
            if (workHoursEl) {
                new ApexCharts(workHoursEl, {
                    ...buildBaseOptions(),
                    chart: { ...buildBaseOptions().chart, type: 'line', height: 270 },
                    series: [
                        { name: 'Worked Hours', data: d.work_hours.worked },
                        { name: 'Target Hours', data: d.work_hours.target }
                    ],
                    xaxis: { categories: d.work_hours.labels },
                    yaxis: { min: 0, max: 12, tickAmount: 6 },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                const h = Math.floor(val);
                                const m = Math.round((val - h) * 60);
                                return h + "h " + (m < 10 ? "0" + m : m) + "m";
                            }
                        }
                    },
                    stroke: { curve: 'smooth', width: [3, 2], dashArray: [0, 6] },
                    colors: ['#10b981', '#94a3b8'],
                    markers: { size: 4 },
                    legend: { position: 'top', horizontalAlign: 'left', fontSize: '12px', labels: { colors: '#64748b' } }
                }).render();
            }

            // 4. Leave Usage (Dynamic)
            const leaveUsageEl = document.querySelector('#leaveUsageApexChart');
            if (leaveUsageEl) {
                window.leaveUsageChart = new ApexCharts(leaveUsageEl, {
                    ...buildBaseOptions(),
                    chart: { ...buildBaseOptions().chart, type: 'bar', height: 270 },
                    series: [],
                    xaxis: { categories: [] },
                    colors: ['#6C4CF1', '#f59e0b', '#0ea5e9'],
                    plotOptions: { bar: { borderRadius: 6, columnWidth: '42%' } },
                    legend: { position: 'bottom', horizontalAlign: 'center', fontSize: '12px', labels: { colors: '#64748b' } }
                });
                window.leaveUsageChart.render();
                const fromFilter = document.getElementById('leaveFromFilter');
                const toFilter = document.getElementById('leaveToFilter');
                if (fromFilter && toFilter) {
                    const updateRange = () => fetchLeaveAnalytics(fromFilter.value, toFilter.value);
                    fromFilter.addEventListener('change', updateRange);
                    toFilter.addEventListener('change', updateRange);
                    updateRange();
                }
            }

        } catch (err) {
            console.error('Charts Data Error:', err);
        }
    }

    async function fetchLeaveAnalytics(from, to) {
        try {
            const res = await fetch(`../includes/api/stats_handler.php?action=get_leave_analytics&from=${from}&to=${to}`);
            const d = await res.json();
            if (d.status === 'success') {
                if (window.leaveUsageChart) {
                    window.leaveUsageChart.updateOptions({
                        xaxis: { categories: d.data.categories }
                    });
                    window.leaveUsageChart.updateSeries(d.data.series);
                }
            }
        } catch (err) {
            console.error('Leave Analytics Fetch Error:', err);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        initApexDashboardCharts();

        const filter = document.getElementById('leaveMonthFilter');
        if (filter) {
            filter.addEventListener('change', (e) => {
                fetchLeaveAnalytics(e.target.value);
            });
        }
    });
})();

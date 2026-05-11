// Admin dashboard — ApexCharts (matches user portal pattern)
(function () {
    const chartFont = 'Inter, system-ui, sans-serif';

    function buildBaseOptions() {
        return {
            chart: {
                fontFamily: chartFont,
                toolbar: { show: false }
            },
            dataLabels: { enabled: false },
            grid: { borderColor: 'rgba(15, 23, 42, 0.08)' },
            tooltip: { theme: 'light' }
        };
    }

function formatSalaryTooltip(value) {
    try {
        return new Intl.NumberFormat('en-PK', {
            style: 'currency',
            currency: 'PKR',
            maximumFractionDigits: 0
        }).format(value);
    } catch {
        return 'PKR ' + Math.round(value).toLocaleString('en-PK');
    }
}

function initLeaveTypeVibrantBars() {
    const wrap = document.getElementById('leaveTypeVibrantWrap');
    if (!wrap) return;

    const items = [
        { label: 'Annual', days: 180, gradient: 'linear-gradient(90deg, #6d28d9 0%, #a78bfa 100%)' },
        { label: 'Sick', days: 95, gradient: 'linear-gradient(90deg, #b91c1c 0%, #fb7185 100%)' },
        { label: 'Casual', days: 72, gradient: 'linear-gradient(90deg, #c2410c 0%, #fdba74 100%)' },
        { label: 'Unpaid', days: 12, gradient: 'linear-gradient(90deg, #475569 0%, #94a3b8 100%)' },
        { label: 'WFH', days: 48, gradient: 'linear-gradient(90deg, #047857 0%, #34d399 100%)' }
    ];
    const max = Math.max(...items.map((i) => i.days), 1);
    const total = items.reduce((s, i) => s + i.days, 0);

    wrap.innerHTML = `
        <div class="leave-type-vibrant-rows">
            ${items
                .map(
                    (row) => `
                <div class="leave-type-row">
                    <div class="leave-type-row-head">
                        <span class="leave-type-name">${row.label}</span>
                        <span class="leave-type-days font-600">${row.days}<span class="leave-type-unit">d</span></span>
                    </div>
                    <div class="leave-type-track" role="presentation">
                        <span class="leave-type-fill" style="width:${(row.days / max) * 100}%; background:${row.gradient};"></span>
                    </div>
                </div>`
                )
                .join('')}
        </div>
        <p class="leave-type-vibrant-total font-12 text-light mb-0">Total approved days (MTD): <strong class="text-dark">${total}</strong></p>
    `;
}

    function initAdminApexCharts() {
        if (typeof ApexCharts === 'undefined') return;

        const base = buildBaseOptions();

        const attendanceEl = document.querySelector('#adminAttendanceChart');
        if (attendanceEl) {
            new ApexCharts(attendanceEl, {
                ...base,
                chart: {
                    ...base.chart,
                    type: 'area',
                    height: 240,
                    zoom: { enabled: false }
                },
                series: [{ name: 'Presence (%)', data: [92, 95, 88, 94, 91, 75, 78] }],
                xaxis: { categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] },
                yaxis: { min: 0, max: 100 },
                stroke: { curve: 'smooth', width: 2 },
                colors: ['#6C4CF1'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.25,
                        opacityTo: 0.05,
                        stops: [0, 90, 100]
                    }
                },
                markers: {
                    size: 4,
                    strokeWidth: 2,
                    strokeColors: '#fff',
                    colors: ['#6C4CF1']
                }
            }).render();
        }

        const mixEl = document.querySelector('#adminMixChart');
        if (mixEl) {
            const mixLabels = ['Present', 'Absent', 'On leave'];
            const mixData = [456, 12, 26];
            const mixColors = ['#22c55e', '#ef4444', '#f59e0b'];
            const presentCount = mixData[0];
            new ApexCharts(mixEl, {
                ...base,
                chart: {
                    ...base.chart,
                    type: 'donut',
                    height: 260
                },
                stroke: {
                    show: true,
                    width: 3,
                    colors: ['#ffffff', '#ffffff', '#ffffff']
                },
                labels: mixLabels,
                series: mixData,
                colors: mixColors,
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center',
                    floating: false,
                    fontSize: '12px',
                    labels: { colors: '#64748b' },
                    markers: {
                        width: 8,
                        height: 8,
                        radius: 12,
                        strokeWidth: 0
                    },
                    itemMargin: {
                        horizontal: 14,
                        vertical: 0
                    }
                },
                plotOptions: {
                    pie: {
                        expandOnClick: false,
                        donut: {
                            size: '80%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    showAlways: true,
                                    label: 'Present',
                                    fontSize: '18px',
                                    fontWeight: 600,
                                    color: '#0f172a',
                                    formatter: function () {
                                        return String(presentCount);
                                    }
                                }
                            }
                        }
                    }
                }
            }).render();
        }

        const salaryEl = document.querySelector('#adminSalaryChart');
        if (salaryEl) {
            const deptLabels = ['Engineering', 'Sales', 'Operations', 'HR', 'Finance', 'Support'];
            const deptTotals = [2450000, 1820000, 1180000, 465000, 598000, 352000];
            const palette = ['#4338ca', '#4f46e5', '#5839D6', '#6C4CF1', '#8A6FFF', '#a78bfa'];
            new ApexCharts(salaryEl, {
                ...base,
                chart: {
                    ...base.chart,
                    type: 'bar',
                    height: 320
                },
                series: [{ name: 'Monthly payroll', data: deptTotals }],
                xaxis: {
                    categories: deptLabels,
                    labels: {
                        rotate: -35,
                        rotateAlways: deptLabels.some((l) => l.length > 8),
                        style: { fontSize: '11px' }
                    }
                },
                yaxis: {
                    labels: {
                        formatter: function (val) {
                            if (val >= 1e6) return (val / 1e6).toFixed(val >= 1e7 ? 0 : 1) + 'M';
                            if (val >= 1e3) return Math.round(val / 1e3) + 'K';
                            return String(Math.round(val));
                        }
                    }
                },
                colors: palette,
                plotOptions: {
                    bar: {
                        borderRadius: 8,
                        columnWidth: '52%',
                        distributed: true,
                        dataLabels: { position: 'top' }
                    }
                },
                legend: { show: false },
                tooltip: {
                    theme: 'light',
                    y: {
                        formatter: function (val) {
                            return formatSalaryTooltip(val) + ' / mo';
                        }
                    }
                },
                dataLabels: { enabled: false }
            }).render();
        }

        const headcountEl = document.querySelector('#adminHeadcountChart');
        if (headcountEl) {
        const hcLabels = ['Engineering', 'Sales', 'Operations', 'HR', 'Finance', 'Support'];
        const hcData = [165, 110, 78, 35, 42, 52];
        const hcColors = ['#6C4CF1', '#8b7af5', '#a78bfa', '#22c55e', '#0ea5e9', '#f59e0b'];
            const hcSum = hcData.reduce((a, b) => a + b, 0);
            new ApexCharts(headcountEl, {
                ...base,
                chart: {
                    ...base.chart,
                    type: 'donut',
                    height: 260
                },
                labels: hcLabels,
                series: hcData,
                colors: hcColors,
                legend: {
                    position: 'bottom',
                    fontSize: '11px',
                    labels: { colors: '#64748b' },
                    horizontalAlign: 'center',
                    markers: { width: 8, height: 8, radius: 2 }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '82%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    showAlways: true,
                                    label: 'Headcount',
                                    fontSize: '18px',
                                    fontWeight: 600,
                                    color: '#0f172a',
                                    formatter: function () {
                                        return String(hcSum);
                                    }
                                }
                            }
                        }
                    }
                }
            }).render();
        }

        const funnelEl = document.querySelector('#adminFunnelChart');
        if (funnelEl) {
        const funnelLabels = ['Applied', 'Screened', 'Interview', 'Offer', 'Hired'];
        const funnelData = [240, 156, 89, 34, 18];
            const funnelColor = '#8a6df1';
            new ApexCharts(funnelEl, {
                ...base,
                chart: {
                    ...base.chart,
            type: 'bar',
                    height: 280
                },
                stroke: { width: 0 },
                series: [{ name: 'Candidates', data: funnelData }],
                xaxis: {
                    categories: funnelLabels,
                    min: 0,
                    max: 250,
                    tickAmount: 5,
                    labels: {
                        style: {
                            colors: '#94a3b8',
                            fontSize: '11px'
                        }
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: {
                        style: {
                            colors: '#64748b',
                            fontSize: '12px',
                            fontWeight: 500
                        }
                    }
                },
                grid: {
                    borderColor: 'rgba(148, 163, 184, 0.35)',
                    strokeDashArray: 0,
                    xaxis: {
                        lines: { show: false }
                    },
                    yaxis: {
                        lines: { show: true }
                    },
                    padding: {
                        top: 4,
                        right: 8,
                        bottom: 4,
                        left: 0
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        barHeight: '64%',
                        borderRadius: 12,
                        borderRadiusApplication: 'end',
                        dataLabels: {
                            position: 'top',
                            hideOverflowingLabels: false
                        }
                    }
                },
                colors: [funnelColor],
                dataLabels: {
                    enabled: true,
                    textAnchor: 'end',
                    offsetX: -12,
                    style: {
                        colors: ['#ffffff'],
                        fontSize: '12px',
                        fontWeight: 600,
                        fontFamily: chartFont
                    },
                    dropShadow: { enabled: false }
                },
                tooltip: {
                    theme: 'light',
                    y: {
                        formatter: function (val) {
                            return val + ' candidates';
                        }
                    }
                },
                legend: { show: false }
            }).render();
        }

        const turnoverEl = document.querySelector('#adminTurnoverChart');
        if (turnoverEl) {
            new ApexCharts(turnoverEl, {
                ...base,
                chart: {
                    ...base.chart,
                    type: 'area',
                    height: 240,
                    zoom: { enabled: false }
                },
                series: [{ name: 'Attrition %', data: [1.2, 1.8, 1.4, 1.1, 2.0, 1.5] }],
                xaxis: { categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'] },
                yaxis: {
                    min: 0,
                    max: 3,
                    tickAmount: 3,
                    labels: {
                        formatter: function (v) {
                                return v + '%';
                        }
                    }
                },
                stroke: { curve: 'smooth', width: 2 },
                colors: ['#ef4444'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.35,
                        opacityTo: 0.05,
                        stops: [0, 90, 100]
                    }
                },
                markers: {
                    size: 4,
                    strokeWidth: 2,
                    strokeColors: '#fff',
                    colors: ['#ef4444']
                }
            }).render();
        }

        const punctualityEl = document.querySelector('#adminPunctualityChart');
        if (punctualityEl) {
    const total = 482;
    const rings = [
        { label: 'On time', color: '#22c55e', count: 398 },
        { label: 'Late', color: '#f59e0b', count: 38 },
        { label: 'Half day', color: '#0ea5e9', count: 24 },
        { label: 'Absent', color: '#ef4444', count: 22 }
    ];
            const series = rings.map((r) => Math.round((r.count / total) * 100 * 100) / 100);
            new ApexCharts(punctualityEl, {
                ...base,
                chart: {
                    ...base.chart,
                    type: 'radialBar',
                    height: 260
                },
                series: series,
                labels: rings.map((r) => r.label),
                colors: rings.map((r) => r.color),
                plotOptions: {
                    radialBar: {
                        hollow: { size: '18%' },
                        track: { background: 'rgba(15, 23, 42, 0.08)' },
                        dataLabels: {
                            name: { fontSize: '11px' },
                            value: {
                                fontSize: '14px',
                                formatter: function (_val, opts) {
                                    return String(rings[opts.seriesIndex].count);
                                }
                            },
                            total: {
                                show: true,
                                label: 'Total',
                                fontSize: '13px',
                                formatter: function () {
                                    return String(total);
                                }
                            }
                        }
                    }
                },
                legend: {
                    show: true,
                    position: 'bottom',
                    fontSize: '11px',
                    labels: { colors: '#64748b' }
                }
            }).render();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
    initLeaveTypeVibrantBars();
        initAdminApexCharts();
});
})();

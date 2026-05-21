// Admin dashboard — ApexCharts (dynamic data from stats_handler)
(function () {
    const chartFont = 'Inter, system-ui, sans-serif';
    const chartInstances = {};
    const leaveGradients = [
        'linear-gradient(90deg, #6d28d9 0%, #a78bfa 100%)',
        'linear-gradient(90deg, #b91c1c 0%, #fb7185 100%)',
        'linear-gradient(90deg, #c2410c 0%, #fdba74 100%)',
        'linear-gradient(90deg, #475569 0%, #94a3b8 100%)',
        'linear-gradient(90deg, #047857 0%, #34d399 100%)',
    ];

    function buildBaseOptions() {
        return {
            chart: { fontFamily: chartFont, toolbar: { show: false } },
            dataLabels: { enabled: false },
            grid: { borderColor: 'rgba(15, 23, 42, 0.08)' },
            tooltip: { theme: 'light' },
        };
    }

    function formatSalaryTooltip(value) {
        try {
            return new Intl.NumberFormat('en-PK', {
                style: 'currency',
                currency: 'PKR',
                maximumFractionDigits: 0,
            }).format(value);
        } catch {
            return 'PKR ' + Math.round(value).toLocaleString('en-PK');
        }
    }

    function destroyChart(key) {
        if (chartInstances[key]) {
            try {
                chartInstances[key].destroy();
            } catch (e) {
                /* ignore */
            }
            chartInstances[key] = null;
        }
    }

    const funnelStageColors = ['#a78bfa', '#8a6df1', '#94a3b8', '#6C4CF1', '#22c55e'];

    function renderHiringFunnelBars(labels, data, total) {
        const wrap = document.getElementById('adminFunnelChart');
        if (!wrap) return;

        const stageLabels = labels || ['New', 'Interview', 'Shortlisted', 'Offer', 'Hired'];
        const counts = (data || [0, 0, 0, 0]).map((n) => Number(n) || 0);
        const max = Math.max(...counts, 1);
        const sum = total !== undefined ? total : counts.reduce((s, n) => s + n, 0);

        wrap.innerHTML =
            '<div class="hiring-funnel-rows">' +
            stageLabels
                .map((label, idx) => {
                    const count = counts[idx] || 0;
                    const pct = count > 0 ? (count / max) * 100 : 0;
                    const color = funnelStageColors[idx % funnelStageColors.length];
                    return (
                        '<div class="hiring-funnel-row">' +
                        '<div class="hiring-funnel-head">' +
                        '<span class="hiring-funnel-stage">' +
                        label +
                        '</span>' +
                        '<span class="hiring-funnel-count">' +
                        count +
                        '</span>' +
                        '</div>' +
                        '<div class="hiring-funnel-track" role="presentation">' +
                        '<span class="hiring-funnel-fill" style="width:' +
                        pct +
                        '%;background:' +
                        color +
                        ';"></span>' +
                        '</div>' +
                        '</div>'
                    );
                })
                .join('') +
            '</div>' +
            '<p class="hiring-funnel-total font-12 text-light mb-0">Pipeline total: <strong class="text-dark">' +
            sum +
            '</strong> candidate' +
            (sum === 1 ? '' : 's') +
            '</p>';
    }

    function renderLeaveTypeBars(items, periodLabel) {
        const wrap = document.getElementById('leaveTypeVibrantWrap');
        if (!wrap) return;

        const periodText = periodLabel || 'Payroll period';

        if (!items || !items.length) {
            wrap.innerHTML =
                '<p class="font-13 text-light py-20 mb-0">No approved leave in ' + periodText + ' yet.</p>';
            return;
        }

        const mapped = items.map((row) => ({
            label: row.name,
            days: parseInt(row.days, 10) || 0,
        }));
        const max = Math.max(...mapped.map((i) => i.days), 1);
        const total = mapped.reduce((s, i) => s + i.days, 0);

        wrap.innerHTML =
            '<div class="leave-type-vibrant-rows">' +
            mapped
                .map(
                    (row, idx) => `
                <div class="leave-type-row">
                    <div class="leave-type-row-head">
                        <span class="leave-type-name">${row.label}</span>
                        <span class="leave-type-days font-600">${row.days}<span class="leave-type-unit">d</span></span>
                    </div>
                    <div class="leave-type-track" role="presentation">
                        <span class="leave-type-fill" style="width:${(row.days / max) * 100}%; background:${leaveGradients[idx % leaveGradients.length]};"></span>
                    </div>
                </div>`
                )
                .join('') +
            '</div>' +
            '<p class="leave-type-vibrant-total font-12 text-light mb-0">Total approved days (' +
            periodText +
            '): <strong class="text-dark">' +
            total +
            '</strong></p>';
    }

    window.initAdminChartsFromData = function (charts) {
        if (typeof ApexCharts === 'undefined') return;

        if (!charts) {
            charts = getEmptyCharts();
        }

        const base = buildBaseOptions();
        const palette = ['#4338ca', '#4f46e5', '#5839D6', '#6C4CF1', '#8A6FFF', '#a78bfa'];

        // Attendance trend
        const attendanceEl = document.querySelector('#adminAttendanceChart');
        destroyChart('attendance');
        if (attendanceEl) {
            const labels = charts.attendance_trend?.labels || [];
            const values = charts.attendance_trend?.values || [];
            const numericValues = values.map((v) => (v === null || v === undefined ? null : Number(v)));
            const maxPresent = Math.max(0, ...numericValues.filter((v) => v !== null));
            const yMax = maxPresent > 0 ? Math.ceil(maxPresent * 1.15) : 10;
            chartInstances.attendance = new ApexCharts(attendanceEl, {
                ...base,
                chart: { ...base.chart, type: 'area', height: 240, zoom: { enabled: false } },
                series: [{ name: 'Present', data: numericValues }],
                xaxis: { categories: labels },
                yaxis: {
                    min: 0,
                    max: yMax,
                    tickAmount: Math.min(yMax, 6),
                    labels: {
                        formatter: (val) => Math.round(val),
                    },
                },
                stroke: { curve: 'smooth', width: 2 },
                colors: ['#6C4CF1'],
                fill: {
                    type: 'gradient',
                    gradient: { shadeIntensity: 1, opacityFrom: 0.25, opacityTo: 0.05, stops: [0, 90, 100] },
                },
                markers: { size: 4, strokeWidth: 2, strokeColors: '#fff', colors: ['#6C4CF1'] },
                tooltip: {
                    theme: 'light',
                    y: {
                        formatter: (val) => (val == null ? '—' : val + ' present'),
                    },
                },
            });
            chartInstances.attendance.render();
        }

        // Today mix
        const mixEl = document.querySelector('#adminMixChart');
        destroyChart('mix');
        if (mixEl) {
            const mix = charts.today_mix || {};
            const mixLabels = ['On Time', 'Late In', 'Half Day', 'Absent'];
            const mixData = [
                mix['ON TIME'] || 0,
                mix['LATE IN'] || 0,
                mix['HALF DAY'] || 0,
                mix['ABSENT'] || 0,
            ];
            const mixColors = ['#22c55e', '#f59e0b', '#0ea5e9', '#ef4444'];
            const mixTotal = mixData.reduce((sum, n) => sum + n, 0);
            chartInstances.mix = new ApexCharts(mixEl, {
                ...base,
                chart: { ...base.chart, type: 'donut', height: 260 },
                stroke: {
                    show: true,
                    width: 3,
                    colors: ['#ffffff', '#ffffff', '#ffffff', '#ffffff'],
                },
                labels: mixLabels,
                series: mixData,
                colors: mixColors,
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center',
                    fontSize: '12px',
                    labels: { colors: '#64748b' },
                    markers: { width: 8, height: 8, radius: 12, strokeWidth: 0 },
                    itemMargin: { horizontal: 10, vertical: 0 },
                },
                tooltip: {
                    theme: 'light',
                    y: { formatter: (val) => val + ' employee(s)' },
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
                                    label: 'Total',
                                    fontSize: '18px',
                                    fontWeight: 600,
                                    color: '#0f172a',
                                    formatter: function () {
                                        return String(mixTotal);
                                    },
                                },
                            },
                        },
                    },
                },
            });
            chartInstances.mix.render();
        }

        // Salary by dept
        const salaryEl = document.querySelector('#adminSalaryChart');
        destroyChart('salary');
        if (salaryEl) {
            const deptLabels = charts.dept_labels?.length ? charts.dept_labels : ['No data'];
            const deptTotals = charts.dept_salary?.length ? charts.dept_salary : [0];
            chartInstances.salary = new ApexCharts(salaryEl, {
                ...base,
                chart: { ...base.chart, type: 'bar', height: 320 },
                series: [{ name: 'Monthly payroll', data: deptTotals }],
                xaxis: {
                    categories: deptLabels,
                    labels: {
                        rotate: -35,
                        rotateAlways: deptLabels.some((l) => l.length > 8),
                        style: { fontSize: '11px' },
                    },
                },
                yaxis: {
                    labels: {
                        formatter: function (val) {
                            if (val >= 1e6) return (val / 1e6).toFixed(val >= 1e7 ? 0 : 1) + 'M';
                            if (val >= 1e3) return Math.round(val / 1e3) + 'K';
                            return String(Math.round(val));
                        },
                    },
                },
                colors: palette,
                plotOptions: {
                    bar: {
                        borderRadius: 8,
                        columnWidth: '52%',
                        distributed: true,
                        dataLabels: { position: 'top' },
                    },
                },
                legend: { show: false },
                tooltip: {
                    theme: 'light',
                    y: { formatter: (val) => formatSalaryTooltip(val) + ' / mo' },
                },
            });
            chartInstances.salary.render();
        }

        // Headcount
        const headcountEl = document.querySelector('#adminHeadcountChart');
        destroyChart('headcount');
        if (headcountEl) {
            const hcLabels = charts.dept_labels?.length ? charts.dept_labels : ['No data'];
            const hcData = charts.dept_headcount?.length ? charts.dept_headcount : [0];
            const hcColors = ['#6C4CF1', '#8b7af5', '#a78bfa', '#22c55e', '#0ea5e9', '#f59e0b'];
            const hcSum = hcData.reduce((a, b) => a + b, 0);
            chartInstances.headcount = new ApexCharts(headcountEl, {
                ...base,
                chart: { ...base.chart, type: 'donut', height: 260 },
                labels: hcLabels,
                series: hcData,
                colors: hcColors.slice(0, hcLabels.length),
                legend: {
                    position: 'bottom',
                    fontSize: '11px',
                    labels: { colors: '#64748b' },
                    horizontalAlign: 'center',
                    markers: { width: 8, height: 8, radius: 2 },
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
                                    },
                                },
                            },
                        },
                    },
                },
            });
            chartInstances.headcount.render();
        }

        renderLeaveTypeBars(charts.leave_types_mtd, charts.leave_period_label);

        // Hiring funnel (HTML bars — clear stage labels + counts)
        destroyChart('funnel');
        renderHiringFunnelBars(
            charts.funnel_labels,
            charts.funnel_data,
            charts.funnel_total
        );

    };

    function getEmptyCharts() {
        return {
            attendance_trend: { labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'], values: [0, 0, 0, 0, 0], subtitle: '' },
            today_mix: { 'ON TIME': 0, 'LATE IN': 0, 'HALF DAY': 0, 'ABSENT': 0 },
            dept_labels: [],
            dept_headcount: [],
            dept_salary: [],
            leave_types_mtd: [],
            funnel_labels: [],
            funnel_data: [],
        };
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof window.initAdminChartsFromData === 'function') {
            window.initAdminChartsFromData(null);
        }
    });
})();

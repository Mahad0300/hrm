/**
 * Static interview calendar (Job Management).
 * Click slot → candidate-detail.php
 */
(function () {
    var INTERVIEWS = [
        { date: '2026-03-03', time: '21:30', name: 'Muhammad Mazhar Raheel Saeed', job: 'Operations Assistant' },
        { date: '2026-03-03', time: '21:30', name: 'Arslan Khan', job: 'Operations Assistant' },
        { date: '2026-03-03', time: '21:30', name: 'Abdul Hannan', job: 'Operations Assistant' },
        { date: '2026-03-03', time: '21:30', name: 'Syed Aashir Salman', job: 'Operations Assistant' },
        { date: '2026-03-10', time: '10:00', name: 'Syed Shahir Ali', job: 'Operations Assistant' },
        { date: '2026-03-10', time: '14:30', name: 'Harmain Masood', job: 'SEO Executive' },
        { date: '2026-03-15', time: '11:00', name: 'M Sadiq Ahmed', job: 'Final Expense Sales Executive' },
        { date: '2026-03-18', time: '16:00', name: 'Ibad Uddin', job: 'SEO Executive' },
        { date: '2026-03-22', time: '09:30', name: 'Muhammad Wasim', job: 'SEO Executive' },
        { date: '2026-03-28', time: '15:00', name: 'Gohar Iqbal Khan', job: 'Final Expense Sales Executive' }
    ];

    var monthNames = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];

    var gridEl;
    var titleEl;
    var year = 2026;
    var month = 2;

    function pad(n) {
        return n < 10 ? '0' + n : '' + n;
    }

    function dateKey(y, m, d) {
        return y + '-' + pad(m + 1) + '-' + pad(d);
    }

    function getInterviewList() {
        return INTERVIEWS;
    }

    function interviewsForDate(key, list) {
        return list.filter(function (r) {
            return r.date === key;
        }).sort(function (a, b) {
            return a.time.localeCompare(b.time);
        });
    }

    function render() {
        if (!gridEl) return;
        var filtered = getInterviewList();
        var first = new Date(year, month, 1);
        var startPad = first.getDay();
        var daysInMonth = new Date(year, month + 1, 0).getDate();
        var html = '';
        var i;
        for (i = 0; i < startPad; i++) {
            html += '<div class="interview-cal-cell interview-cal-cell--empty"></div>';
        }
        for (var d = 1; d <= daysInMonth; d++) {
            var key = dateKey(year, month, d);
            var slots = interviewsForDate(key, filtered);
            html += '<div class="interview-cal-cell' + (slots.length ? ' interview-cal-cell--has' : '') + '">';
            html += '<div class="interview-cal-cell__num">' + d + '</div>';
            html += '<div class="interview-cal-cell__slots">';
            slots.forEach(function (s) {
                var label = s.time + ' - ' + s.name;
                html +=
                    '<a class="interview-slot" href="candidate-detail.php" title="' +
                    escapeAttr(s.job + ' · ' + s.name) +
                    '">' +
                    escapeHtml(label) +
                    '</a>';
            });
            html += '</div></div>';
        }
        gridEl.innerHTML = html;
        if (titleEl) {
            titleEl.textContent = monthNames[month] + ' ' + year;
        }
    }

    function escapeHtml(s) {
        var d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function escapeAttr(s) {
        return String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');
    }

    document.addEventListener('DOMContentLoaded', function () {
        gridEl = document.getElementById('interviewCalGrid');
        titleEl = document.getElementById('interviewCalMonthTitle');
        if (!gridEl) return;

        document.getElementById('interviewCalPrev') &&
            document.getElementById('interviewCalPrev').addEventListener('click', function () {
                month--;
                if (month < 0) {
                    month = 11;
                    year--;
                }
                render();
            });
        document.getElementById('interviewCalNext') &&
            document.getElementById('interviewCalNext').addEventListener('click', function () {
                month++;
                if (month > 11) {
                    month = 0;
                    year++;
                }
                render();
            });
        document.getElementById('interviewCalToday') &&
            document.getElementById('interviewCalToday').addEventListener('click', function () {
                var n = new Date();
                year = n.getFullYear();
                month = n.getMonth();
                render();
            });

        render();
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
})();

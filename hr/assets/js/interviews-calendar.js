/**
 * Static interview calendar (Job Management).
 * Click slot → candidate-detail.php
 */
(function () {
    var interviews = [];
    var loading = false;

    var monthNames = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];

    var gridEl;
    var titleEl;
    var year = new Date().getFullYear();
    var month = new Date().getMonth();

    async function fetchInterviews() {
        loading = true;
        render(); // Show loading state
        try {
            const response = await fetch('assets/api/calendar_handler.php?action=fetch_interviews');
            const result = await response.json();
            if (result.status === 'success') {
                interviews = result.data;
            }
        } catch (error) {
            console.error('Error fetching interviews:', error);
        } finally {
            loading = false;
            render();
        }
    }

    function pad(n) {
        return n < 10 ? '0' + n : '' + n;
    }

    function dateKey(y, m, d) {
        return y + '-' + pad(m + 1) + '-' + pad(d);
    }

    function interviewsForDate(key, list) {
        return list.filter(function (r) {
            return r.date === key;
        }).sort(function (a, b) {
            return a.time.localeCompare(b.time);
        });
    }

    function formatTime12h(timeStr) {
        if (!timeStr) return '';
        const parts = timeStr.split(':');
        if (parts.length < 2) return timeStr;
        let h = parseInt(parts[0]);
        const m = parts[1];
        const ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        return `${h}:${String(m).padStart(2, '0')} ${ampm}`;
    }

    function render() {
        if (!gridEl) return;
        
        if (loading) {
            gridEl.innerHTML = '<div class="w-full py-100 flex-center flex-column gap-16"><div class="spinner border-primary"></div><p class="text-light font-13">Fetching interviews...</p></div>';
            return;
        }

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
            var slots = interviewsForDate(key, interviews);
            html += '<div class="interview-cal-cell' + (slots.length ? ' interview-cal-cell--has' : '') + '">';
            html += '<div class="interview-cal-cell__num">' + d + '</div>';
            html += '<div class="interview-cal-cell__slots">';
            
            // Show only first 3
            var visibleSlots = slots.slice(0, 3);
            visibleSlots.forEach(function (s) {
                var label = formatTime12h(s.time) + ' - ' + s.name;
                html +=
                    '<a class="interview-slot" href="candidate-detail.php?id=' + s.candidate_id + '" title="' +
                    escapeAttr(s.job + ' · ' + s.name) +
                    '">' +
                    escapeHtml(label) +
                    '</a>';
            });

            if (slots.length > 3) {
                var moreCount = slots.length - 3;
                html += '<div class="more-events-tag" onclick="event.stopPropagation(); showDayInterviews(\'' + key + '\')">+' + moreCount + ' more</div>';
            }

            html += '</div></div>';
        }
        gridEl.innerHTML = html;
        if (titleEl) {
            titleEl.textContent = monthNames[month] + ' ' + year;
        }
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    window.showDayInterviews = function(dateStr) {
        const slots = interviewsForDate(dateStr, interviews);
        const [y, m, d] = dateStr.split('-').map(Number);
        const dateObj = new Date(y, m - 1, d);
        const dateTitle = monthNames[dateObj.getMonth()] + ' ' + d;
        
        document.getElementById('dayModalTitle').textContent = `Interviews on ${dateTitle}`;
        const listContainer = document.getElementById('dayInterviewsList');
        listContainer.innerHTML = '';

        slots.forEach(s => {
            const item = document.createElement('div');
            // Using 'meeting' class for purple border consistency
            item.className = `day-event-item meeting`;
            item.innerHTML = `
                <div class="event-info" onclick="window.location.href='candidate-detail.php?id=${s.candidate_id}'">
                    <div class="event-name">🤝 ${s.name}</div>
                    <div class="event-time">${formatTime12h(s.time)} • ${s.job}</div>
                </div>
                <div class="type-icon-box border" style="width: 28px; height: 28px; padding: 0;" onclick="window.location.href='candidate-detail.php?id=${s.candidate_id}'">
                    <i data-lucide="chevron-right" size="14"></i>
                </div>
            `;
            listContainer.appendChild(item);
        });

        if (typeof lucide !== 'undefined') lucide.createIcons();
        openModal('dayInterviewsModal');
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

        fetchInterviews();
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });

    // Helper functions for modal
    window.openModal = function(id) { document.getElementById(id).classList.add('active'); }
    window.closeModal = function(id) { document.getElementById(id).classList.remove('active'); }

})();

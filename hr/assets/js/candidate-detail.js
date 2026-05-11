/**
 * Candidate detail: schedule interview modal (date & time).
 */
(function () {
    function pad2(n) {
        return n < 10 ? '0' + n : String(n);
    }

    function todayLocalISO() {
        var d = new Date();
        return d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate());
    }

    function openScheduleInterviewModal() {
        var modal = document.getElementById('scheduleInterviewModal');
        var dateEl = document.getElementById('scheduleInterviewDate');
        var timeEl = document.getElementById('scheduleInterviewTime');
        var form = document.getElementById('scheduleInterviewForm');
        if (!modal || !dateEl || !timeEl || !form) return;

        form.reset();
        dateEl.value = todayLocalISO();
        timeEl.value = '10:00';

        var sub = document.getElementById('scheduleInterviewSubtitle');
        var nameEl = document.querySelector('.candidate-detail-summary-card h2');
        if (sub && nameEl) {
            sub.textContent = 'Set date and time for ' + nameEl.textContent.trim();
        }

        if (typeof openModal === 'function') {
            openModal('scheduleInterviewModal');
        } else {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.getElementById('scheduleInterviewBtn');
        var form = document.getElementById('scheduleInterviewForm');
        if (btn) btn.addEventListener('click', openScheduleInterviewModal);

        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var date = document.getElementById('scheduleInterviewDate').value;
                var time = document.getElementById('scheduleInterviewTime').value;
                if (typeof closeModal === 'function') {
                    closeModal('scheduleInterviewModal');
                } else {
                    var m = document.getElementById('scheduleInterviewModal');
                    if (m) {
                        m.classList.remove('active');
                        document.body.style.overflow = 'auto';
                    }
                }
                window.alert('Interview scheduled for ' + date + ' at ' + time + '.');
            });
        }
    });
})();

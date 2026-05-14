// user/assets/js/calendar.js

document.addEventListener('DOMContentLoaded', function() {
    let now = new Date();
    let currentMonth = now.getMonth();
    let currentYear = now.getFullYear();
    let allEvents = [];
    let activeFilter = null;
    let currentSearchQuery = '';

    const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    const calendarGrid = document.getElementById('calendarGrid');
    const monthYearText = document.getElementById('monthYear');

    if (!calendarGrid) {
        console.error('Calendar Grid not found!');
        return;
    }

    // Initial Rendering
    renderCalendar();
    
    // Fetch Data
    fetchEvents();
    setInterval(fetchEvents, 5000);

    if (typeof lucide !== 'undefined') lucide.createIcons();

    // --- Helpers ---
    function getEmoji(type) {
        const t = (type || '').toUpperCase();
        return {'MEETING': '🤝', 'CELEBRATION': '🎉', 'HOLIDAY': '📅'}[t] || '📢';
    }

    function formatEventDate(dateStr) {
        const [year, month, day] = dateStr.split('-').map(Number);
        const d = new Date(year, month - 1, day);
        if (isNaN(d.getTime())) return dateStr;
        
        const dayFormatted = String(d.getDate()).padStart(2, '0');
        const monthName = monthNames[d.getMonth()];
        const yearNum = d.getFullYear();
        return `${monthName} ${dayFormatted} - ${yearNum}`;
    }

    function formatTime12h(timeStr) {
        if (!timeStr) return 'All Day';
        const parts = timeStr.split(':');
        if (parts.length < 2) return timeStr;
        let h = parseInt(parts[0]);
        const m = parts[1];
        const ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        return `${h}:${m} ${ampm}`;
    }

    function timeAgo(date) {
        if (!date) return 'Just now';
        try {
            const seconds = Math.floor((new Date() - new Date(date)) / 1000);
            if (seconds < 60) return "Just now";
            let interval = Math.floor(seconds / 31536000);
            if (interval >= 1) return interval + " years ago";
            interval = Math.floor(seconds / 2592000);
            if (interval >= 1) return interval + " months ago";
            interval = Math.floor(seconds / 86400);
            if (interval >= 1) return interval + " days ago";
            interval = Math.floor(seconds / 3600);
            if (interval >= 1) return interval + " hours ago";
            interval = Math.floor(seconds / 60);
            if (interval >= 1) return interval + " minutes ago";
            return "Just now";
        } catch (e) { return 'Recent'; }
    }

    // --- API Calls ---
    function fetchEvents() {
        fetch('assets/api/calendar_handler.php?action=fetch')
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    allEvents = res.data;
                    renderCalendar();
                    renderRecentActivity();
                }
            })
            .catch(err => {
                console.error('Event Fetch Error:', err);
                renderCalendar();
            });
    }

    function renderCalendar() {
        if (!calendarGrid) return;
        calendarGrid.innerHTML = '';
        
        if (monthYearText) {
            monthYearText.textContent = `${monthNames[currentMonth]} ${currentYear}`;
        }
        
        const firstDay = new Date(currentYear, currentMonth, 1).getDay();
        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
        const daysInPrevMonth = new Date(currentYear, currentMonth, 0).getDate();
        
        const todayStr = new Date().toLocaleDateString('en-CA');

        // Prev Month Days
        for (let i = firstDay; i > 0; i--) {
            calendarGrid.appendChild(createDayElement(daysInPrevMonth - i + 1, 'prev-month'));
        }
        
        // Current Month Days
        for (let i = 1; i <= daysInMonth; i++) {
            const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
            const isToday = dateStr === todayStr;
            const dayDiv = createDayElement(i, isToday ? 'today' : '', dateStr);
            
            const dayEvents = allEvents.filter(e => {
                const matchesDate = e.event_date === dateStr;
                const matchesFilter = !activeFilter || e.category === activeFilter;
                const matchesSearch = !currentSearchQuery || e.title.toLowerCase().includes(currentSearchQuery.toLowerCase());
                return matchesDate && matchesFilter && matchesSearch;
            });

            const eventList = dayDiv.querySelector('.event-list');
            dayEvents.slice(0, 2).forEach(event => {
                const eventTag = document.createElement('div');
                eventTag.className = `event-tag event-${event.category.toLowerCase()}`;
                eventTag.textContent = `${getEmoji(event.category)} ${event.title}`;
                eventTag.onclick = (e) => {
                    e.stopPropagation();
                    viewEventDetails(event);
                };
                eventList.appendChild(eventTag);
            });

            if (dayEvents.length > 2) {
                const moreTag = document.createElement('div');
                moreTag.className = 'more-events-tag';
                moreTag.textContent = `+${dayEvents.length - 2} more`;
                
                moreTag.onclick = (e) => {
                    e.stopPropagation();
                    showDayEvents(dateStr, dayEvents);
                };

                moreTag.onmouseenter = (e) => showQuickPopover(e, dayEvents, dateStr);
                moreTag.onmouseleave = () => hideQuickPopover();

                eventList.appendChild(moreTag);
            }
            
            calendarGrid.appendChild(dayDiv);
        }
        
        // Next Month Days
        const totalShown = firstDay + daysInMonth;
        const remainingCells = totalShown > 35 ? (42 - totalShown) : (35 - totalShown);
        for (let i = 1; i <= remainingCells; i++) {
            calendarGrid.appendChild(createDayElement(i, 'next-month'));
        }

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function createDayElement(num, className, date = '') {
        const div = document.createElement('div');
        div.className = `calendar-day ${className}`;
        div.innerHTML = `<span class="day-number">${num}</span><div class="event-list"></div>`;
        return div;
    }

    // --- Navigation ---
    const prevBtn = document.getElementById('prevMonth');
    if (prevBtn) prevBtn.onclick = () => {
        currentMonth--;
        if (currentMonth < 0) { currentMonth = 11; currentYear--; }
        renderCalendar();
    };
    const nextBtn = document.getElementById('nextMonth');
    if (nextBtn) nextBtn.onclick = () => {
        currentMonth++;
        if (currentMonth > 11) { currentMonth = 0; currentYear++; }
        renderCalendar();
    };
    const todayBtn = document.getElementById('todayBtn');
    if (todayBtn) todayBtn.onclick = () => {
        const d = new Date();
        currentMonth = d.getMonth();
        currentYear = d.getFullYear();
        renderCalendar();
    };

    // --- Sidebar Filters ---
    const filterOptions = document.querySelectorAll('.filter-option');
    filterOptions.forEach(opt => {
        opt.onclick = function() {
            const cat = this.dataset.category;
            if (activeFilter === cat) {
                activeFilter = null;
                this.classList.remove('active');
            } else {
                filterOptions.forEach(o => o.classList.remove('active'));
                activeFilter = cat;
                this.classList.add('active');
            }
            renderCalendar();
        };
    });

    // --- Popover & List Modal ---
    function showQuickPopover(e, events, dateStr) {
        hideQuickPopover();
        const popover = document.createElement('div');
        popover.id = 'eventQuickPopover';
        popover.className = 'event-popover';
        
        let html = `<div class="popover-header">${formatEventDate(dateStr)}</div>`;
        events.forEach(ev => {
            html += `
                <div class="popover-item ${ev.category.toLowerCase()}">
                    <span class="dot"></span>
                    <span class="time">${formatTime12h(ev.event_time)}</span>
                    <span class="title">${ev.title}</span>
                </div>
            `;
        });

        popover.innerHTML = html;
        popover.style.pointerEvents = 'none';
        document.body.appendChild(popover);

        const rect = e.target.getBoundingClientRect();
        popover.style.left = `${rect.left}px`;
        popover.style.top = `${rect.top - popover.offsetHeight - 5}px`; 
    }

    function hideQuickPopover() {
        const popover = document.getElementById('eventQuickPopover');
        if (popover) popover.remove();
    }

    function showDayEvents(dateStr, events) {
        document.getElementById('dayModalTitle').textContent = `Events on ${formatEventDate(dateStr)}`;
        const listContainer = document.getElementById('dayEventsList');
        if (listContainer) {
            listContainer.innerHTML = '';
            events.forEach(event => {
                const item = document.createElement('div');
                item.className = `day-event-item ${event.category.toLowerCase()}`;
                item.innerHTML = `
                    <div class="event-info">
                        <div class="event-name">${getEmoji(event.category)} ${event.title}</div>
                        <div class="event-time">${formatTime12h(event.event_time)} • ${event.category}</div>
                    </div>
                    <i data-lucide="chevron-right" size="18"></i>
                `;
                item.onclick = () => {
                    closeModal('dayEventsModal');
                    viewEventDetails(event);
                };
                listContainer.appendChild(item);
            });
        }
        if (typeof lucide !== 'undefined') lucide.createIcons();
        openModal('dayEventsModal');
    }

    function viewEventDetails(event) {
        document.getElementById('detailTitle').textContent = event.title;
        document.getElementById('detailCategory').textContent = event.category;
        document.getElementById('detailCategory').className = `badge badge-${event.category.toLowerCase()}`;
        document.getElementById('detailDesc').textContent = event.description || 'No description provided.';
        document.getElementById('detailDept').textContent = event.target_dept;
        document.getElementById('detailDateTime').textContent = `${formatEventDate(event.event_date)} at ${formatTime12h(event.event_time)}`;
        document.getElementById('detailCreatedBy').textContent = event.author_name || 'System Admin';
        document.getElementById('detailUpdatedAt').textContent = timeAgo(event.updated_at || event.created_at);

        const visibilityEl = document.getElementById('detailVisibility');
        if (visibilityEl) {
            visibilityEl.textContent = event.show_in_announcement == 1 ? 'Show in Announcement' : "Not in Announcement";
            visibilityEl.className = `value font-13 ${event.show_in_announcement == 1 ? 'text-success' : 'text-light'}`;
        }
        openModal('eventDetailModal');
    }

    function renderRecentActivity() {
        const container = document.getElementById('recentActivity');
        if (!container) return;
        
        const sorted = [...allEvents].sort((a, b) => {
            const timeA = new Date(a.updated_at || a.created_at).getTime();
            const timeB = new Date(b.updated_at || b.created_at).getTime();
            return timeB - timeA;
        });

        const recent = sorted.slice(0, 3);
        if (recent.length === 0) {
            container.innerHTML = '<p class="text-light font-12">No recent events found.</p>';
            return;
        }

        container.innerHTML = '';
        recent.forEach(item => {
            const div = document.createElement('div');
            div.className = 'activity-item cursor-pointer';
            div.innerHTML = `
                <p class="activity-text"><b>${item.title}</b> was ${item.updated_at ? 'updated' : 'created'}</p>
                <span class="activity-meta">${timeAgo(item.updated_at || item.created_at)}</span>
            `;
            div.onclick = () => viewEventDetails(item);
            container.appendChild(div);
        });
    }

    const searchInput = document.getElementById('eventSearch');
    if (searchInput) searchInput.oninput = function() {
        currentSearchQuery = this.value;
        renderCalendar();
    };

});

function openModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.add('active');
}
function closeModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.remove('active');
}

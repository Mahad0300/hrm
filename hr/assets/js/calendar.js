// admin/assets/js/calendar.js

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

    // Initial Rendering (Show empty calendar immediately)
    renderCalendar();
    
    // Fetch Data
    fetchDepartments();
    fetchEvents();

    if (typeof lucide !== 'undefined') lucide.createIcons();

    // --- Helpers ---
    function getEmoji(type) {
        const t = (type || '').toUpperCase();
        return {'MEETING': '🤝', 'CELEBRATION': '🎉', 'HOLIDAY': '📅'}[t] || '📢';
    }

    function formatDeptLabel(value) {
        if (!value) return 'All Departments';
        const normalized = String(value).trim();
        if (normalized.toLowerCase() === 'everyone' || normalized.toLowerCase() === 'all') {
            return 'All Departments';
        }
        return normalized.split(',').map(v => v.trim()).filter(Boolean).join(', ');
    }

    function formatEventDate(dateStr) {
        // Parse manually to avoid timezone shifts with new Date(dateStr)
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

    function todayIso() {
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        return now.toISOString().slice(0, 10);
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
    function fetchDepartments() {
        fetch('assets/api/calendar_handler.php?action=fetch_depts')
            .then(res => res.json())
            .then(res => {
                const deptContainer = document.getElementById('eventDeptSelection');
                if (res.status === 'success' && deptContainer) {
                    const allPill = deptContainer.querySelector('[data-dept="everyone"]');
                    deptContainer.innerHTML = '';
                    if (allPill) {
                        deptContainer.appendChild(allPill);
                    }
                    res.data.forEach(dept => {
                        const pill = document.createElement('div');
                        pill.className = 'category-pill';
                        pill.dataset.dept = dept.name;
                        pill.textContent = dept.name;
                        deptContainer.appendChild(pill);
                    });
                    bindEventDeptPills();
                    setEventDeptSelection(document.getElementById('eventDept')?.value || 'everyone');
                }
            }).catch(e => console.error('Dept Fetch Error:', e));
    }

    function bindEventDeptPills() {
        const deptContainer = document.getElementById('eventDeptSelection');
        if (!deptContainer) return;

        deptContainer.querySelectorAll('.category-pill').forEach(pill => {
            pill.onclick = () => toggleEventDeptPill(pill);
        });
    }

    function toggleEventDeptPill(pill) {
        const deptContainer = document.getElementById('eventDeptSelection');
        const hiddenInput = document.getElementById('eventDept');
        if (!deptContainer || !hiddenInput) return;

        if (pill.dataset.dept === 'everyone') {
            deptContainer.querySelectorAll('.category-pill').forEach(p => p.classList.remove('active'));
            pill.classList.add('active');
            hiddenInput.value = 'everyone';
            return;
        }

        const everyonePill = deptContainer.querySelector('[data-dept="everyone"]');
        if (everyonePill) everyonePill.classList.remove('active');
        pill.classList.toggle('active');

        const selected = Array.from(deptContainer.querySelectorAll('.category-pill.active'))
            .map(p => p.dataset.dept)
            .filter(Boolean);

        if (selected.length === 0) {
            if (everyonePill) everyonePill.classList.add('active');
            hiddenInput.value = 'everyone';
        } else {
            hiddenInput.value = selected.join(',');
        }
    }

    function setEventDeptSelection(value) {
        const deptContainer = document.getElementById('eventDeptSelection');
        const hiddenInput = document.getElementById('eventDept');
        if (!deptContainer || !hiddenInput) return;

        const normalizedValue = !value || String(value).toLowerCase() === 'all' ? 'everyone' : String(value);
        const selected = normalizedValue.split(',').map(v => v.trim()).filter(Boolean);
        const isEveryone = selected.some(v => v.toLowerCase() === 'everyone' || v.toLowerCase() === 'all');

        deptContainer.querySelectorAll('.category-pill').forEach(pill => {
            pill.classList.toggle('active', isEveryone ? pill.dataset.dept === 'everyone' : selected.includes(pill.dataset.dept));
        });

        hiddenInput.value = isEveryone ? 'everyone' : (selected.length ? selected.join(',') : 'everyone');
    }

    function fetchEvents() {
        console.log('Fetching events...');
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
                renderCalendar(); // Render anyway
            });
    }

    function renderCalendar() {
        console.log('Rendering Calendar for:', currentMonth, currentYear);
        if (!calendarGrid) return;
        calendarGrid.innerHTML = '';
        
        if (monthYearText) {
            monthYearText.textContent = `${monthNames[currentMonth]} ${currentYear}`;
        }
        
        const firstDay = new Date(currentYear, currentMonth, 1).getDay();
        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
        const daysInPrevMonth = new Date(currentYear, currentMonth, 0).getDate();
        
        const todayStr = todayIso();

        // Prev Month Days
        for (let i = firstDay; i > 0; i--) {
            const dayDiv = createDayElement(daysInPrevMonth - i + 1, 'prev-month');
            calendarGrid.appendChild(dayDiv);
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
                
                // Keep existing modal click for touch devices
                moreTag.onclick = (e) => {
                    e.stopPropagation();
                    showDayEvents(dateStr, dayEvents);
                };

                // Add Hover Popover for Quick View
                moreTag.onmouseenter = (e) => showQuickPopover(e, dayEvents, dateStr);
                moreTag.onmouseleave = () => hideQuickPopover();

                eventList.appendChild(moreTag);
            }
            
            if (dateStr >= todayStr) {
                dayDiv.onclick = () => openEventModal(dateStr);
            } else {
                dayDiv.classList.add('calendar-day--past');
                dayDiv.title = 'Past dates cannot be used for new events';
            }
            calendarGrid.appendChild(dayDiv);
        }
        
        // Next Month Days
        const totalShown = firstDay + daysInMonth;
        const remainingCells = totalShown > 35 ? (42 - totalShown) : (35 - totalShown); // Row check
        for (let i = 1; i <= remainingCells; i++) {
            const dayDiv = createDayElement(i, 'next-month');
            calendarGrid.appendChild(dayDiv);
        }

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function showQuickPopover(e, events, dateStr) {
        hideQuickPopover(); // Remove existing if any

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
        popover.style.pointerEvents = 'none'; // Make it non-interactive to prevent flickering
        document.body.appendChild(popover);

        const rect = e.target.getBoundingClientRect();
        popover.style.left = `${rect.left}px`;
        popover.style.top = `${rect.top - popover.offsetHeight - 5}px`; 

        // No need for popover mouse events if pointer-events is none
    }

    function hideQuickPopover() {
        const popover = document.getElementById('eventQuickPopover');
        if (popover) popover.remove();
    }

    function showDayEvents(dateStr, events) {
        document.getElementById('dayModalTitle').textContent = `Events on ${formatEventDate(dateStr)}`;
        const listContainer = document.getElementById('dayEventsList');
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

        if (typeof lucide !== 'undefined') lucide.createIcons();
        openModal('dayEventsModal');
    }
    
    function createDayElement(num, className, date = '') {
        const div = document.createElement('div');
        div.className = `calendar-day ${className}`;
        div.innerHTML = `<span class="day-number">${num}</span><div class="event-list"></div>`;
        return div;
    }

    // --- Navigation ---
    document.getElementById('prevMonth').onclick = () => {
        currentMonth--;
        if (currentMonth < 0) { currentMonth = 11; currentYear--; }
        renderCalendar();
    };
    document.getElementById('nextMonth').onclick = () => {
        currentMonth++;
        if (currentMonth > 11) { currentMonth = 0; currentYear++; }
        renderCalendar();
    };
    document.getElementById('todayBtn').onclick = () => {
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

    // --- Modal Logic ---
    window.openEventModal = function(date = '') {
        const form = document.getElementById('eventForm');
        form.reset();
        
        const dateInput = document.getElementById('eventDate');
        const today = todayIso();
        if (dateInput) {
            dateInput.setAttribute('min', today);
        }

        document.getElementById('eventId').value = '';
        document.getElementById('modalTitle').textContent = 'Create New Event';
        document.getElementById('eventDate').value = date && date >= today ? date : today;
        setEventDeptSelection('everyone');
        openModal('eventModal');
    };

    document.getElementById('eventForm').onsubmit = function(e) {
        e.preventDefault();
        const event_date = document.getElementById('eventDate').value;
        const event_time = document.getElementById('eventTime').value;

        if (!event_date || !event_time) {
            Swal.fire('Error', 'Date and Time are required.', 'error');
            return;
        }

        const selectedDateTime = new Date(`${event_date}T${event_time}`);
        if (selectedDateTime < new Date()) {
            Swal.fire('Error', 'Please select a future date and time.', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'save');
        formData.append('id', document.getElementById('eventId').value);
        formData.append('title', document.getElementById('eventTitle').value);
        formData.append('category', document.getElementById('eventCategory').value);
        formData.append('target_dept', document.getElementById('eventDept').value);
        formData.append('event_date', event_date);
        formData.append('event_time', event_time);
        formData.append('description', document.getElementById('eventDesc').value);
        if (document.getElementById('eventShowInAccount').checked) {
            formData.append('show_in_announcement', '1');
        }

        fetch('assets/api/calendar_handler.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                Swal.fire('Success', res.message, 'success');
                closeModal('eventModal');
                fetchEvents();
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        });
    };

    function viewEventDetails(event) {
        document.getElementById('detailTitle').textContent = event.title;
        document.getElementById('detailCategory').textContent = event.category;
        document.getElementById('detailCategory').className = `badge badge-${event.category.toLowerCase()}`;
        document.getElementById('detailDesc').textContent = event.description || 'No description provided.';
        document.getElementById('detailDept').textContent = formatDeptLabel(event.target_dept);
        document.getElementById('detailDateTime').textContent = `${formatEventDate(event.event_date)} at ${formatTime12h(event.event_time)}`;
        document.getElementById('detailCreatedBy').textContent = event.author_name || 'System Admin';
        document.getElementById('detailUpdatedAt').textContent = timeAgo(event.updated_at || event.created_at);

        const visibilityEl = document.getElementById('detailVisibility');
        if (visibilityEl) {
            visibilityEl.textContent = event.show_in_announcement == 1 ? 'Show in Announcement' : "Don't Show in Announcement";
            visibilityEl.className = `value font-13 ${event.show_in_announcement == 1 ? 'text-success' : 'text-light'}`;
        }
        
        document.getElementById('editEventBtn').onclick = () => {
            closeModal('eventDetailModal');
            editEvent(event);
        };

        document.getElementById('deleteEventBtnDetail').onclick = () => {
            Swal.fire({
                title: 'Are you sure?',
                text: "This event will be removed!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const fd = new FormData();
                    fd.append('action', 'delete');
                    fd.append('id', event.id);
                    fetch('assets/api/calendar_handler.php', { method: 'POST', body: fd })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === 'success') {
                            Swal.fire('Deleted!', '', 'success');
                            closeModal('eventDetailModal');
                            fetchEvents();
                        }
                    });
                }
            });
        };
        
        openModal('eventDetailModal');
    }

    function editEvent(event) {
        openEventModal(event.event_date);
        document.getElementById('modalTitle').textContent = 'Edit Event';
        document.getElementById('eventId').value = event.id;
        document.getElementById('eventTitle').value = event.title;
        document.getElementById('eventCategory').value = event.category;
        setEventDeptSelection(event.target_dept);
        document.getElementById('eventDate').value = event.event_date;
        document.getElementById('eventTime').value = event.event_time;
        document.getElementById('eventDesc').value = event.description;
        document.getElementById('eventShowInAccount').checked = event.show_in_announcement == 1;
    }

    function renderRecentActivity() {
        const container = document.getElementById('recentActivity');
        if (!container) return;
        
        // Sort by actual time (updated_at or created_at) DESC
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

    document.getElementById('eventSearch').oninput = function() {
        currentSearchQuery = this.value;
        renderCalendar();
    };

});

function openModal(id) { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }

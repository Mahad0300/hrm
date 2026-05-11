// Event Calendar Logic
document.addEventListener('DOMContentLoaded', function() {
    let currentMonth = 2; // March (0-indexed: 0=Jan, 1=Feb, 2=Mar)
    let currentYear = 2026;
    
    // Mock Data
    let events = [
        { id: 1, title: 'Project Kickoff', date: '2026-03-05', time: '10:00', category: 'Meeting', dept: 'Engineering', desc: 'Initial meeting for the rtg HRM project. ' },
        { id: 2, title: 'Employee Onboarding', date: '2026-03-12', time: '09:00', category: 'Workshop', dept: 'HR', desc: 'Onboarding session for new joiners.' },
        { id: 3, title: 'Independence Day', date: '2026-03-23', time: '', category: 'Holiday', dept: 'All', desc: 'National Holiday.' },
        { id: 4, title: 'Code Review Sync', date: '2026-03-05', time: '14:00', category: 'Meeting', dept: 'Engineering', desc: 'Sync for recent pull requests.' },
        { id: 5, title: 'Design Feedback', date: '2026-03-18', time: '11:00', category: 'Meeting', dept: 'Design', desc: 'Reviewing the new calendar UI designs.' }
    ];

    const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    const calendarGrid = document.getElementById('calendarGrid');
    const monthYearText = document.getElementById('monthYear');

    if (!calendarGrid) {
        console.error('Event calendar: #calendarGrid missing');
        return;
    }

    // Initialize Calendar
    function renderCalendar() {
        calendarGrid.innerHTML = '';
        if (monthYearText) {
            monthYearText.textContent = `${monthNames[currentMonth]} ${currentYear}`;
        }
        
        const firstDay = new Date(currentYear, currentMonth, 1).getDay();
        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
        const daysInPrevMonth = new Date(currentYear, currentMonth, 0).getDate();
        
        // Prev Month Days
        for (let i = firstDay; i > 0; i--) {
            const dayDiv = createDayElement(daysInPrevMonth - i + 1, 'prev-month');
            calendarGrid.appendChild(dayDiv);
        }
        
        // Current Month Days
        const today = new Date();
        for (let i = 1; i <= daysInMonth; i++) {
            const isToday = today.getDate() === i && today.getMonth() === currentMonth && today.getFullYear() === currentYear;
            const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
            const dayDiv = createDayElement(i, isToday ? 'today' : '', dateStr);
            
            // Render Events for this day
            const dayEvents = events.filter(e => e.date === dateStr);
            const eventList = dayDiv.querySelector('.event-list');
            dayEvents.forEach(event => {
                const eventTag = document.createElement('div');
                eventTag.className = `event-tag event-${event.category.toLowerCase()}`;
                eventTag.textContent = event.title;
                eventTag.onclick = (e) => {
                    e.stopPropagation();
                    viewEventDetails(event);
                };
                eventList.appendChild(eventTag);
            });
            
            dayDiv.onclick = () => openEventModal(dateStr);
            calendarGrid.appendChild(dayDiv);
        }
        
        // Next Month Days
        const remainingCells = 42 - (firstDay + daysInMonth);
        for (let i = 1; i <= remainingCells; i++) {
            const dayDiv = createDayElement(i, 'next-month');
            calendarGrid.appendChild(dayDiv);
        }

        lucide.createIcons();
    }
    
    function createDayElement(num, className, fullDate) {
        const div = document.createElement('div');
        div.className = `calendar-day ${className}`;
        div.innerHTML = `
            <span class="day-number">${num}</span>
            <div class="event-list"></div>
        `;
        return div;
    }
    
    // Navigation
    const prevMonthBtn = document.getElementById('prevMonth');
    const nextMonthBtn = document.getElementById('nextMonth');
    const todayBtnEl = document.getElementById('todayBtn');
    if (prevMonthBtn) {
        prevMonthBtn.onclick = () => {
            currentMonth--;
            if (currentMonth < 0) { currentMonth = 11; currentYear--; }
            renderCalendar();
        };
    }
    if (nextMonthBtn) {
        nextMonthBtn.onclick = () => {
            currentMonth++;
            if (currentMonth > 11) { currentMonth = 0; currentYear++; }
            renderCalendar();
        };
    }
    if (todayBtnEl) {
        todayBtnEl.onclick = () => {
            const d = new Date();
            currentMonth = d.getMonth();
            currentYear = d.getFullYear();
            renderCalendar();
        };
    }
    
    // Modal Functions
    window.openEventModal = function(date = '') {
        const modal = document.getElementById('eventModal');
        const form = document.getElementById('eventForm');
        form.reset();
        document.getElementById('eventId').value = '';
        document.getElementById('modalTitle').textContent = 'Create New Event';
        document.getElementById('modalSubtitle').textContent = 'Schedule and manage company events';
        document.getElementById('eventDate').value = date;
        document.getElementById('eventShowInAccount').checked = false;
        openModal('eventModal');
        lucide.createIcons();
    };
    
    // Save Event
    document.getElementById('eventForm').onsubmit = function(e) {
        e.preventDefault();
        const id = document.getElementById('eventId').value;
        const newEvent = {
            id: id ? parseInt(id) : Date.now(),
            title: document.getElementById('eventTitle').value,
            category: document.getElementById('eventCategory').value,
            dept: document.getElementById('eventDept').value,
            date: document.getElementById('eventDate').value,
            time: document.getElementById('eventTime').value,
            desc: document.getElementById('eventDesc').value,
            showInAccount: document.getElementById('eventShowInAccount').checked
        };
        
        if (id) {
            events = events.map(ev => ev.id === parseInt(id) ? newEvent : ev);
        } else {
            events.push(newEvent);
        }
        
        closeModal('eventModal');
        renderCalendar();
        showToast(id ? 'Event updated successfully' : 'Event created successfully');
    };
    
    // View Details
    function viewEventDetails(event) {
        const modal = document.getElementById('eventDetailModal');
        document.getElementById('detailTitle').textContent = event.title;
        document.getElementById('detailCategory').textContent = event.category;
        document.getElementById('detailCategory').className = `badge badge-${event.category.toLowerCase()}`;
        document.getElementById('detailCategoryText').textContent = `${event.category} Details`;
        document.getElementById('detailDesc').textContent = event.desc || 'No description provided.';
        document.getElementById('detailDept').textContent = event.dept;
        document.getElementById('detailDateTime').textContent = `${event.date} at ${event.time || 'All Day'}`;
        
        const visibilityEl = document.getElementById('detailVisibility');
        if (visibilityEl) {
            visibilityEl.textContent = event.showInAccount ? 'Show in Announcement' : "Don't Show in Announcement";
            visibilityEl.className = `value font-13 ${event.showInAccount ? 'text-success' : 'text-light'}`;
        }
        
        document.getElementById('editEventBtn').onclick = () => {
            closeModal('eventDetailModal');
            editEvent(event);
        };

        const deleteBtnDetail = document.getElementById('deleteEventBtnDetail');
        if (deleteBtnDetail) {
            deleteBtnDetail.onclick = () => {
                if (confirm('Are you sure you want to delete this event?')) {
                    events = events.filter(ev => ev.id !== event.id);
                    closeModal('eventDetailModal');
                    renderCalendar();
                    showToast('Event deleted successfully');
                }
            };
        }
        
        openModal('eventDetailModal');
        lucide.createIcons();
    }
    
    function editEvent(event) {
        openEventModal(event.date);
        document.getElementById('modalTitle').textContent = 'Edit Event';
        document.getElementById('modalSubtitle').textContent = 'Update the details for this event';
        document.getElementById('eventId').value = event.id;
        document.getElementById('eventTitle').value = event.title;
        document.getElementById('eventCategory').value = event.category;
        document.getElementById('eventDept').value = event.dept;
        document.getElementById('eventDate').value = event.date;
        document.getElementById('eventTime').value = event.time;
        document.getElementById('eventDesc').value = event.desc;
        document.getElementById('eventShowInAccount').checked = event.showInAccount || false;
        // Removed delete button logic from here
    }
    
    // Delete logic moved inside viewEventDetails
    
    // Filters & Search
    document.getElementById('eventSearch').oninput = function() {
        const query = this.value.toLowerCase();
        document.querySelectorAll('.event-tag').forEach(tag => {
            const isVisible = tag.textContent.toLowerCase().includes(query);
            tag.style.display = isVisible ? 'block' : 'none';
        });
    };
    
    function renderCalendarFiltered(dept = '', category = null) {
        // Simple visual filter (could be more robust)
        document.querySelectorAll('.event-tag').forEach(tag => {
            // This is a naive implementation, real rendering should re-filter the events array
        });
    }

    // View Tab Toggling
    const viewTabs = document.querySelectorAll('.view-tab');
    viewTabs.forEach(tab => {
        tab.onclick = function() {
            viewTabs.forEach(t => t.classList.remove('btn-active'));
            this.classList.add('btn-active');
            // Here you would implement the actual view change logic
            showToast(`Switched to ${this.dataset.view} view`);
        };
    });

    // Mock Activity Data
    const mockActivities = [
        { text: "<b>Project Kickoff</b> event created by Admin", time: "2 hours ago" },
        { text: "<b>Design Feedback</b> event updated by Sarah", time: "5 hours ago" },
        { text: "<b>Holiday: Eid</b> event added by Admin", time: "Yesterday" }
    ];

    function renderRecentActivity() {
        const container = document.getElementById('recentActivity');
        if (!container) return;
        
        container.innerHTML = mockActivities.map(item => `
            <div class="activity-item">
                <p class="activity-text">${item.text}</p>
                <span class="activity-meta">${item.time}</span>
            </div>
        `).join('');
    }

    // Dummy Toast
    function showToast(msg) {
        console.log("Toast: " + msg);
        // You could implement a visual toast here
    }

    renderCalendar();
    renderRecentActivity();
});

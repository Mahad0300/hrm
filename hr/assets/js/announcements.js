document.addEventListener('DOMContentLoaded', function() {
    // Universal Selection Logic
    function setupSelection(gridId, hiddenInputId, itemClass, multiSelect = false) {
        const grid = document.getElementById(gridId);
        if (!grid) return;
        const items = grid.querySelectorAll('.' + itemClass);
        items.forEach(item => {
            item.addEventListener('click', function() {
                if (multiSelect) {
                    const isEveryone = this.dataset.dept === 'Everyone';
                    
                    if (isEveryone) {
                        // If "Everyone" is clicked, clear others
                        items.forEach(i => i.classList.remove('active'));
                        this.classList.add('active');
                    } else {
                        // If specific dept clicked, clear "Everyone"
                        items.forEach(i => {
                            if (i.dataset.dept === 'Everyone') i.classList.remove('active');
                        });
                        this.classList.toggle('active');
                    }

                    // Update hidden input with comma separated values
                    const activeValues = Array.from(grid.querySelectorAll('.' + itemClass + '.active'))
                        .map(i => i.dataset.dept || i.dataset.type);
                    
                    const hiddenInput = document.getElementById(hiddenInputId);
                    if (hiddenInput) {
                        hiddenInput.value = activeValues.join(',');
                    }
                    
                    // Fallback: If nothing is selected, default back to "Everyone" or empty
                    if (activeValues.length === 0) {
                        const everyoneBtn = Array.from(items).find(i => i.dataset.dept === 'Everyone');
                        if (everyoneBtn) {
                            everyoneBtn.classList.add('active');
                            hiddenInput.value = 'Everyone';
                        }
                    }
                } else {
                    // Single select logic (for Types)
                    items.forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                    const hiddenInput = document.getElementById(hiddenInputId);
                    if (hiddenInput) {
                        hiddenInput.value = this.dataset.type || this.dataset.dept;
                    }
                }
            });
        });
    }

    // Setup for Create Modal
    setupSelection('annTypeSelection', 'selectedAnnType', 'ann-type-card', false);
    setupSelection('deptSelection', 'selectedAnnDept', 'category-pill', true);

    // Setup for Edit Modal
    setupSelection('editAnnTypeSelection', 'edit_selectedAnnType', 'ann-type-card', false);
    setupSelection('editDeptSelection', 'edit_selectedAnnDept', 'category-pill', true);

    // Rich Text Toolbar Logic (Universal)
    const toolbarBtns = document.querySelectorAll('.toolbar-btn');
    toolbarBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const command = this.getAttribute('title');
            document.execCommand(command, false, null);
            this.classList.toggle('active');
        });
    });
});

function openEditAnnouncementModal(id) {
    // In a real app, you'd fetch data by ID. Using mock data for now.
    const mockData = {
        'ANN-1': {
            title: 'Q3 Town Hall Meeting',
            type: 'IMPORTANT',
            dept: 'Everyone',
            desc: 'Join us for the quarterly town hall meeting where we\'ll discuss our achievements and future strategy. Attendance is mandatory for all departments.',
            start: '2026-03-14',
            end: '2026-03-21'
        },
        'ANN-2': {
            title: 'Scheduled System Maintenance',
            type: 'UPDATE',
            dept: 'Engineering',
            desc: 'The internal servers will be under maintenance this Friday from 10 PM to 2 AM. Please save your work and log out of all systems before the scheduled time.',
            start: '2026-03-14',
            end: '2026-03-15'
        }
    };

    const data = mockData[id];
    if (data) {
        // Basic fields
        document.getElementById('edit_ann_title').value = data.title;
        document.getElementById('edit_ann_start').value = data.start;
        document.getElementById('edit_ann_end').value = data.end;
        
        // Custom Selection states: Type
        const typeGrid = document.getElementById('editAnnTypeSelection');
        const typeCards = typeGrid.querySelectorAll('.ann-type-card');
        typeCards.forEach(card => {
            card.classList.toggle('active', card.dataset.type === data.type);
        });
        document.getElementById('edit_selectedAnnType').value = data.type;

        // Custom Selection states: Dept
        const deptGrid = document.getElementById('editDeptSelection');
        if (deptGrid) {
            const deptPills = deptGrid.querySelectorAll('.category-pill');
            const selectedDepts = data.dept.split(',');
            deptPills.forEach(pill => {
                pill.classList.toggle('active', selectedDepts.includes(pill.dataset.dept));
            });
            document.getElementById('edit_selectedAnnDept').value = data.dept;
        }

        // Rich Text Content
        const editor = document.getElementById('edit_ann_rich_desc');
        if (editor) {
            editor.innerHTML = data.desc;
        }
        
        openModal('editAnnouncementModal');
    }
}

function viewAnnouncementDetail(id) {
    // Using the same mock data for consistency
    const mockData = {
        'ANN-1': {
            title: 'Q3 Town Hall Meeting',
            type: 'IMPORTANT',
            emoji: '🚨',
            dept: 'Everyone',
            desc: 'Join us for the quarterly town hall meeting where we\'ll discuss our achievements and future strategy. Attendance is mandatory for all departments.',
            start: '2026-03-14',
            end: '2026-03-21'
        },
        'ANN-2': {
            title: 'Scheduled System Maintenance',
            type: 'UPDATE',
            emoji: '📢',
            dept: 'Engineering',
            desc: 'The internal servers will be under maintenance this Friday from 10 PM to 2 AM. Please save your work and log out of all systems before the scheduled time.',
            start: '2026-03-14',
            end: '2026-03-15'
        }
    };

    const data = mockData[id];
    if (data) {
        document.getElementById('view_ann_title').textContent = data.title;
        document.getElementById('view_ann_date_range').textContent = `${data.start} to ${data.end}`;
        
        // Type Badge
        const typeBadge = document.getElementById('view_ann_type_badge');
        typeBadge.textContent = `${data.emoji} ${data.type}`;
        typeBadge.className = 'badge'; // Reset
        const typeClassMap = {
            'IMPORTANT': 'badge-danger',
            'CELEBRATION': 'badge-warning',
            'UPDATE': 'badge-primary',
            'HOLIDAY': 'badge-success'
        };
        typeBadge.classList.add(typeClassMap[data.type] || 'badge-info');

        // Departments
        const deptGrid = document.getElementById('view_ann_depts');
        deptGrid.innerHTML = '';
        const depts = data.dept.split(',');
        depts.forEach(dept => {
            const pill = document.createElement('div');
            pill.className = 'category-pill active';
            pill.textContent = dept;
            deptGrid.appendChild(pill);
        });

        // Description
        document.getElementById('view_ann_desc').innerHTML = data.desc;

        openModal('viewAnnouncementModal');
    }
}

function deleteAnnouncement(id) {
    if (confirm('Are you sure you want to delete this announcement? This action cannot be undone.')) {
        console.log('Deleting announcement:', id);
        // Add actual deletion logic here
        alert('Announcement deleted successfully!');
    }
}

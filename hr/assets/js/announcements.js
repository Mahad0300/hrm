// admin/assets/js/announcements.js

document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('announcementsContainer');
    const searchInput = document.getElementById('searchTitle');
    const categorySelect = document.getElementById('filterCategory');
    const statusSelect = document.getElementById('filterStatus');
    let allData = [];

    if (!container) return;

    // Initial Load
    fetchAnnouncements();
    fetchDepartments();

    function fetchAnnouncements() {
        fetch('assets/api/announcement_handler.php?action=fetch')
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    allData = res.data;
                    renderAnnouncements(allData);
                }
            });
    }

    function fetchDepartments() {
        fetch('assets/api/announcement_handler.php?action=fetch_depts')
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    const depts = res.data;
                    const containers = [document.getElementById('deptSelection'), document.getElementById('editDeptSelection'), document.getElementById('view_ann_depts')];
                    
                    containers.forEach(container => {
                        if (!container) return;
                        
                        // Attach listener to pre-existing "Everyone" pill if it exists
                        const everyonePill = container.querySelector('[data-dept="everyone"]');
                        if (everyonePill) {
                            everyonePill.onclick = () => toggleDeptPill(everyonePill, container);
                        }

                        // For View modal, clear all except for the pills we will add
                        if (container.id === 'view_ann_depts') container.innerHTML = '';
                        
                        depts.forEach(dept => {
                            const pill = document.createElement('div');
                            pill.className = 'category-pill';
                            pill.dataset.dept = dept.name;
                            pill.textContent = dept.name;
                            pill.onclick = () => toggleDeptPill(pill, container);
                            container.appendChild(pill);
                        });
                    });
                }
            });
    }

    function toggleDeptPill(pill, container) {
        if (container.id === 'view_ann_depts') return; // Read-only

        const hiddenInputId = container.id === 'deptSelection' ? 'selectedAnnDepts' : 'edit_selectedAnnDepts';
        const hiddenInput = document.getElementById(hiddenInputId);

        if (pill.dataset.dept === 'everyone') {
            container.querySelectorAll('.category-pill').forEach(p => p.classList.remove('active'));
            pill.classList.add('active');
            hiddenInput.value = 'everyone';
        } else {
            const everyonePill = container.querySelector('[data-dept="everyone"]');
            if (everyonePill) everyonePill.classList.remove('active');
            
            pill.classList.toggle('active');
            
            const activePills = Array.from(container.querySelectorAll('.category-pill.active')).map(p => p.dataset.dept);
            hiddenInput.value = activePills.length > 0 ? activePills.join(',') : 'everyone';
            
            if (activePills.length === 0 && everyonePill) {
                everyonePill.classList.add('active');
                hiddenInput.value = 'everyone';
            }
        }
    }

    function getBadgeClass(type) {
        const t = (type || '').toUpperCase();
        if (t === 'IMPORTANT') return 'type-important-sm';
        if (t === 'CELEBRATION') return 'type-celebration-sm';
        if (t === 'HOLIDAY') return 'type-holiday-sm';
        return 'type-update-sm';
    }

    function renderAnnouncements(data) {
        container.innerHTML = '';
        if (data.length === 0) {
            container.innerHTML = `
                <div class="empty-state-container">
                    <div class="empty-state-icon"><i data-lucide="megaphone" size="32"></i></div>
                    <h4 class="empty-state-title">No Announcements Found</h4>
                    <p class="empty-state-desc">Try broadening your search or filter parameters.</p>
                </div>`;
            if (typeof lucide !== 'undefined') lucide.createIcons();
            return;
        }

        data.forEach(ann => {
            const card = document.createElement('div');
            card.className = 'announcement-card';
            
            let emoji = '📢';
            if (ann.type === 'CELEBRATION') emoji = '🎉';
            if (ann.type === 'HOLIDAY') emoji = '📅';
            if (ann.type === 'IMPORTANT') emoji = '🚨';
            if (ann.type === 'MEETING') emoji = '🤝';

            card.innerHTML = `
                <div class="card-shape shape-1"></div>
                <div class="card-shape shape-2"></div>
                <div class="announcement-content">
                    <div class="flex-between mb-20">
                        <span class="type-badge-sm ${getBadgeClass(ann.type)}">${emoji} ${ann.type}</span>
                        <span class="status-badge status-${ann.calculated_status.toLowerCase()}">${ann.calculated_status}</span>
                    </div>
                    <h3 class="mb-12 font-18">${ann.title}</h3>
                    <p class="font-14 text-light mb-20">${stripHtml(ann.content).substring(0, 120)}...</p>
                </div>
                <div class="announcement-footer">
                    <div class="flex-center gap-10">
                        <img src="${ann.profile_pic || '../images/profile-image/default-avatar.svg'}" 
                             class="icon-box-sm" onerror="this.src='../images/profile-image/default-avatar.svg'">
                        <span class="font-13 font-500">${ann.author_name || 'System Admin'}</span>
                    </div>
                    <div class="flex-center gap-10">
                        <button class="action-btn info" title="View" onclick="viewAnnouncementDetail(${ann.id}, '${ann.source}')"><i data-lucide="eye" size="14"></i></button>
                        ${ann.source === 'announcement' ? `
                            <button class="action-btn primary" title="Edit" onclick="openEditAnnouncementModal(${ann.id})"><i data-lucide="edit-2" size="14"></i></button>
                            <button class="action-btn danger" title="Delete" onclick="deleteAnnouncement(${ann.id})"><i data-lucide="trash-2" size="14"></i></button>
                        ` : ''}
                    </div>
                </div>`;
            container.appendChild(card);
        });

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function stripHtml(html) {
        let tmp = document.createElement("DIV");
        tmp.innerHTML = html;
        return tmp.textContent || tmp.innerText || "";
    }

    // --- Create Logic ---
    const typeCards = document.querySelectorAll('#annTypeSelection .ann-type-card');
    typeCards.forEach(card => {
        card.onclick = () => {
            typeCards.forEach(c => c.classList.remove('active'));
            card.classList.add('active');
            document.getElementById('selectedAnnType').value = card.dataset.type;
        };
    });

    const editTypeCards = document.querySelectorAll('#editAnnTypeSelection .ann-type-card');
    editTypeCards.forEach(card => {
        card.onclick = () => {
            editTypeCards.forEach(c => c.classList.remove('active'));
            card.classList.add('active');
            document.getElementById('edit_selectedAnnType').value = card.dataset.type;
        };
    });

    document.getElementById('announcementForm').onsubmit = function(e) {
        e.preventDefault();
        saveAnnouncement('create');
    };

    document.getElementById('editAnnouncementForm').onsubmit = function(e) {
        e.preventDefault();
        saveAnnouncement('edit');
    };

    function todayIso() {
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        return now.toISOString().slice(0, 10);
    }

    function syncAnnouncementDateLimits(prefix) {
        const startEl = document.getElementById(`${prefix}ann_start`);
        const endEl = document.getElementById(`${prefix}ann_end`);
        if (!startEl || !endEl) return;

        startEl.min = todayIso();
        endEl.min = startEl.value || todayIso();

        if (endEl.value && endEl.value < endEl.min) {
            endEl.value = endEl.min;
        }
    }

    ['add_', 'edit_'].forEach(prefix => {
        const startEl = document.getElementById(`${prefix}ann_start`);
        if (startEl) {
            syncAnnouncementDateLimits(prefix);
            startEl.addEventListener('change', () => syncAnnouncementDateLimits(prefix));
        }
    });

    function saveAnnouncement(mode) {
        if (window.HR_PERMS) {
            const permType = mode === 'edit' ? 'edit' : 'create';
            if (!HR_PERMS.can('announcements', permType)) {
                HR_PERMS.showDenied(permType);
                return;
            }
        }

        const prefix = mode === 'edit' ? 'edit_' : 'add_';
        const typePrefix = mode === 'edit' ? 'edit_' : '';
        const richEditor = document.querySelector(mode === 'edit' ? '#edit_ann_rich_desc' : '#announcementForm .rich-text-editor');
        const startDate = document.getElementById(`${prefix}ann_start`).value;
        const endDate = document.getElementById(`${prefix}ann_end`).value;

        if (startDate < todayIso()) {
            Swal.fire('Warning', 'Start date cannot be a past date.', 'warning');
            return;
        }

        if (endDate < startDate) {
            Swal.fire('Warning', 'End date cannot be before start date.', 'warning');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'save');
        if (mode === 'edit') formData.append('id', currentEditId);
        formData.append('title', document.getElementById(`${prefix}ann_title`).value);
        formData.append('type', document.getElementById(`${typePrefix}selectedAnnType`).value);
        formData.append('target_depts', document.getElementById(`${typePrefix}selectedAnnDepts`).value);
        formData.append('content', richEditor.innerHTML);
        formData.append('start_date', startDate);
        formData.append('end_date', endDate);

        fetch('assets/api/announcement_handler.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    Swal.fire('Success', res.message, 'success');
                    closeModal(mode === 'edit' ? 'editAnnouncementModal' : 'createAnnouncementModal');
                    fetchAnnouncements();
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            });
    }

    // --- Global Actions ---
    let currentEditId = null;
    window.openEditAnnouncementModal = function(id) {
        const ann = allData.find(a => a.id == id && a.source === 'announcement');
        if (!ann) return;
        currentEditId = id;

        document.getElementById('edit_ann_title').value = ann.title;
        document.getElementById('edit_selectedAnnType').value = ann.type;
        document.getElementById('edit_selectedAnnDepts').value = ann.target_depts;
        document.getElementById('edit_ann_rich_desc').innerHTML = ann.content;
        document.getElementById('edit_ann_start').value = ann.start_date;
        document.getElementById('edit_ann_end').value = ann.end_date;
        syncAnnouncementDateLimits('edit_');

        // Activate Type card
        editTypeCards.forEach(c => {
            c.classList.toggle('active', c.dataset.type === ann.type);
        });

        // Activate Dept pills
        const depts = ann.target_depts.split(',');
        const container = document.getElementById('editDeptSelection');
        container.querySelectorAll('.category-pill').forEach(p => {
            p.classList.toggle('active', depts.includes(p.dataset.dept));
        });

        openModal('editAnnouncementModal');
    };

    window.deleteAnnouncement = function(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This announcement will be permanently removed!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('action', 'delete');
                fd.append('id', id);
                fetch('assets/api/announcement_handler.php', { method: 'POST', body: fd })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === 'success') {
                            Swal.fire('Deleted!', '', 'success');
                            fetchAnnouncements();
                        }
                    });
            }
        });
    };

    window.viewAnnouncementDetail = function(id, source) {
        const item = allData.find(a => a.id == id && a.source == source);
        if(!item) return;

        document.getElementById('view_ann_title').textContent = item.title;
        document.getElementById('view_ann_desc').innerHTML = item.content;
        document.getElementById('view_ann_type_badge').className = `type-badge-sm ${getBadgeClass(item.type)}`;
        document.getElementById('view_ann_type_badge').textContent = item.type;
        document.getElementById('view_ann_status_badge').className = `status-badge status-${item.calculated_status.toLowerCase()}`;
        document.getElementById('view_ann_status_badge').textContent = item.calculated_status;
        document.getElementById('view_ann_date_range').textContent = `${item.start_date} to ${item.end_date}`;
        document.getElementById('view_ann_author_name').textContent = item.author_name || 'System Admin';
        document.getElementById('view_ann_author_img').src = item.profile_pic || '../images/profile-image/default-avatar.svg';

        // Target depts in view
        const deptContainer = document.getElementById('view_ann_depts');
        deptContainer.innerHTML = '';
        const depts = item.target_depts.split(',');
        depts.forEach(d => {
            const pill = document.createElement('div');
            pill.className = 'category-pill active';
            pill.textContent = d;
            deptContainer.appendChild(pill);
        });

        openModal('viewAnnouncementModal');
    };

    // --- Search & Filters ---
    function applyFilters() {
        const query = searchInput.value.toLowerCase();
        const category = categorySelect.value;
        const status = statusSelect.value;

        const filtered = allData.filter(ann => {
            const matchesQuery = ann.title.toLowerCase().includes(query) || ann.content.toLowerCase().includes(query);
            const matchesCategory = category === '' || ann.type === category;
            const matchesStatus = status === '' || ann.calculated_status === status;
            return matchesQuery && matchesCategory && matchesStatus;
        });

        renderAnnouncements(filtered);
    }

    searchInput.addEventListener('input', applyFilters);
    categorySelect.addEventListener('change', applyFilters);
    statusSelect.addEventListener('change', applyFilters);

    // --- Rich Text Toolbar ---
    document.querySelectorAll('.toolbar-btn').forEach(btn => {
        btn.onclick = (e) => {
            e.preventDefault();
            const cmd = btn.getAttribute('title').split(' ')[0]; // Basic bold/italic/etc
            document.execCommand(cmd, false, null);
        };
    });

});

function openModal(id) { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }

// Close modal when clicking X
document.querySelectorAll('.js-modal-close').forEach(btn => {
    btn.onclick = () => {
        const modal = btn.closest('.modal-overlay');
        if (modal) closeModal(modal.id);
    };
});

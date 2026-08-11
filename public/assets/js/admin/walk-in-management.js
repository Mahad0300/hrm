/**
 * Walk-In Candidates Management (Admin)
 */

document.addEventListener('DOMContentLoaded', function () {
    let candidates = [];
    let filteredCandidates = [];
    let currentPage = 1;
    let perPage = 10;

    const tableBody = document.getElementById('candidateTableBody');
    const searchInput = document.getElementById('candidateSearch');
    const filterDept = document.getElementById('filterDept');
    const filterStatus = document.getElementById('filterStatus');
    const sortBy = document.getElementById('sortBy');
    const perPageSelect = document.getElementById('perPageSelect');
    const paginationInfo = document.getElementById('paginationInfo');
    const tableSummary = document.getElementById('tableSummary');
    const pageNumbers = document.getElementById('pageNumbers');
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');

    function getApiUrl(action) {
        const base = (window.HRM && typeof window.HRM.api === 'function')
            ? window.HRM.api('job_handler.php')
            : '/assets/api/job_handler.php';
        return action ? `${base}?action=${action}` : base;
    }

    function fetchWalkInCandidates() {
        fetch(`${getApiUrl('fetch_walk_in_candidates')}&_=${Date.now()}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    candidates = data.data || [];
                    const totalCountEl = document.getElementById('totalWalkInsCount');
                    if (totalCountEl) {
                        totalCountEl.textContent = candidates.length;
                    }
                    populateDepartmentFilter();
                    applyFilters();
                }
            })
            .catch(err => console.error('Error fetching walk-in candidates:', err));
    }

    function populateDepartmentFilter() {
        if (!filterDept) return;
        const currentVal = filterDept.value;
        const depts = new Set();
        candidates.forEach(c => {
            if (c.department_name) depts.add(c.department_name);
        });

        filterDept.innerHTML = '<option value="">All Departments</option>';
        Array.from(depts).sort().forEach(d => {
            const opt = document.createElement('option');
            opt.value = d;
            opt.textContent = d;
            filterDept.appendChild(opt);
        });
        filterDept.value = currentVal;
    }

    function applyFilters() {
        const query = (searchInput ? searchInput.value : '').toLowerCase();
        const selectedDept = filterDept ? filterDept.value : '';
        const selectedStatus = filterStatus ? filterStatus.value : '';
        const selectedSort = sortBy ? sortBy.value : 'Sort by: Newest';

        filteredCandidates = candidates.filter(c => {
            const matchesSearch = !query || 
                (c.name && c.name.toLowerCase().includes(query)) ||
                (c.job_title && c.job_title.toLowerCase().includes(query));
            
            const matchesDept = !selectedDept || c.department_name === selectedDept;
            const matchesStatus = !selectedStatus || c.status === selectedStatus;

            return matchesSearch && matchesDept && matchesStatus;
        });

        if (selectedSort === 'Sort by: Oldest') {
            filteredCandidates.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
        } else {
            filteredCandidates.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        }

        currentPage = 1;
        renderTable();
    }

    function renderTable() {
        if (!tableBody) return;
        tableBody.innerHTML = '';

        const total = filteredCandidates.length;
        if (perPageSelect) {
            perPage = perPageSelect.value === 'all' ? total : parseInt(perPageSelect.value, 10);
        }

        const totalPages = Math.ceil(total / perPage) || 1;
        if (currentPage > totalPages) currentPage = totalPages;

        const start = (currentPage - 1) * perPage;
        const end = perPageSelect.value === 'all' ? total : Math.min(start + perPage, total);
        const pageData = filteredCandidates.slice(start, end);

        if (tableSummary) tableSummary.textContent = `Showing ${total} entries`;
        if (paginationInfo) paginationInfo.textContent = total === 0 ? 'Showing 0 to 0 of 0 entries' : `Showing ${start + 1} to ${end} of ${total} entries`;

        if (pageData.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-40 text-muted">
                        <i data-lucide="user-x" size="32" class="mb-8" style="opacity: 0.5;"></i>
                        <div>No walk-in candidates found.</div>
                    </td>
                </tr>
            `;
            if (typeof lucide !== 'undefined') lucide.createIcons();
            renderPagination(totalPages);
            return;
        }

        pageData.forEach(cand => {
            const tr = document.createElement('tr');
            
            const initials = (cand.name || 'C').split(' ').filter(Boolean).map(n => n[0]).join('').substring(0, 2).toUpperCase() || 'C';
            const avatarColors = ['#22c55e', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];
            const avatarBg = avatarColors[(cand.name || '').length % avatarColors.length];
            const statusSlug = (cand.status || 'New').toLowerCase().replace(/\s+/g, '-');
            const detailUrl = (window.HRM && typeof window.HRM.url === 'function') ? window.HRM.url('recruitment/detail') + '?id=' + cand.id : 'recruitment/detail?id=' + cand.id;

            tr.innerHTML = `
                <td>
                    <div class="flex-center gap-12">
                        <div class="avatar-initial" style="background: ${avatarBg}20; color: ${avatarBg}; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0;">
                            ${initials}
                        </div>
                        <div>
                            <div class="font-14 font-700 text-dark">${escapeHtml(cand.name)}</div>
                            <div class="font-11 text-light mt-1">${escapeHtml(cand.email || '')}</div>
                        </div>
                    </div>
                </td>
                <td class="font-14">${escapeHtml(cand.job_title || 'N/A')}</td>
                <td class="font-14">${escapeHtml(cand.department_name || 'N/A')}</td>
                <td>
                    <span class="badge-select ${statusSlug}">${escapeHtml(cand.status || 'New')}</span>
                </td>
                <td class="text-center">
                    <div class="flex-center justify-center gap-8">
                        <a href="${detailUrl}" class="action-btn action-btn-view" title="View details">
                            <i data-lucide="eye" size="16"></i>
                        </a>
                        <button onclick="window.deleteWalkInCandidate(${cand.id})" class="action-btn action-btn-delete" title="Delete Candidate">
                            <i data-lucide="trash-2" size="16"></i>
                        </button>
                    </div>
                </td>
            `;
            tableBody.appendChild(tr);
        });

        if (typeof lucide !== 'undefined') lucide.createIcons();
        renderPagination(totalPages);
    }

    function getStatusBadgeClass(status) {
        switch (status) {
            case 'New': return 'badge-info';
            case 'Shortlisted': return 'badge-primary';
            case 'Interview': return 'badge-warning';
            case 'Offer': return 'badge-purple';
            case 'Hired': return 'badge-success';
            case 'Rejected': return 'badge-danger';
            case 'Banned': return 'badge-dark';
            case 'Duplicated': return 'badge-secondary';
            default: return 'badge-light';
        }
    }

    function renderPagination(totalPages) {
        if (!pageNumbers) return;
        pageNumbers.innerHTML = '';

        if (prevBtn) prevBtn.disabled = currentPage === 1;
        if (nextBtn) nextBtn.disabled = currentPage === totalPages || totalPages === 0;

        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.className = `action-btn ${i === currentPage ? 'active' : ''}`;
            btn.textContent = i;
            btn.addEventListener('click', () => {
                currentPage = i;
                renderTable();
            });
            pageNumbers.appendChild(btn);
        }
    }

    function escapeHtml(str) {
        return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // Attach Event Listeners
    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (filterDept) filterDept.addEventListener('change', applyFilters);
    if (filterStatus) filterStatus.addEventListener('change', applyFilters);
    if (sortBy) sortBy.addEventListener('change', applyFilters);
    if (perPageSelect) perPageSelect.addEventListener('change', () => { currentPage = 1; renderTable(); });

    if (prevBtn) prevBtn.addEventListener('click', () => { if (currentPage > 1) { currentPage--; renderTable(); } });
    if (nextBtn) nextBtn.addEventListener('click', () => { currentPage++; renderTable(); });

    // Global delete function
    window.deleteWalkInCandidate = function(candId) {
        Swal.fire({
            title: 'Delete Candidate?',
            text: 'Are you sure you want to remove this walk-in candidate application?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Delete'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'delete_candidate');
                formData.append('id', candId);

                fetch(getApiUrl(), {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Candidate has been deleted.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        fetchWalkInCandidates();
                    } else {
                        Swal.fire('Error', data.message || 'Failed to delete candidate.', 'error');
                    }
                })
                .catch(err => Swal.fire('Error', 'Server communication failed.', 'error'));
            }
        });
    };

    // Expose globally for WebSocket real-time updates
    window.fetchWalkInCandidates = fetchWalkInCandidates;

    // WebSocket real-time updates
    window.addEventListener('ws_event', function(e) {
        if (e.detail && (e.detail.type === 'candidates_updated' || e.detail.type === 'walkin_updated')) {
            fetchWalkInCandidates();
        }
    });

    // Initial Load
    fetchWalkInCandidates();
});

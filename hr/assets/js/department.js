document.addEventListener('DOMContentLoaded', () => {
    const deptTableBody = document.getElementById('deptTableBody');
    const addDeptForm = document.getElementById('addDeptForm');

    // Initial Mock Data
    const defaultDepts = [
        { id: 1, name: 'Engineering', manager: 'Oliver Mitchell', head: 'CTO', total: 156 },
        { id: 2, name: 'Design', manager: 'Sophia Reynolds', head: 'CPO', total: 42 },
        { id: 3, name: 'Human Resources', manager: 'Emma Williams', head: 'CHRO', total: 12 },
        { id: 4, name: 'Sales & Marketing', manager: 'James Wilson', head: 'Managing Director', total: 84 }
    ];

    let depts = JSON.parse(localStorage.getItem('hrm_departments')) || defaultDepts;

    const perPageSelect = document.getElementById('perPageSelect');
    const tableSummary = document.getElementById('tableSummary');
    const paginationInfo = document.getElementById('paginationInfo');
    const pageNumbersContainer = document.getElementById('pageNumbers');
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');

    let currentPage = 1;
    let rowsPerPage = parseInt(perPageSelect.value) || 10;

    function renderDepts() {
        if (!deptTableBody) return;
        
        const totalRows = depts.length;
        if (totalRows === 0) {
            deptTableBody.innerHTML = `<tr><td colspan="5" class="text-center p-40 text-light">No departments found. Add one to get started.</td></tr>`;
            return;
        }

        const totalPages = rowsPerPage === -1 ? 1 : Math.ceil(totalRows / rowsPerPage);
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const start = (currentPage - 1) * rowsPerPage;
        const end = rowsPerPage === -1 ? totalRows : start + rowsPerPage;
        const currentItems = rowsPerPage === -1 ? depts : depts.slice(start, end);

        deptTableBody.innerHTML = currentItems.map(dept => `
            <tr>
                <td>
                    <div class="flex-center gap-12">
                        <div class="stat-icon primary sm">
                            <i data-lucide="building-2" size="16"></i>
                        </div>
                        <span class="font-600 text-dark">${dept.name}</span>
                    </div>
                </td>
                <td>
                    <span class="font-14">${dept.manager || '<span class="text-light italic">Not Assigned</span>'}</span>
                </td>
                <td>
                    <span class="font-14 text-dark">${dept.head || 'Not Assigned'}</span>
                </td>
                <td>
                    <div class="flex-start gap-8">
                        <div class="progress-bar-container w-80">
                            <div class="progress-bar success" style="width: ${Math.min((dept.total / 200) * 100, 100)}%"></div>
                        </div>
                        <span class="font-12 text-light font-600">${dept.total || 0}</span>
                    </div>
                </td>
                <td class="text-right px-30">
                    <div class="btn-group justify-end">
                        <button class="action-btn action-btn-edit" title="Edit" onclick="editDept(${dept.id})"><i data-lucide="edit-2" size="14"></i></button>
                        <button class="action-btn action-btn-delete" title="Delete" onclick="deleteDept(${dept.id})"><i data-lucide="trash-2" size="14"></i></button>
                    </div>
                </td>
            </tr>
        `).join('');

        // Update Info Text
        const showingStart = totalRows === 0 ? 0 : start + 1;
        const showingEnd = Math.min(end, totalRows);
        const infoText = `Showing ${showingStart} to ${showingEnd} of ${totalRows} entries`;
        if (paginationInfo) paginationInfo.textContent = infoText;
        if (tableSummary) tableSummary.textContent = infoText;

        updatePaginationControls(totalPages);
        lucide.createIcons();
    }

    function updatePaginationControls(totalPages) {
        if (!pageNumbersContainer) return;
        pageNumbersContainer.innerHTML = '';
        
        if (totalPages <= 1) {
            prevBtn.parentElement.classList.add('hidden');
            return;
        } else {
            prevBtn.parentElement.classList.remove('hidden');
        }

        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages;
        prevBtn.style.opacity = currentPage === 1 ? '0.5' : '1';
        nextBtn.style.opacity = currentPage === totalPages ? '0.5' : '1';

        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.className = `action-btn ${i === currentPage ? 'btn-active' : ''}`;
            btn.textContent = i;
            btn.onclick = () => {
                currentPage = i;
                renderDepts();
            };
            pageNumbersContainer.appendChild(btn);
        }
    }

    if (perPageSelect) {
        perPageSelect.addEventListener('change', () => {
            const val = perPageSelect.value;
            rowsPerPage = val === 'all' ? -1 : parseInt(val);
            currentPage = 1;
            renderDepts();
        });
    }

    if (prevBtn) {
        prevBtn.onclick = () => {
            if (currentPage > 1) {
                currentPage--;
                renderDepts();
            }
        };
    }

    if (nextBtn) {
        nextBtn.onclick = () => {
            const totalPages = rowsPerPage === -1 ? 1 : Math.ceil(depts.length / rowsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                renderDepts();
            }
        };
    }

    if (addDeptForm) {
        addDeptForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const nameInput = document.getElementById('deptName');
            const managerInput = document.getElementById('deptManager');
            const headInput = document.getElementById('deptHead');

            const newDept = {
                id: Date.now(),
                name: nameInput.value,
                manager: managerInput.value,
                head: headInput.value,
                total: Math.floor(Math.random() * 50) + 1 // Mock total for new ones
            };

            depts.push(newDept);
            localStorage.setItem('hrm_departments', JSON.stringify(depts));
            renderDepts();
            
            if (window.closeModal) window.closeModal('addDeptModal');
            addDeptForm.reset();
        });
    }

    const editDeptForm = document.getElementById('editDeptForm');
    if (editDeptForm) {
        editDeptForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const id = parseInt(document.getElementById('editDeptId').value);
            const deptIndex = depts.findIndex(d => d.id === id);

            if (deptIndex !== -1) {
                depts[deptIndex] = {
                    ...depts[deptIndex],
                    name: document.getElementById('editDeptName').value,
                    manager: document.getElementById('editDeptManager').value,
                    head: document.getElementById('editDeptHead').value
                };

                localStorage.setItem('hrm_departments', JSON.stringify(depts));
                renderDepts();
                if (window.closeModal) window.closeModal('editDeptModal');
            }
        });
    }

    window.deleteDept = (id) => {
        if (confirm('Are you sure you want to delete this department? This action cannot be undone.')) {
            depts = depts.filter(d => d.id !== id);
            localStorage.setItem('hrm_departments', JSON.stringify(depts));
            renderDepts();
        }
    };

    window.editDept = (id) => {
        const dept = depts.find(d => d.id === id);
        if (dept) {
            document.getElementById('editDeptId').value = dept.id;
            document.getElementById('editDeptName').value = dept.name;
            document.getElementById('editDeptManager').value = dept.manager || '';
            document.getElementById('editDeptHead').value = dept.head || '';
            
            if (window.openModal) window.openModal('editDeptModal');
        }
    };

    renderDepts();
});

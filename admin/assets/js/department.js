document.addEventListener('DOMContentLoaded', () => {
    const deptTableBody = document.getElementById('deptTableBody');
    const addDeptForm = document.getElementById('addDeptForm');

    // Remove Initial Mock Data and localStorage
    let depts = [];

    const perPageSelect = document.getElementById('perPageSelect');
    const tableSummary = document.getElementById('tableSummary');
    const paginationInfo = document.getElementById('paginationInfo');
    const pageNumbersContainer = document.getElementById('pageNumbers');
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');

    let currentPage = 1;
    let rowsPerPage = parseInt(perPageSelect.value) || 10;

    // --- AJAX: Fetch Departments ---
    async function fetchDepartments() {
        try {
            const response = await fetch('assets/api/department_handler.php?action=fetch');
            const result = await response.json();

            if (result.status === 'success') {
                depts = result.data;
                renderDepts();
            } else {
                console.error(result.message);
                deptTableBody.innerHTML = `<tr><td colspan="5" class="text-center p-40 text-danger">Error: ${result.message}</td></tr>`;
            }
        } catch (error) {
            console.error('Fetch error:', error);
            deptTableBody.innerHTML = `<tr><td colspan="5" class="text-center p-40 text-danger">Error loading departments.</td></tr>`;
        }
    }

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
                    <span class="font-14">${dept.manager_name || '<span class="text-light italic">Not Assigned</span>'}</span>
                </td>
                <td>
                    <span class="font-14 text-dark">${dept.head_name || 'Not Assigned'}</span>
                </td>
                <td>
                    <div class="flex-start gap-8">
                        <div class="progress-bar-container w-80">
                            <div class="progress-bar success" style="width: ${Math.min((dept.total / 100) * 100, 100)}%"></div>
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

    // --- AJAX: Add Department ---
    if (addDeptForm) {
        addDeptForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = document.getElementById('addDeptFormSubmit');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Creating...';
            }

            const formData = new FormData();
            formData.append('name', document.getElementById('deptName').value);
            formData.append('manager', document.getElementById('deptManager').value);
            formData.append('head', document.getElementById('deptHead').value);

            try {
                const response = await fetch('assets/api/department_handler.php?action=add', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.status === 'success') {
                    // Refresh data
                    fetchDepartments();
                    if (window.closeModal) window.closeModal('addDeptModal');
                    addDeptForm.reset();
                    showToast('Department created successfully!', 'success');
                } else {
                    showToast('Error: ' + result.message, 'error');
                }
            } catch (error) {
                console.error('Submit error:', error);
                showToast('An error occurred. Please try again.', 'error');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Create Department';
                }
            }
        });
    }

    // --- AJAX: Edit Department ---
    const editDeptForm = document.getElementById('editDeptForm');
    if (editDeptForm) {
        editDeptForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('editDeptId').value;
            const submitBtn = document.getElementById('editDeptFormSubmit');
            if (submitBtn) submitBtn.disabled = true;

            const formData = new FormData();
            formData.append('id', id);
            formData.append('name', document.getElementById('editDeptName').value);
            formData.append('manager', document.getElementById('editDeptManager').value);
            formData.append('head', document.getElementById('editDeptHead').value);

            try {
                const response = await fetch('assets/api/department_handler.php?action=edit', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.status === 'success') {
                    fetchDepartments();
                    if (window.closeModal) window.closeModal('editDeptModal');
                    showToast('Department updated successfully!', 'success');
                } else {
                    showToast('Error: ' + result.message, 'error');
                }
            } catch (error) {
                console.error('Update error:', error);
                showToast('An error occurred.', 'error');
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    }

    // --- AJAX: Delete Department ---
    window.deleteDept = async (id) => {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this department deletion!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#6C4CF1',
            cancelButtonColor: '#ef4444',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            background: '#ffffff',
            borderRadius: '16px'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('id', id);

                try {
                    const response = await fetch('assets/api/department_handler.php?action=delete', {
                        method: 'POST',
                        body: formData
                    });
                    const res = await response.json();

                    if (res.status === 'success') {
                        fetchDepartments();
                        showToast('Department deleted successfully!', 'success');
                    } else {
                        showToast('Error: ' + res.message, 'error');
                    }
                } catch (error) {
                    console.error('Delete error:', error);
                    showToast('An error occurred.', 'error');
                }
            }
        });
    };

    window.editDept = (id) => {
        const dept = depts.find(d => d.id == id);
        if (dept) {
            document.getElementById('editDeptId').value = dept.id;
            document.getElementById('editDeptName').value = dept.name;
            document.getElementById('editDeptManager').value = dept.manager || '';
            document.getElementById('editDeptHead').value = dept.head || '';

            if (window.openModal) window.openModal('editDeptModal');
        }
    };

    async function loadHierarchyRoles() {
        try {
            const response = await fetch('assets/api/employee_handler.php?action=fetch_hierarchy_roles');
            const result = await response.json();
            
            if (result.status === 'success') {
                const managerSelects = [document.getElementById('deptManager'), document.getElementById('editDeptManager')];
                const headSelects = [document.getElementById('deptHead'), document.getElementById('editDeptHead')];
                
                const managerHtml = '<option value="">Select Manager</option>' + 
                    result.managers.map(m => {
                        const name = [m.first_name, m.middle_name, m.last_name].filter(v => v && v.trim() !== '').join(' ');
                        return `<option value="${m.id}">${name}</option>`;
                    }).join('');
                
                const headHtml = '<option value="">Select Head</option>' + 
                    result.heads.map(h => {
                        const name = [h.first_name, h.middle_name, h.last_name].filter(v => v && v.trim() !== '').join(' ');
                        return `<option value="${h.id}">${name}</option>`;
                    }).join('');
                
                managerSelects.forEach(s => { if(s) s.innerHTML = managerHtml; });
                headSelects.forEach(s => { if(s) s.innerHTML = headHtml; });
            }
        } catch (error) {
            console.error('Error loading roles:', error);
        }
    }

    // Initial Load
    fetchDepartments();
    loadHierarchyRoles();
});

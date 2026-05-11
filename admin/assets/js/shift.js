document.addEventListener('DOMContentLoaded', () => {
    const shiftTableBody = document.querySelector('.data-table tbody');
    const shiftForm = document.getElementById('shiftForm');
    const editShiftForm = document.getElementById('editShiftForm');

    let shifts = [];

    // --- AJAX: Fetch Shifts ---
    async function fetchShifts() {
        try {
            const response = await fetch('assets/api/shift_handler.php?action=fetch');
            const result = await response.json();

            if (result.status === 'success') {
                shifts = result.data;
                renderShifts();
            } else {
                console.error(result.message);
                shiftTableBody.innerHTML = `<tr><td colspan="6" class="text-center p-40 text-danger">Error: ${result.message}</td></tr>`;
            }
        } catch (error) {
            console.error('Fetch error:', error);
            shiftTableBody.innerHTML = `<tr><td colspan="6" class="text-center p-40 text-danger">Error loading shifts.</td></tr>`;
        }
    }

    function renderShifts() {
        if (!shiftTableBody) return;

        if (shifts.length === 0) {
            shiftTableBody.innerHTML = `<tr><td colspan="6" class="text-center p-40 text-light">No shifts found. Add one to get started.</td></tr>`;
            return;
        }

        shiftTableBody.innerHTML = shifts.map(shift => {
            const formatTime = (timeStr) => {
                if (!timeStr || timeStr === 'N/A') return 'N/A';
                const [hours, minutes] = timeStr.split(':');
                const h = parseInt(hours);
                const ampm = h >= 12 ? 'PM' : 'AM';
                const hh = h % 12 || 12;
                return `${hh.toString().padStart(2, '0')}:${minutes} ${ampm}`;
            };

            const calculateDuration = (start, end) => {
                if (!start || !end) return 0;
                const [sH, sM] = start.split(':').map(Number);
                const [eH, eM] = end.split(':').map(Number);
                let diff = (eH * 60 + eM) - (sH * 60 + sM);
                if (diff < 0) diff += 24 * 60; // Night shift handling
                const totalHours = diff / 60;
                return totalHours.toFixed(1).replace('.0', '');
            };

            const duration = calculateDuration(shift.start_time, shift.end_time);
            const halfdayFormatted = parseFloat(shift.halfday_hours);

            return `
                <tr>
                    <td>
                        <div class="emp-profile">
                            <div class="icon-box-32 primary">
                                <i data-lucide="clock" size="16"></i>
                            </div>
                            <div class="emp-info">
                                <span class="name">${shift.name}</span>
                                <span class="email">Mon - Fri • ${duration} Hours</span>
                            </div>
                        </div>
                    </td>
                    <td>${formatTime(shift.start_time)}</td>
                    <td>${formatTime(shift.end_time)}</td>
                    <td>
                        <span class="badge badge-info text-uppercase">${shift.grace_time} Mins</span>
                    </td>
                    <td>${halfdayFormatted} Hours</td>
                    <td>
                        <div class="btn-group justify-end px-20">
                            <button class="action-btn action-btn-edit" title="Edit Shift" onclick="openEditShiftModal(${shift.id})"><i data-lucide="edit-2" size="14"></i></button>
                            <button class="action-btn action-btn-delete" title="Delete Shift" onclick="deleteShift(${shift.id})"><i data-lucide="trash-2" size="14"></i></button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        lucide.createIcons();
    }

    // --- AJAX: Add Shift ---
    if (shiftForm) {
        shiftForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = document.getElementById('addShiftFormSubmit');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i data-lucide="loader-2" size="18" class="spin"></i> Creating...';
                lucide.createIcons();
            }

            const formData = new FormData();
            formData.append('name', document.getElementById('shift_name').value);
            formData.append('start_time', document.getElementById('shift_start').value);
            formData.append('end_time', document.getElementById('shift_end').value);
            formData.append('grace_time', document.getElementById('shift_grace').value);
            formData.append('halfday_hours', document.getElementById('shift_halfday').value);

            try {
                const response = await fetch('assets/api/shift_handler.php?action=add', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.status === 'success') {
                    fetchShifts();
                    if (window.closeModal) window.closeModal('addShiftModal');
                    shiftForm.reset();
                    showToast('Shift created successfully!', 'success');
                } else {
                    showToast('Error: ' + result.message, 'error');
                }
            } catch (error) {
                console.error('Submit error:', error);
                showToast('An error occurred.', 'error');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Finalize & Create Shift <i data-lucide="arrow-right" size="18"></i>';
                    lucide.createIcons();
                }
            }
        });
    }

    // --- AJAX: Edit Shift ---
    if (editShiftForm) {
        editShiftForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('edit_shift_id').value;
            const submitBtn = document.getElementById('editShiftFormSubmit');
            if (submitBtn) submitBtn.disabled = true;

            const formData = new FormData();
            formData.append('id', id);
            formData.append('name', document.getElementById('edit_shift_name').value);
            formData.append('start_time', document.getElementById('edit_shift_start').value);
            formData.append('end_time', document.getElementById('edit_shift_end').value);
            formData.append('grace_time', document.getElementById('edit_shift_grace').value);
            formData.append('halfday_hours', document.getElementById('edit_shift_halfday').value);

            try {
                const response = await fetch('assets/api/shift_handler.php?action=edit', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.status === 'success') {
                    fetchShifts();
                    if (window.closeModal) window.closeModal('editShiftModal');
                    showToast('Shift updated successfully!', 'success');
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

    // --- AJAX: Delete Shift ---
    window.deleteShift = (id) => {
        Swal.fire({
            title: 'Are you sure?',
            text: "This will remove the shift from the active list!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#6C4CF1',
            cancelButtonColor: '#ef4444',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            borderRadius: '16px'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('id', id);

                try {
                    const response = await fetch('assets/api/shift_handler.php?action=delete', {
                        method: 'POST',
                        body: formData
                    });
                    const res = await response.json();

                    if (res.status === 'success') {
                        fetchShifts();
                        showToast('Shift deleted successfully!', 'success');
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

    window.openEditShiftModal = (id) => {
        const shift = shifts.find(s => s.id == id);
        if (shift) {
            document.getElementById('edit_shift_id').value = shift.id;
            document.getElementById('edit_shift_name').value = shift.name;
            document.getElementById('edit_shift_start').value = shift.start_time;
            document.getElementById('edit_shift_end').value = shift.end_time;
            document.getElementById('edit_shift_grace').value = shift.grace_time;
            document.getElementById('edit_shift_halfday').value = parseFloat(shift.halfday_hours);

            if (window.openModal) window.openModal('editShiftModal');
        }
    };

    // Initial Load
    fetchShifts();
});

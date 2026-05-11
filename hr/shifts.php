<?php 
$page_title = "Shift Management";
$page_subtitle = "Configure and manage employee work schedules.";
include 'includes/header.php'; 
?>
<?php include 'includes/sidebar.php'; ?>

<!-- Page Action Area -->
<div class="page-action-area">
    <button class="btn-primary" onclick="openModal('addShiftModal')">
        <i data-lucide="plus"></i>
        <span>Add New Shift</span>
    </button>
</div>

<!-- Shifts Table -->
<div class="card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>SHIFT NAME</th>
                    <th>START TIME</th>
                    <th>END TIME</th>
                    <th>GRACE TIME</th>
                    <th>HALFDAY</th>
                    <th class="text-right">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="emp-profile">
                            <div class="icon-box-32 primary">
                                <i data-lucide="clock" size="16"></i>
                            </div>
                            <div class="emp-info">
                                <span class="name">Shift A</span>
                                <span class="email">Mon - Fri • 9 Hours</span>
                            </div>
                        </div>
                    </td>
                    <td>09:00 AM</td>
                    <td>06:00 PM</td>
                    <td>
                        <span class="badge badge-info text-uppercase">15 Mins</span>
                    </td>
                    <td>04 Hours</td>
                    <td>
                        <div class="btn-group justify-end px-20">
                            <button class="action-btn action-btn-edit" title="Edit Shift" onclick="openEditShiftModal('Shift A', '09:00', '18:00', 15, 4)"><i data-lucide="edit-2" size="14"></i></button>
                            <button class="action-btn action-btn-delete" title="Delete Shift"><i data-lucide="trash-2" size="14"></i></button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="emp-profile">
                            <div class="icon-box-32 info">
                                <i data-lucide="clock" size="16"></i>
                            </div>
                            <div class="emp-info">
                                <span class="name">Shift B</span>
                                <span class="email">Mon - Fri • 9 Hours</span>
                            </div>
                        </div>
                    </td>
                    <td>04:00 PM</td>
                    <td>01:00 AM</td>
                    <td>
                        <span class="badge badge-info text-uppercase">10 Mins</span>
                    </td>
                    <td>04 Hours</td>
                    <td>
                        <div class="btn-group justify-end px-20">
                            <button class="action-btn action-btn-edit" title="Edit Shift" onclick="openEditShiftModal('Shift B', '16:00', '01:00', 10, 4)"><i data-lucide="edit-2" size="14"></i></button>
                            <button class="action-btn action-btn-delete" title="Delete Shift"><i data-lucide="trash-2" size="14"></i></button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="emp-profile">
                            <div class="icon-box-32 success">
                                <i data-lucide="clock" size="16"></i>
                            </div>
                            <div class="emp-info">
                                <span class="name">Shift C</span>
                                <span class="email">Mon - Fri • 9 Hours</span>
                            </div>
                        </div>
                    </td>
                    <td>10:00 PM</td>
                    <td>07:00 AM</td>
                    <td>
                        <span class="badge badge-info text-uppercase">15 Mins</span>
                    </td>
                    <td>04 Hours</td>
                    <td>
                        <div class="btn-group justify-end px-20">
                            <button class="action-btn action-btn-edit" title="Edit Shift" onclick="openEditShiftModal('Shift C', '22:00', '07:00', 15, 4)"><i data-lucide="edit-2" size="14"></i></button>
                            <button class="action-btn action-btn-delete" title="Delete Shift"><i data-lucide="trash-2" size="14"></i></button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Shift Modal -->
<div class="modal-overlay" id="addShiftModal">
    <div class="modal-content premium">
        <div class="modal-header">
            <div>
                <h3>Add New Shift</h3>
                <p class="font-12 text-light mt-1">Define timing and policies for a new employee shift</p>
            </div>
            <button class="icon-btn js-modal-close"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body p-30">
            <form id="shiftForm">
                <div class="form-group mb-24">
                    <label class="admin-form-label">Shift Name</label>
                    <input type="text" class="form-control bg-white-input" placeholder="e.g. Standard Morning">
                </div>
                
                <div class="form-grid-2">
                    <div class="form-group mb-24">
                        <label class="admin-form-label">Start Time</label>
                        <input type="time" class="form-control bg-white-input">
                    </div>
                    <div class="form-group mb-24">
                        <label class="admin-form-label">End Time</label>
                        <input type="time" class="form-control bg-white-input">
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group mb-24">
                        <label class="admin-form-label">Grace Time (Mins)</label>
                        <input type="number" class="form-control bg-white-input" placeholder="e.g. 15">
                    </div>
                    <div class="form-group mb-24">
                        <label class="admin-form-label">Halfday (Hours)</label>
                        <input type="number" class="form-control bg-white-input" placeholder="e.g. 4">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer p-30 border-top-0">
            <button type="submit" form="shiftForm" class="btn-primary px-30">
                Finalize & Create Shift <i data-lucide="arrow-right" size="18"></i>
            </button>
        </div>
    </div>
</div>

<!-- Edit Shift Modal -->
<div class="modal-overlay" id="editShiftModal">
    <div class="modal-content premium">
        <div class="modal-header">
            <div>
                <h3>Edit Shift</h3>
                <p class="font-12 text-light mt-1">Modify timing and policies for an existing employee shift</p>
            </div>
            <button class="icon-btn js-modal-close"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body p-30">
            <form id="editShiftForm">
                <div class="form-group mb-24">
                    <label class="admin-form-label">Shift Name</label>
                    <input type="text" class="form-control bg-white-input" id="edit_shift_name" value="Shift A">
                </div>
                
                <div class="form-grid-2">
                    <div class="form-group mb-24">
                        <label class="admin-form-label">Start Time</label>
                        <input type="time" class="form-control bg-white-input" id="edit_shift_start" value="09:00">
                    </div>
                    <div class="form-group mb-24">
                        <label class="admin-form-label">End Time</label>
                        <input type="time" class="form-control bg-white-input" id="edit_shift_end" value="18:00">
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group mb-24">
                        <label class="admin-form-label">Grace Time (Mins)</label>
                        <input type="number" class="form-control bg-white-input" id="edit_shift_grace" value="15">
                    </div>
                    <div class="form-group mb-24">
                        <label class="admin-form-label">Halfday (Hours)</label>
                        <input type="number" class="form-control bg-white-input" id="edit_shift_halfday" value="4">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer p-30 border-top-0">
            <button type="submit" form="editShiftForm" class="btn-primary px-30">
                Update Shift <i data-lucide="check" size="18"></i>
            </button>
        </div>
    </div>
</div>

<script>
function openEditShiftModal(name, start, end, grace, halfday) {
    document.getElementById('edit_shift_name').value = name;
    document.getElementById('edit_shift_start').value = start;
    document.getElementById('edit_shift_end').value = end;
    document.getElementById('edit_shift_grace').value = grace;
    document.getElementById('edit_shift_halfday').value = halfday;
    openModal('editShiftModal');
}

document.addEventListener('DOMContentLoaded', function() {
    const editForm = document.getElementById('editShiftForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = editForm.closest('.modal-content').querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i data-lucide="loader-2" class="spin" size="18"></i> Updating...';
            if (typeof lucide !== 'undefined') lucide.createIcons();
            
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                if (typeof lucide !== 'undefined') lucide.createIcons();
                
                closeModal('editShiftModal');
                alert('Shift updated successfully!');
            }, 800);
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>

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
            <tbody id="shiftTableBody">
                <!-- Shifts will be loaded here via JS -->
                <tr>
                    <td colspan="6" class="text-center p-40 text-light">Loading shifts...</td>
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
                    <input type="text" id="shift_name" class="form-control bg-white-input"
                        placeholder="e.g. Standard Morning" required>
                </div>

                <div class="form-grid-2">
                    <div class="form-group mb-24">
                        <label class="admin-form-label">Start Time</label>
                        <input type="time" id="shift_start" class="form-control bg-white-input" required>
                    </div>
                    <div class="form-group mb-24">
                        <label class="admin-form-label">End Time</label>
                        <input type="time" id="shift_end" class="form-control bg-white-input" required>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group mb-24">
                        <label class="admin-form-label">Grace Time (Mins)</label>
                        <input type="number" id="shift_grace" class="form-control bg-white-input" placeholder="e.g. 15">
                    </div>
                    <div class="form-group mb-24">
                        <label class="admin-form-label">Halfday (Hours)</label>
                        <input type="number" id="shift_halfday" class="form-control bg-white-input"
                            placeholder="e.g. 4">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer p-30 border-top-0">
            <button type="submit" form="shiftForm" id="addShiftFormSubmit" class="btn-primary px-30">
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
                <input type="hidden" id="edit_shift_id">
                <div class="form-group mb-24">
                    <label class="admin-form-label">Shift Name</label>
                    <input type="text" class="form-control bg-white-input" id="edit_shift_name" required>
                </div>

                <div class="form-grid-2">
                    <div class="form-group mb-24">
                        <label class="admin-form-label">Start Time</label>
                        <input type="time" class="form-control bg-white-input" id="edit_shift_start" required>
                    </div>
                    <div class="form-group mb-24">
                        <label class="admin-form-label">End Time</label>
                        <input type="time" class="form-control bg-white-input" id="edit_shift_end" required>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group mb-24">
                        <label class="admin-form-label">Grace Time (Mins)</label>
                        <input type="number" class="form-control bg-white-input" id="edit_shift_grace">
                    </div>
                    <div class="form-group mb-24">
                        <label class="admin-form-label">Halfday (Hours)</label>
                        <input type="number" class="form-control bg-white-input" id="edit_shift_halfday">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer p-30 border-top-0">
            <button type="submit" form="editShiftForm" id="editShiftFormSubmit" class="btn-primary px-30">
                Update Shift <i data-lucide="check" size="18"></i>
            </button>
        </div>
    </div>
</div>

<script src="assets/js/shift.js"></script>
<?php include 'includes/footer.php'; ?>
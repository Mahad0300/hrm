<?php
use App\Core\View;
$page_title = "Hierarchy Settings";
$page_subtitle = "Configure CEO, CIO, managers, and org structure.";
include __DIR__ . '/../partials/admin/header.php';
?>
<?php include __DIR__ . '/../partials/admin/sidebar.php'; ?>

<div class="row justify-center hierarchy-settings-page">
    <div class="col-md-10 col-lg-9">
        <div class="card p-0 overflow-hidden mb-24">
            <div class="card-header-v2 p-24 border-bottom flex-between flex-wrap gap-16">
                <div class="flex-center gap-12">
                    <div class="type-icon-box primary">
                        <i data-lucide="network" size="20"></i>
                    </div>
                    <div>
                        <h3 class="font-18 font-700 m-0">Organization Hierarchy</h3>
                        <p class="font-12 text-light m-0">CEO → CIO → Managers → Heads → Staff</p>
                    </div>
                </div>
                <a href="<?= View::to('hierarchy') ?>" class="btn btn-outline btn-sm">
                    <i data-lucide="eye" size="16"></i>
                    <span>View Chart</span>
                </a>
            </div>

            <form id="hierarchySettingsForm" class="p-24">
                <div class="highlight-box bg-primary-light border-primary-light mb-24">
                    <div class="flex-center gap-12">
                        <i data-lucide="info" class="text-primary-color mt-4" size="20"></i>
                        <p class="font-13 text-dark leading-relaxed m-0">
                            Set executive roles here. <strong>Department Heads</strong> and <strong>Staff</strong> are managed from
                            <a href="department-management.php" class="text-primary-color font-600">Dept Management</a> and
                            <a href="employees.php" class="text-primary-color font-600">Employees</a>.
                        </p>
                    </div>
                </div>

                <div class="hier-section">
                    <h4 class="hier-section-title">
                        <span class="badge badge-primary">1</span> Management Department Setting
                    </h4>
                    <div class="form-group mb-0">
                        <label class="admin-form-label" for="managementDeptId">Management Department</label>
                        <select id="managementDeptId" class="form-control bg-white-input">
                            <option value="">Select Department...</option>
                        </select>
                        <small class="text-light font-11 mt-4 block">Select the department that contains managers/executives. Dropdowns on this page will only list employees from this department.</small>
                    </div>
                </div>

                <div class="hier-section">
                    <h4 class="hier-section-title">
                        <span class="badge badge-primary">2</span> CEO
                    </h4>
                    <div class="hier-radio-group">
                        <label class="hier-radio-option">
                            <input type="radio" name="ceo_mode" value="employee" id="ceoModeEmployee">
                            <span>Select from employees</span>
                        </label>
                        <label class="hier-radio-option">
                            <input type="radio" name="ceo_mode" value="manual" id="ceoModeManual" checked>
                            <span>Manual entry (no account)</span>
                        </label>
                    </div>
                    <div id="ceoEmployeeBlock" class="form-group hidden mb-0">
                        <label class="admin-form-label">CEO Employee</label>
                        <select id="ceoEmployeeId" class="form-control bg-white-input">
                            <option value="">Select CEO...</option>
                        </select>
                    </div>
                    <div id="ceoManualBlock" class="hier-fields-2">
                        <div class="form-group mb-0">
                            <label class="admin-form-label">CEO Name *</label>
                            <input type="text" id="ceoManualName" class="form-control bg-white-input" placeholder="Enter CEO full name">
                        </div>
                        <div class="form-group mb-0">
                            <label class="admin-form-label">Title</label>
                            <input type="text" id="ceoManualTitle" class="form-control bg-white-input" value="CEO" placeholder="CEO">
                        </div>
                    </div>
                </div>

                <div class="hier-section">
                    <h4 class="hier-section-title">
                        <span class="badge badge-primary">3</span> CIO
                    </h4>
                    <div class="form-group mb-0">
                        <label class="admin-form-label">CIO Employee</label>
                        <select id="ctoEmployeeId" class="form-control bg-white-input">
                            <option value="">Select CIO (optional)...</option>
                        </select>
                        <small class="text-light font-11 mt-4 block">CIO appears directly under CEO on the org chart.</small>
                    </div>
                </div>

                <div class="hier-section">
                    <div class="hier-section-head">
                        <h4 class="hier-section-title m-0">
                            <span class="badge badge-primary">4</span> Managers
                        </h4>
                        <button type="button" id="addManagerRow" class="btn btn-outline btn-sm">
                            <i data-lucide="plus" size="16"></i>
                            <span>Add Manager</span>
                        </button>
                    </div>
                    <p class="hier-section-desc">Assign which departments each manager oversees. Managers appear under CIO (or CEO if no CIO).</p>
                    <div id="managerRows" class="manager-rows"></div>
                    <p id="noManagersMsg" class="hier-empty-msg hidden">No managers added yet. Click "Add Manager" to assign department managers.</p>
                </div>

                <div class="hier-section hier-section--info">
                    <h4 class="hier-section-title mb-8">
                        <span class="badge badge-secondary">5 & 6</span> Heads & Staff
                    </h4>
                    <p class="hier-section-desc m-0">
                        Set department heads in <a href="department-management.php" class="text-primary-color font-600">Dept Management</a>.
                        Assign employees to departments in <a href="employees.php" class="text-primary-color font-600">Employees</a> — they appear as staff under each head automatically.
                    </p>
                </div>

                <div class="border-top pt-24 text-right">
                    <button type="submit" class="btn btn-primary px-40 btn-premium-lg">
                        <i data-lucide="save" size="18"></i>
                        <span>Save Hierarchy Settings</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= \App\Core\View::asset('js/shared/hierarchy-settings.js?v=1.0.1') ?>"></script>

<?php include __DIR__ . '/../partials/admin/footer.php'; ?>


<?php
$page_title = "Policy Management";
$page_subtitle = "Create company policies with rich text; use the chevron under status on each tile to expand the full content.";
$load_policy_management = true;
include 'includes/header.php';
?>
<?php include 'includes/sidebar.php'; ?>

<div class="page-action-area">
    <button type="button" class="btn-primary" id="policyBtnOpenAdd" data-hr-perm-action="create">
        <i data-lucide="plus"></i>
        <span>Add Policy</span>
    </button>
</div>

<!-- Filters -->
<div class="card p-24 mb-24">
    <div class="filter-grid-3">
        <div class="filter-item">
            <label class="admin-form-label font-12" for="policyFilterSearch">Search</label>
            <div class="search-box w-full">
                <i data-lucide="search" size="16"></i>
                <input type="text" id="policyFilterSearch" class="form-control" placeholder="Search by title...">
            </div>
        </div>
        <div class="filter-item">
            <label class="admin-form-label font-12" for="policyFilterStatus">Status</label>
            <select id="policyFilterStatus" class="form-control">
                <option value="">All statuses</option>
                <option>Draft</option>
                <option>Active</option>
                <option>Archived</option>
            </select>
        </div>
    </div>
</div>

<p class="font-12 text-light mb-15 policy-empty-hint" id="policyEmptyHint" style="display:none;">No policies match your
    filters. <button type="button" class="policy-mgmt-btn-link font-12" id="policyClearFilters">Clear filters</button>
</p>

<div class="policy-tiles-grid" id="policyTilesRoot" aria-live="polite"></div>

<!-- Add Policy Modal -->
<div class="modal-overlay" id="policyAddModal">
    <div class="modal-content wide-md modal-content--policy">
        <div class="modal-header">
            <div>
                <h3 class="mb-4">Add Policy</h3>
                <p class="font-12 text-light">Use the editor for headings, lists, and emphasis. Saved policies appear as
                    tiles below.</p>
            </div>
            <button type="button" class="icon-btn js-modal-close"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body p-30">
            <form id="policyAddForm">
                <div class="form-group mb-20">
                    <label class="admin-form-label" for="policyAddTitle">Policy title</label>
                    <input type="text" id="policyAddTitle" class="form-control bg-white-input"
                        placeholder="e.g. Remote work guidelines" required maxlength="200">
                </div>

                <div class="form-grid-2 mb-20">
                    <div class="form-group">
                        <label class="admin-form-label" for="policyAddStatus">Status</label>
                        <select id="policyAddStatus" class="form-control bg-white-input" required>
                            <option value="Draft">Draft</option>
                            <option value="Active" selected>Active</option>
                            <option value="Archived">Archived</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="admin-form-label" for="policyAddEffective">Effective from</label>
                        <input type="date" id="policyAddEffective" class="form-control bg-white-input"
                            min="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <div class="form-group mb-0">
                    <label class="admin-form-label">Policy content (rich text)</label>
                    <div class="rich-text-container policy-modal-rich">
                        <div class="rich-text-toolbar">
                            <button type="button" class="toolbar-btn" title="Bold"><i data-lucide="bold"></i></button>
                            <button type="button" class="toolbar-btn" title="Italic"><i
                                    data-lucide="italic"></i></button>
                            <button type="button" class="toolbar-btn" title="Underline"><i
                                    data-lucide="underline"></i></button>
                            <div class="toolbar-divider"></div>
                            <button type="button" class="toolbar-btn" title="InsertUnorderedList"><i
                                    data-lucide="list"></i></button>
                            <button type="button" class="toolbar-btn" title="InsertOrderedList"><i
                                    data-lucide="list-ordered"></i></button>
                            <div class="toolbar-divider"></div>
                            <button type="button" class="toolbar-btn" title="CreateLink"><i
                                    data-lucide="link"></i></button>
                        </div>
                        <div id="policyAddRichEditor" class="rich-text-editor" contenteditable="true"
                            data-placeholder="Write your policy details here..."></div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer modal-footer--policy-actions">
            <button type="button" class="btn-ghost js-modal-close">Cancel</button>
            <button type="submit" form="policyAddForm" class="btn-primary" data-hr-perm-action="create">Add Policy</button>
        </div>
    </div>
</div>

<!-- Edit Policy Modal -->
<div class="modal-overlay" id="policyEditModal">
    <div class="modal-content wide-md modal-content--policy">
        <div class="modal-header">
            <div>
                <h3 class="mb-4">Edit Policy</h3>
                <p class="font-12 text-light">Update title, status, effective date, and policy content.</p>
            </div>
            <button type="button" class="icon-btn js-modal-close"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body p-30">
            <form id="policyEditForm">
                <div class="form-group mb-20">
                    <label class="admin-form-label" for="policyEditTitle">Policy title</label>
                    <input type="text" id="policyEditTitle" class="form-control bg-white-input"
                        placeholder="e.g. Remote work guidelines" required maxlength="200">
                </div>

                <div class="form-grid-2 mb-20">
                    <div class="form-group">
                        <label class="admin-form-label" for="policyEditStatus">Status</label>
                        <select id="policyEditStatus" class="form-control bg-white-input" required>
                            <option value="Draft">Draft</option>
                            <option value="Active" selected>Active</option>
                            <option value="Archived">Archived</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="admin-form-label" for="policyEditEffective">Effective from</label>
                        <input type="date" id="policyEditEffective" class="form-control bg-white-input"
                            min="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <div class="form-group mb-0">
                    <label class="admin-form-label">Policy content (rich text)</label>
                    <div class="rich-text-container policy-modal-rich">
                        <div class="rich-text-toolbar">
                            <button type="button" class="toolbar-btn" title="Bold"><i data-lucide="bold"></i></button>
                            <button type="button" class="toolbar-btn" title="Italic"><i
                                    data-lucide="italic"></i></button>
                            <button type="button" class="toolbar-btn" title="Underline"><i
                                    data-lucide="underline"></i></button>
                            <div class="toolbar-divider"></div>
                            <button type="button" class="toolbar-btn" title="InsertUnorderedList"><i
                                    data-lucide="list"></i></button>
                            <button type="button" class="toolbar-btn" title="InsertOrderedList"><i
                                    data-lucide="list-ordered"></i></button>
                            <div class="toolbar-divider"></div>
                            <button type="button" class="toolbar-btn" title="CreateLink"><i
                                    data-lucide="link"></i></button>
                        </div>
                        <div id="policyEditRichEditor" class="rich-text-editor" contenteditable="true"
                            data-placeholder="Write your policy details here..."></div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer modal-footer--policy-actions">
            <button type="button" class="btn-ghost js-modal-close">Cancel</button>
            <button type="submit" form="policyEditForm" class="btn-primary" data-hr-perm-action="edit">Update Policy</button>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
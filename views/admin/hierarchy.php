<?php
$page_title = "Company Hierarchy";
$page_subtitle = "Visualize the organizational structure and reporting lines.";
include __DIR__ . '/../partials/admin/header.php';
?>
<?php include __DIR__ . '/../partials/admin/sidebar.php'; ?>

<style>
    .tree .vertical-stack {
        margin-top: 0 !important;
    }
    .hierarchy-empty-state {
        background: #fff;
        border: 1px dashed #cbd5e1;
        border-radius: 16px;
        padding: 32px 24px;
        max-width: 460px;
        margin: 0 auto;
        text-align: center;
    }
</style>

<div class="hierarchy-page-wrapper">
    <div class="hierarchy-legend" aria-label="Hierarchy role color legend">
        <p class="hierarchy-legend__title">Role Colors</p>
        <ul class="hierarchy-legend__list">
            <li><span class="hierarchy-legend__swatch ceo"></span> CEO</li>
            <li><span class="hierarchy-legend__swatch cto"></span> CIO</li>
            <li><span class="hierarchy-legend__swatch manager"></span> Manager</li>
            <li><span class="hierarchy-legend__swatch head"></span> Head</li>
            <li><span class="hierarchy-legend__swatch employee"></span> Employee</li>
        </ul>
    </div>
    <div class="hierarchy-controls">
        <a href="<?= \App\Core\View::to('hierarchy.settings') ?>" class="control-btn" title="Hierarchy Settings">
            <i data-lucide="settings-2" size="16"></i>
        </a>
        <div class="control-divider"></div>
        <button id="zoomOut" class="control-btn" title="Zoom Out"><i data-lucide="minus"></i></button>
        <button id="zoomReset" class="control-btn" title="Reset Zoom"><i data-lucide="maximize"></i></button>
        <button id="zoomIn" class="control-btn" title="Zoom In"><i data-lucide="plus"></i></button>
        <div class="control-divider"></div>
        <button id="expandAll" class="control-btn" title="Expand All"><i data-lucide="chevrons-down"></i></button>
        <button id="collapseAll" class="control-btn" title="Collapse All"><i data-lucide="chevrons-up"></i></button>
    </div>

    <div class="hierarchy-container" id="hierarchyContainer">
        <div class="hierarchy-canvas" id="hierarchyCanvas">
            <ul class="tree">
                <?php include __DIR__ . '/../partials/shared/hierarchy_tree.php'; ?>
            </ul>
        </div>
    </div>
</div>

<script src="<?= \App\Core\View::asset('js/user/hierarchy.js') ?>"></script>

<?php include __DIR__ . '/../partials/admin/footer.php'; ?>


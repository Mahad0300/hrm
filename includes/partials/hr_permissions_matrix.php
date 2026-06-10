<?php
/**
 * Admin HR permissions matrix rows (capabilities-driven).
 * Expects access_control_helper.php loaded.
 */
$matrixSections = hrPageMatrixSections();
$matrixLabels = hrPageMatrixLabels();
$actionCaps = ['create', 'edit', 'delete', 'export'];

foreach ($matrixSections as $sectionTitle => $pageKeys):
?>
                    <tr class="perm-matrix-section">
                        <td colspan="6" class="perm-matrix-section__cell"><?= htmlspecialchars($sectionTitle) ?></td>
                    </tr>
<?php
    foreach ($pageKeys as $pageKey):
        $meta = $matrixLabels[$pageKey] ?? ['label' => $pageKey, 'icon' => 'file'];
        $isAdminOnly = in_array($pageKey, HR_ADMIN_ONLY_PAGES, true);
        $inheritsView = hrPageInheritsView($pageKey);
        $viewParentKey = $inheritsView ? (hrPagePermissionParent()[$pageKey] ?? null) : null;
        $viewParentLabel = $viewParentKey ? ($matrixLabels[$viewParentKey]['label'] ?? $viewParentKey) : '';
        $rowClass = $isAdminOnly ? 'perm-row-admin-only' : '';
?>
                    <tr data-page="<?= htmlspecialchars($pageKey) ?>" class="<?= $rowClass ?><?= $inheritsView ? ' perm-row-inherited-view' : '' ?>"<?= $inheritsView ? ' data-inherits-view="' . htmlspecialchars($viewParentKey) . '"' : '' ?>>
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="<?= htmlspecialchars($meta['icon']) ?>" size="16"></i>
                                <span>
                                    <?= htmlspecialchars($meta['label']) ?>
                                    <?php if (!empty($meta['badge'])): ?>
                                        <span class="font-11 text-light">(<?= htmlspecialchars($meta['badge']) ?>)</span>
                                    <?php endif; ?>
                                    <?php if ($inheritsView && $viewParentLabel): ?>
                                        <span class="font-11 text-light">(uses <?= htmlspecialchars($viewParentLabel) ?>)</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </td>
                        <td class="text-center">
                            <?php if ($isAdminOnly || $inheritsView): ?>
                                <span class="perm-na-label" title="<?= $inheritsView ? 'View access follows ' . htmlspecialchars($viewParentLabel) : 'Not available' ?>">—</span>
                            <?php else: ?>
                                <label class="switch">
                                    <input type="checkbox" class="view-toggle" checked>
                                    <span class="slider round"></span>
                                </label>
                            <?php endif; ?>
                        </td>
<?php
        foreach ($actionCaps as $col):
            $cap = hrMatrixColumnCapability($pageKey, $col);
            if ($isAdminOnly || $cap === null):
?>
                        <td class="text-center perm-na" data-cap="<?= $col ?>"><span class="perm-na-label" title="Not available on this page">—</span></td>
<?php
            else:
                $capLabel = hrMatrixCapabilityLabel($cap);
?>
                        <td class="text-center">
                            <label class="perm-action-cell">
                                <input type="checkbox" class="action-check" data-cap="<?= htmlspecialchars($cap) ?>" checked title="<?= htmlspecialchars($capLabel) ?>">
                                <?php if (in_array($cap, ['mark_read', 'toggle_status', 'schedule_interview', 'update_pipeline', 'reject_ban'], true)): ?>
                                    <span class="perm-action-cell__text"><?= htmlspecialchars($capLabel) ?></span>
                                <?php endif; ?>
                            </label>
                        </td>
<?php
            endif;
        endforeach;
?>
                    </tr>
<?php
    endforeach;
endforeach;

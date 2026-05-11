<?php 
$page_title = "Company Hierarchy";
$page_subtitle = "Visualize the organizational structure and reporting lines.";
require_once '../includes/db_connect.php';
include 'includes/header.php'; 
?>
<?php include 'includes/sidebar.php'; ?>

<div class="hierarchy-page-wrapper">
    <!-- Overlay Controls -->
    <div class="hierarchy-controls">
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
                <li>
                    <!-- Root Node: CEO (Fixed/Default as per admin side) -->
                    <div class="hier-node root ceo" onclick="toggleNode(this)">
                        <div class="hier-info">
                            <h4>Shayan Siddiqui</h4>
                            <p>CEO</p>
                        </div>
                        <div class="hier-toggle"><i data-lucide="chevron-down"></i></div>
                    </div>

                    <ul>
                        <?php
                        // 1. Fetch All Departments with their Managers and Heads
                        $deptStmt = $pdo->query("
                            SELECT d.id, d.name, d.manager as manager_id, 
                                   CONCAT_WS(' ', em.first_name, NULLIF(em.middle_name, ''), em.last_name) as manager_fullname,
                                   d.head as head_id, 
                                   CONCAT_WS(' ', eh.first_name, NULLIF(eh.middle_name, ''), eh.last_name) as head_fullname
                            FROM departments d
                            LEFT JOIN employees em ON d.manager = em.id
                            LEFT JOIN employees eh ON d.head = eh.id
                            WHERE d.deleted_at IS NULL
                            ORDER BY manager_fullname ASC, d.name ASC
                        ");
                        $all_depts = $deptStmt->fetchAll();

                        // 2. Group Departments by Manager
                        $groupedManagers = [];
                        foreach ($all_depts as $dept) {
                            $m_id = $dept['manager_id'] ?: 'none';
                            if (!isset($groupedManagers[$m_id])) {
                                $groupedManagers[$m_id] = [
                                    'fullname' => $dept['manager_fullname'],
                                    'departments' => [],
                                    'dept_list' => []
                                ];
                            }
                            $groupedManagers[$m_id]['departments'][] = $dept;
                            $groupedManagers[$m_id]['dept_list'][] = $dept['name'];
                        }

                        // 3. Render Groups
                        foreach ($groupedManagers as $m_id => $data) {
                            $hasManager = ($m_id !== 'none');
                            if ($hasManager) { ?>
                                <li>
                                    <div class="hier-node cio" onclick="toggleNode(this)">
                                        <div class="hier-info">
                                            <h4><?php echo htmlspecialchars($data['fullname']); ?></h4>
                                            <p>Manager | <?php echo htmlspecialchars(implode(' | ', array_unique($data['dept_list']))); ?></p>
                                        </div>
                                        <div class="hier-toggle"><i data-lucide="chevron-down"></i></div>
                                    </div>
                                    <ul>
                            <?php }

                            foreach ($data['departments'] as $dept) {
                                if ($dept['head_id']) { ?>
                                    <li>
                                        <div class="hier-node manager" onclick="toggleNode(this)">
                                            <div class="hier-info">
                                                <h4><?php echo htmlspecialchars($dept['head_fullname']); ?></h4>
                                                <p>Head | <?php echo htmlspecialchars($dept['name']); ?></p>
                                            </div>
                                            <div class="hier-toggle"><i data-lucide="chevron-down"></i></div>
                                        </div>

                                        <?php
                                        // Level 3: Staff (Fetch and Display)
                                        $staffStmt = $pdo->prepare("SELECT CONCAT_WS(' ', first_name, NULLIF(middle_name, ''), last_name) as fullname FROM employees WHERE department_id = ? AND id NOT IN (?, ?) AND deleted_at IS NULL");
                                        $staffStmt->execute([$dept['id'], $dept['manager_id'] ?: 0, $dept['head_id']]);
                                        $staff = $staffStmt->fetchAll();

                                        if (!empty($staff)) {
                                            echo '<ul class="vertical-stack">';
                                            foreach ($staff as $s) {
                                                echo '<li><div class="hier-node staff"><div class="hier-info">';
                                                echo '<h4>' . htmlspecialchars($s['fullname']) . '</h4>';
                                                echo '<p>Employee | ' . htmlspecialchars($dept['name']) . '</p>';
                                                echo '</div></div></li>';
                                            }
                                            echo '</ul>';
                                        }
                                        ?>
                                    </li>
                                <?php }
                            }

                            if ($hasManager) echo '</ul></li>';
                        }
                        ?>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>

<script src="assets/js/hierarchy.js"></script>

<?php include 'includes/footer.php'; ?>

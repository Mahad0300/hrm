<?php
$page_title = "Company Hierarchy";
$page_subtitle = "Visualize the organizational structure and reporting lines.";
require_once '../includes/db_connect.php';
include 'includes/header.php';
?>
<?php include 'includes/sidebar.php'; ?>

<style>
    /* Fix for departments with no head to maintain tree alignment */
    .no-head-staff-group {
        padding-top: 20px;
        position: relative;
    }
    .no-head-staff-group::before {
        content: '';
        position: absolute;
        top: 0;
        left: 50%;
        width: 1px;
        height: 20px;
        background: #e2e8f0;
    }
    .tree .vertical-stack {
        margin-top: 0 !important;
    }
</style>

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
                    <!-- Root Node: CEO (Fixed/Default as per request) -->
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
                        SELECT d.id, d.name, 
                               d.manager as manager_id, 
                               CONCAT_WS(' ', em.first_name, NULLIF(em.middle_name, ''), em.last_name) as manager_fullname,
                               em.job_title as m_title,
                               d.head as head_id, 
                               CONCAT_WS(' ', eh.first_name, NULLIF(eh.middle_name, ''), eh.last_name) as head_fullname,
                               eh.job_title as h_title
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
                                    'title' => $dept['m_title'],
                                    'dept_list' => [],
                                    'departments' => []
                                ];
                            }
                            $groupedManagers[$m_id]['departments'][] = $dept;
                            $groupedManagers[$m_id]['dept_list'][] = $dept['name'];
                        }

                        // 3. Render Groups
                                foreach ($groupedManagers as $m_id => $data) {
                                    if ($m_id !== 'none') {
                                        // Level 1: Unique Department Manager (Purple)
                                        $dept_str = implode(' | ', array_unique($data['dept_list']));
                                        ?>
                                        <li>
                                            <div class="hier-node cio" onclick="toggleNode(this)">
                                                <div class="hier-info">
                                                    <h4><?php echo htmlspecialchars($data['fullname']); ?></h4>
                                                    <p>Manager | <?php echo htmlspecialchars($dept_str); ?></p>
                                                </div>
                                                <div class="hier-toggle"><i data-lucide="chevron-down"></i></div>
                                            </div>

                                            <ul>
                                                <?php foreach ($data['departments'] as $dept) {
                                                    // Level 2: Department Head (Green) under this specific manager
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
                                                            // Level 3: Staff (White - Vertical Stack)
                                                            $staffStmt = $pdo->prepare("SELECT CONCAT_WS(' ', first_name, NULLIF(middle_name, ''), last_name) as fullname, job_title FROM employees WHERE department_id = ? AND id NOT IN (?, ?) AND deleted_at IS NULL");
                                                            $staffStmt->execute([$dept['id'], $dept['manager_id'], $dept['head_id']]);
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
                                                    <?php } else {
                                                        // Level 2: Show Staff directly if no Head (under Manager)
                                                        // Use a wrapper li to maintain tree spacing
                                                        $staffStmt = $pdo->prepare("SELECT CONCAT_WS(' ', first_name, NULLIF(middle_name, ''), last_name) as fullname, job_title FROM employees WHERE department_id = ? AND id != ? AND deleted_at IS NULL");
                                                        $staffStmt->execute([$dept['id'], $dept['manager_id']]);
                                                        $staff = $staffStmt->fetchAll();

                                                        if (!empty($staff)) {
                                                            echo '<li class="no-head-staff-group">';
                                                            echo '<ul class="vertical-stack">';
                                                            foreach ($staff as $s) {
                                                                echo '<li><div class="hier-node staff"><div class="hier-info">';
                                                                echo '<h4>' . htmlspecialchars($s['fullname']) . '</h4>';
                                                                echo '<p>Employee | ' . htmlspecialchars($dept['name']) . '</p>';
                                                                echo '</div></div></li>';
                                                            }
                                                            echo '</ul></li>';
                                                        }
                                                    }
                                                } ?>
                                            </ul>
                                        </li>
                                        <?php
                                    } else {
                                        // Handle Orphan Departments (No Manager assigned)
                                        foreach ($data['departments'] as $dept) {
                                            // Exclude 'Manager' department from orphan list (since they are already the managers)
                                            if ($dept['name'] === 'Manager') continue;
                                            
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
                                                    // Level 3: Staff (White - Vertical Stack)
                                                    $staffStmt = $pdo->prepare("SELECT CONCAT_WS(' ', first_name, NULLIF(middle_name, ''), last_name) as fullname, job_title FROM employees WHERE department_id = ? AND id NOT IN (?, ?) AND deleted_at IS NULL");
                                                    $staffStmt->execute([$dept['id'], null, $dept['head_id']]);
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
                                            <?php } else {
                                                // Level 1: Show Staff directly for Orphan Department with no Head
                                                $staffStmt = $pdo->prepare("SELECT CONCAT_WS(' ', first_name, NULLIF(middle_name, ''), last_name) as fullname, job_title FROM employees WHERE department_id = ? AND deleted_at IS NULL");
                                                $staffStmt->execute([$dept['id']]);
                                                $staff = $staffStmt->fetchAll();

                                                if (!empty($staff)) {
                                                    echo '<li><ul class="vertical-stack">';
                                                    foreach ($staff as $s) {
                                                        echo '<li><div class="hier-node staff"><div class="hier-info">';
                                                        echo '<h4>' . htmlspecialchars($s['fullname']) . '</h4>';
                                                        echo '<p>Employee | ' . htmlspecialchars($dept['name']) . '</p>';
                                                        echo '</div></div></li>';
                                                    }
                                                    echo '</ul></li>';
                                                }
                                            }
                                        }
                                    }
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
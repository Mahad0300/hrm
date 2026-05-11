<?php 
$page_title = "Company Hierarchy";
$page_subtitle = "Visualize the organizational structure and reporting lines.";
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
                    <div class="hier-node root ceo" onclick="toggleNode(this)">
                        <div class="hier-info">
                            <h4>Shayan Siddiqui</h4>
                            <p>CEO</p>
                        </div>
                        <div class="hier-toggle"><i data-lucide="chevron-down"></i></div>
                    </div>
                    <ul>
                        <!-- Admin Member 1: Ahsan Uz Zaman -->
                        <li>
                            <div class="hier-node cio" onclick="toggleNode(this)">
                                <div class="hier-info">
                                    <h4>Ahsan Uz Zaman</h4>
                                    <p>Admin Member</p>
                                </div>
                                <div class="hier-toggle"><i data-lucide="chevron-down"></i></div>
                            </div>
                            <ul>
                                <!-- HOD 1: Zain Ul Abidin (Production Head - 3 Team Members) -->
                                <li>
                                    <div class="hier-node manager" onclick="toggleNode(this)">
                                        <div class="hier-info"><h4>Zain Ul Abidin</h4><p>Production Head</p></div>
                                        <div class="hier-toggle"><i data-lucide="chevron-down"></i></div>
                                    </div>
                                    <ul class="vertical-stack">
                                        <li><div class="hier-node staff"><div class="hier-info"><h4>TM 1</h4><p>Staff</p></div></div></li>
                                        <li><div class="hier-node staff"><div class="hier-info"><h4>TM 2</h4><p>Staff</p></div></div></li>
                                        <li><div class="hier-node staff"><div class="hier-info"><h4>TM 3</h4><p>Staff</p></div></div></li>
                                    </ul>
                                </li>
                                <!-- HOD 2: Owais (Marketing Head - 3 Team Members) -->
                                <li>
                                    <div class="hier-node manager" onclick="toggleNode(this)">
                                        <div class="hier-info"><h4>Owais</h4><p>Marketing Head</p></div>
                                        <div class="hier-toggle"><i data-lucide="chevron-down"></i></div>
                                    </div>
                                    <ul class="vertical-stack">
                                        <li><div class="hier-node staff"><div class="hier-info"><h4>TM 1</h4><p>Staff</p></div></div></li>
                                        <li><div class="hier-node staff"><div class="hier-info"><h4>TM 2</h4><p>Staff</p></div></div></li>
                                        <li><div class="hier-node staff"><div class="hier-info"><h4>TM 3</h4><p>Staff</p></div></div></li>
                                    </ul>
                                </li>
                                <!-- HOD 3: Anoushay (Chat Support Head - 1 Member) -->
                                <li>
                                    <div class="hier-node manager" onclick="toggleNode(this)">
                                        <div class="hier-info"><h4>Anoushay</h4><p>Chat Support Head</p></div>
                                        <div class="hier-toggle"><i data-lucide="chevron-down"></i></div>
                                    </div>
                                    <ul class="vertical-stack">
                                        <li><div class="hier-node staff"><div class="hier-info"><h4>TM 1</h4><p>Staff</p></div></div></li>
                                    </ul>
                                </li>
                                <!-- HOD 4: Moiz (Final Expense Sale Head - 1 Member) -->
                                <li>
                                    <div class="hier-node manager" onclick="toggleNode(this)">
                                        <div class="hier-info"><h4>Moiz</h4><p>Final Expense Sale Head</p></div>
                                        <div class="hier-toggle"><i data-lucide="chevron-down"></i></div>
                                    </div>
                                    <ul class="vertical-stack">
                                        <li><div class="hier-node staff"><div class="hier-info"><h4>TM 1</h4><p>Staff</p></div></div></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>

                        <!-- Admin Member 2: Ahmed (under 1 HOD Hadi - 6 team member) -->
                        <li>
                            <div class="hier-node cio" onclick="toggleNode(this)">
                                <div class="hier-info">
                                    <h4>Ahmed</h4>
                                    <p>Admin Member</p>
                                </div>
                                <div class="hier-toggle"><i data-lucide="chevron-down"></i></div>
                            </div>
                            <ul>
                                <li>
                                    <div class="hier-node manager" onclick="toggleNode(this)">
                                        <div class="hier-info"><h4>Hadi</h4><p>HOD</p></div>
                                        <div class="hier-toggle"><i data-lucide="chevron-down"></i></div>
                                    </div>
                                    <ul class="vertical-stack">
                                        <li><div class="hier-node staff"><div class="hier-info"><h4>TM 1</h4><p>Staff</p></div></div></li>
                                        <li><div class="hier-node staff"><div class="hier-info"><h4>TM 2</h4><p>Staff</p></div></div></li>
                                        <li><div class="hier-node staff"><div class="hier-info"><h4>TM 3</h4><p>Staff</p></div></div></li>
                                        <li><div class="hier-node staff"><div class="hier-info"><h4>TM 4</h4><p>Staff</p></div></div></li>
                                        <li><div class="hier-node staff"><div class="hier-info"><h4>TM 5</h4><p>Staff</p></div></div></li>
                                        <li><div class="hier-node staff"><div class="hier-info"><h4>TM 6</h4><p>Staff</p></div></div></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>

                        <!-- Admin Member 3: Wahaj -->
                        <li>
                            <div class="hier-node cio" onclick="toggleNode(this)">
                                <div class="hier-info">
                                    <h4>Wahaj</h4>
                                    <p>Admin Member</p>
                                </div>
                                <div class="hier-toggle"><i data-lucide="chevron-down"></i></div>
                            </div>
                        </li>

                        <!-- Admin Member 4: Ahad Iqbal -->
                        <li>
                            <div class="hier-node cio" onclick="toggleNode(this)">
                                <div class="hier-info">
                                    <h4>Ahad Iqbal</h4>
                                    <p>Admin Member</p>
                                </div>
                                <div class="hier-toggle"><i data-lucide="chevron-down"></i></div>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>

<script>
    const container = document.getElementById('hierarchyContainer');
    const canvas = document.getElementById('hierarchyCanvas');
    let isDown = false;
    let startX, startY, scrollLeft, scrollTop;
    let zoomLevel = 1;

    // Pan Functionality
    container.addEventListener('mousedown', (e) => {
        if (e.target.closest('.hier-node')) return; 
        isDown = true;
        container.style.cursor = 'grabbing';
        startX = e.pageX - container.offsetLeft;
        startY = e.pageY - container.offsetTop;
        scrollLeft = container.scrollLeft;
        scrollTop = container.scrollTop;
    });
    
    container.addEventListener('mouseleave', () => { isDown = false; container.style.cursor = 'grab'; });
    container.addEventListener('mouseup', () => { isDown = false; container.style.cursor = 'grab'; });
    
    container.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - container.offsetLeft;
        const y = e.pageY - container.offsetTop;
        const walkX = (x - startX) * 2;
        const walkY = (y - startY) * 2;
        container.scrollLeft = scrollLeft - walkX;
        container.scrollTop = scrollTop - walkY;
    });

    // Toggle Functionality
    function toggleNode(node) {
        const parentLi = node.parentElement;
        if (!parentLi.querySelector('ul')) return; // If no children, nothing to toggle
        
        parentLi.classList.toggle('collapsed');
        
        // Change icon
        const icon = node.querySelector('.hier-toggle i');
        if (icon) {
            if (parentLi.classList.contains('collapsed')) {
                icon.setAttribute('data-lucide', 'plus-circle');
            } else {
                icon.setAttribute('data-lucide', 'chevron-down');
            }
            lucide.createIcons();
        }
    }

    // Zoom Controls
    document.getElementById('zoomIn').onclick = () => {
        zoomLevel = Math.min(zoomLevel + 0.1, 1.5);
        updateZoom();
    };
    document.getElementById('zoomOut').onclick = () => {
        zoomLevel = Math.max(zoomLevel - 0.1, 0.5);
        updateZoom();
    };
    document.getElementById('zoomReset').onclick = () => {
        zoomLevel = 1;
        updateZoom();
    };
    
    function updateZoom() {
        canvas.style.transform = `scale(${zoomLevel})`;
        canvas.style.transformOrigin = 'top center';
    }

    // Expand/Collapse All
    document.getElementById('expandAll').onclick = () => {
        document.querySelectorAll('.tree li').forEach(li => li.classList.remove('collapsed'));
        document.querySelectorAll('.hier-toggle i').forEach(i => i.setAttribute('data-lucide', 'chevron-down'));
        lucide.createIcons();
    };
    document.getElementById('collapseAll').onclick = () => {
        document.querySelectorAll('.tree li').forEach(li => {
            if (li.querySelector('ul')) li.classList.add('collapsed');
        });
        document.querySelectorAll('.hier-toggle i').forEach(i => i.setAttribute('data-lucide', 'plus-circle'));
        lucide.createIcons();
    };
    
    // Auto-collapse second level on load for better view
    window.onload = () => {
        lucide.createIcons();
    }
</script>

<?php include 'includes/footer.php'; ?>

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('hierarchyContainer');
    const canvas = document.getElementById('hierarchyCanvas');
    let isDown = false;
    let startX, startY, scrollLeft, scrollTop;
    let zoomLevel = 1;

    if (!container || !canvas) return;

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

    container.addEventListener('mouseleave', () => { 
        isDown = false; 
        container.style.cursor = 'grab'; 
    });
    
    container.addEventListener('mouseup', () => { 
        isDown = false; 
        container.style.cursor = 'grab'; 
    });

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

    // Zoom Controls
    const zoomInBtn = document.getElementById('zoomIn');
    const zoomOutBtn = document.getElementById('zoomOut');
    const zoomResetBtn = document.getElementById('zoomReset');

    if (zoomInBtn) {
        zoomInBtn.onclick = () => {
            zoomLevel = Math.min(zoomLevel + 0.1, 1.5);
            updateZoom();
        };
    }

    if (zoomOutBtn) {
        zoomOutBtn.onclick = () => {
            zoomLevel = Math.max(zoomLevel - 0.1, 0.5);
            updateZoom();
        };
    }

    if (zoomResetBtn) {
        zoomResetBtn.onclick = () => {
            zoomLevel = 1;
            updateZoom();
        };
    }

    function updateZoom() {
        canvas.style.transform = `scale(${zoomLevel})`;
        canvas.style.transformOrigin = 'top center';
    }

    // Expand/Collapse All
    const expandAllBtn = document.getElementById('expandAll');
    const collapseAllBtn = document.getElementById('collapseAll');

    if (expandAllBtn) {
        expandAllBtn.onclick = () => {
            document.querySelectorAll('.tree li').forEach(li => li.classList.remove('collapsed'));
            document.querySelectorAll('.hier-toggle i').forEach(i => i.setAttribute('data-lucide', 'chevron-down'));
            if (typeof lucide !== 'undefined') lucide.createIcons();
        };
    }

    if (collapseAllBtn) {
        collapseAllBtn.onclick = () => {
            document.querySelectorAll('.tree li').forEach(li => {
                if (li.querySelector('ul')) li.classList.add('collapsed');
            });
            document.querySelectorAll('.hier-toggle i').forEach(i => i.setAttribute('data-lucide', 'plus-circle'));
            if (typeof lucide !== 'undefined') lucide.createIcons();
        };
    }

    // Initialize Icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});

// Toggle Functionality (Defined globally because of separate onclick implementation in HTML)
function toggleNode(node) {
    const parentLi = node.parentElement;
    if (!parentLi.querySelector('ul')) return; // If no children, nothing to toggle

    parentLi.classList.toggle('collapsed');

    // Change icon
    const icon = node.querySelector('.hier-toggle [data-lucide]');
    if (icon) {
        if (parentLi.classList.contains('collapsed')) {
            icon.setAttribute('data-lucide', 'plus-circle');
        } else {
            icon.setAttribute('data-lucide', 'chevron-down');
        }
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
}

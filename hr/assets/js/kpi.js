/**
 * KPI Management Logic - Persistent Employee-Specific Goals
 */

// Global state for employee goals (mock persistence using localStorage or session)
let employeeGoals = JSON.parse(localStorage.getItem('hrm_employee_kpi_goals')) || {};

let currentEmployee = 'Emma Williams';

document.addEventListener('DOMContentLoaded', function() {
    initKpiStarRating();
    initKpiForm();
    initPeriodSelection();
    
    // Initial setup for the first employee in the table or a default
    const initialEmp = document.querySelector('.emp-profile .name')?.innerText || 'Emma Williams';
    window.renderEmployeeGoals(initialEmp);
});

/**
 * Render Specific KPI Goals based on Employee Name
 */
window.renderEmployeeGoals = function(empName) {
    currentEmployee = empName;
    const container = document.getElementById('dynamicKpiContainer');
    if (!container) return;

    // Load goals for this specific employee
    const goals = employeeGoals[empName] || [];
    container.innerHTML = '';
    
    if (goals.length === 0) {
        container.innerHTML = `
            <div class="col-span-2 py-30 text-center">
                <p class="font-14 text-light">No Goals Defined Yet.</p>
            </div>
        `;
    } else {
        goals.forEach((goal) => {
            createGoalElement(goal, 80); // Default 80% for new review
        });
    }

    // Update modal name
    const modalName = document.getElementById('modalEmpName');
    if (modalName) modalName.innerText = empName;

    if (typeof lucide !== 'undefined') lucide.createIcons();
};

/**
 * Add a custom Goal and persist it for this employee
 */
window.addCustomGoal = function() {
    const input = document.getElementById('customGoalInput');
    const goalTitle = input.value.trim();
    
    if (!goalTitle) {
        alert('Please enter a goal name');
        return;
    }

    // Save to global state
    if (!employeeGoals[currentEmployee]) employeeGoals[currentEmployee] = [];
    
    // Avoid duplicates
    if (!employeeGoals[currentEmployee].includes(goalTitle)) {
        employeeGoals[currentEmployee].push(goalTitle);
        localStorage.setItem('hrm_employee_kpi_goals', JSON.stringify(employeeGoals));
    }

    // If it was the first goal, clear the empty placeholder
    const container = document.getElementById('dynamicKpiContainer');
    if (container.querySelector('.italic')) container.innerHTML = '';

    createGoalElement(goalTitle, 50); // New goals start at 50%
    input.value = ''; // Clear input
    
    showToast(`Goal "${goalTitle}" added for ${currentEmployee}!`, 'success');
};

/**
 * Helper to create a goal input row with feedback
 */
function createGoalElement(title, val) {
    const container = document.getElementById('dynamicKpiContainer');
    const id = 'kpi_' + Math.random().toString(36).substr(2, 9);
    
    const html = `
        <div class="form-group animate-slide-in p-16 mb-8">
            <div class="flex-between mb-12">
                <label class="admin-form-label">${title}</label>
                <span class="badge badge-primary-light font-12 font-700" id="val_${id}">${val}%</span>
            </div>
            <input type="range" class="kpi-range-input mb-16" min="0" max="100" value="${val}"
                oninput="document.getElementById('val_${id}').innerText = this.value + '%'">
            
            <div class="specific-feedback-area mt-4">
                <input type="text" class="form-control font-12 p-10 h-36 bg-white border-dashed" 
                    placeholder="Enter notes for ${title}...">
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', html);
}

/**
 * Simple Toast Utility (if not already defined)
 */
function showToast(msg, type) {
    // Check if custom-toast exists, if not create it
    let toast = document.querySelector('.custom-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.className = 'custom-toast';
        document.body.appendChild(toast);
    }
    
    toast.className = `custom-toast active ${type}`;
    toast.innerHTML = `<div class="toast-content"><i data-lucide="${type === 'success' ? 'check-circle' : 'info'}"></i><span>${msg}</span></div>`;
    
    if (typeof lucide !== 'undefined') lucide.createIcons();
    
    setTimeout(() => {
        toast.classList.remove('active');
    }, 3000);
}

/**
 * Handle view switching
 */
function showKpiDetail(name, dept, score) {
    currentEmployee = name;
    const master = document.getElementById('kpiMasterView');
    const detail = document.getElementById('kpiDetailView');
    
    // Update Detail UI with selected employee data
    document.getElementById('detailName').innerText = name;
    document.getElementById('detailDept').innerText = dept + " Department";
    document.getElementById('detailScore').innerText = score;
    document.getElementById('modalEmpName').innerText = name;
    
    // Update status badge based on score
    const statusBadge = document.getElementById('detailStatus');
    const scoreVal = parseFloat(score);
    if (scoreVal >= 4.5) {
        statusBadge.className = 'badge badge-success';
        statusBadge.innerText = 'Excelling';
    } else if (scoreVal >= 3.5) {
        statusBadge.className = 'badge badge-primary';
        statusBadge.innerText = 'Good';
    } else if (scoreVal >= 2.5) {
        statusBadge.className = 'badge badge-warning';
        statusBadge.innerText = 'On Track';
    } else {
        statusBadge.className = 'badge badge-danger';
        statusBadge.innerText = 'Below Target';
    }

    // Render Dynamic Goals in Scorecard
    renderScorecardGoals(name);

    master.classList.add('hidden');
    detail.classList.remove('hidden');
    
    // Smooth scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/**
 * Render Goals in the Scorecard (Detail View)
 */
function renderScorecardGoals(empName) {
    const container = document.getElementById('detailGoalsContainer');
    if (!container) return;

    const allGoals = employeeGoals[empName] || [];

    container.innerHTML = '';
    
    if (allGoals.length === 0) {
        container.innerHTML = `<p class="font-13 italic text-light text-center py-20">No goals found for this employee.</p>`;
        return;
    }

    allGoals.forEach(goal => {
        const val = Math.floor(Math.random() * 30) + 65; // Random mock value for detail view
        const status = val >= 90 ? 'success' : val >= 75 ? 'primary' : 'warning';
        
        // Mock feedback for the detail view
        const mockFeedback = val >= 90 ? 'Consistently exceeding targets.' : 
                             val >= 75 ? 'Good output, watch for minor details.' : 
                             'Needs more consistency in delivery.';

        const html = `
            <div class="goal-item mb-20">
                <div class="flex-between mb-8">
                    <span class="font-14 font-600">${goal}</span>
                    <span class="font-13 text-primary-color font-600">${val}%</span>
                </div>
                <div class="progress-bar-container h-6 mb-8">
                    <div class="progress-bar ${status}" style="width: ${val}%;"></div>
                </div>
                <p class="font-11 text-light italic">"${mockFeedback}"</p>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
    });
}


function hideKpiDetail() {
    const master = document.getElementById('kpiMasterView');
    const detail = document.getElementById('kpiDetailView');
    
    detail.classList.add('hidden');
    master.classList.remove('hidden');
}

/**
 * Set sentiment and update star UI
 */
window.setSentiment = function(val) {
    const input = document.getElementById('reviewRatingInput');
    const text = document.getElementById('sentimentText');
    const stars = document.querySelectorAll('#starRatingSelect [data-lucide="star"]');
    
    if (input) input.value = val;
    
    // Update Stars
    stars.forEach((star, idx) => {
        if (idx < val) {
            star.classList.remove('empty');
            star.classList.add('filled');
        } else {
            star.classList.remove('filled');
            star.classList.add('empty');
        }
    });

    const feedbacks = {
        1: 'Critical improvement needed.',
        2: 'Below average, needs focus.',
        3: 'Average performance.',
        4: 'Good, exceeding targets.',
        5: 'Exceptional work!'
    };
    if (text) text.innerText = feedbacks[val] || '';
};

/**
 * Period Selection
 */
window.selectPeriod = function(el, label) {
    document.querySelectorAll('.period-card').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
};

/**
 * Placeholder functions for backward compatibility (during DOMContentLoaded)
 */
function initKpiStarRating() {}
function initPeriodSelection() {}

/**
 * Form Submission
 */
function initKpiForm() {
    const form = document.getElementById('addReviewForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Mock success
        const empName = document.getElementById('modalEmpName').innerText;
        alert(`Performance review for ${empName} submitted successfully!`);
        
        // Close modal
        if (typeof closeModal === 'function') {
            closeModal('addReviewModal');
        } else {
            document.getElementById('addReviewModal').classList.remove('active');
        }
    });
}

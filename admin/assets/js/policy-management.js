/**
 * Policy Management — Tiles + Filters + Dynamic Database Persistence.
 */
(function () {
    'use strict';

    var allPolicies = [];
    var editingId = null;

    function plainFromHtml(html) {
        var d = document.createElement('div');
        d.innerHTML = html || '';
        return (d.textContent || '').replace(/\s+/g, '').trim();
    }

    function getAddRichHtml() {
        var el = document.getElementById('policyAddRichEditor');
        return el ? el.innerHTML : '';
    }

    function getEditRichHtml() {
        var el = document.getElementById('policyEditRichEditor');
        return el ? el.innerHTML : '';
    }

    function bindPolicyRichToolbar(modalRoot) {
        if (!modalRoot) return;
        modalRoot.addEventListener('mousedown', function (e) {
            var btn = e.target.closest('.toolbar-btn');
            if (btn && modalRoot.contains(btn)) e.preventDefault();
        });
        modalRoot.addEventListener('click', function (e) {
            var btn = e.target.closest('.toolbar-btn');
            if (!btn || !modalRoot.contains(btn)) return;
            e.preventDefault();
            var editor = modalRoot.querySelector('.rich-text-editor');
            if (editor) editor.focus();
            var command = btn.getAttribute('title');
            document.execCommand(command, false, null);
            btn.classList.toggle('active');
        });
    }

    async function loadPolicies() {
        try {
            const response = await fetch('assets/api/policy_handler.php?action=fetch_policies');
            const result = await response.json();
            if (result.status === 'success') {
                allPolicies = result.data;
                render();
            } else {
                console.error('Failed to load policies:', result.message);
            }
        } catch (e) {
            console.error('Error fetching policies:', e);
        }
    }

    function escapeHtml(s) {
        if (s == null || s === '') return '';
        var d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function statusClass(st) {
        if (st === 'Active') return 'policy-status--active';
        if (st === 'Draft') return 'policy-status--draft';
        return 'policy-status--archived';
    }

    function getFiltered() {
        var list = allPolicies;
        var qEl = document.getElementById('policyFilterSearch');
        var sEl = document.getElementById('policyFilterStatus');
        var q = (qEl && qEl.value ? qEl.value : '').trim().toLowerCase();
        var st = sEl ? sEl.value : '';

        if (q) {
            list = list.filter(function (p) {
                return p.title.toLowerCase().indexOf(q) !== -1;
            });
        }
        if (st) {
            list = list.filter(function (p) {
                return p.status === st;
            });
        }
        return list;
    }

    function render() {
        var root = document.getElementById('policyTilesRoot');
        var emptyHint = document.getElementById('policyEmptyHint');
        if (!root) return;

        var list = getFiltered();
        if (list.length === 0) {
            root.innerHTML = `
                <div class="empty-state-container">
                    <div class="empty-state-icon">
                        <i data-lucide="shield" size="32"></i>
                    </div>
                    <h4 class="empty-state-title">No Policies Found</h4>
                    <p class="empty-state-desc">Try broadening your search or filter parameters.</p>
                </div>
            `;
            if (emptyHint) emptyHint.style.display = 'none'; 
            if (typeof lucide !== 'undefined') lucide.createIcons();
            return;
        }
        if (emptyHint) emptyHint.style.display = 'none';

        root.innerHTML = list
            .map(function (p) {
                var ed = p.effective_date ? escapeHtml(p.effective_date) : '—';
                
                var updatedHtml = '';
                if (p.updated_at && p.updated_at !== '0000-00-00 00:00:00') {
                    var ud = new Date(p.updated_at).toLocaleDateString(undefined, {
                        year: 'numeric', month: 'short', day: 'numeric'
                    });
                    updatedHtml = '<p class="policy-tile__meta policy-tile__meta--with-icon">' +
                        '<i data-lucide="calendar-clock" class="policy-tile__meta-icon" width="14" height="14"></i>' +
                        'Updated <span class="policy-tile__meta-val">' + escapeHtml(ud) + '</span></p>';
                }

                return (
                    '<article class="policy-tile policy-tile--pro" data-id="' + escapeHtml(p.id) + '">' +
                    '<details class="policy-tile__details">' +
                    '<summary class="policy-tile__summary" aria-label="Toggle policy content">' +
                    '<div class="policy-tile__top">' +
                    '<div class="policy-tile__head">' +
                    '<div class="policy-tile__head-main">' +
                    '<h3 class="policy-tile__title">' + escapeHtml(p.title) + '</h3>' +
                    updatedHtml +
                    '</div>' +
                    '<div class="policy-tile__head-aside">' +
                    '<span class="policy-badge policy-badge--status ' + statusClass(p.status) + '">' + escapeHtml(p.status) + '</span>' +
                    '<span class="policy-tile__chev-wrap"><i data-lucide="chevron-down" class="policy-tile__chev" width="18" height="18"></i></span>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '</summary>' +
                    '<div class="policy-tile__body ql-snow"><div class="ql-editor policy-tile__html">' +
                    (p.content || '') +
                    '</div></div>' +
                    '</details>' +
                    '<div class="policy-tile__footer">' +
                    '<p class="policy-tile__footer-effective policy-tile__footer-effective--with-icon">' +
                    '<i data-lucide="calendar" class="policy-tile__meta-icon" width="14" height="14"></i>' +
                    'Effective <span class="policy-tile__meta-val">' + ed + '</span></p>' +
                    '<div class="policy-tile__actions">' +
                    '<button type="button" class="action-btn primary policy-btn-edit" data-id="' + escapeHtml(p.id) + '" title="Edit"><i data-lucide="edit-2" width="14" height="14"></i></button>' +
                    '<button type="button" class="action-btn danger policy-btn-del" data-id="' + escapeHtml(p.id) + '" title="Delete"><i data-lucide="trash-2" width="14" height="14"></i></button>' +
                    '</div>' +
                    '</div>' +
                    '</article>'
                );
            })
            .join('');

        root.querySelectorAll('.policy-btn-edit').forEach(function (btn) {
            btn.addEventListener('click', function () {
                openEdit(btn.getAttribute('data-id'));
            });
        });
        root.querySelectorAll('.policy-btn-del').forEach(function (btn) {
            btn.addEventListener('click', function () {
                removePolicy(btn.getAttribute('data-id'));
            });
        });

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function openAdd() {
        editingId = null;
        var form = document.getElementById('policyAddForm');
        if (form) form.reset();

        var eff = document.getElementById('policyAddEffective');
        if (eff) eff.value = new Date().toISOString().slice(0, 10);

        var st = document.getElementById('policyAddStatus');
        if (st) st.value = 'Active';

        var addEd = document.getElementById('policyAddRichEditor');
        if (addEd) addEd.innerHTML = '';

        if (typeof openModal === 'function') openModal('policyAddModal');
        setTimeout(function () {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }, 0);
    }

    function openEdit(id) {
        var p = allPolicies.find(x => x.id == id);
        if (!p) return;
        editingId = id;

        var titleEl = document.getElementById('policyEditTitle');
        if (titleEl) titleEl.value = p.title || '';

        var stEl = document.getElementById('policyEditStatus');
        if (stEl) stEl.value = p.status || 'Active';

        var eff = document.getElementById('policyEditEffective');
        if (eff) eff.value = p.effective_date || new Date().toISOString().slice(0, 10);

        var editEd = document.getElementById('policyEditRichEditor');
        if (editEd) editEd.innerHTML = p.content || '';

        if (typeof openModal === 'function') openModal('policyEditModal');
        setTimeout(function () {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }, 0);
    }

    async function removePolicy(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This policy will be permanently deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const formData = new FormData();
                    formData.append('action', 'delete_policy');
                    formData.append('id', id);

                    const response = await fetch('assets/api/policy_handler.php', {
                        method: 'POST',
                        body: formData
                    });
                    const resultData = await response.json();
                    if (resultData.status === 'success') {
                        loadPolicies();
                        Swal.fire({
                            title: 'Deleted!',
                            text: resultData.message,
                            icon: 'success',
                            confirmButtonColor: '#6c4cf1'
                        });
                    } else {
                        Swal.fire('Error', resultData.message, 'error');
                    }
                } catch (e) {
                    console.error('Error deleting policy:', e);
                    Swal.fire('Error', 'Something went wrong!', 'error');
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var btnAdd = document.getElementById('policyBtnOpenAdd');
        if (btnAdd) btnAdd.addEventListener('click', openAdd);

        bindPolicyRichToolbar(document.getElementById('policyAddModal'));
        bindPolicyRichToolbar(document.getElementById('policyEditModal'));

        // Form Submissions
        [ {id: 'policyAddForm', editor: getAddRichHtml, modal: 'policyAddModal'},
          {id: 'policyEditForm', editor: getEditRichHtml, modal: 'policyEditModal'}
        ].forEach(config => {
            const form = document.getElementById(config.id);
            if (form) {
                form.addEventListener('submit', async function (e) {
                    e.preventDefault();
                    const prefix = config.id.includes('Add') ? 'policyAdd' : 'policyEdit';
                    const title = document.getElementById(prefix + 'Title').value.trim();
                    const status = document.getElementById(prefix + 'Status').value;
                    const eff = document.getElementById(prefix + 'Effective').value;
                    const content = config.editor();

                    if (!title || !plainFromHtml(content)) {
                        Swal.fire('Warning', 'Please enter title and policy content.', 'warning');
                        return;
                    }

                    const formData = new FormData();
                    formData.append('action', 'save_policy');
                    if (editingId) formData.append('id', editingId);
                    formData.append('title', title);
                    formData.append('status', status);
                    formData.append('effective_date', eff);
                    formData.append('content', content);

                    try {
                        const response = await fetch('assets/api/policy_handler.php', {
                            method: 'POST',
                            body: formData
                        });
                        const result = await response.json();
                        if (result.status === 'success') {
                            if (typeof closeModal === 'function') closeModal(config.modal);
                            loadPolicies();
                            Swal.fire('Success', result.message, 'success');
                        } else {
                            Swal.fire('Error', result.message, 'error');
                        }
                    } catch (err) {
                        console.error('Error saving policy:', err);
                    }
                });
            }
        });

        // Search & Filter
        ['policyFilterSearch', 'policyFilterStatus'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener(id.includes('Search') ? 'input' : 'change', render);
        });

        const clr = document.getElementById('policyClearFilters');
        if (clr) {
            clr.addEventListener('click', function () {
                ['policyFilterSearch', 'policyFilterStatus'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.value = '';
                });
                render();
            });
        }

        loadPolicies();
    });
})();

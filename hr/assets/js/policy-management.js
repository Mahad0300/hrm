/**
 * Policy Management — tiles + filters + announcement-style rich text; localStorage demo persistence.
 */
(function () {
    'use strict';

    var STORAGE_KEY = 'hrm_admin_policies_v3';

    var defaultPolicies = [
        {
            id: 'seed-1',
            title: 'Annual Leave Policy',
            category: 'Leave',
            status: 'Active',
            effectiveDate: '2025-01-01',
            updatedAt: '2026-03-15T10:00:00.000Z',
            bodyHtml:
                '<p>This policy sets out how <strong>annual leave</strong> (paid time off) is accrued, requested, and approved for all employees.</p>' +
                '<p><strong>1. Entitlement</strong></p>' +
                '<ul><li>Full-time employees accrue annual leave in line with local employment law and their contract.</li>' +
                '<li>Part-time and fixed-term employees accrue on a <em>pro-rata</em> basis.</li></ul>' +
                '<p><strong>2. Booking &amp; notice</strong></p>' +
                '<ul><li>Requests must be submitted through the HR / attendance system at least <strong>7 working days</strong> in advance where possible.</li>' +
                '<li>Peak business periods may be subject to blackout dates; your manager will communicate these in advance.</li></ul>' +
                '<p><strong>3. Approval &amp; carry-forward</strong></p>' +
                '<ul><li>Line managers approve leave based on team coverage and business need.</li>' +
                '<li>Unused leave may be carried forward only where permitted by law or company rules; any cap will be communicated by HR.</li></ul>' +
                '<p><strong>4. During notice period</strong></p>' +
                '<p>Annual leave during resignation or redundancy notice is subject to manager and HR approval.</p>'
        },
        {
            id: 'seed-2',
            title: 'Code of Conduct',
            category: 'Conduct',
            status: 'Active',
            effectiveDate: '2025-03-01',
            updatedAt: '2026-03-15T10:00:00.000Z',
            bodyHtml:
                '<p>Our Code of Conduct describes how we work together with <strong>integrity, respect, and professionalism</strong>.</p>' +
                '<p><strong>1. Workplace behaviour</strong></p>' +
                '<p>We expect <em>respectful communication</em>, honest dealings, and collaboration across teams and with clients, suppliers, and partners.</p>' +
                '<p><strong>2. Equal opportunity &amp; inclusion</strong></p>' +
                '<p>Harassment, bullying, and discrimination are <strong>zero tolerance</strong> violations. Report concerns through your manager, HR, or the confidential reporting channel.</p>' +
                '<p><strong>3. Conflicts of interest &amp; confidentiality</strong></p>' +
                '<ul><li>Avoid situations where personal interests conflict with company interests; disclose potential conflicts to HR.</li>' +
                '<li>Protect company and customer confidential information at all times.</li></ul>' +
                '<p><strong>4. Compliance</strong></p>' +
                '<p>Employees must follow applicable laws, regulations, and internal policies. Non-compliance may result in disciplinary action.</p>'
        },
        {
            id: 'seed-3',
            title: 'Remote & Hybrid Work Policy',
            category: 'General',
            status: 'Active',
            effectiveDate: '2025-06-01',
            updatedAt: '2026-03-15T10:00:00.000Z',
            bodyHtml:
                '<p>This policy applies to roles approved for <strong>remote</strong> or <strong>hybrid</strong> working arrangements.</p>' +
                '<p><strong>1. Eligibility</strong></p>' +
                '<p>Arrangements are approved by the line manager and HR based on role requirements, security, and business need.</p>' +
                '<p><strong>2. Availability &amp; communication</strong></p>' +
                '<ul><li>Employees must be reachable during agreed core hours and use approved collaboration tools.</li>' +
                '<li>Attendance for on-site meetings or team days is mandatory when scheduled.</li></ul>' +
                '<p><strong>3. Equipment &amp; security</strong></p>' +
                '<p>Use company-approved devices and VPN where required. Do not store confidential data on personal devices without IT approval.</p>'
        },
        {
            id: 'seed-4',
            title: 'IT & Acceptable Use Policy',
            category: 'IT & Security',
            status: 'Active',
            effectiveDate: '2025-04-01',
            updatedAt: '2026-03-15T10:00:00.000Z',
            bodyHtml:
                '<p>Company IT systems, networks, and accounts must be used for <strong>legitimate business purposes</strong> and in line with security standards.</p>' +
                '<p><strong>1. Acceptable use</strong></p>' +
                '<ul><li>No unlawful activity, harassment, or distribution of malware.</li>' +
                '<li>Limited reasonable personal use is allowed where it does not affect productivity or security.</li></ul>' +
                '<p><strong>2. Passwords &amp; access</strong></p>' +
                '<p>Keep credentials confidential; use multi-factor authentication where enabled. Report suspected incidents to IT immediately.</p>' +
                '<p><strong>3. Monitoring</strong></p>' +
                '<p>The company may monitor systems as permitted by law and policy to protect assets and investigate misuse.</p>'
        },
        {
            id: 'seed-5',
            title: 'Attendance & Punctuality Policy',
            category: 'Attendance',
            status: 'Active',
            effectiveDate: '2025-01-15',
            updatedAt: '2026-03-15T10:00:00.000Z',
            bodyHtml:
                '<p>Regular attendance and punctuality support team delivery and client commitments.</p>' +
                '<p><strong>1. Working hours</strong></p>' +
                '<p>Employees must follow their contracted schedule and clock in / out (or record attendance) as required by local process.</p>' +
                '<p><strong>2. Lateness &amp; absence</strong></p>' +
                '<ul><li>Notify your manager as soon as possible if you will be late or unable to work.</li>' +
                '<li>Unexplained or repeated absence may be addressed under the disciplinary procedure.</li></ul>' +
                '<p><strong>3. Medical &amp; emergency</strong></p>' +
                '<p>Genuine illness and emergencies should be reported according to the leave and sickness guidelines from HR.</p>'
        }
    ];

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
        if (!modalRoot) {
            return;
        }
        modalRoot.addEventListener('mousedown', function (e) {
            var btn = e.target.closest('.toolbar-btn');
            if (btn && modalRoot.contains(btn)) {
                e.preventDefault();
            }
        });
        modalRoot.addEventListener('click', function (e) {
            var btn = e.target.closest('.toolbar-btn');
            if (!btn || !modalRoot.contains(btn)) {
                return;
            }
            e.preventDefault();
            var editor = modalRoot.querySelector('.rich-text-editor');
            if (editor) {
                editor.focus();
            }
            var command = btn.getAttribute('title');
            document.execCommand(command, false, null);
            btn.classList.toggle('active');
        });
    }

    function loadPolicies() {
        try {
            var raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) {
                return defaultPolicies.slice();
            }
            var parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : defaultPolicies.slice();
        } catch (e) {
            return defaultPolicies.slice();
        }
    }

    function savePolicies(list) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(list));
        } catch (e) {}
    }

    function escapeHtml(s) {
        if (s == null || s === '') {
            return '';
        }
        var d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function statusClass(st) {
        if (st === 'Active') {
            return 'policy-status--active';
        }
        if (st === 'Draft') {
            return 'policy-status--draft';
        }
        return 'policy-status--archived';
    }

    function getFiltered() {
        var list = loadPolicies();
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
        if (!root) {
            return;
        }

        var list = getFiltered();
        if (list.length === 0) {
            root.innerHTML = '';
            if (emptyHint) {
                emptyHint.style.display = 'block';
            }
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
            return;
        }
        if (emptyHint) {
            emptyHint.style.display = 'none';
        }

        root.innerHTML = list
            .map(function (p) {
                var ed = p.effectiveDate ? escapeHtml(p.effectiveDate) : '—';
                var ud = p.updatedAt
                    ? new Date(p.updatedAt).toLocaleDateString(undefined, {
                          year: 'numeric',
                          month: 'short',
                          day: 'numeric'
                      })
                    : '—';
                return (
                    '<article class="policy-tile policy-tile--pro" data-id="' +
                    escapeHtml(p.id) +
                    '">' +
                    '<details class="policy-tile__details">' +
                    '<summary class="policy-tile__summary" aria-label="Toggle policy content">' +
                    '<div class="policy-tile__top">' +
                    '<div class="policy-tile__head">' +
                    '<div class="policy-tile__head-main">' +
                    '<h3 class="policy-tile__title">' +
                    escapeHtml(p.title) +
                    '</h3>' +
                    '<p class="policy-tile__meta policy-tile__meta--with-icon">' +
                    '<i data-lucide="calendar-clock" class="policy-tile__meta-icon" width="14" height="14" aria-hidden="true"></i>' +
                    'Updated <span class="policy-tile__meta-val">' +
                    escapeHtml(ud) +
                    '</span></p>' +
                    '</div>' +
                    '<div class="policy-tile__head-aside">' +
                    '<span class="policy-badge policy-badge--status ' +
                    statusClass(p.status) +
                    '">' +
                    escapeHtml(p.status) +
                    '</span>' +
                    '<span class="policy-tile__chev-wrap"><i data-lucide="chevron-down" class="policy-tile__chev" width="18" height="18" aria-hidden="true"></i></span>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '</summary>' +
                    '<div class="policy-tile__body ql-snow"><div class="ql-editor policy-tile__html">' +
                    (p.bodyHtml || '') +
                    '</div></div>' +
                    '</details>' +
                    '<div class="policy-tile__footer">' +
                    '<p class="policy-tile__footer-effective policy-tile__footer-effective--with-icon">' +
                    '<i data-lucide="calendar" class="policy-tile__meta-icon" width="14" height="14" aria-hidden="true"></i>' +
                    'Effective <span class="policy-tile__meta-val">' +
                    ed +
                    '</span></p>' +
                    '<div class="policy-tile__actions">' +
                    '<button type="button" class="action-btn primary policy-btn-edit" data-id="' +
                    escapeHtml(p.id) +
                    '" title="Edit"><i data-lucide="edit-2" width="14" height="14"></i></button>' +
                    '<button type="button" class="action-btn danger policy-btn-del" data-id="' +
                    escapeHtml(p.id) +
                    '" title="Delete"><i data-lucide="trash-2" width="14" height="14"></i></button>' +
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

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    function openAdd() {
        editingId = null;
        var form = document.getElementById('policyAddForm');
        if (form) {
            form.reset();
        }

        var eff = document.getElementById('policyAddEffective');
        if (eff) {
            eff.value = new Date().toISOString().slice(0, 10);
        }

        var st = document.getElementById('policyAddStatus');
        if (st) {
            st.value = 'Active';
        }

        var addEd = document.getElementById('policyAddRichEditor');
        if (addEd) {
            addEd.innerHTML = '';
        }

        if (typeof openModal === 'function') {
            openModal('policyAddModal');
        }
        setTimeout(function () {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }, 0);
    }

    function openEdit(id) {
        var list = loadPolicies();
        var p = null;
        for (var i = 0; i < list.length; i++) {
            if (list[i].id === id) {
                p = list[i];
                break;
            }
        }
        if (!p) {
            return;
        }
        editingId = id;

        var titleEl = document.getElementById('policyEditTitle');
        if (titleEl) {
            titleEl.value = p.title || '';
        }

        var stEl = document.getElementById('policyEditStatus');
        if (stEl) {
            stEl.value = p.status || 'Active';
        }

        var eff = document.getElementById('policyEditEffective');
        if (eff) {
            eff.value = p.effectiveDate || new Date().toISOString().slice(0, 10);
        }

        var editEd = document.getElementById('policyEditRichEditor');
        if (editEd) {
            editEd.innerHTML = p.bodyHtml || '';
        }

        if (typeof openModal === 'function') {
            openModal('policyEditModal');
        }
        setTimeout(function () {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }, 0);
    }

    function removePolicy(id) {
        if (!confirm('Delete this policy?')) {
            return;
        }
        var list = loadPolicies().filter(function (x) {
            return x.id !== id;
        });
        savePolicies(list);
        render();
    }

    document.addEventListener('DOMContentLoaded', function () {
        var btnAdd = document.getElementById('policyBtnOpenAdd');
        if (btnAdd) {
            btnAdd.addEventListener('click', openAdd);
        }

        bindPolicyRichToolbar(document.getElementById('policyAddModal'));
        bindPolicyRichToolbar(document.getElementById('policyEditModal'));

        var addForm = document.getElementById('policyAddForm');
        if (addForm) {
            addForm.addEventListener('submit', function (e) {
                e.preventDefault();
                var title = document.getElementById('policyAddTitle');
                var status = document.getElementById('policyAddStatus');
                var eff = document.getElementById('policyAddEffective');

                var titleVal = title && title.value ? title.value.trim() : '';
                var stVal = status ? status.value : 'Active';
                var effVal = eff && eff.value ? eff.value : new Date().toISOString().slice(0, 10);
                var bodyHtml = getAddRichHtml();
                var plain = plainFromHtml(bodyHtml);

                if (!titleVal || !plain) {
                    alert('Please enter title and policy content.');
                    return;
                }

                var list = loadPolicies();
                var now = new Date().toISOString();
                list.push({
                    id: 'p-' + Date.now(),
                    title: titleVal,
                    category: 'General',
                    status: stVal,
                    effectiveDate: effVal,
                    bodyHtml: bodyHtml,
                    updatedAt: now
                });

                savePolicies(list);
                if (typeof closeModal === 'function') {
                    closeModal('policyAddModal');
                }
                render();
            });
        }

        var editForm = document.getElementById('policyEditForm');
        if (editForm) {
            editForm.addEventListener('submit', function (e) {
                e.preventDefault();

                var title = document.getElementById('policyEditTitle');
                var status = document.getElementById('policyEditStatus');
                var eff = document.getElementById('policyEditEffective');

                var titleVal = title && title.value ? title.value.trim() : '';
                var stVal = status ? status.value : 'Active';
                var effVal = eff && eff.value ? eff.value : new Date().toISOString().slice(0, 10);
                var bodyHtml = getEditRichHtml();
                var plain = plainFromHtml(bodyHtml);

                if (!titleVal || !plain) {
                    alert('Please enter title and policy content.');
                    return;
                }

                var list = loadPolicies();
                var now = new Date().toISOString();
                var oldCategory = 'General';
                for (var i = 0; i < list.length; i++) {
                    if (list[i].id === editingId) {
                        oldCategory = list[i].category || 'General';
                        break;
                    }
                }

                list = list.map(function (x) {
                    if (x.id !== editingId) {
                        return x;
                    }
                    return {
                        id: x.id,
                        title: titleVal,
                        category: oldCategory,
                        status: stVal,
                        effectiveDate: effVal,
                        bodyHtml: bodyHtml,
                        updatedAt: now
                    };
                });

                savePolicies(list);
                if (typeof closeModal === 'function') {
                    closeModal('policyEditModal');
                }
                render();
            });
        }

        var fs = document.getElementById('policyFilterSearch');
        var fst = document.getElementById('policyFilterStatus');
        if (fs) {
            fs.addEventListener('input', render);
        }
        if (fst) {
            fst.addEventListener('change', render);
        }
        var clr = document.getElementById('policyClearFilters');
        if (clr) {
            clr.addEventListener('click', function () {
                if (fs) {
                    fs.value = '';
                }
                if (fst) {
                    fst.value = '';
                }
                render();
            });
        }

        render();
    });
})();

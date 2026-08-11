/**
 * User portal — company policies (read-only). Same storage key as admin for demo sync.
 */
(function () {
    'use strict';    let allPolicies = [];

    function slugify(text) {
        if (!text) return '';
        return text
            .toString()
            .toLowerCase()
            .trim()
            .replace(/\s+/g, '-')
            .replace(/[^\w\-]+/g, '')
            .replace(/\-\-+/g, '-')
            .replace(/^-+/, '')
            .replace(/-+$/, '');
    }

    function fetchPolicies() {
        const root = document.getElementById('policyTilesRoot');
        const detailRoot = document.getElementById('policyDetailContent');
        
        fetch('/assets/api/policy_handler.php?action=fetch_policies')
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    allPolicies = res.data;
                    if (root) renderList();
                    if (detailRoot) renderDetail();
                } else {
                    console.error('Policy Fetch Error:', res.message);
                }
            })
            .catch(err => {
                console.error('API Error:', err);
            });
    }

    const escapeHtml = window.HRM_UTILS.escapeHtml;

    function escapeAttr(s) {
        if (s == null || s === '') return '';
        return escapeHtml(String(s)).replace(/"/g, '&quot;');
    }

    function formatPolicyDate(value) {
        if (!value) return '—';
        const date = new Date(String(value) + 'T00:00:00');
        if (Number.isNaN(date.getTime())) return value;
        return date.toLocaleDateString('en-US', {
            month: 'long',
            day: 'numeric',
            year: 'numeric'
        });
    }

    function statusClass(st) {
        if (st === 'Active') return 'policy-status--active';
        if (st === 'Draft') return 'policy-status--draft';
        return 'policy-status--archived';
    }

    function renderList() {
        const root = document.getElementById('policyTilesRoot');
        const emptyHint = document.getElementById('policyUserEmptyHint');
        if (!root) return;

        if (allPolicies.length === 0) {
            root.innerHTML = `
                <div class="empty-state-container">
                    <div class="empty-state-icon">
                        <i data-lucide="shield" size="32"></i>
                    </div>
                    <h4 class="empty-state-title">No Policies Found</h4>
                    <p class="empty-state-desc">No active company policies are available right now.</p>
                </div>
            `;
            if (emptyHint) emptyHint.style.display = 'none';
            if (typeof lucide !== 'undefined') lucide.createIcons();
            return;
        }
        
        if (emptyHint) emptyHint.style.display = 'none';

        root.innerHTML = allPolicies.map(p => {
            const ed = escapeHtml(formatPolicyDate(p.effective_date));
            
            let updatedHtml = '';
            if (p.updated_at && p.updated_at !== '0000-00-00 00:00:00') {
                const ud = new Date(p.updated_at).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
                updatedHtml = `
                    <span class="policy-tile-meta-item">
                        <i data-lucide="calendar-clock" class="policy-tile-meta-icon" width="14" height="14"></i>
                        <span class="policy-tile__meta">Updated <span class="policy-tile__meta-val">${escapeHtml(ud)}</span></span>
                    </span>
                    <span class="policy-tile-meta-sep">·</span>
                `;
            }

            const slug = slugify(p.title) || p.id;
            const detailHref = window.HRM.url('policies/detail/' + encodeURIComponent(slug));
            
            return `
                <a href="${escapeHtml(detailHref)}" class="policy-tile policy-tile--pro policy-tile--user policy-tile--clickable" data-id="${escapeHtml(p.id)}">
                    <div class="policy-tile__top">
                        <div class="policy-tile__head">
                            <div class="policy-tile__head-main">
                                <h3 class="policy-tile__title">
                                    <span class="policy-tile__title-text">${escapeHtml(p.title)}</span>
                                </h3>
                                <div class="policy-tile-meta-row">
                                    ${updatedHtml}
                                    <span class="policy-tile-meta-item">
                                        <i data-lucide="calendar" class="policy-tile-meta-icon" width="14" height="14"></i>
                                        <span class="policy-tile__meta">Effective <span class="policy-tile__meta-val">${ed}</span></span>
                                    </span>
                                </div>
                            </div>
                            <div class="policy-tile__head-aside policy-tile__head-aside--badge-only">
                                <span class="policy-badge policy-badge--status ${statusClass(p.status)}">${escapeHtml(p.status)}</span>
                            </div>
                        </div>
                    </div>
                </a>
            `;
        }).join('');

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function renderDetail() {
        const root = document.getElementById('policyDetailContent');
        const missing = document.getElementById('policyDetailMissing');
        const loading = document.getElementById('policyDetailLoading');
        if (!root) return;

        const pathParts = window.location.pathname.split('/');
        const lastPart = decodeURIComponent(pathParts[pathParts.length - 1]);
        const id = (!isNaN(lastPart) && lastPart !== '') ? lastPart : new URLSearchParams(window.location.search).get('id');

        if (loading) loading.style.display = 'none';

        let p = allPolicies.find(item => String(item.id) === String(id));
        if (!p && lastPart) {
            p = allPolicies.find(item => slugify(item.title) === lastPart);
        }
        if (!p) {
            if (missing) missing.style.display = 'block';
            root.style.display = 'none';
            return;
        }

        if (missing) missing.style.display = 'none';
        root.style.display = 'block';

        const ed = escapeHtml(formatPolicyDate(p.effective_date));

        let updatedHtml = '';
        if (p.updated_at && p.updated_at !== '0000-00-00 00:00:00') {
            const ud = new Date(p.updated_at).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
            updatedHtml = `
                <span class="policy-detail-meta-item">
                    <i data-lucide="calendar-clock" class="policy-detail-meta-icon" width="16" height="16"></i>
                    <span class="policy-detail-meta">Updated <span class="policy-tile__meta-val">${escapeHtml(ud)}</span></span>
                </span>
                <span class="policy-detail-meta-sep">·</span>
            `;
        }

        root.innerHTML = `
            <header class="policy-detail-head">
                <div class="policy-detail-head-main">
                    <h1 class="policy-detail-title">${escapeHtml(p.title)}</h1>
                    <div class="policy-detail-meta-row">
                        ${updatedHtml}
                        <span class="policy-detail-meta-item">
                            <i data-lucide="calendar" class="policy-detail-meta-icon" width="16" height="16"></i>
                            <span class="policy-detail-effective-inline">Effective from <strong>${ed}</strong></span>
                        </span>
                    </div>
                </div>
                <span class="policy-badge policy-badge--status ${statusClass(p.status)}">${escapeHtml(p.status)}</span>
            </header>
            <div class="policy-detail-body ql-snow">
                <div class="ql-editor policy-tile__html policy-detail-prose">
                    ${p.content || ''}
                </div>
            </div>
        `;

        if (p.title) document.title = p.title + ' | HRM';

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    document.addEventListener('DOMContentLoaded', fetchPolicies);
    window.refreshPolicies = fetchPolicies;
})();

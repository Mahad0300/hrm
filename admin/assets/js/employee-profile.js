(function () {
    'use strict';

    // Static demo taken counts for this profile
    var taken = {
        sick: 3,
        casual: 2,
        annual: 6
    };

    var QUOTA_KEY = 'hrm_admin_leave_quotas_v1';
    var defaultQuotas = { sick: 10, casual: 8, annual: 20 };

    function getQuotas() {
        try {
            var raw = localStorage.getItem(QUOTA_KEY);
            if (!raw) return Object.assign({}, defaultQuotas);
            var o = JSON.parse(raw);
            return {
                sick: Math.max(0, parseInt(o.sick, 10) || defaultQuotas.sick),
                casual: Math.max(0, parseInt(o.casual, 10) || defaultQuotas.casual),
                annual: Math.max(0, parseInt(o.annual, 10) || defaultQuotas.annual)
            };
        } catch (e) {
            return Object.assign({}, defaultQuotas);
        }
    }

    function setText(id, v) {
        var el = document.getElementById(id);
        if (el) el.textContent = String(v);
    }

    document.addEventListener('DOMContentLoaded', function () {
        var q = getQuotas();
        var rem = {
            sick: Math.max(0, q.sick - taken.sick),
            casual: Math.max(0, q.casual - taken.casual),
            annual: Math.max(0, q.annual - taken.annual)
        };

        setText('empLeaveSickUsed', taken.sick);
        setText('empLeaveSickRemaining', rem.sick);
        setText('empLeaveCasualUsed', taken.casual);
        setText('empLeaveCasualRemaining', rem.casual);
        setText('empLeaveAnnualUsed', taken.annual);
        setText('empLeaveAnnualRemaining', rem.annual);
    });
})();


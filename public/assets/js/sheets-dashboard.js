/* Google Sheets Dashboard Localized Logic */

const getApiUrl = (endpoint) => {
  if (window.HRM && typeof window.HRM.api === 'function') {
    return window.HRM.api(endpoint);
  }
  const base = typeof window !== 'undefined' && window.APP_BASE_URL !== undefined ? window.APP_BASE_URL : '';
  return base.replace(/\/+$/, '') + '/assets/api/' + endpoint.replace(/^\//, '');
};

const getBaseUrl = () => typeof window !== 'undefined' && window.APP_BASE_URL !== undefined ? window.APP_BASE_URL : '';

let allUserSheets = [];
let activeTab = 'all';

document.addEventListener('DOMContentLoaded', () => {
  // Initial load
  loadSheets();
  loadAccessRequests();

  // Tab switching
  const tabs = document.querySelectorAll('.dash-tab-btn');
  tabs.forEach(tab => {
    tab.addEventListener('click', (e) => {
      tabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      activeTab = tab.getAttribute('data-tab');
      
      const gridSection = document.getElementById('sheetsGridSection');
      const requestSection = document.getElementById('requestsSection');
      const startSection = document.getElementById('startSection');

      if (activeTab === 'requests') {
        gridSection.style.display = 'none';
        startSection.style.display = 'none';
        requestSection.style.display = 'block';
        loadAccessRequests();
      } else {
        gridSection.style.display = 'block';
        startSection.style.display = (activeTab === 'all') ? 'block' : 'none';
        requestSection.style.display = 'none';
        filterSheets();
      }
    });
  });

  // Search input filtering
  const searchInput = document.getElementById('searchInput');
  if (searchInput) {
    searchInput.addEventListener('input', (e) => {
      const q = e.target.value.toLowerCase();
      const cards = document.querySelectorAll('.sheet-card');
      cards.forEach(card => {
        const name = card.querySelector('.sheet-name').textContent.toLowerCase();
        if (name.includes(q)) {
          card.style.display = 'block';
        } else {
          card.style.display = 'none';
        }
      });
    });
  }
});

function loadSheets() {
  const grid = document.getElementById('sheetsGrid');
  if (!grid) return;

  grid.innerHTML = '<div style="grid-column:1/-1; text-align:center; padding:20px; color:#5f6368;">Loading spreadsheets...</div>';

  fetch(getApiUrl('sheets_handler.php?action=list'))
    .then(r => r.json())
    .then(res => {
      if (res.status === 'success') {
        allUserSheets = res.spreadsheets || [];
        filterSheets();
      } else {
        grid.innerHTML = `<div style="grid-column:1/-1; text-align:center; color:#d93025;">Error: ${res.message}</div>`;
      }
    })
    .catch(err => {
      console.error(err);
      grid.innerHTML = '<div style="grid-column:1/-1; text-align:center; color:#d93025;">Failed to connect to sheets server.</div>';
    });
}

function filterSheets() {
  let filtered = allUserSheets;

  if (activeTab === 'mine') {
    filtered = allUserSheets.filter(s => s.is_owner == 1 || s.is_owner === true);
  } else if (activeTab === 'shared') {
    filtered = allUserSheets.filter(s => s.is_owner == 0 || s.is_owner === false);
  }

  renderSheetsGrid(filtered);
}

function renderSheetsGrid(sheets) {
  const grid = document.getElementById('sheetsGrid');
  if (!grid) return;
  grid.innerHTML = '';

  if (sheets.length === 0) {
    grid.innerHTML = `
      <div style="grid-column:1/-1; text-align:center; padding:40px; color:#5f6368;">
        No spreadsheets found in this section.
      </div>
    `;
    return;
  }

  sheets.forEach(sheet => {
    const card = document.createElement('div');
    card.className = 'sheet-card';
    card.onclick = () => window.open(getBaseUrl() + '/sheets/editor?id=' + sheet.id, '_blank');

    // Format date nicely
    const dateObj = new Date(sheet.updated_at || sheet.created_at);
    const dateStr = dateObj.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });

    card.innerHTML = `
      <div class="sheet-thumb">
        <div class="sheet-thumb-svg" style="display:flex; flex-direction:column; padding:12px; gap:4px; width:100%; height:100%;">
          <div style="height:12px; background:#e8f0fe; border-radius:2px; width:70%;"></div>
          <div style="height:10px; background:#f1f3f4; border-radius:2px;"></div>
          <div style="height:10px; background:#f1f3f4; border-radius:2px; width:85%;"></div>
          <div style="height:10px; background:#f1f3f4; border-radius:2px; width:50%;"></div>
        </div>
      </div>
      <div class="sheet-details">
        <div class="sheet-info">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="#0f9d58"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z"/><path d="M7 7h10v2H7zm0 4h10v2H7zm0 4h7v2H7z"/></svg>
          <div>
            <div class="sheet-name" title="${sheet.title || 'Untitled spreadsheet'}">${sheet.title || 'Untitled spreadsheet'}</div>
            <div class="sheet-meta" style="font-size: 11px; color: #5f6368;">Owner: ${sheet.ownerName} • ${dateStr}</div>
          </div>
        </div>
        ${sheet.is_owner ? `<span class="material-symbols-outlined" style="font-size:18px; color:#5f6368; cursor:pointer;" onclick="event.stopPropagation(); deleteSpreadsheet('${sheet.id}')" title="Delete sheet">delete</span>` : ''}
      </div>
    `;
    grid.appendChild(card);
  });
}

function createNewSpreadsheet() {
  const formData = new FormData();
  formData.append('title', 'Untitled spreadsheet');

  fetch(getApiUrl('sheets_handler.php?action=create'), {
    method: 'POST',
    body: formData
  })
    .then(r => r.json())
    .then(res => {
      if (res.status === 'success' && res.id) {
        window.open(getBaseUrl() + '/sheets/editor?id=' + res.id, '_blank');
        loadSheets();
      } else {
        alert('Failed to create spreadsheet: ' + (res.message || 'Unknown error'));
      }
    })
    .catch(err => {
      console.error(err);
      alert('Error creating spreadsheet. Please check connection.');
    });
}

function deleteSpreadsheet(id) {
  if (!confirm('Are you sure you want to delete this spreadsheet?')) return;

  const formData = new FormData();
  formData.append('id', id);

  fetch(getApiUrl('sheets_handler.php?action=delete'), {
    method: 'POST',
    body: formData
  })
    .then(r => r.json())
    .then(res => {
      if (res.status === 'success') {
        loadSheets();
      } else {
        alert('Failed to delete spreadsheet: ' + res.message);
      }
    })
    .catch(err => {
      console.error(err);
      alert('Error connecting to backend.');
    });
}

function loadAccessRequests() {
  const container = document.getElementById('requestsContainer');
  if (!container) return;

  container.innerHTML = '<div style="padding:20px; text-align:center; color:#5f6368;">Loading requests...</div>';

  fetch(getApiUrl('sheets_handler.php?action=list_requests'))
    .then(r => r.json())
    .then(res => {
      if (res.status === 'success') {
        renderAccessRequests(res.requests || []);
      } else {
        container.innerHTML = `<div style="padding:20px; text-align:center; color:#d93025;">Error: ${res.message}</div>`;
      }
    })
    .catch(err => {
      console.error(err);
      container.innerHTML = '<div style="padding:20px; text-align:center; color:#d93025;">Failed to load access requests.</div>';
    });
}

function renderAccessRequests(requests) {
  const container = document.getElementById('requestsContainer');
  if (!container) return;

  container.innerHTML = '';

  if (requests.length === 0) {
    container.innerHTML = `
      <div style="text-align:center; padding:48px 24px; color:#5f6368; background:#f8f9fa; border-radius:12px; border:1px solid #e1e3e6; font-family: inherit;">
        <span class="material-symbols-outlined" style="font-size:54px; color:#bdc1c6; margin-bottom:12px;">lock_person</span>
        <div style="font-weight:600; font-size:15px; color:#202124; margin-bottom:4px;">No Pending Access Requests</div>
        <div style="font-size:13px; color:#5f6368;">All collaborator access requests for your spreadsheets will appear here.</div>
      </div>
    `;
    return;
  }

  requests.forEach(req => {
    const card = document.createElement('div');
    card.style.cssText = `
      display: flex; 
      align-items: center; 
      justify-content: space-between; 
      background: #ffffff; 
      padding: 16px 20px; 
      border-radius: 10px; 
      border: 1px solid #e1e3e6; 
      margin-bottom: 12px;
      transition: all 0.2s ease;
    `;

    card.onmouseenter = () => {
      card.style.borderColor = '#c0c4c9';
      card.style.boxShadow = '0 2px 8px rgba(0,0,0,0.04)';
    };
    card.onmouseleave = () => {
      card.style.borderColor = '#e1e3e6';
      card.style.boxShadow = 'none';
    };

    const dateObj = new Date(req.created_at);
    const dateStr = dateObj.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
    
    const defaultAvatarPath = getBaseUrl() + '/assets/images/profile-image/default-avatar.svg';
    const avatarUrl = req.profile_pic ? (getBaseUrl() + '/' + req.profile_pic.replace(/^\//, '')) : defaultAvatarPath;

    const isEditor = req.requested_role === 'edit' || req.requested_level === 'edit';
    const roleBadgeBg = isEditor ? '#e6f4ea' : '#e8f0fe';
    const roleBadgeText = isEditor ? '#137333' : '#1a73e8';

    card.innerHTML = `
      <div style="display: flex; align-items: center; gap: 14px; flex: 1; padding-right: 16px;">
        <!-- User Profile Avatar -->
        <img src="${avatarUrl}" alt="${req.requestor_name}" style="width: 42px; height: 42px; border-radius: 50%; object-fit: cover; flex-shrink: 0; background: #f1f3f4;" onerror="this.src='${defaultAvatarPath}'">

        <div style="flex: 1;">
          <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span style="font-weight: 600; font-size: 14px; color: #202124;">${req.requestor_name}</span>
            <span style="font-size: 13px; color: #5f6368;">(${req.requestor_email})</span>
            <span style="background: ${roleBadgeBg}; color: ${roleBadgeText}; font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 10px; display: inline-flex; align-items: center; gap: 4px;">
              <span class="material-symbols-outlined" style="font-size: 13px;">${isEditor ? 'edit' : 'visibility'}</span>
              <span>${isEditor ? 'Editor' : 'Viewer'}</span>
            </span>
          </div>

          <div style="font-size: 13px; color: #3c4043; margin-top: 4px; display: flex; align-items: center; gap: 6px;">
            <span>Requested access on:</span>
            <a href="${getBaseUrl()}/sheets/editor?id=${req.spreadsheet_id}" style="color: #0f9d58; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="#0f9d58"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z"/><path d="M7 7h10v2H7zm0 4h10v2H7zm0 4h7v2H7z"/></svg>
              <span>${req.spreadsheet_title || 'Untitled spreadsheet'}</span>
            </a>
          </div>

          ${req.message ? `
            <div style="font-size: 12.5px; color: #3c4043; background: #f8f9fa; border-left: 3px solid #0f9d58; padding: 6px 10px; border-radius: 0 4px 4px 0; margin-top: 6px; font-style: italic;">
              "${req.message}"
            </div>
          ` : ''}

          <div style="font-size: 11px; color: #80868b; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
            <span class="material-symbols-outlined" style="font-size: 13px;">schedule</span>
            <span>Requested on ${dateStr}</span>
          </div>
        </div>
      </div>

      <div style="display: flex; gap: 8px; align-items: center; flex-shrink: 0;">
        <button onclick="handleRequest(${req.id}, 'approve')" style="background: #0f9d58; color: #ffffff; border: none; padding: 7px 18px; font-weight: 500; font-size: 13px; border-radius: 20px; cursor: pointer; display: flex; align-items: center; gap: 6px;" onmouseenter="this.style.background='#0b8043'" onmouseleave="this.style.background='#0f9d58'">
          <span class="material-symbols-outlined" style="font-size: 16px;">check_circle</span>
          <span>Approve</span>
        </button>
        <button onclick="handleRequest(${req.id}, 'decline')" style="background: #f1f3f4; color: #5f6368; border: 1px solid #dadce0; padding: 7px 16px; font-weight: 500; font-size: 13px; border-radius: 20px; cursor: pointer; display: flex; align-items: center; gap: 6px;" onmouseenter="this.style.background='#e8eaed'; this.style.color='#202124'" onmouseleave="this.style.background='#f1f3f4'; this.style.color='#5f6368'">
          <span class="material-symbols-outlined" style="font-size: 16px;">cancel</span>
          <span>Decline</span>
        </button>
      </div>
    `;
    container.appendChild(card);
  });
}

function handleRequest(requestId, decision) {
  const formData = new FormData();
  formData.append('request_id', requestId);
  formData.append('decision', decision);

  fetch(getApiUrl('sheets_handler.php?action=handle_request'), {
    method: 'POST',
    body: formData
  })
    .then(r => r.json())
    .then(res => {
      if (res.status === 'success') {
        loadAccessRequests();
      } else {
        alert('Action failed: ' + res.message);
      }
    })
    .catch(err => {
      console.error(err);
      alert('Connection error.');
    });
}


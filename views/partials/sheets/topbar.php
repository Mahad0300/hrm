<!-- Top Header Bar -->
<div id="topbar">
  <svg class="sheets-icon" viewBox="0 0 48 48" style="cursor:pointer;" onclick="window.location.href='<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/sheets'">
    <rect x="6" y="4" width="36" height="40" rx="3" fill="#0F9D58"/>
    <path d="M14 14h20M14 21h20M14 28h20M14 35h12" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/>
  </svg>
  <div class="title-section">
    <div class="title-row" style="display:flex; align-items:center;">
      <input id="docTitle" value="<?= htmlspecialchars($docTitle ?? 'Untitled spreadsheet') ?>" title="Rename spreadsheet">
      <span id="saveStatus" style="font-size:12px; color:#5f6368; display:inline-flex; align-items:center; gap:4px; margin-left:12px; cursor:pointer;" title="Document status">
        <span class="material-symbols-outlined" style="font-size:16px; color:#5f6368;">cloud_done</span>
        <span id="saveStatusText">Saved to server</span>
      </span>
    </div>
    <div class="menubar">
      <div class="menu-item" data-menu="file">File</div>
      <div class="menu-item" data-menu="edit">Edit</div>
      <div class="menu-item" data-menu="view">View</div>
      <div class="menu-item" data-menu="insert">Insert</div>
      <div class="menu-item" data-menu="format">Format</div>
      <div class="menu-item" data-menu="data">Data</div>
      <div class="menu-item" data-menu="tools">Tools</div>
      <div class="menu-item" data-menu="extensions">Extensions</div>
      <div class="menu-item" data-menu="help">Help</div>
    </div>
  </div>

  <div class="top-actions" style="position:relative; display:flex; align-items:center; gap:8px;">
    <!-- Live Active Collaborators Avatars List -->
    <div id="activeCollaboratorsContainer" style="display:flex; align-items:center; gap:4px; margin-right:4px;"></div>

    <button class="share-btn" id="shareBtn">
      <span class="material-symbols-outlined">lock</span>
      <span>Share</span>
      <span class="material-symbols-outlined" style="font-size:18px;">arrow_drop_down</span>
    </button>

    <div class="avatar" id="userAvatarBtn" title="HRM Account" style="display:flex; cursor:pointer; position:relative; background:#1a73e8; color:#fff; border-radius:50%; width:32px; height:32px; align-items:center; justify-content:center; font-weight:bold; font-size:14px; overflow:hidden;">
      <span id="userAvatarText"><?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?></span>
      <img id="userAvatarImg" src="" alt="" referrerpolicy="no-referrer" style="display:none; width:100%; height:100%; border-radius:50%; object-fit:cover;" onerror="this.style.display='none'; document.getElementById('userAvatarText').style.display='block';">

      <!-- User Profile Dropdown Popover -->
      <div id="userProfilePopover" class="dropdown-menu" style="display:none; position:absolute; top:42px; right:0; width:260px; padding:16px; background:#fff; border-radius:16px; box-shadow:0 4px 20px rgba(0,0,0,0.18); z-index:99999; text-align:center; cursor:default;">
        <div id="popoverAvatarText" style="width:56px; height:56px; border-radius:50%; background:#1a73e8; color:#fff; font-size:24px; font-weight:bold; display:flex; align-items:center; justify-content:center; margin:0 auto 10px auto;"><?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?></div>
        <div id="popoverUserName" style="font-weight:600; font-size:15px; color:#202124; margin-bottom:2px;"><?= htmlspecialchars($_SESSION['user_name'] ?? 'HRM User') ?></div>
        <div id="popoverUserEmail" style="font-size:13px; color:#5f6368; margin-bottom:16px; word-break:break-all;"><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></div>
        <div style="border-top:1px solid #e1e3e6; padding-top:12px;">
          <button id="signOutBtn" onclick="window.location.href='<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>/sheets'" class="btn btn-secondary" style="width:100%; justify-content:center; display:inline-flex; align-items:center; gap:8px; padding:8px 16px; border-radius:20px; border:1px solid #dadce0; font-size:13px; font-weight:500; cursor:pointer; color:#3c4043;">
            <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
            <span>Back to Dashboard</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

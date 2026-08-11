<!-- Pixel-Perfect Google Sheets Share Modal -->
<div class="modal-overlay" id="shareModal">
  <div class="modal-card" style="width: 520px; border-radius: 16px; padding: 24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
      <h3 style="margin:0; font-size:20px; font-weight:400; color:#202124;">Share '<span id="shareDocTitle">Untitled spreadsheet</span>'</h3>
      <div style="display:flex; align-items:center; gap:12px; color:#5f6368;">
        <span class="material-symbols-outlined" style="cursor:pointer; font-size:20px;">help</span>
        <span class="material-symbols-outlined" style="cursor:pointer; font-size:20px;">settings</span>
        <span class="material-symbols-outlined" id="closeShareBtn" style="cursor:pointer; font-size:20px;">close</span>
      </div>
    </div>

    <!-- Floating Input Container -->
    <div style="position:relative; margin-bottom:16px;">
      <div style="position:absolute; top:-9px; left:12px; background:#fff; padding:0 4px; font-size:12px; color:#1a73e8; font-weight:500; z-index:10;">Add people, groups, spaces and calendar events</div>
      <div style="display:flex; align-items:center; border:2px solid #1a73e8; border-radius:8px; padding:8px 12px; gap:8px; min-height:42px; position:relative; z-index:5;">
        <input type="text" id="shareEmailInput" placeholder="Enter email address or name..." style="flex:1; border:none; outline:none; font-size:14px; font-family:inherit;" autocomplete="off">
        <select id="shareRoleSelect" style="border:none; outline:none; background:transparent; font-size:13px; color:#3c4043; cursor:pointer; font-weight:500;">
          <option value="editor">Editor</option>
          <option value="viewer">Viewer</option>
        </select>
        <button class="btn btn-primary" id="addShareUserBtn" style="padding:6px 16px; border-radius:18px; font-size:13px;">Add</button>
      </div>
      
      <!-- Suggestions Autocomplete Dropdown List -->
      <div id="shareAutocompleteDropdown" style="display:none; position:absolute; top:100%; left:0; right:0; background:#fff; border:1px solid #dadce0; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.15); max-height:200px; overflow-y:auto; z-index:99999; margin-top:4px;"></div>
    </div>

    <!-- Invitation Notification Helper Notice -->
    <div id="emailNoticeBanner" style="display:none; background:#e8f0fe; border-radius:8px; padding:10px 14px; font-size:13px; color:#174ea6; margin-bottom:20px; align-items:center; justify-content:space-between;">
      <span id="emailNoticeText">Access granted to user!</span>
      <a id="openGmailBtn" href="#" target="_blank" style="color:#1a73e8; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">
        <span>Send via Gmail</span>
        <span class="material-symbols-outlined" style="font-size:16px;">open_in_new</span>
      </a>
    </div>

    <!-- Pending Access Requests Section -->
    <div id="pendingRequestsContainer" style="display:none; margin-bottom:20px; background:#e8f0fe; border-radius:8px; padding:12px; border:1px solid #c2e7ff;"></div>

    <!-- People With Access Section -->
    <div style="margin-bottom:24px;">
      <div style="font-size:14px; font-weight:600; color:#202124; margin-bottom:12px;">People with access</div>
      
      <div id="sharedUsersList" style="display:flex; flex-direction:column; gap:12px; max-height:160px; overflow-y:auto; padding-right:4px;">
        <!-- Dynamic Owner & Shared Users List -->
        <div style="display:flex; align-items:center; justify-content:space-between;">
          <div style="display:flex; align-items:center; gap:12px;">
            <div id="ownerAvatarCircle" style="width:36px; height:36px; border-radius:50%; background:#c5221f; color:#fff; font-weight:bold; font-size:16px; display:flex; align-items:center; justify-content:center;">Z</div>
            <div>
              <div id="ownerDisplayName" style="font-size:14px; font-weight:500; color:#202124;">Owner Name (you)</div>
              <div id="ownerEmailText" style="font-size:12px; color:#5f6368;">owner@gmail.com</div>
            </div>
          </div>
          <span style="font-size:13px; color:#5f6368; font-weight:500;">Owner</span>
        </div>
      </div>
    </div>

    <!-- General Access Section -->
    <div style="margin-bottom:28px;">
      <div style="font-size:14px; font-weight:600; color:#202124; margin-bottom:12px;">General access</div>
      <div style="display:flex; align-items:center; gap:14px;">
        <div style="width:40px; height:40px; border-radius:50%; background:#e8eaed; display:flex; align-items:center; justify-content:center; color:#5f6368;">
          <span class="material-symbols-outlined" id="generalAccessIcon">lock</span>
        </div>
        <div style="flex:1;">
          <select id="generalAccessSelect" style="border:none; outline:none; background:transparent; font-size:14px; font-weight:600; color:#202124; cursor:pointer; padding:0;">
            <option value="restricted">Restricted</option>
            <option value="public_viewer">Anyone with the link (Viewer)</option>
            <option value="public_editor">Anyone with the link (Editor)</option>
          </select>
          <div style="font-size:12px; color:#5f6368; margin-top:2px;" id="generalAccessDesc">Only people with access can open with the link</div>
        </div>
      </div>
    </div>

    <!-- Modal Footer Actions -->
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px;">
      <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
        <button class="btn btn-secondary" id="copyShareLinkBtn" style="display:inline-flex; align-items:center; gap:8px; border:1px solid #dadce0; border-radius:20px; padding:8px 20px; font-size:14px; font-weight:500; color:#1a73e8; background:#fff; cursor:pointer;">
          <span class="material-symbols-outlined" style="font-size:18px;">link</span>
          <span>Copy link</span>
        </button>
        <button class="btn" id="shareOnChatroxBtn" style="display:none; align-items:center; gap:8px; border:1px solid #dadce0; border-radius:20px; padding:7px 18px 7px 12px; font-size:14px; font-weight:500; color:#1a73e8; background:#fff; cursor:pointer; transition:all 0.18s ease;">
          <img id="chatroxBtnLogo" src="" alt="Chatrox" style="width:18px; height:18px; object-fit:contain; flex-shrink:0;">
          <span>Share on Chatrox</span>
        </button>
      </div>
      <button class="btn btn-primary" id="doneShareBtn" style="border-radius:20px; padding:8px 24px; font-size:14px; font-weight:500;">Done</button>
    </div>
  </div>
</div>

<!-- Find & Replace Modal -->
<div class="modal-overlay" id="findReplaceModal">
  <div class="modal-card" style="width: 480px;">
    <h3>Find and replace</h3>
    <div class="modal-field">
      <label>Find</label>
      <input type="text" id="findInput" placeholder="Find in sheet...">
    </div>
    <div class="modal-field">
      <label>Replace with</label>
      <input type="text" id="replaceInput" placeholder="Replace with...">
    </div>
    <div style="font-size: 12px; color: var(--muted); margin-bottom: 12px; height: 16px;" id="findMatchCount"></div>
    <div class="modal-actions" style="justify-content: space-between; display: flex; align-items: center;">
      <button class="btn btn-secondary" id="closeFindBtn">Close</button>
      <div style="display: flex; gap: 6px;">
        <button class="btn btn-secondary" id="findPrevBtn">Previous</button>
        <button class="btn btn-secondary" id="findNextBtn">Find Next</button>
        <button class="btn btn-secondary" id="replaceBtn">Replace</button>
        <button class="btn btn-primary" id="replaceAllBtn">Replace All</button>
      </div>
    </div>
  </div>
</div>

<!-- Insert Link Modal -->
<div class="modal-overlay" id="insertLinkModal">
  <div class="modal-card" style="width: 420px;">
    <h3>Insert link</h3>
    <div class="modal-field">
      <label>Text to display</label>
      <input type="text" id="linkTextDisplayInput" placeholder="Text">
    </div>
    <div class="modal-field">
      <label>Link / URL</label>
      <input type="text" id="linkUrlInput" value="https://" placeholder="Paste or search a link">
    </div>
    <div class="modal-actions">
      <button class="btn btn-secondary" id="closeLinkBtn">Cancel</button>
      <button class="btn btn-primary" id="applyLinkBtn">Apply</button>
    </div>
  </div>
</div>

<!-- Insert Comment Modal -->
<div class="modal-overlay" id="insertCommentModal">
  <div class="modal-card" style="width: 400px;">
    <h3>Add comment</h3>
    <div class="modal-field">
      <textarea id="commentTextInput" rows="3" placeholder="Comment or @mention someone" style="width:100%; border:1px solid #dadce0; border-radius:4px; padding:8px; font-family:inherit; outline:none; resize:none;"></textarea>
    </div>
    <div class="modal-actions">
      <button class="btn btn-secondary" id="closeCommentBtn">Cancel</button>
      <button class="btn btn-primary" id="applyCommentBtn">Comment</button>
    </div>
  </div>
</div>

<!-- Chart Preview Modal -->
<div class="modal-overlay" id="chartModal">
  <div class="modal-card" style="width: 520px;">
    <h3>Chart Preview</h3>
    <div id="chartContainer" style="height: 260px; display: flex; align-items: flex-end; justify-content: space-around; padding: 20px 10px; background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 16px;"></div>
    <div class="modal-actions">
      <button class="btn btn-secondary" id="closeChartBtn">Close</button>
    </div>
  </div>
</div>

<!-- Keyboard Shortcuts Modal -->
<div class="modal-overlay" id="shortcutsModal">
  <div class="modal-card" style="width: 460px;">
    <h3>Keyboard Shortcuts</h3>
    <div style="max-height:280px; overflow-y:auto; font-size:13px; display:grid; grid-template-columns:1fr 1fr; gap:10px 16px;">
      <div><strong>Ctrl + Z</strong>: Undo</div>
      <div><strong>Ctrl + Y</strong>: Redo</div>
      <div><strong>Ctrl + C</strong>: Copy</div>
      <div><strong>Ctrl + X</strong>: Cut</div>
      <div><strong>Ctrl + V</strong>: Paste</div>
      <div><strong>Ctrl + B</strong>: Bold</div>
      <div><strong>Ctrl + I</strong>: Italic</div>
      <div><strong>Ctrl + F</strong>: Find & Replace</div>
      <div><strong>Ctrl + A</strong>: Select All</div>
      <div><strong>Ctrl + P</strong>: Print</div>
      <div><strong>F2 / Enter</strong>: Edit Cell</div>
      <div><strong>Delete</strong>: Clear Cell</div>
    </div>
    <div class="modal-actions">
      <button class="btn btn-primary" id="closeShortcutsBtn">Close</button>
    </div>
  </div>
</div>

<!-- Dynamic Context Menu & Dropdown Popovers -->
<div class="dropdown-menu" id="contextMenu">
  <div class="dropdown-item" id="ctxCut">
    <span class="material-symbols-outlined" style="font-size:18px;">content_cut</span>
    <span>Cut</span>
    <span class="shortcut">Ctrl+X</span>
  </div>
  <div class="dropdown-item" id="ctxCopy">
    <span class="material-symbols-outlined" style="font-size:18px;">content_copy</span>
    <span>Copy</span>
    <span class="shortcut">Ctrl+C</span>
  </div>
  <div class="dropdown-item" id="ctxPaste">
    <span class="material-symbols-outlined" style="font-size:18px;">content_paste</span>
    <span>Paste</span>
    <span class="shortcut">Ctrl+V</span>
  </div>
  <div class="dropdown-divider"></div>
  <div class="dropdown-item" id="ctxInsertRow">
    <span class="material-symbols-outlined" style="font-size:18px;">table_rows</span>
    <span>Insert 1 row above</span>
  </div>
  <div class="dropdown-item" id="ctxInsertCol">
    <span class="material-symbols-outlined" style="font-size:18px;">view_column</span>
    <span>Insert 1 column left</span>
  </div>
  <div class="dropdown-item" id="ctxDeleteRow">
    <span class="material-symbols-outlined" style="font-size:18px;">delete</span>
    <span>Delete row</span>
  </div>
  <div class="dropdown-item" id="ctxDeleteCol">
    <span class="material-symbols-outlined" style="font-size:18px;">delete</span>
    <span>Delete column</span>
  </div>
  <div class="dropdown-divider"></div>
  <div class="dropdown-item" id="ctxDropdown">
    <span class="material-symbols-outlined" style="font-size:18px;">arrow_drop_down_circle</span>
    <span>Dropdown</span>
  </div>
  <div class="dropdown-divider"></div>
  <div class="dropdown-item" id="ctxClear">
    <span class="material-symbols-outlined" style="font-size:18px;">backspace</span>
    <span>Clear contents</span>
  </div>
</div>

<!-- Data Validation / Dropdown Rules Modal -->
<div class="modal-overlay" id="dropdownModal">
  <div class="modal-card" style="width: 440px; border-radius: 16px; padding: 24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
      <h3 style="margin:0; font-size:18px; font-weight:500; color:#202124;">Data validation rules</h3>
      <span class="material-symbols-outlined" id="closeDropdownModalBtn" style="cursor:pointer; font-size:20px; color:#5f6368;">close</span>
    </div>

    <!-- Apply to range -->
    <div style="margin-bottom:16px;">
      <label style="display:block; font-size:12px; font-weight:600; color:#5f6368; margin-bottom:6px;">Apply to range</label>
      <input type="text" id="dropdownRangeInput" readonly style="width:100%; border:1px solid #dadce0; border-radius:6px; padding:8px 12px; font-size:13px; background:#f8f9fa; color:#202124; box-sizing:border-box;">
    </div>

    <!-- Criteria -->
    <div style="margin-bottom:16px;">
      <label style="display:block; font-size:12px; font-weight:600; color:#5f6368; margin-bottom:6px;">Criteria</label>
      <select id="dropdownCriteriaSelect" style="width:100%; border:1px solid #dadce0; border-radius:6px; padding:8px 12px; font-size:13px; color:#202124; background:#fff; outline:none; box-sizing:border-box;">
        <option value="dropdown">Dropdown</option>
      </select>
    </div>

    <!-- Options List Container -->
    <div style="margin-bottom:16px;">
      <label style="display:block; font-size:12px; font-weight:600; color:#5f6368; margin-bottom:8px;">Options</label>
      <div id="dropdownItemsList" style="display:flex; flex-direction:column; gap:8px; max-height:220px; overflow-y:auto; padding-right:4px;">
        <!-- Dynamic Option Rows -->
      </div>

      <button type="button" id="addDropdownOptionBtn" style="margin-top:10px; background:#fff; border:1px solid #dadce0; border-radius:6px; padding:6px 14px; font-size:13px; font-weight:500; color:#1a73e8; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
        <span class="material-symbols-outlined" style="font-size:16px;">add</span>
        <span>Add another item</span>
      </button>
    </div>

    <!-- Footer Actions -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:24px; border-top:1px solid #f1f3f4; padding-top:16px;">
      <button type="button" id="removeDropdownRuleBtn" class="btn btn-secondary" style="color:#d93025; background:#fff; border:1px solid #fce8e6; font-size:13px;">Remove rule</button>
      <button type="button" id="saveDropdownRuleBtn" class="btn btn-primary" style="background:#1e8e3e; border:none; border-radius:20px; padding:8px 24px; font-size:13px; font-weight:500;">Done</button>
    </div>
  </div>
</div>

<!-- Create Pivot Table Modal -->
<div class="modal-overlay" id="createPivotModal">
  <div class="modal-card" style="width: 440px; border-radius: 16px; padding: 24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
      <h3 style="margin:0; font-size:18px; font-weight:500; color:#202124;">Create pivot table</h3>
      <span class="material-symbols-outlined" id="closePivotModalBtn" style="cursor:pointer; font-size:20px; color:#5f6368;">close</span>
    </div>

    <!-- Data range -->
    <div style="margin-bottom:18px;">
      <label style="display:block; font-size:12px; font-weight:600; color:#5f6368; margin-bottom:6px;">Data range</label>
      <div style="position:relative; display:flex; align-items:center;">
        <input type="text" id="pivotDataRangeInput" style="width:100%; border:1px solid #747775; border-radius:6px; padding:10px 36px 10px 12px; font-size:13px; color:#202124; box-sizing:border-box; outline:none;" placeholder="e.g. Sheet1!A1:D100">
        <span class="material-symbols-outlined" style="position:absolute; right:10px; color:#5f6368; font-size:20px; cursor:pointer;" title="Click cell in sheet to select">grid_on</span>
      </div>
    </div>

    <!-- Insert to -->
    <div style="margin-bottom:24px;">
      <label style="display:block; font-size:12px; font-weight:600; color:#5f6368; margin-bottom:10px;">Insert to</label>
      <div style="display:flex; flex-direction:column; gap:10px;">
        <label style="display:flex; align-items:center; gap:10px; font-size:13px; color:#202124; cursor:pointer;">
          <input type="radio" name="pivotInsertDestination" value="new" checked style="accent-color:#146c2e; width:16px; height:16px;">
          <span>New sheet</span>
        </label>
        <label style="display:flex; align-items:center; gap:10px; font-size:13px; color:#202124; cursor:pointer;">
          <input type="radio" name="pivotInsertDestination" value="existing" style="accent-color:#146c2e; width:16px; height:16px;">
          <span>Existing sheet</span>
        </label>
      </div>

      <!-- Existing Location Input -->
      <div id="pivotExistingLocationContainer" style="display:none; margin-top:10px;">
        <div style="position:relative; display:flex; align-items:center;">
          <input type="text" id="pivotExistingLocationInput" placeholder="e.g. Sheet1!E6" style="width:100%; border:1px solid #747775; border-radius:6px; padding:10px 36px 10px 12px; font-size:13px; box-sizing:border-box; outline:none;">
          <span class="material-symbols-outlined" style="position:absolute; right:10px; color:#5f6368; font-size:20px; cursor:pointer;" title="Click cell in sheet to select location">grid_on</span>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div style="display:flex; justify-content:flex-end; align-items:center; gap:12px;">
      <button type="button" class="btn btn-secondary" id="cancelPivotBtn" style="border-radius:18px; padding:8px 20px; font-size:13px;">Cancel</button>
      <button type="button" class="btn btn-primary" id="confirmCreatePivotBtn" style="background:#146c2e; border:none; border-radius:18px; padding:8px 24px; font-size:13px; font-weight:500;">Create</button>
    </div>
  </div>
</div>

<!-- Pivot Table Editor Side Panel Drawer -->
<div id="pivotEditorSidePanel" style="display:none; position:fixed; top:85px; right:0; width:300px; bottom:0; background:#ffffff; border-left:1px solid #dadce0; box-shadow:-2px 0 10px rgba(0,0,0,0.08); z-index:99990; font-family:Inter,Roboto,sans-serif; flex-direction:column; overflow-y:auto;">
  <div style="padding:16px 20px; border-bottom:1px solid #e0e0e0; display:flex; justify-content:space-between; align-items:center; background:#f8f9fa;">
    <div style="display:flex; align-items:center; gap:8px;">
      <span class="material-symbols-outlined" style="color:#0f9d58; font-size:20px;">pivot_table_chart</span>
      <h3 style="margin:0; font-size:15px; font-weight:600; color:#202124;">Pivot table editor</h3>
    </div>
    <span class="material-symbols-outlined" id="closePivotEditorBtn" style="cursor:pointer; font-size:18px; color:#5f6368;">close</span>
  </div>

  <div style="padding:16px; display:flex; flex-direction:column; gap:16px;">
    <!-- Range Box -->
    <div>
      <label style="display:block; font-size:11px; font-weight:700; color:#5f6368; text-transform:uppercase; margin-bottom:6px;">Data Range</label>
      <div id="pivotEditorSourceRangeBadge" style="background:#e8f0fe; color:#174ea6; font-size:12px; font-weight:600; padding:6px 10px; border-radius:6px; border:1px solid #c2e7ff; word-break:break-all;">Sheet1!A1:D100</div>
    </div>

    <!-- Rows Selection -->
    <div>
      <label style="display:block; font-size:11px; font-weight:700; color:#5f6368; text-transform:uppercase; margin-bottom:6px;">Rows (Group By)</label>
      <select id="pivotSelectRowsCol" style="width:100%; border:1px solid #dadce0; border-radius:6px; padding:8px; font-size:13px; outline:none; background:#fff;">
        <option value="">Select column...</option>
      </select>
    </div>

    <!-- Columns Selection -->
    <div>
      <label style="display:block; font-size:11px; font-weight:700; color:#5f6368; text-transform:uppercase; margin-bottom:6px;">Columns (Optional Header)</label>
      <select id="pivotSelectColsCol" style="width:100%; border:1px solid #dadce0; border-radius:6px; padding:8px; font-size:13px; outline:none; background:#fff;">
        <option value="">None (Single Summary Column)</option>
      </select>
    </div>

    <!-- Values Selection -->
    <div>
      <label style="display:block; font-size:11px; font-weight:700; color:#5f6368; text-transform:uppercase; margin-bottom:6px;">Values (Aggregate Field)</label>
      <select id="pivotSelectValsCol" style="width:100%; border:1px solid #dadce0; border-radius:6px; padding:8px; font-size:13px; outline:none; background:#fff; margin-bottom:10px;">
        <option value="">Select value column...</option>
      </select>
      <label style="display:block; font-size:11px; font-weight:700; color:#5f6368; text-transform:uppercase; margin-bottom:6px;">Summarize by</label>
      <select id="pivotSelectAggFunc" style="width:100%; border:1px solid #dadce0; border-radius:6px; padding:8px; font-size:13px; outline:none; background:#fff;">
        <option value="SUM">SUM</option>
        <option value="COUNTA">COUNTA (Count)</option>
        <option value="AVERAGE">AVERAGE</option>
        <option value="MIN">MIN</option>
        <option value="MAX">MAX</option>
      </select>
    </div>

    <!-- Generate / Update Button -->
    <div style="margin-top:10px;">
      <button type="button" id="applyPivotTableBtn" class="btn btn-primary" style="width:100%; background:#146c2e; border:none; border-radius:8px; padding:10px; font-size:13px; font-weight:600; cursor:pointer;">
        Apply Pivot Table
      </button>
    </div>
  </div>
</div>

<!-- Official Google Sheets "You need access" Full-Page Screen -->
<div id="accessDeniedPage" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:#ffffff; z-index:999999; font-family:'Google Sans',Roboto,Arial,sans-serif; overflow-y:auto;">
  <!-- Header Bar -->
  <div style="padding: 24px 40px; display:flex; align-items:center; gap:12px;">
    <svg class="sheets-icon" viewBox="0 0 48 48" style="width:36px; height:36px; cursor:pointer;" onclick="window.location.href='<?= defined('BASE_PATH') ? BASE_PATH : '' ?>/dashboard'">
      <rect x="6" y="4" width="36" height="40" rx="3" fill="#0F9D58"/>
      <path d="M14 14h20M14 21h20M14 28h20M14 35h12" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/>
    </svg>
    <span style="font-size:22px; font-weight:400; color:#5f6368;">Google Sheets</span>
  </div>

  <!-- Main Request Container -->
  <div style="max-width: 900px; margin: 20px auto; padding: 0 40px; display:flex; justify-content:space-between; align-items:flex-start; gap:40px;">
    <!-- Left Request Form -->
    <div style="max-width: 480px; flex:1;">
      <h1 style="font-size: 32px; font-weight: 400; color: #202124; margin: 0 0 12px 0;">You need access</h1>
      <p style="font-size: 14px; color: #5f6368; line-height: 1.5; margin: 0 0 24px 0;">
        Request access, or switch to an account with access. <a href="#" style="color:#1a73e8; text-decoration:none;">Learn more</a>
      </p>

      <!-- Role Radios -->
      <div style="display:flex; flex-direction:column; gap:14px; margin-bottom:28px;">
        <label style="display:flex; align-items:center; gap:12px; font-size:14px; color:#202124; cursor:pointer;">
          <input type="radio" name="requestRoleChoice" value="viewer" style="accent-color:#1a73e8; width:18px; height:18px;">
          <span>Viewer</span>
        </label>
        <label style="display:flex; align-items:center; gap:12px; font-size:14px; color:#202124; cursor:pointer;">
          <input type="radio" name="requestRoleChoice" value="commenter" style="accent-color:#1a73e8; width:18px; height:18px;">
          <span>Commenter</span>
        </label>
        <label style="display:flex; align-items:center; gap:12px; font-size:14px; color:#202124; cursor:pointer;">
          <input type="radio" name="requestRoleChoice" value="editor" checked style="accent-color:#1a73e8; width:18px; height:18px;">
          <span>Editor</span>
        </label>
      </div>

      <!-- Message Textarea -->
      <div style="margin-bottom:28px;">
        <textarea id="accessRequestMsg" placeholder="Message (optional)" rows="4" style="width:100%; border:1px solid #747775; border-radius:4px; padding:14px; font-family:inherit; font-size:14px; outline:none; resize:none; box-sizing:border-box;"></textarea>
      </div>

      <!-- Submit Request Button -->
      <div style="margin-bottom:40px;">
        <button id="sendRequestAccessBtn" class="btn btn-primary" style="background-color:#1a73e8; color:#fff; border:none; border-radius:20px; padding:10px 28px; font-size:14px; font-weight:500; cursor:pointer;">
          Request access
        </button>
      </div>

      <!-- Signed In Status Badge -->
      <div style="margin-top: 20px; text-align:center;">
        <div style="font-size: 12px; color: #5f6368; margin-bottom: 8px;" id="signedInAsTitle">You're signed in as</div>
        <div id="signedInUserBadge" style="display:inline-flex; align-items:center; gap:8px; border:1px solid #dadce0; border-radius:20px; padding:6px 16px 6px 8px; font-size:13px; color:#3c4043; background:#fff;">
          <div id="signedInUserAvatar" style="width:24px; height:24px; border-radius:50%; background:#1a73e8; color:#fff; font-size:12px; font-weight:bold; display:flex; align-items:center; justify-content:center;">U</div>
          <span id="signedInUserEmail">user@gmail.com</span>
        </div>
      </div>
    </div>

    <!-- Right Side Illustration -->
    <div style="width: 320px; padding-top:20px; display:flex; flex-direction:column; align-items:center; justify-content:center;">
      <svg width="260" height="220" viewBox="0 0 260 220" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="30" y="30" width="160" height="150" rx="16" fill="#FCE8E6" transform="rotate(-6 110 105)"/>
        <rect x="40" y="20" width="160" height="150" rx="16" fill="#FEF7E0" transform="rotate(4 120 95)"/>
        <circle cx="210" cy="40" r="14" fill="#34A853"/>
        <circle cx="190" cy="150" r="28" fill="#FAD2CF"/>
        <path d="M110 90C110 78.9543 118.954 70 130 70C141.046 70 150 78.9543 150 90V130H110V90Z" fill="#EA4335"/>
      </svg>
    </div>
  </div>
</div>


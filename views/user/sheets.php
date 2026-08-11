<?php
/**
 * Sheets Dashboard View
 */

$page_title = "Google Sheets";
$page_subtitle = "Create, edit, and share spreadsheets collaboratively within your department and team.";

// Dynamic layout wrappers based on employee role
$userRole = strtolower(\App\Core\Auth::role() ?? 'user');
if ($userRole === 'hr') {
    $headerPath = __DIR__ . '/../partials/hr/header.php';
    $sidebarPath = __DIR__ . '/../partials/hr/sidebar.php';
    $footerPath = __DIR__ . '/../partials/hr/footer.php';
} elseif ($userRole === 'admin') {
    $headerPath = __DIR__ . '/../partials/admin/header.php';
    $sidebarPath = __DIR__ . '/../partials/admin/sidebar.php';
    $footerPath = __DIR__ . '/../partials/admin/footer.php';
} else {
    $headerPath = __DIR__ . '/../partials/user/header.php';
    $sidebarPath = __DIR__ . '/../partials/user/sidebar.php';
    $footerPath = __DIR__ . '/../partials/user/footer.php';
}

include $headerPath;
include $sidebarPath;
?>

<!-- Sheets Styling -->
<link rel="stylesheet" href="<?= \App\Core\View::asset('css/sheets-dashboard.css') ?>">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

<style>
.sheets-tabs-bar {
  display: flex;
  border-bottom: 1px solid #e1e3e6;
  margin-bottom: 24px;
  gap: 8px;
}
.dash-tab-btn {
  background: transparent;
  border: none;
  border-bottom: 3px solid transparent;
  padding: 8px 16px;
  font-size: 14px;
  font-weight: 500;
  color: #5f6368;
  cursor: pointer;
  outline: none;
  transition: all 0.2s;
}
.dash-tab-btn:hover {
  color: #202124;
  background: #f1f3f4;
  border-radius: 4px 4px 0 0;
}
.dash-tab-btn.active {
  color: #0f9d58;
  border-bottom-color: #0f9d58;
}
.dash-search-container {
  display: flex;
  align-items: center;
  margin-bottom: 20px;
  gap: 12px;
}
.search-input-wrapper {
  position: relative;
  flex: 1;
  max-width: 480px;
}
.search-input-wrapper .search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #5f6368;
}
.search-input-wrapper input {
  width: 100%;
  padding: 8px 12px 8px 36px;
  border: 1px solid #dadce0;
  border-radius: 20px;
  outline: none;
  font-size: 14px;
}
.search-input-wrapper input:focus {
  border-color: #0f9d58;
  box-shadow: 0 1px 4px rgba(15,157,88,0.25);
}
</style>

<div class="container-fluid" style="padding: 20px; background: #ffffff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); min-height: 80vh;">
  
  <!-- Search and New Button Bar -->
  <div class="dash-search-container">
    <div class="search-input-wrapper">
      <span class="material-symbols-outlined search-icon">search</span>
      <input type="text" id="searchInput" placeholder="Search spreadsheets by title...">
    </div>
    <div style="margin-left: auto;">
      <button onclick="createNewSpreadsheet()" class="btn btn-primary" style="background: #0f9d58; color: #fff; border: none; padding: 8px 20px; border-radius: 20px; font-weight: 500; font-size: 14px; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 2px 4px rgba(15,157,88,0.2);">
        <span class="material-symbols-outlined" style="font-size: 20px;">add</span>
        <span>New Spreadsheet</span>
      </button>
    </div>
  </div>

  <!-- Sheets Tabs Selection -->
  <div class="sheets-tabs-bar">
    <button class="dash-tab-btn active" data-tab="all">All Sheets</button>
    <button class="dash-tab-btn" data-tab="mine">My Sheets</button>
    <button class="dash-tab-btn" data-tab="shared">Shared with Me</button>
    <button class="dash-tab-btn" data-tab="requests">
      Access Requests
    </button>
  </div>

  <!-- Start New Spreadsheet Banner (Only on All Sheets) -->
  <div class="start-section" id="startSection" style="margin-bottom: 24px; border-radius: 8px;">
    <div class="section-header">
      <span style="font-size: 14px; font-weight: 600; color: #202124;">Create a new spreadsheet from blank template</span>
    </div>
    <div class="template-card" onclick="createNewSpreadsheet()" style="margin-top: 10px;">
      <div class="template-thumb" style="width: 120px; height: 140px; border-radius: 8px;">
        <svg width="40" height="40" viewBox="0 0 36 36">
          <path fill="#4285F4" d="M16 16v-9a2 2 0 0 1 4 0v9h9a2 2 0 0 1 0 4h-9v9a2 2 0 0 1-4 0v-9h-9a2 2 0 0 1 0-4h9z"/>
          <path fill="#34A853" d="M16 20h-9a2 2 0 0 1 0-4h9v4z"/>
          <path fill="#FBBC05" d="M20 16v-9a2 2 0 0 0-4 0v9h4z"/>
          <path fill="#EA4335" d="M20 20v9a2 2 0 0 1-4 0v-9h4z"/>
        </svg>
      </div>
      <div class="template-title" style="text-align: center; width: 120px; font-size: 12px; margin-top: 8px; font-weight: 500;">Blank spreadsheet</div>
    </div>
  </div>

  <!-- Recent Gallery Section -->
  <div class="recent-section" id="sheetsGridSection" style="padding: 0;">
    <div class="recent-title" style="margin-bottom: 16px; font-size: 15px; font-weight: 600; color: #5f6368;">Spreadsheets</div>
    <div class="sheets-grid" id="sheetsGrid">
      <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #5f6368;">
        Loading spreadsheets...
      </div>
    </div>
  </div>

  <!-- Access Requests Manager Section -->
  <div id="requestsSection" style="display: none;">
    <div style="font-size: 15px; font-weight: 600; color: #5f6368; margin-bottom: 16px;">Pending Access Requests</div>
    <div id="requestsContainer">
      <div style="padding: 20px; text-align: center; color: #5f6368;">Loading requests...</div>
    </div>
  </div>

</div>

<!-- Injected dynamic settings for JS -->
<script>
  window.APP_BASE_URL = '<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>';
  window.CSRF_TOKEN = '<?= \App\Helpers\CSRFToken::generate() ?>';
  
  (function() {
    const originalFetch = window.fetch;
    window.fetch = function(url, options) {
      options = options || {};
      const method = (options.method || 'GET').toUpperCase();
      if (['POST', 'PUT', 'DELETE', 'PATCH'].includes(method)) {
        options.headers = options.headers || {};
        if (typeof window.CSRF_TOKEN !== 'undefined') {
          if (options.headers instanceof Headers) {
            options.headers.set('X-CSRF-Token', window.CSRF_TOKEN);
          } else {
            options.headers['X-CSRF-Token'] = window.CSRF_TOKEN;
          }
        }
        if (options.body instanceof FormData && typeof window.CSRF_TOKEN !== 'undefined') {
          options.body.set('csrf_token', window.CSRF_TOKEN);
        }
      }
      return originalFetch(url, options);
    };
  })();
</script>

<!-- Sheets Dashboard script -->
<script src="<?= \App\Core\View::asset('js/sheets-dashboard.js') ?>"></script>

<?php include $footerPath; ?>

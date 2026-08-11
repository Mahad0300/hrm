<?php
if (!\App\Core\Auth::isLoggedIn() || ($_SESSION['user_role'] ?? '') !== 'Admin') {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $scriptDir  = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    $basePath   = preg_match('#/public$#', $scriptDir) ? substr($scriptDir, 0, -7) : $scriptDir;
    header('Location: ' . ($basePath === '/' ? '' : $basePath) . '/');
    exit;
}

// Fetch Chatrox integration settings
$stmtChatroxAll  = $pdo->query("SELECT meta_key, meta_value FROM settings WHERE meta_key LIKE 'chatrox_%'");
$chatroxSettings = $stmtChatroxAll ? $stmtChatroxAll->fetchAll(\PDO::FETCH_KEY_PAIR) : [];
$chatroxUrl      = rtrim(trim($chatroxSettings['chatrox_url'] ?? ''), '/');
$chatroxType     = trim($chatroxSettings['chatrox_type'] ?? 'domain'); // 'domain' or 'ip'
$chatroxIp       = trim($chatroxSettings['chatrox_ip'] ?? '');
$chatroxPort     = (int)($chatroxSettings['chatrox_port'] ?? 80);
$chatroxDomain   = trim($chatroxSettings['chatrox_domain'] ?? '');
$chatroxConnected = !empty($chatroxUrl);

// Fetch Biometric Machine integration settings
$stmtBio = $pdo->query("SELECT meta_key, meta_value FROM settings WHERE meta_key LIKE 'biometric_%'");
$bioSettings = $stmtBio ? $stmtBio->fetchAll(\PDO::FETCH_KEY_PAIR) : [];

$bioName         = trim($bioSettings['biometric_name'] ?? '');
$bioModel        = trim($bioSettings['biometric_model'] ?? '');
$bioIp           = trim($bioSettings['biometric_ip'] ?? '');
$bioPort         = (int)($bioSettings['biometric_port'] ?? 4370);
$bioCommKey      = (int)($bioSettings['biometric_comm_key'] ?? 0);
$bioMode         = strtoupper(trim($bioSettings['biometric_mode'] ?? 'UDP'));
$bioAutoSync     = (int)($bioSettings['biometric_auto_sync'] ?? 1);
$bioSyncInterval = (int)($bioSettings['biometric_sync_interval'] ?? 10);
$bioConnected    = !empty($bioIp);

$protocolStr = ($req = $_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
$baseUri = \App\Core\View::basePath();
$admsPushUrl = rtrim($protocolStr . ($baseUri === '/' ? '' : $baseUri), '/') . '/assets/api/biometric_push.php';

$page_title    = 'Connected Apps & Hardware Integrations';
$page_subtitle = 'Manage third-party applications and attendance machine hardware connected to your HRM portal.';
include __DIR__ . '/../partials/admin/header.php';
?>
<?php include __DIR__ . '/../partials/admin/sidebar.php'; ?>

<style>
  /* ── Page ──────────────────────────────────────────── */
  .ca-page { width: 100%; padding: 0 2px 48px; }

  /* ── Section header ────────────────────────────────── */
  .ca-section-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #94a3b8;
    margin-bottom: 16px;
  }

  /* ── App card ──────────────────────────────────────── */
  .ca-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 28px 28px 24px;
    display: flex;
    align-items: flex-start;
    gap: 20px;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
    transition: box-shadow .2s, border-color .2s;
    width: 100%;
    margin-bottom: 24px;
  }
  .ca-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,.08); border-color: #93c5fd; }

  /* Logo container */
  .ca-app-logo {
    width: 68px;
    height: 68px;
    border-radius: 16px;
    background: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
    border: 1px solid #e2e8f0;
  }
  .ca-app-logo img { width: 52px; height: 52px; object-fit: contain; }

  /* Body */
  .ca-card-body { flex: 1; min-width: 0; }
  .ca-card-body h3 {
    margin: 0 0 4px;
    font-size: 17px;
    font-weight: 700;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .ca-card-body p {
    margin: 0 0 18px;
    font-size: 13.5px;
    color: #64748b;
    line-height: 1.65;
  }

  /* Feature pills */
  .ca-features {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 12px;
  }
  .ca-feature-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 5px 13px;
    font-size: 12px;
    color: #475569;
    font-weight: 500;
  }
  .ca-feature-pill svg { color: #6c4cf1; }

  /* Footer / Right actions row */
  .ca-card-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-left: auto;
    align-self: center;
    flex-shrink: 0;
  }

  /* Status */
  .ca-status {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-size: 13px;
    font-weight: 600;
    padding: 5px 14px;
    border-radius: 20px;
  }
  .ca-status.connected  { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
  .ca-status.disconnected { background: #f8fafc; color: #94a3b8; border: 1px solid #e2e8f0; }
  .ca-status-dot {
    width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0;
  }
  .ca-status.connected .ca-status-dot    { background: #22c55e; box-shadow: 0 0 0 2px rgba(34,197,94,.25); }
  .ca-status.disconnected .ca-status-dot { background: #cbd5e1; }

  /* Buttons */
  .ca-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 18px;
    border-radius: 10px;
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    font-family: inherit;
    transition: all .18s;
    text-decoration: none;
    white-space: nowrap;
  }
  .ca-btn-primary {
    background: linear-gradient(135deg, #6c4cf1, #4f46e5);
    color: #fff;
    box-shadow: 0 2px 8px rgba(108,76,241,.3);
  }
  .ca-btn-primary:hover { opacity: .95; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(108,76,241,.35); }
  .ca-btn-secondary {
    background: #f1f5f9;
    color: #0f172a;
    border: 1px solid #cbd5e1;
  }
  .ca-btn-secondary:hover { background: #e2e8f0; color: #0284c7; }
  .ca-btn-ghost {
    background: #f8fafc;
    color: #334155;
    border: 1px solid #e2e8f0;
  }
  .ca-btn-ghost:hover { background: #f1f5f9; }
  .ca-btn-danger {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
  }
  .ca-btn-danger:hover { background: #fee2e2; }
  .ca-btn-outline-danger {
    background: transparent;
    color: #dc2626;
    border: 1.5px solid #fca5a5;
  }
  .ca-btn-outline-danger:hover { background: #fef2f2; border-color: #dc2626; }

  /* Connection Type Toggle */
  .ca-type-toggle {
    display: flex;
    gap: 0;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
  }
  .ca-type-btn {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 9px 16px;
    font-size: 13px;
    font-weight: 600;
    font-family: inherit;
    color: #64748b;
    background: #f8fafc;
    border: none;
    cursor: pointer;
    transition: all .15s;
  }
  .ca-type-btn:first-child { border-right: 1.5px solid #e2e8f0; }
  .ca-type-btn:hover { background: #f1f5f9; color: #334155; }
  .ca-type-btn.active {
    background: linear-gradient(135deg, #6c4cf1, #4f46e5);
    color: #fff;
  }

  /* ── Modal ─────────────────────────────────────────── */
  .ca-overlay {
    position: fixed; inset: 0;
    background: rgba(15,23,42,.45);
    backdrop-filter: blur(3px);
    z-index: 9000;
    display: flex; align-items: center; justify-content: center;
    opacity: 0; pointer-events: none;
    transition: opacity .22s;
  }
  .ca-overlay.active { opacity: 1; pointer-events: auto; }

  .ca-modal {
    background: #fff;
    border-radius: 20px;
    width: 520px;
    max-width: 94vw;
    box-shadow: 0 24px 80px rgba(0,0,0,.22);
    overflow: hidden;
    transform: translateY(20px) scale(.96);
    transition: transform .22s cubic-bezier(.34,1.56,.64,1);
  }
  .ca-overlay.active .ca-modal { transform: translateY(0) scale(1); }

  .ca-modal-head {
    padding: 24px 28px 18px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    gap: 16px;
  }
  .ca-modal-logo {
    width: 52px; height: 52px;
    border-radius: 14px;
    overflow: hidden;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
  }
  .ca-modal-logo img { width: 40px; height: 40px; object-fit: contain; }
  .ca-modal-head-text h3 { margin: 0 0 3px; font-size: 17px; font-weight: 700; color: #0f172a; }
  .ca-modal-head-text p  { margin: 0; font-size: 12.5px; color: #64748b; }

  .ca-modal-body { padding: 20px 28px; max-height: 75vh; overflow-y: auto; }
  .ca-modal-body label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .05em;
    text-transform: uppercase;
    color: #475569;
    margin-bottom: 6px;
  }
  .ca-modal-body input[type="text"],
  .ca-modal-body input[type="number"],
  .ca-modal-body input[type="url"],
  .ca-modal-body select {
    width: 100%;
    padding: 11px 14px;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: 13.5px;
    outline: none;
    box-sizing: border-box;
    color: #0f172a;
    background: #f8fafc;
    transition: border-color .15s, box-shadow .15s;
    margin-bottom: 16px;
  }
  .ca-modal-body input:focus,
  .ca-modal-body select:focus {
    border-color: #6c4cf1;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(108,76,241,.12);
  }
  .ca-modal-body .ca-hint {
    margin-top: -10px;
    margin-bottom: 16px;
    font-size: 12px;
    color: #94a3b8;
    line-height: 1.5;
  }
  .ca-modal-foot {
    padding: 16px 28px 24px;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    border-top: 1px solid #f1f5f9;
  }
  .ca-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
  }
</style>

<div class="content-wrapper">
  <div class="ca-page">

    <div class="ca-section-label">Hardware & Device Integrations</div>

    <!-- ── ZKTeco & ADMS Biometric Machine Card ── -->
    <div class="ca-card">
      <div class="ca-app-logo" style="background:#fff; border-color:#e2e8f0; padding:6px;">
        <img src="<?= \App\Core\View::asset('images/Zkteco-Logo.png') ?>" alt="ZKTeco" style="width:100%; height:100%; object-fit:contain;">
      </div>
      <div class="ca-card-body">
        <h3>
          <?= htmlspecialchars($bioName) ?>
          <?php if (!empty($bioModel)): ?>
            <span style="font-size:13px; font-weight:500; color:#64748b; margin-left:2px;">(<?= htmlspecialchars($bioModel) ?>)</span>
          <?php endif; ?>
          <?php if ($bioConnected && $bioAutoSync): ?>
            <span class="ca-status connected">
              <span class="ca-status-dot"></span> Connected
            </span>
          <?php elseif ($bioConnected): ?>
            <span class="ca-status disconnected">
              <span class="ca-status-dot"></span> Configured (Auto-Sync Off)
            </span>
          <?php else: ?>
            <span class="ca-status disconnected">
              <span class="ca-status-dot"></span> Not connected
            </span>
          <?php endif; ?>
        </h3>

        <p>Dynamic ZKTeco K60 / ID attendance machine integration via UDP/TCP socket and ADMS HTTP live cloud push receiver.</p>

        <?php if ($bioConnected): ?>
          <div class="ca-features">
            <span class="ca-feature-pill"><i data-lucide="server" size="14"></i> Device IP: <strong><?= htmlspecialchars($bioIp) ?>:<?= $bioPort ?></strong></span>
            <span class="ca-feature-pill"><i data-lucide="shield-check" size="14"></i> Comm Key: <strong><?= $bioCommKey ?></strong></span>
            <span class="ca-feature-pill"><i data-lucide="cpu" size="14"></i> Protocol: <strong><?= htmlspecialchars($bioMode) ?> Mode</strong></span>
            <span class="ca-feature-pill"><i data-lucide="refresh-cw" size="14"></i> Interval: <strong><?= $bioSyncInterval ?>s</strong></span>
          </div>
        <?php endif; ?>
      </div>

      <div class="ca-card-actions">
        <?php if ($bioConnected): ?>
          <button type="button" class="ca-btn ca-btn-danger" onclick="openBiometricDisconnectModal()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.36 6.64a9 9 0 11-12.73 0M12 2v10"/></svg>
            Disconnect
          </button>
          <button type="button" class="ca-btn ca-btn-primary" onclick="openBiometricModal()">
            <i data-lucide="settings" size="15"></i> Configure
          </button>
        <?php else: ?>
          <button type="button" class="ca-btn ca-btn-primary" onclick="openBiometricModal()">
            <i data-lucide="plug-zap" size="15"></i> Connect Machine
          </button>
        <?php endif; ?>
      </div>
    </div>

    <div class="ca-section-label" style="margin-top: 32px;">Software Integrations</div>

    <!-- ── Chatrox Card ── -->
    <div class="ca-card">

      <!-- Logo -->
      <div class="ca-app-logo">
        <img src="<?= $chatroxConnected ? rtrim($chatroxUrl, '/') . '/assets/images/logo.png' : 'http://localhost/chatrox/assets/images/logo.png' ?>" alt="Chatrox"
             onerror="this.onerror=null;this.src='data:image/svg+xml,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'%234f46e5\'><path d=\'M20 2H4C2.9 2 2 2.9 2 4v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z\'/></svg>'">
      </div>

      <!-- Body -->
      <div class="ca-card-body">
        <h3>
          Chatrox
          <?php if ($chatroxConnected): ?>
            <span class="ca-status connected">
              <span class="ca-status-dot"></span> Connected
            </span>
          <?php else: ?>
            <span class="ca-status disconnected">
              <span class="ca-status-dot"></span> Not connected
            </span>
          <?php endif; ?>
        </h3>

        <p>Share HRM spreadsheets directly into Chatrox DMs and channels. Users can pick contacts and send sheet links without leaving the spreadsheet.</p>
      </div>

      <!-- Actions (Right side) -->
      <div class="ca-card-actions">
        <?php if ($chatroxConnected): ?>
          <button type="button" class="ca-btn ca-btn-outline-danger" onclick="openChatroxAppModal('disconnect')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.36 6.64a9 9 0 11-12.73 0M12 2v10"/></svg>
            Disconnect
          </button>
          <button type="button" class="ca-btn ca-btn-primary" onclick="openChatroxConfigModal()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 15a3 3 0 100-6 3 3 0 000 6z"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
            Configure
          </button>
        <?php else: ?>
          <button type="button" class="ca-btn ca-btn-primary" onclick="openChatroxConfigModal()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
            Connect Chatrox
          </button>
        <?php endif; ?>
      </div>

    </div><!-- /ca-card -->

  </div><!-- /ca-page -->
</div>

<!-- ════════════════════════════════════════════
     Biometric Machine Configure Modal
════════════════════════════════════════════ -->
<div class="ca-overlay" id="biometricOverlay">
  <div class="ca-modal">
    <div class="ca-modal-head">
      <div class="ca-modal-logo" style="background:#fff; border-color:#e2e8f0; padding:6px;">
        <img src="<?= \App\Core\View::asset('images/Zkteco-Logo.png') ?>" alt="ZKTeco" style="width:100%; height:100%; object-fit:contain;">
      </div>
      <div class="ca-modal-head-text">
        <h3>Configure Biometric Machine</h3>
        <p>Set machine IP, socket port, communication key, and sync parameters.</p>
      </div>
    </div>

    <form id="biometricSettingsForm" onsubmit="saveBiometricSettings(event)">
      <div class="ca-modal-body">
        <div class="ca-grid-2">
          <div>
            <label for="bioNameInput">Device Name</label>
            <input type="text" id="bioNameInput" value="<?= htmlspecialchars($bioName) ?>" placeholder="e.g. Main Entrance Gate" required>
          </div>
          <div>
            <label for="bioModelInput">Device Model</label>
            <input type="text" id="bioModelInput" value="<?= htmlspecialchars($bioModel) ?>" placeholder="e.g. ZKTeco K60/ID" required>
          </div>
        </div>

        <div class="ca-grid-2">
          <div>
            <label for="bioIpInput">Device IP Address</label>
            <input type="text" id="bioIpInput" value="<?= htmlspecialchars($bioIp) ?>" placeholder="e.g. 192.168.1.200" required>
          </div>
          <div>
            <label for="bioPortInput">Port</label>
            <input type="number" id="bioPortInput" value="<?= $bioPort ?>" placeholder="4370" required>
          </div>
        </div>

        <div class="ca-grid-2">
          <div>
            <label for="bioCommKeyInput">Communication Key</label>
            <input type="number" id="bioCommKeyInput" value="<?= $bioCommKey ?>" placeholder="0">
          </div>
          <div>
            <label for="bioModeInput">Connection Mode</label>
            <select id="bioModeInput">
              <option value="UDP" <?= $bioMode === 'UDP' ? 'selected' : '' ?>>UDP (Fast Standard)</option>
              <option value="TCP" <?= $bioMode === 'TCP' ? 'selected' : '' ?>>TCP (Reliable Socket)</option>
              <option value="ADMS_PUSH" <?= $bioMode === 'ADMS_PUSH' ? 'selected' : '' ?>>ADMS Cloud Push</option>
            </select>
          </div>
        </div>

        <div class="ca-grid-2">
          <div>
            <label for="bioAutoSyncInput">Background Auto-Sync</label>
            <select id="bioAutoSyncInput">
              <option value="1" <?= $bioAutoSync === 1 ? 'selected' : '' ?>>Enabled</option>
              <option value="0" <?= $bioAutoSync === 0 ? 'selected' : '' ?>>Disabled</option>
            </select>
          </div>
          <div>
            <label for="bioIntervalInput">Sync Interval (Seconds)</label>
            <input type="number" id="bioIntervalInput" value="<?= $bioSyncInterval ?>" min="5" max="3600">
          </div>
        </div>

        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:14px; margin-top:4px;">
          <label style="margin-bottom:4px; color:#334155; font-size:11px;">ADMS HTTP Cloud Push Webserver URL</label>
          <div style="display:flex; gap:8px; align-items:center;">
            <input type="text" id="admsPushUrlInput" value="<?= htmlspecialchars($admsPushUrl) ?>" readonly style="margin:0; font-family:'Fira Code', monospace; font-size:11.5px; background:#fff;">
            <button type="button" class="ca-btn ca-btn-ghost" style="padding:8px 12px; font-size:12px;" onclick="copyAdmsPushUrl()">Copy</button>
          </div>
          <p class="ca-hint" style="margin:6px 0 0 0;">Paste this URL into your ZKTeco ADMS Webserver menu for direct live push logging.</p>
        </div>

        <div style="display:flex; gap:10px; margin-top:16px; padding-top:14px; border-top:1px solid #f1f5f9;">
          <button type="button" class="ca-btn ca-btn-ghost" style="flex:1; justify-content:center; padding:10px;" onclick="testBiometricConn()" id="btnTestBio">
            <i data-lucide="activity" size="14"></i> Test Connection
          </button>
          <button type="button" class="ca-btn ca-btn-secondary" style="flex:1; justify-content:center; padding:10px;" onclick="syncBiometricNow()" id="btnSyncBio">
            <i data-lucide="refresh-cw" size="14"></i> Sync Now
          </button>
        </div>
      </div>

      <div class="ca-modal-foot" style="justify-content: flex-end; gap: 10px;">
        <button type="button" class="ca-btn ca-btn-ghost" onclick="closeModals()">Cancel</button>
        <button type="submit" class="ca-btn ca-btn-primary" id="bioSaveBtn">
          <i data-lucide="check" size="14"></i> Save Settings
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ════════════════════════════════════════════
     Biometric Machine Disconnect Confirm Modal
════════════════════════════════════════════ -->
<div class="ca-overlay" id="biometricDisconnectOverlay">
  <div class="ca-modal">
    <div class="ca-modal-head">
      <div class="ca-modal-logo" style="background:#fef2f2; border-color:#fecaca;">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><path d="M18.36 6.64a9 9 0 11-12.73 0M12 2v10"/></svg>
      </div>
      <div class="ca-modal-head-text">
        <h3 style="color:#dc2626;">Disconnect Biometric Machine</h3>
        <p>This will disable automatic biometric sync and clear the device connection.</p>
      </div>
    </div>
    <div class="ca-modal-body">
      <p style="font-size:14px; color:#475569; line-height:1.7; margin:0;">
        The attendance machine integration will be marked as <strong>Not Connected</strong>. Automatic background polling will stop until you reconnect.
      </p>
    </div>
    <div class="ca-modal-foot">
      <button type="button" class="ca-btn ca-btn-ghost" onclick="closeModals()">Cancel</button>
      <button type="button" class="ca-btn ca-btn-danger" id="bioDisconnectBtn" onclick="doBiometricDisconnect()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.36 6.64a9 9 0 11-12.73 0M12 2v10"/></svg>
        Yes, Disconnect Machine
      </button>
    </div>
  </div>
</div>

<!-- ════════════════════════════════════════════
     Configure Chatrox Modal
════════════════════════════════════════════ -->
<div class="ca-overlay" id="connectOverlay">
  <div class="ca-modal">
    <div class="ca-modal-head">
      <div class="ca-modal-logo">
        <img src="<?= $chatroxConnected ? rtrim($chatroxUrl, '/') . '/assets/images/logo.png' : 'http://localhost/chatrox/assets/images/logo.png' ?>" alt="Chatrox"
             onerror="this.onerror=null;this.style.display='none'">
      </div>
      <div class="ca-modal-head-text">
        <h3 id="connectModalTitle">Configure Chatrox</h3>
        <p id="connectModalSub">Set the connection type and address for your Chatrox instance.</p>
      </div>
    </div>

    <div class="ca-modal-body">

      <!-- Connection type toggle -->
      <div style="margin-bottom:20px;">
        <label style="display:block; margin-bottom:8px; font-weight:600; font-size:13px; color:#374151;">Connection Type</label>
        <div class="ca-type-toggle">
          <button type="button" id="typeDomainBtn" class="ca-type-btn active" onclick="setChatroxType('domain')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
            Domain / Full URL
          </button>
          <button type="button" id="typeIpBtn" class="ca-type-btn" onclick="setChatroxType('ip')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            IP Address &amp; Port
          </button>
        </div>
      </div>

      <!-- Domain mode -->
      <div id="chatroxDomainFields">
        <label for="chatroxDomainInput">Chatrox Domain / URL</label>
        <input type="text" id="chatroxDomainInput"
               placeholder="e.g. https://chatrox.mycompany.com or http://localhost/chatrox"
               value="<?= htmlspecialchars($chatroxDomain ?: $chatroxUrl) ?>">
        <p class="ca-hint">Full URL of your self-hosted Chatrox — e.g. <code>http://192.168.1.50/chatrox</code> or <code>https://chat.example.com</code></p>
      </div>

      <!-- IP + Port mode -->
      <div id="chatroxIpFields" style="display:none;">
        <div class="ca-grid-2" style="margin-bottom:0;">
          <div>
            <label for="chatroxIpInput">IP Address</label>
            <input type="text" id="chatroxIpInput" placeholder="e.g. 192.168.1.50" value="<?= htmlspecialchars($chatroxIp) ?>">
          </div>
          <div>
            <label for="chatroxPortInput">Port</label>
            <input type="number" id="chatroxPortInput" placeholder="80" min="1" max="65535" value="<?= $chatroxPort ?: '' ?>">
          </div>
        </div>
        <p class="ca-hint" style="margin-top:8px;">The Chatrox server will be reached at <code>http://&lt;IP&gt;:&lt;Port&gt;</code></p>
      </div>

    </div>

    <div class="ca-modal-foot">
      <button type="button" class="ca-btn ca-btn-ghost" onclick="closeModals()">Cancel</button>
      <button type="button" class="ca-btn ca-btn-primary" id="connectSaveBtn" onclick="saveChatroxConfig()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
        Save Settings
      </button>
    </div>
  </div>
</div>

<!-- ════════════════════════════════════════════
     Disconnect Chatrox Confirm Modal
════════════════════════════════════════════ -->
<div class="ca-overlay" id="disconnectOverlay">
  <div class="ca-modal">
    <div class="ca-modal-head">
      <div class="ca-modal-logo" style="background:#fef2f2; border-color:#fecaca;">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><path d="M18.36 6.64a9 9 0 11-12.73 0M12 2v10"/></svg>
      </div>
      <div class="ca-modal-head-text">
        <h3 style="color:#dc2626;">Disconnect Chatrox</h3>
        <p>This will remove the Chatrox integration.</p>
      </div>
    </div>
    <div class="ca-modal-body">
      <p style="font-size:14px; color:#475569; line-height:1.7; margin:0;">
        The <strong>Share on Chatrox</strong> button will disappear from spreadsheet share modals.
        You can reconnect at any time.
      </p>
    </div>
    <div class="ca-modal-foot">
      <button type="button" class="ca-btn ca-btn-ghost" onclick="closeModals()">Cancel</button>
      <button type="button" class="ca-btn ca-btn-danger" id="disconnectBtn" onclick="doDisconnect()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.36 6.64a9 9 0 11-12.73 0M12 2v10"/></svg>
        Yes, Disconnect
      </button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const API = '<?= \App\Core\View::api('connected_apps_api.php') ?>';

function openBiometricModal() {
  closeModals();
  document.getElementById('biometricOverlay').classList.add('active');
  if (typeof lucide !== 'undefined') lucide.createIcons();
}

function openBiometricDisconnectModal() {
  closeModals();
  document.getElementById('biometricDisconnectOverlay').classList.add('active');
}

async function doBiometricDisconnect() {
  const btn = document.getElementById('bioDisconnectBtn');
  btn.textContent = 'Disconnecting…';
  btn.disabled = true;
  try {
    const fd = new FormData();
    fd.append('action', 'disconnect_biometric');
    const res  = await fetch(API, { method: 'POST', body: fd });
    const json = await res.json();
    if (json.status === 'success') { location.reload(); }
    else { alert(json.message || 'Failed to disconnect.'); }
  } catch { alert('Network error.'); }
  finally { btn.textContent = 'Yes, Disconnect Machine'; btn.disabled = false; }
}

function copyAdmsPushUrl() {
  const input = document.getElementById('admsPushUrlInput');
  input.select();
  navigator.clipboard.writeText(input.value);
  Swal.fire({
    icon: 'success',
    title: 'Copied!',
    text: 'ADMS Push Webserver URL copied to clipboard.',
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 2500
  });
}

async function saveBiometricSettings(e) {
  e.preventDefault();
  const btn = document.getElementById('bioSaveBtn');
  const orig = btn.innerHTML;
  btn.innerHTML = '<i data-lucide="loader-2" class="spin" size="14"></i> Saving…';
  btn.disabled = true;

  try {
    const fd = new FormData();
    fd.append('action', 'save_biometric_settings');
    fd.append('biometric_name', document.getElementById('bioNameInput').value.trim());
    fd.append('biometric_model', document.getElementById('bioModelInput').value.trim());
    fd.append('biometric_ip', document.getElementById('bioIpInput').value.trim());
    fd.append('biometric_port', document.getElementById('bioPortInput').value);
    fd.append('biometric_comm_key', document.getElementById('bioCommKeyInput').value);
    fd.append('biometric_mode', document.getElementById('bioModeInput').value);
    fd.append('biometric_auto_sync', document.getElementById('bioAutoSyncInput').value);
    fd.append('biometric_sync_interval', document.getElementById('bioIntervalInput').value);

    const res = await fetch(API, { method: 'POST', body: fd });
    const json = await res.json();

    if (json.status === 'success') {
      Swal.fire({
        icon: 'success',
        title: 'Settings Saved',
        text: json.message,
        confirmButtonColor: '#6c4cf1'
      }).then(() => location.reload());
    } else {
      Swal.fire('Error', json.message || 'Failed to save biometric settings.', 'error');
    }
  } catch (err) {
    Swal.fire('Error', 'Network error occurred.', 'error');
  } finally {
    btn.innerHTML = orig;
    btn.disabled = false;
  }
}

async function testBiometricConn() {
  const btn = document.getElementById('btnTestBio');
  const orig = btn.innerHTML;
  btn.innerHTML = '<i data-lucide="loader-2" class="spin" size="15"></i> Testing…';
  btn.disabled = true;

  try {
    const fd = new FormData();
    fd.append('action', 'test_biometric_connection');
    fd.append('biometric_ip', document.getElementById('bioIpInput')?.value || '<?= htmlspecialchars($bioIp) ?>');
    fd.append('biometric_port', document.getElementById('bioPortInput')?.value || '<?= $bioPort ?>');
    fd.append('biometric_comm_key', document.getElementById('bioCommKeyInput')?.value || '<?= $bioCommKey ?>');
    fd.append('biometric_mode', document.getElementById('bioModeInput')?.value || '<?= htmlspecialchars($bioMode) ?>');

    const res = await fetch(API, { method: 'POST', body: fd });
    const json = await res.json();

    if (json.status === 'success') {
      Swal.fire({
        icon: 'success',
        title: 'Connection Successful!',
        html: `<strong>${json.message}</strong><br><br><span style="font-size:13px; color:#64748b;">Device total punches available: <strong>${json.data.log_count}</strong></span>`,
        confirmButtonColor: '#6c4cf1'
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Connection Failed',
        text: json.message,
        confirmButtonColor: '#ef4444'
      });
    }
  } catch (err) {
    Swal.fire('Error', 'Network error while testing connection.', 'error');
  } finally {
    btn.innerHTML = orig;
    btn.disabled = false;
    if (typeof lucide !== 'undefined') lucide.createIcons();
  }
}

async function syncBiometricNow() {
  const btn = document.getElementById('btnSyncBio');
  const orig = btn.innerHTML;
  btn.innerHTML = '<i data-lucide="loader-2" class="spin" size="15"></i> Syncing…';
  btn.disabled = true;

  try {
    const fd = new FormData();
    fd.append('action', 'trigger_biometric_sync');

    const res = await fetch(API, { method: 'POST', body: fd });
    const json = await res.json();

    if (json.status === 'success') {
      Swal.fire({
        icon: 'success',
        title: 'Attendance Synced!',
        text: json.message,
        confirmButtonColor: '#6c4cf1'
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Sync Warning',
        text: json.message,
        confirmButtonColor: '#ef4444'
      });
    }
  } catch (err) {
    Swal.fire('Error', 'Network error while syncing attendance.', 'error');
  } finally {
    btn.innerHTML = orig;
    btn.disabled = false;
    if (typeof lucide !== 'undefined') lucide.createIcons();
  }
}

// Current active connection type in the modal
let _chatroxType = '<?= htmlspecialchars($chatroxType) ?>';

function setChatroxType(type) {
  _chatroxType = type;
  document.getElementById('typeDomainBtn').classList.toggle('active', type === 'domain');
  document.getElementById('typeIpBtn').classList.toggle('active', type === 'ip');
  document.getElementById('chatroxDomainFields').style.display = (type === 'domain') ? '' : 'none';
  document.getElementById('chatroxIpFields').style.display     = (type === 'ip')     ? '' : 'none';
}

function openChatroxAppModal(mode) {
  closeModals();
  if (mode === 'disconnect') {
    document.getElementById('disconnectOverlay').classList.add('active');
    return;
  }
}

function openChatroxConfigModal() {
  closeModals();
  // Restore saved type
  setChatroxType(_chatroxType);
  document.getElementById('connectOverlay').classList.add('active');
  setTimeout(() => {
    const f = _chatroxType === 'ip'
      ? document.getElementById('chatroxIpInput')
      : document.getElementById('chatroxDomainInput');
    if (f) f.focus();
  }, 250);
}

function closeModals() {
  document.querySelectorAll('.ca-overlay').forEach(el => el.classList.remove('active'));
}
document.querySelectorAll('.ca-overlay').forEach(el => {
  el.addEventListener('click', e => { if (e.target === el) closeModals(); });
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModals(); });

async function saveChatroxConfig() {
  const btn  = document.getElementById('connectSaveBtn');
  const orig = btn.innerHTML;

  let finalUrl = '';
  let ipVal = '', portVal = '', domainVal = '';

  if (_chatroxType === 'ip') {
    ipVal   = document.getElementById('chatroxIpInput').value.trim();
    portVal = document.getElementById('chatroxPortInput').value.trim();
    if (!ipVal) {
      document.getElementById('chatroxIpInput').style.borderColor = '#f87171';
      document.getElementById('chatroxIpInput').focus();
      return;
    }
    document.getElementById('chatroxIpInput').style.borderColor = '';
    finalUrl = 'http://' + ipVal + (portVal && portVal !== '80' ? ':' + portVal : '');
  } else {
    domainVal = document.getElementById('chatroxDomainInput').value.trim().replace(/\/+$/, '');
    if (!domainVal) {
      document.getElementById('chatroxDomainInput').style.borderColor = '#f87171';
      document.getElementById('chatroxDomainInput').focus();
      return;
    }
    document.getElementById('chatroxDomainInput').style.borderColor = '';
    finalUrl = domainVal;
  }

  btn.innerHTML = '<svg class="spin" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 11-4.22-7.66"/></svg> Saving…';
  btn.disabled  = true;

  try {
    const saves = [
      { key: 'chatrox_url',    value: finalUrl },
      { key: 'chatrox_type',   value: _chatroxType },
      { key: 'chatrox_domain', value: domainVal },
      { key: 'chatrox_ip',     value: ipVal },
      { key: 'chatrox_port',   value: portVal || '80' }
    ];
    for (const s of saves) {
      const fd = new FormData();
      fd.append('action', 'save_integration');
      fd.append('key', s.key);
      fd.append('value', s.value);
      await fetch(API, { method: 'POST', body: fd });
    }
    location.reload();
  } catch {
    Swal.fire('Error', 'Network error. Please try again.', 'error');
  } finally {
    btn.innerHTML = orig;
    btn.disabled  = false;
  }
}

async function doDisconnect() {
  const btn = document.getElementById('disconnectBtn');
  btn.textContent = 'Disconnecting…';
  btn.disabled = true;
  try {
    const fd = new FormData();
    fd.append('action', 'save_integration');
    fd.append('key', 'chatrox_url');
    fd.append('value', '');
    const res  = await fetch(API, { method: 'POST', body: fd });
    const json = await res.json();
    if (json.status === 'success') { location.reload(); }
    else { alert(json.message || 'Failed to disconnect.'); }
  } catch { alert('Network error.'); }
  finally { btn.textContent = 'Yes, Disconnect'; btn.disabled = false; }
}
</script>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
.spin { animation: spin .7s linear infinite; display: inline-block; }
</style>

<?php include __DIR__ . '/../partials/admin/footer.php'; ?>

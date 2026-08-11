<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?? 'Google Sheets - Editor' ?></title>

  <!-- Google Fonts & Material Symbols -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=block" />
  <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;700&family=Comic+Neue:ital,wght@0,400;0,700;1,400&family=Courier+Prime:ital,wght@0,400;0,700;1,400&family=Inter:wght@300;400;500;600;700&family=Lora:ital,wght@0,400;0,600;1,400&family=Montserrat:wght@400;600;700&family=Open+Sans:wght@400;600;700&family=Oswald:wght@400;600&family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Poppins:wght@400;500;600;700&family=Roboto+Mono:wght@400;500&family=Roboto:wght@300;400;500;700&family=Source+Code+Pro:wght@400;600&family=Spectral:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">

  <!-- Sheet Engine Stylesheet -->
  <link rel="stylesheet" href="<?= \App\Core\View::asset('css/sheets-style.css') ?>">

  <script>
    window.APP_BASE_URL = "<?= (defined('BASE_PATH') ? BASE_PATH : '') ?>";
    window.CURRENT_USER = {
      email: "<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>",
      name: "<?= htmlspecialchars($_SESSION['user_name'] ?? 'HRM User') ?>"
    };
    window.CSRF_TOKEN = "<?= \App\Helpers\CSRFToken::generate() ?>";
    <?php
      $db = \App\Core\Database::connection();
      $stmt = $db->query("SELECT meta_value FROM settings WHERE meta_key = 'chatrox_url' LIMIT 1");
      $chatroxUrlVal = $stmt ? (string)$stmt->fetchColumn() : '';
    ?>
    window.CHATROX_URL = "<?= htmlspecialchars(rtrim($chatroxUrlVal, '/')) ?>";
    
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
</head>
<body style="margin:0; padding:0; font-family:'Google Sans', 'Inter', sans-serif; background:#ffffff; color:#202124; overflow:hidden;">

  <!-- Official Google Sheets Top Loading Line Bar -->
  <div id="topLoadingLine">
    <div class="loading-bar-inner"></div>
  </div>

  <div id="appContainer">
    <?php require __DIR__ . '/../partials/sheets/topbar.php'; ?>
    <?php require __DIR__ . '/../partials/sheets/toolbar.php'; ?>
    <?php require __DIR__ . '/../partials/sheets/formulabar.php'; ?>
    <?php require __DIR__ . '/../partials/sheets/grid.php'; ?>
    <?php require __DIR__ . '/../partials/sheets/bottombar.php'; ?>
    <?php require __DIR__ . '/../partials/sheets/modals.php'; ?>
  </div>

  <!-- Sheets Core Script -->
  <script src="<?= \App\Core\View::asset('js/sheets-app.js') ?>"></script>
</body>
</html>

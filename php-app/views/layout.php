<?php

declare(strict_types=1);

use App\Domain\RoomConfig;

$titleText = isset($title) && is_string($title) ? $title : 'LangerThuisinHuis | Woonveiligheid via foto-assessment';
$currentPath = isset($currentPath) && is_string($currentPath) ? $currentPath : '/';
$queryRoom = isset($queryRoom) && is_string($queryRoom) ? $queryRoom : '';
$activeRoomKey = RoomConfig::activeRoomKey($currentPath, $queryRoom);
$isStartActive = $currentPath === '/' || $currentPath === '/assessment';
$isAdviceActive = $currentPath === '/contact';
$navItems = isset($navItems) && is_array($navItems) ? $navItems : RoomConfig::navItems();
?>
<!doctype html>
<html lang="nl" class="text-scale-normal">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= htmlspecialchars($titleText, ENT_QUOTES) ?></title>
    <meta name="description" content="Inzicht in val- en struikelrisico's, met direct toepasbare verbeterpunten." />
    <link rel="stylesheet" href="/assets/css/app.css" />
    <script>
      (function () {
        try {
          var key = 'lthih_text_scale';
          var value = window.localStorage.getItem(key);
          var scale = value === 'large' ? 'large' : 'normal';
          var root = document.documentElement;
          root.classList.remove('text-scale-normal', 'text-scale-large');
          root.classList.add(scale === 'large' ? 'text-scale-large' : 'text-scale-normal');
        } catch (error) {
          document.documentElement.classList.remove('text-scale-large');
          document.documentElement.classList.add('text-scale-normal');
        }
      })();
    </script>
  </head>
  <body>
    <div class="site-shell">
      <header class="app-header-shell no-print" role="banner">
        <div class="topnav-inner">
          <a href="/assessment" class="topnav-logo" aria-label="Naar start van foto-assessment">
            <img src="/ltih-logo.png" alt="LangerThuisinHuis" width="228" height="140" class="topnav-logo-image" />
          </a>

          <nav class="topnav-desktop" aria-label="Hoofdnavigatie">
            <a href="/assessment" class="topnav-link <?= $isStartActive ? 'active' : '' ?>">Start</a>

            <div class="topnav-dropdown" data-desktop-dropdown>
              <button type="button" class="topnav-link topnav-dropdown-trigger <?= $activeRoomKey ? 'active' : '' ?>" aria-expanded="false" aria-controls="desktop-rooms-menu">
                <span>Veilig thuis</span>
                <svg viewBox="0 0 16 16" aria-hidden="true" class="topnav-chevron">
                  <path d="M3.6 5.9L8 10.1l4.4-4.2" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
              </button>
              <div id="desktop-rooms-menu" class="topnav-dropdown-menu" aria-label="Veilig thuis menu">
                <?php foreach ($navItems as $room): ?>
                  <a href="<?= htmlspecialchars((string) $room['href'], ENT_QUOTES) ?>" class="topnav-dropdown-item <?= $activeRoomKey === $room['roomKey'] ? 'active' : '' ?>">
                    <span class="topnav-dropdown-title"><?= htmlspecialchars((string) $room['label'], ENT_QUOTES) ?></span>
                    <span class="topnav-dropdown-hint"><?= htmlspecialchars((string) $room['hint'], ENT_QUOTES) ?></span>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>

            <a href="/contact" class="topnav-link <?= $isAdviceActive ? 'active' : '' ?>">Adviesgesprek</a>
          </nav>

          <div class="topnav-actions">
            <div class="text-size-control topnav-text-size-desktop" data-text-size-root>
              <span class="text-size-control-label">Tekst</span>
              <div class="text-size-control-group" role="group" aria-label="Tekst aanpassen">
                <button type="button" class="text-size-control-btn" data-text-scale="normal" aria-label="Tekstgrootte normaal">A</button>
                <button type="button" class="text-size-control-btn" data-text-scale="large" aria-label="Tekstgrootte groter">A+</button>
              </div>
            </div>

            <a href="<?= htmlspecialchars((string) $mainSiteUrl, ENT_QUOTES) ?>" target="_blank" rel="noopener noreferrer" title="Opent in nieuw tabblad" class="topnav-external-link" aria-label="LangerThuisinHuis.nl (opent in nieuw tabblad)">
              <span>LangerThuisinHuis.nl</span>
              <svg viewBox="0 0 16 16" aria-hidden="true" class="topnav-external-icon">
                <path d="M6 3.5h6.5V10m-.4-6.1L5.3 10.7M13 12.5H3V2.5h4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
              </svg>
            </a>
          </div>

          <button type="button" class="topnav-mobile-toggle" aria-expanded="false" aria-controls="mobile-main-menu" aria-label="Open navigatiemenu" data-mobile-menu-toggle>
            <span class="topnav-mobile-toggle-bars" aria-hidden="true"></span>
            <span>Menu</span>
          </button>
        </div>

        <button type="button" class="topnav-mobile-overlay" aria-label="Sluit mobiel menu" data-mobile-overlay></button>

        <aside id="mobile-main-menu" class="topnav-mobile-panel" aria-hidden="true" data-mobile-panel>
          <div class="topnav-mobile-panel-header">
            <strong>Navigatie</strong>
            <button type="button" class="topnav-mobile-close" data-mobile-menu-close aria-label="Sluit menu">Sluiten</button>
          </div>

          <div class="text-size-control topnav-text-size-mobile" data-text-size-root>
            <span class="text-size-control-label">Tekstgrootte</span>
            <div class="text-size-control-group" role="group" aria-label="Tekst aanpassen">
              <button type="button" class="text-size-control-btn" data-text-scale="normal" aria-label="Tekstgrootte normaal">A</button>
              <button type="button" class="text-size-control-btn" data-text-scale="large" aria-label="Tekstgrootte groter">A+</button>
            </div>
          </div>

          <nav class="topnav-mobile-links" aria-label="Mobiele hoofdnavigatie">
            <a href="/assessment" class="topnav-mobile-link <?= $isStartActive ? 'active' : '' ?>">Start</a>
            <?php foreach ($navItems as $room): ?>
              <a href="<?= htmlspecialchars((string) $room['href'], ENT_QUOTES) ?>" class="topnav-mobile-room-item <?= $activeRoomKey === $room['roomKey'] ? 'active' : '' ?>">
                <span class="topnav-mobile-room-title"><?= htmlspecialchars((string) $room['label'], ENT_QUOTES) ?></span>
                <span class="topnav-mobile-room-hint"><?= htmlspecialchars((string) $room['hint'], ENT_QUOTES) ?></span>
              </a>
            <?php endforeach; ?>
            <a href="/contact" class="topnav-mobile-link <?= $isAdviceActive ? 'active' : '' ?>">Adviesgesprek</a>
            <a href="<?= htmlspecialchars((string) $mainSiteUrl, ENT_QUOTES) ?>" target="_blank" rel="noopener noreferrer" title="Opent in nieuw tabblad" class="topnav-mobile-external">
              <span>LangerThuisinHuis.nl</span>
            </a>
          </nav>
        </aside>
      </header>

      <main class="container">
        <?= $content ?>
      </main>
    </div>

    <script src="/assets/js/app.js" defer></script>
  </body>
</html>

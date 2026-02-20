<?php
/** @var string $roomType */
/** @var array<string,mixed> $roomConfig */
?>
<form id="assessment-wizard" class="grid" style="gap: 1.2rem;" data-room-type="<?= htmlspecialchars($roomType, ENT_QUOTES) ?>" data-min-photos="<?= (int) $roomConfig['minPhotos'] ?>" data-max-photos="<?= (int) $roomConfig['maxPhotos'] ?>">
  <nav class="assessment-stepper" aria-label="Voortgang foto-assessment">
    <ol class="assessment-stepper-list">
      <li class="assessment-step completed"><span class="assessment-step-index">1</span><span class="assessment-step-label">Kies ruimte</span></li>
      <li class="assessment-step active"><span class="assessment-step-index">2</span><span class="assessment-step-label">Upload foto's</span></li>
      <li class="assessment-step upcoming"><span class="assessment-step-index">3</span><span class="assessment-step-label">Resultaat</span></li>
    </ol>
  </nav>

  <section class="card">
    <h1><?= htmlspecialchars((string) $roomConfig['title'], ENT_QUOTES) ?></h1>
    <p class="muted"><?= htmlspecialchars((string) $roomConfig['subtitle'], ENT_QUOTES) ?></p>

    <div class="upload-progress" aria-live="polite">
      <strong><span id="selected-count">0</span>/<?= (int) $roomConfig['maxPhotos'] ?> foto's toegevoegd</strong>
      <span id="progress-hint">Minimaal <?= (int) $roomConfig['minPhotos'] ?> foto's nodig om door te gaan.</span>
    </div>

    <div class="prompt-section">
      <p class="prompt-section-title">Minimaal nodig (<?= (int) $roomConfig['minPhotos'] ?> foto's)</p>
      <ul class="prompt-list">
        <?php foreach ($roomConfig['items'] as $item): ?>
          <?php if (!($item['required'] ?? false)) { continue; } ?>
          <li data-item-id="<?= htmlspecialchars((string) $item['id'], ENT_QUOTES) ?>">
            <div class="prompt-item-row">
              <div class="prompt-item-copy">
                <span class="prompt-item-label"><?= htmlspecialchars((string) $item['label'], ENT_QUOTES) ?></span>
                <span class="prompt-item-tip"><?= htmlspecialchars((string) $item['tip'], ENT_QUOTES) ?></span>
              </div>
              <div class="prompt-item-controls">
                <img src="" alt="" class="prompt-thumb" hidden />
                <button type="button" class="btn btn-secondary file-picker-btn js-open-camera">Maak foto</button>
                <button type="button" class="prompt-link-btn js-open-gallery">Kies uit galerij</button>
                <button type="button" class="btn btn-secondary prompt-action-btn js-remove" hidden>Verwijder</button>

                <input class="file-picker-input js-camera-input" type="file" accept="image/*" capture="environment" />
                <input class="file-picker-input js-gallery-input" type="file" accept="image/png,image/jpeg" />
              </div>
            </div>
            <p class="prompt-item-error" hidden></p>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="prompt-section">
      <p class="prompt-section-title">Aanbevolen (voor beter advies)</p>
      <ul class="prompt-list">
        <?php foreach ($roomConfig['items'] as $item): ?>
          <?php if (($item['required'] ?? false)) { continue; } ?>
          <li data-item-id="<?= htmlspecialchars((string) $item['id'], ENT_QUOTES) ?>">
            <div class="prompt-item-row">
              <div class="prompt-item-copy">
                <span class="prompt-item-label"><?= htmlspecialchars((string) $item['label'], ENT_QUOTES) ?></span>
                <span class="prompt-item-tip"><?= htmlspecialchars((string) $item['tip'], ENT_QUOTES) ?></span>
              </div>
              <div class="prompt-item-controls">
                <img src="" alt="" class="prompt-thumb" hidden />
                <button type="button" class="btn btn-secondary file-picker-btn js-open-camera">Maak foto</button>
                <button type="button" class="prompt-link-btn js-open-gallery">Kies uit galerij</button>
                <button type="button" class="btn btn-secondary prompt-action-btn js-remove" hidden>Verwijder</button>

                <input class="file-picker-input js-camera-input" type="file" accept="image/*" capture="environment" />
                <input class="file-picker-input js-gallery-input" type="file" accept="image/png,image/jpeg" />
              </div>
            </div>
            <p class="prompt-item-error" hidden></p>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <p class="muted" style="margin-top: 0.85rem; margin-bottom: 0;">Je foto's worden alleen gebruikt voor dit advies.</p>
  </section>

  <section class="card">
    <label style="display: flex; gap: 0.6rem; align-items: flex-start;">
      <input type="checkbox" id="consent-checkbox" />
      <span>Ik geef toestemming om deze foto's tijdelijk te verwerken voor een woonveiligheidsanalyse.</span>
    </label>
    <p class="muted" style="margin-bottom: 0;">
      Beelden worden geoptimaliseerd en tijdelijk opgeslagen. Je kunt ze na afloop verwijderen.
    </p>
  </section>

  <p class="error" id="wizard-error" hidden></p>
  <button class="btn" id="wizard-submit" type="submit" disabled>Analyse starten</button>
</form>

<script src="/assets/js/wizard.js" defer></script>

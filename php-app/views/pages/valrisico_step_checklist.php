<div class="grid" style="gap: 1rem;">
  <?php $currentStep = (int) $stepNumber; $totalSteps = (int) $totalSteps; include __DIR__ . '/../partials/valrisico_stepper.php'; ?>

  <form method="post" action="/valrisico/antwoord" class="card valrisico-question-form">
    <input type="hidden" name="action" value="answer_checklist" />

    <p class="kicker">Valrisico check</p>
    <h1>Herkent u één of meer van deze punten?</h1>

    <p class="muted">U kunt meerdere punten aanvinken. Als niets past, laat alles uit.</p>

    <div class="valrisico-checklist-group">
      <?php foreach ($riskFactors as $factor): ?>
        <?php $factorKey = (string) $factor['key']; ?>
        <label class="valrisico-checklist-item">
          <input type="checkbox" name="risk_factors[]" value="<?= htmlspecialchars($factorKey, ENT_QUOTES) ?>" <?= !empty($selectedFactors[$factorKey]) ? 'checked' : '' ?> />
          <span>
            <strong><?= htmlspecialchars((string) $factor['label'], ENT_QUOTES) ?></strong>
            <small><?= htmlspecialchars((string) $factor['why'], ENT_QUOTES) ?></small>
          </span>
        </label>
      <?php endforeach; ?>
    </div>

    <details class="valrisico-why">
      <summary aria-label="Waarom vragen we dit?">Waarom vragen we dit?</summary>
      <p>We stellen deze vragen omdat deze punten vaak samenhangen met een hogere kans op vallen.</p>
      <p>Als u één of meer punten herkent, is extra advies of ondersteuning soms verstandig.</p>
      <p>Herkent u niets? Laat alles gerust leeg.</p>
    </details>

    <div class="valrisico-fixed-actions">
      <a class="btn btn-secondary" href="/valrisico/stap/4">Terug</a>
      <button class="btn" type="submit">Verder</button>
    </div>
  </form>
</div>

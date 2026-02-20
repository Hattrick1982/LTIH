<div class="grid" style="gap:1rem;">
  <section class="card">
    <h1>Valrisico samenvatting</h1>
    <p class="muted">Uitslag: <?= htmlspecialchars((string) strtoupper((string) $riskLevel), ENT_QUOTES) ?></p>
    <p><strong>Disclaimer:</strong> Deze uitslag is een indicatie en geen medische diagnose.</p>
  </section>

  <section class="card">
    <h2>Wat kunt u nu doen?</h2>
    <ul>
      <?php foreach ($content['checklist'] as $item): ?>
        <li><?= htmlspecialchars((string) $item, ENT_QUOTES) ?></li>
      <?php endforeach; ?>
    </ul>
  </section>

  <section class="card no-print" style="display:flex; gap:0.6rem;">
    <button type="button" class="btn" data-print-now>Print nu</button>
    <a href="/valrisico/resultaat" class="btn btn-secondary">Terug</a>
  </section>
</div>

<script src="/assets/js/result.js" defer></script>

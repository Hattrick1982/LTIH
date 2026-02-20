<div class="grid" style="gap: 1rem;">
  <section class="card">
    <h1><?= htmlspecialchars((string) $content['title'], ENT_QUOTES) ?></h1>
    <p>
      <span class="badge <?= htmlspecialchars((string) $content['class'], ENT_QUOTES) ?>"><?= htmlspecialchars((string) $content['badge'], ENT_QUOTES) ?></span>
    </p>
    <p><?= htmlspecialchars((string) $content['intro'], ENT_QUOTES) ?></p>
    <p class="muted"><strong>Disclaimer:</strong> Deze uitslag is een indicatie en geen medische diagnose.</p>

    <section class="valrisico-advice-note">
      <h2>Wilt u weten wat voor u helpt?</h2>
      <p>
        Plan een kort adviesgesprek. Samen kijken we naar oorzaken (zoals balans, kracht, medicatie of veiligheid in huis)
        en maken we een persoonlijk plan.
      </p>
    </section>

    <?php if (!empty($looptest['seconds'])): ?>
      <p class="muted">Looptest: <?= htmlspecialchars((string) $looptest['seconds'], ENT_QUOTES) ?> seconden.</p>
    <?php elseif (!empty($looptest['skipped']) && ($preliminaryRisk ?? '') === 'moderate'): ?>
      <p class="muted">Looptest is overgeslagen. U krijgt alsnog volledig advies.</p>
    <?php endif; ?>

    <div class="valrisico-support-links">
      <?php if (!empty($supportPhoneTel)): ?>
        <a href="<?= htmlspecialchars((string) $supportPhoneTel, ENT_QUOTES) ?>" class="btn">Plan een kort adviesgesprek</a>
      <?php else: ?>
        <a href="/contact" class="btn">Plan een kort adviesgesprek</a>
      <?php endif; ?>
    </div>
  </section>

  <section class="card">
    <h2>Wat betekent dit?</h2>
    <p><?= htmlspecialchars((string) $content['meaning'], ENT_QUOTES) ?></p>
  </section>

  <section class="card">
    <h2>Wat kunt u nu doen?</h2>
    <ul>
      <?php foreach ($content['checklist'] as $item): ?>
        <li><?= htmlspecialchars((string) $item, ENT_QUOTES) ?></li>
      <?php endforeach; ?>
    </ul>
  </section>

  <?php if (!empty($content['today_actions'])): ?>
    <section class="card">
      <h2>Vandaag al</h2>
      <ul>
        <?php foreach ($content['today_actions'] as $item): ?>
          <li><?= htmlspecialchars((string) $item, ENT_QUOTES) ?></li>
        <?php endforeach; ?>
      </ul>
    </section>
  <?php endif; ?>

  <section class="card">
    <h2>Oefeningen voor thuis</h2>
    <p class="muted">Start met 5 minuten, 3 dagen per week. Rustig tempo is prima.</p>
    <a href="/valrisico/oefeningen" class="btn">Start 5 minuten</a>
  </section>

  <section class="card">
    <h2>Veiligheid in huis</h2>
    <p class="muted">Pak risico’s in huis stap voor stap aan met foto-checks per ruimte.</p>

    <div class="valrisico-cta-grid">
      <?php foreach ($content['housing_ctas'] as $cta): ?>
        <a href="<?= htmlspecialchars((string) $cta['href'], ENT_QUOTES) ?>" class="btn <?= empty($cta['primary']) ? 'btn-secondary' : '' ?>">
          <?= htmlspecialchars((string) $cta['label'], ENT_QUOTES) ?>
        </a>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="card">
    <h2>Wanneer hulp inschakelen</h2>
    <details class="valrisico-why" open>
      <summary>Bekijk advies</summary>
      <ul>
        <?php foreach ($content['support_advice'] as $adviceItem): ?>
          <li><?= htmlspecialchars((string) $adviceItem, ENT_QUOTES) ?></li>
        <?php endforeach; ?>
      </ul>
    </details>
  </section>

  <section class="card no-print valrisico-result-actions">
    <a href="/valrisico/print" class="btn btn-secondary">Print samenvatting</a>
    <a href="/valrisico" class="btn btn-secondary">Doe de check opnieuw</a>

    <form method="post" action="/valrisico/reset" style="margin: 0;">
      <button class="btn btn-secondary" type="submit">Verwijder mijn gegevens</button>
    </form>
  </section>
</div>

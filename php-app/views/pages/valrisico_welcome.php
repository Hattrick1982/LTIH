<div class="grid" style="gap: 1rem;">
  <section class="hero valrisico-hero">
    <p class="kicker">Veilig thuis</p>
    <h1>Valpreventie check</h1>
    <p>Samen kijken we hoe veilig u zich thuis kunt bewegen.</p>
    <p>Beantwoord een paar vragen en ontvang meteen advies en rustige oefeningen.</p>
    <p class="valrisico-time-inline">⏱ 2 tot 3 min</p>

    <form method="post" action="/valrisico/antwoord" class="valrisico-welcome-form">
      <input type="hidden" name="action" value="start" />
      <input type="hidden" name="language" value="nl" />

      <label class="valrisico-toggle">
        <input type="checkbox" name="caregiver_mode" value="1" <?= !empty($caregiverMode) ? 'checked' : '' ?> />
        <span>
          <strong>Ik help iemand anders</strong>
          <small>Bijvoorbeeld als u helpt als mantelzorger of familie.</small>
        </span>
      </label>

      <div class="valrisico-actions-row">
        <a href="/valrisico/uitleg" class="btn btn-secondary">Hoe werkt het?</a>
        <button class="btn" type="submit">Start</button>
      </div>
    </form>

    <section class="valrisico-benefits-card" aria-label="Wat u krijgt">
      <p class="valrisico-benefits-kicker">Wat u krijgt</p>
      <ul class="valrisico-benefits-list">
        <li class="valrisico-benefit-item">
          <span class="valrisico-benefit-icon" aria-hidden="true">✓</span>
          <span>Uitslag</span>
        </li>
        <li class="valrisico-benefit-item">
          <span class="valrisico-benefit-icon" aria-hidden="true">⌂</span>
          <span>Tips voor thuis</span>
        </li>
        <li class="valrisico-benefit-item">
          <span class="valrisico-benefit-icon" aria-hidden="true">↺</span>
          <span>Korte oefeningen (5 min)</span>
        </li>
      </ul>
    </section>
  </section>
</div>

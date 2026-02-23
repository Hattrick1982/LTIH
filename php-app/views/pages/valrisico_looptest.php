<div class="grid" style="gap: 1rem;">
  <nav class="assessment-stepper" aria-label="Voortgang valrisico check">
    <ol class="assessment-stepper-list">
      <li class="assessment-step completed"><span class="assessment-step-index">1</span><span class="assessment-step-label">Vragen</span></li>
      <li class="assessment-step active"><span class="assessment-step-index">2</span><span class="assessment-step-label">Looptest (optioneel)</span></li>
      <li class="assessment-step upcoming"><span class="assessment-step-index">3</span><span class="assessment-step-label">Uitslag</span></li>
    </ol>
  </nav>

  <?php if ($phase === 'veiligheid'): ?>
    <form method="post" action="/valrisico/looptest" class="card valrisico-question-form">
      <input type="hidden" name="action" value="save_choice" />
      <h1>Looptest (optioneel)</h1>
      <p>Met deze korte test kunnen we uw advies verfijnen. U kunt dit ook overslaan.</p>

      <section class="valrisico-safety-callout" aria-label="Veiligheid">
        <ul>
          <li>Doe dit alleen als u zich veilig voelt.</li>
          <li>Vraag bij voorkeur hulp van iemand in de buurt (mantelzorger, familie of buur).</li>
          <li>Gebruik uw stok of rollator als u die normaal ook gebruikt.</li>
          <li>Stop meteen bij duizeligheid, pijn of onzekerheid.</li>
        </ul>
      </section>

      <fieldset class="valrisico-answer-group" role="radiogroup" aria-label="Met of zonder hulp">
        <label class="valrisico-answer-option">
          <input type="radio" name="assist_mode" value="with_help" <?= ($assistMode ?? 'with_help') === 'with_help' ? 'checked' : '' ?> required data-looptest-mode />
          <span>Ik doe dit met hulp (aanbevolen)</span>
        </label>
        <label class="valrisico-answer-option">
          <input type="radio" name="assist_mode" value="alone" <?= ($assistMode ?? '') === 'alone' ? 'checked' : '' ?> data-looptest-mode />
          <span>Ik ben alleen</span>
        </label>
      </fieldset>

      <p class="muted" id="looptest-alone-note" <?= ($assistMode ?? '') === 'alone' ? '' : 'hidden' ?>>Alleen thuis? Sla de test gerust over. U krijgt alsnog advies.</p>

      <div class="valrisico-fixed-actions">
        <a class="btn btn-secondary" href="/valrisico/stap/5">Terug</a>
        <button class="btn" type="submit">Verder</button>
      </div>

      <div class="valrisico-fullwidth-action">
        <button class="btn btn-secondary" type="submit" name="action" value="skip">Sla over en ga naar resultaat</button>
      </div>
    </form>
  <?php elseif ($phase === 'instructie'): ?>
    <form method="post" action="/valrisico/looptest" class="card valrisico-question-form">
      <input type="hidden" name="action" value="to_input" />
      <h1>Zo doet u de test</h1>
      <ul>
        <li>Meet 4 meter (gang of woonkamer).</li>
        <li>Zet een startpunt en eindpunt neer (tape of voorwerp).</li>
        <li>Loop in uw normale tempo. Niet haasten.</li>
        <li>Start de tijd zodra uw eerste voet over de startlijn gaat.</li>
        <li>Stop de tijd zodra uw eerste voet over de eindlijn gaat.</li>
      </ul>

      <div class="valrisico-fixed-actions">
        <a class="btn btn-secondary" href="/valrisico/looptest">Terug</a>
        <button class="btn" type="submit">Ik ga meten</button>
      </div>

      <div class="valrisico-fullwidth-action">
        <button class="btn btn-secondary" type="submit" name="action" value="skip">Sla over en ga naar resultaat</button>
      </div>
    </form>
  <?php else: ?>
    <form method="post" action="/valrisico/looptest" class="card valrisico-question-form">
      <input type="hidden" name="action" value="submit_seconds" />
      <h1>Hoeveel seconden deed u erover?</h1>
      <p class="muted">Bijvoorbeeld 5.2</p>

      <?php if (!empty($errorMessage)): ?>
        <p class="error"><?= htmlspecialchars((string) $errorMessage, ENT_QUOTES) ?></p>
      <?php endif; ?>

      <label class="valrisico-time-input-label" for="looptest-seconds">Aantal seconden</label>
      <input id="looptest-seconds" class="input valrisico-time-input" name="seconds" type="text" inputmode="decimal" value="<?= htmlspecialchars((string) ($seconds ?? ''), ENT_QUOTES) ?>" required />

      <div class="valrisico-fixed-actions">
        <a class="btn btn-secondary" href="/valrisico/looptest?fase=instructie">Terug</a>
        <button class="btn" type="submit">Bekijk resultaat</button>
      </div>

      <div class="valrisico-fullwidth-action">
        <button class="btn btn-secondary" type="submit" name="action" value="skip">Sla over en ga naar resultaat</button>
      </div>
    </form>
  <?php endif; ?>
</div>

<script src="/assets/js/valrisico.js" defer></script>

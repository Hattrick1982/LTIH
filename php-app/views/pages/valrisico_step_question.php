<div class="grid" style="gap: 1rem;">
  <?php $currentStep = (int) $stepNumber; $totalSteps = (int) $totalSteps; include __DIR__ . '/../partials/valrisico_stepper.php'; ?>

  <form method="post" action="/valrisico/antwoord" class="card valrisico-question-form">
    <input type="hidden" name="action" value="answer_question" />
    <input type="hidden" name="question_key" value="<?= htmlspecialchars((string) $questionKey, ENT_QUOTES) ?>" />

    <p class="kicker">Valrisico check</p>
    <h1><?= htmlspecialchars((string) $question, ENT_QUOTES) ?></h1>

    <details class="valrisico-why" open>
      <summary aria-label="Waarom vragen we dit?">Waarom vragen we dit?</summary>
      <p><?= htmlspecialchars((string) $why, ENT_QUOTES) ?></p>
    </details>

    <fieldset class="valrisico-answer-group" role="radiogroup" aria-label="Antwoordopties">
      <legend class="sr-only">Kies één antwoord</legend>

      <?php
      $options = [
          'yes' => 'Ja',
          'no' => 'Nee',
          'unknown' => 'Ik weet het niet',
      ];
      foreach ($options as $value => $label):
      ?>
        <label class="valrisico-answer-option">
          <input
            type="radio"
            name="answer"
            value="<?= $value ?>"
            <?= ($currentValue ?? '') === $value ? 'checked' : '' ?>
            required
            oninvalid="this.setCustomValidity('Kies een antwoord om verder te gaan.')"
            onchange="document.querySelectorAll('input[name=answer]').forEach(function(el){el.setCustomValidity('');});"
          />
          <span><?= $label ?></span>
        </label>
      <?php endforeach; ?>
    </fieldset>

    <div class="valrisico-fixed-actions">
      <a class="btn btn-secondary" href="<?= $stepNumber > 1 ? '/valrisico/stap/' . ((int) $stepNumber - 1) : '/valrisico' ?>">Terug</a>
      <button class="btn" type="submit">Verder</button>
    </div>
  </form>
</div>

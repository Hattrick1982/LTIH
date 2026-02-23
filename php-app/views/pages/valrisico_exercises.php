<?php
$exerciseSessionData = isset($exerciseSession) && is_array($exerciseSession) ? $exerciseSession : [];
$params = is_array($exerciseSessionData['params'] ?? null) ? $exerciseSessionData['params'] : [];
$sitValue = (int) ($params['sit_to_stand']['value'] ?? 6);
$heelValue = (int) ($params['heel_raises']['value'] ?? 8);
$marchingValue = (int) ($params['marching']['value'] ?? 45);
$sitTip = (string) ($params['sit_to_stand']['tip'] ?? '');
$heelTip = (string) ($params['heel_raises']['tip'] ?? '');
$marchingTip = (string) ($params['marching']['tip'] ?? '');
$restSeconds = max(0, (int) ($exerciseSessionData['restSeconds'] ?? 0));
$difficultyMode = (string) ($exerciseSessionData['difficultyMode'] ?? 'standard');
$sessionStatus = (string) ($exerciseSessionData['status'] ?? 'idle');
$isPausedBySymptoms = $sessionStatus === 'paused_due_to_symptoms';
$feedbackDialogType = '';
if (isset($feedbackDialog) && is_array($feedbackDialog) && is_string($feedbackDialog['type'] ?? null)) {
    $feedbackDialogType = (string) $feedbackDialog['type'];
}
?>

<div class="grid" style="gap: 1rem;">
  <section class="card">
    <h1>Oefeningen voor thuis</h1>
    <p class="muted">Zet een stoel klaar. Stop bij duizeligheid of pijn.</p>

    <div class="valrisico-program-card">
      <h2>5 minuten startersessie</h2>
      <p>3 oefeningen, 5 tot 8 minuten, 3 dagen per week.</p>
      <p class="muted">Huidige stand: <?= $difficultyMode === 'easier' ? 'Makkelijker modus' : 'Standaard' ?></p>
      <?php if ($restSeconds > 0): ?>
        <p class="valrisico-rest-note">Extra rust tussen oefeningen: <?= $restSeconds ?> seconden.</p>
      <?php endif; ?>
      <ol class="valrisico-starter-list">
        <li>
          <strong>1) Sit-to-stand (opstaan uit stoel)</strong>
          <p class="valrisico-session-target">Doel nu: <?= $sitValue ?> herhalingen</p>
          <ul class="valrisico-starter-subpoints">
            <li>Ga rechtop op een stevige stoel zitten, voeten plat op de grond.</li>
            <li>Leun iets voorover en sta rustig op. Ga daarna weer rustig zitten.</li>
            <li>Doe dit in een rustig tempo.</li>
          </ul>
          <?php if ($sitTip !== ''): ?>
            <p class="valrisico-session-tip"><strong>Tip:</strong> <?= htmlspecialchars($sitTip, ENT_QUOTES) ?></p>
          <?php endif; ?>
        </li>
        <li>
          <strong>2) Hielheffen met steun</strong>
          <p class="valrisico-session-target">Doel nu: <?= $heelValue ?> herhalingen</p>
          <ul class="valrisico-starter-subpoints">
            <li>Sta achter een stoel of bij het aanrecht en houd licht vast.</li>
            <li>Til uw hielen op zodat u op uw tenen staat. Houd 1 seconde vast en zak rustig terug.</li>
            <li>Blijf rechtop staan en beweeg rustig.</li>
          </ul>
          <?php if ($heelTip !== ''): ?>
            <p class="valrisico-session-tip"><strong>Tip:</strong> <?= htmlspecialchars($heelTip, ENT_QUOTES) ?></p>
          <?php endif; ?>
        </li>
        <li>
          <strong>3) Marcheren op de plaats met steun</strong>
          <p class="valrisico-session-target">Doel nu: <?= $marchingValue ?> seconden</p>
          <ul class="valrisico-starter-subpoints">
            <li>Sta bij een stoel of aanrecht en houd licht vast.</li>
            <li>Til om en om een knie op, alsof u rustig op de plaats loopt.</li>
            <li>Houd uw romp rechtop en doe het in een rustig tempo.</li>
          </ul>
          <?php if ($marchingTip !== ''): ?>
            <p class="valrisico-session-tip"><strong>Tip:</strong> <?= htmlspecialchars($marchingTip, ENT_QUOTES) ?></p>
          <?php endif; ?>
        </li>
      </ol>
    </div>

    <form method="post" action="/valrisico/antwoord" class="valrisico-feedback-form">
      <input type="hidden" name="action" value="session_feedback" />
      <h3>Hoe ging het?</h3>
      <div class="valrisico-answer-group">
        <label class="valrisico-answer-option">
          <input
            type="radio"
            name="feedback"
            value="good"
            <?= ($feedback ?? null) === 'good' ? 'checked' : '' ?>
            required
            oninvalid="this.setCustomValidity('Kies een antwoord om verder te gaan.')"
            onchange="document.querySelectorAll('input[name=feedback]').forEach(function(el){el.setCustomValidity('');});"
          />
          <span>Ging goed</span>
        </label>
        <label class="valrisico-answer-option">
          <input type="radio" name="feedback" value="hard" <?= ($feedback ?? null) === 'hard' ? 'checked' : '' ?> />
          <span>Was best lastig</span>
        </label>
        <label class="valrisico-answer-option">
          <input type="radio" name="feedback" value="symptoms" <?= ($feedback ?? null) === 'symptoms' ? 'checked' : '' ?> />
          <span>Ik kreeg klachten (pijn of duizeligheid)</span>
        </label>
      </div>
      <button class="btn" type="submit">Verder</button>
    </form>

    <?php if ($isPausedBySymptoms): ?>
      <p class="valrisico-symptoms-note">
        Deze sessie staat gepauzeerd vanwege klachten. Start pas opnieuw als dit veilig voelt.
      </p>
    <?php endif; ?>

    <p class="muted"><?= htmlspecialchars((string) $progressAdvice, ENT_QUOTES) ?></p>
  </section>

  <section class="grid grid-2" id="valrisico-first-exercise-block" data-first-exercise-block>
    <?php foreach ($exercises as $exercise): ?>
      <article class="card valrisico-exercise-card">
        <p class="kicker"><?= htmlspecialchars((string) ucfirst((string) $exercise['categorie']), ENT_QUOTES) ?></p>
        <h2><?= htmlspecialchars((string) $exercise['titel'], ENT_QUOTES) ?></h2>
        <p class="muted"><?= htmlspecialchars((string) $exercise['doel'], ENT_QUOTES) ?></p>
        <p><strong>Niveau:</strong> <?= htmlspecialchars((string) $exercise['niveau'], ENT_QUOTES) ?></p>
        <p><strong>Duur:</strong> <?= htmlspecialchars((string) $exercise['reps_of_time'], ENT_QUOTES) ?></p>
        <a href="/valrisico/oefeningen/<?= htmlspecialchars((string) $exercise['slug'], ENT_QUOTES) ?>" class="btn">Bekijk oefening</a>
      </article>
    <?php endforeach; ?>
  </section>

  <section class="card no-print" style="display:flex; gap:0.6rem; flex-wrap:wrap;">
    <a href="/valrisico/resultaat" class="btn btn-secondary">Terug naar uitslag</a>
    <form method="post" action="/valrisico/reset" style="margin:0;">
      <button class="btn btn-secondary" type="submit">Verwijder mijn gegevens</button>
    </form>
  </section>

  <button
    type="button"
    class="btn sticky-instructions-cta no-print"
    data-sticky-instructions-btn
    aria-label="Bekijk instructies"
  >
    Bekijk instructies
  </button>

  <?php if ($feedbackDialogType !== ''): ?>
    <div class="valrisico-modal-backdrop" data-feedback-modal role="dialog" aria-modal="true" aria-labelledby="valrisico-feedback-title">
      <div class="valrisico-modal-card">
        <form method="post" action="/valrisico/antwoord" class="valrisico-modal-close-form">
          <input type="hidden" name="action" value="session_feedback_modal" />
          <input type="hidden" name="modal_action" value="close_dialog" />
          <button type="submit" class="valrisico-modal-close" data-modal-close aria-label="Sluiten">Sluiten</button>
        </form>

        <?php if ($feedbackDialogType === 'good'): ?>
          <h2 id="valrisico-feedback-title">Top, goed gedaan</h2>
          <p>Fijn. Wilt u doorgaan of deze sessie nog een keer doen?</p>
        <?php elseif ($feedbackDialogType === 'hard'): ?>
          <h2 id="valrisico-feedback-title">Dank u, goed dat u dit aangeeft</h2>
          <p>We hebben de sessie direct rustiger gezet, met extra pauze en aangepaste doelen.</p>
        <?php elseif ($feedbackDialogType === 'symptoms'): ?>
          <h2 id="valrisico-feedback-title">Stop bij klachten</h2>
          <p>Stop met oefenen bij pijn of duizeligheid. We helpen u graag met veilig advies.</p>
          <p class="valrisico-modal-small">Bij aanhoudende of ernstige klachten: neem contact op met een zorgverlener.</p>
        <?php endif; ?>

        <div class="valrisico-modal-actions">
          <?php if ($feedbackDialogType === 'symptoms'): ?>
            <a href="/contact" class="btn">Plan adviesgesprek</a>
            <form method="post" action="/valrisico/antwoord">
              <input type="hidden" name="action" value="session_feedback_modal" />
              <input type="hidden" name="modal_action" value="show_all_exercises" />
              <button type="submit" class="btn btn-secondary">Bekijk alle oefeningen</button>
            </form>
          <?php else: ?>
            <form method="post" action="/valrisico/antwoord">
              <input type="hidden" name="action" value="session_feedback_modal" />
              <input type="hidden" name="modal_action" value="show_all_exercises" />
              <button type="submit" class="btn">Bekijk alle oefeningen</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<script src="/assets/js/valrisico.js" defer></script>

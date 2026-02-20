<?php

use App\Domain\Presentation;

$result = is_array($result ?? null) ? $result : [];
$hazards = is_array($result['hazards'] ?? null) ? $result['hazards'] : [];
$missingQuestions = is_array($result['missing_info_questions'] ?? null) ? $result['missing_info_questions'] : [];
$riskLabelLower = strtolower((string) $risk['label']);
$score = (int) ($result['overall_risk_score_0_100'] ?? 0);
$scoreMeterClass = $risk['className'] === 'label-low' ? 'score-meter-fill-low' : ($risk['className'] === 'label-medium' ? 'score-meter-fill-medium' : 'score-meter-fill-high');
?>
<div class="grid" style="gap: 1rem;">
  <nav class="assessment-stepper" aria-label="Voortgang foto-assessment">
    <ol class="assessment-stepper-list">
      <li class="assessment-step completed"><span class="assessment-step-index">1</span><span class="assessment-step-label">Kies ruimte</span></li>
      <li class="assessment-step completed"><span class="assessment-step-index">2</span><span class="assessment-step-label">Upload foto's</span></li>
      <li class="assessment-step active"><span class="assessment-step-index">3</span><span class="assessment-step-label">Resultaat</span></li>
    </ol>
  </nav>

  <section class="card">
    <h1>Jouw woonveiligheidsresultaat</h1>
    <p class="score-intro">Je score is <?= $score ?>/100: <?= htmlspecialchars($riskLabelLower, ENT_QUOTES) ?>. Hieronder zie je de belangrijkste aandachtspunten en de eerste stappen om je woning veiliger te maken.</p>
    <div class="score-meter" role="img" aria-label="Risicoscore <?= $score ?> van 100: <?= htmlspecialchars($riskLabelLower, ENT_QUOTES) ?>">
      <div class="score-meter-fill <?= $scoreMeterClass ?>" style="width: <?= max(0, min(100, $score)) ?>%;"></div>
    </div>
  </section>

  <section class="card">
    <h2>Belangrijkste aandachtspunten</h2>
    <?php if ($topIssues === []): ?>
      <p>Er zijn geen duidelijke risico's gedetecteerd op basis van de foto's.</p>
    <?php else: ?>
      <div class="issue-accordion-list" data-issues-accordion>
        <?php foreach ($topIssues as $index => $issue): ?>
          <?php
          $severity = (int) ($issue['severity_1_5'] ?? 0);
          $confidence = (int) round(((float) ($issue['confidence_0_1'] ?? 0)) * 100);
          $severityClass = $severity <= 2 ? 'severity-low' : ($severity === 3 ? 'severity-medium' : 'severity-high');
          $issueId = 'issue-' . $index;
          ?>
          <article class="issue-accordion-card">
            <h3 class="issue-accordion-heading">
              <button id="<?= $issueId ?>-btn" type="button" class="issue-accordion-trigger" aria-expanded="false" aria-controls="<?= $issueId ?>-panel">
                <span class="issue-accordion-title"><?= htmlspecialchars(Presentation::categoryLabel((string) ($issue['category'] ?? 'other')), ENT_QUOTES) ?></span>
                <span class="issue-accordion-meta">
                  <span class="issue-badge-compact <?= $severityClass ?>">Ernst <?= $severity ?>/5</span>
                  <span class="issue-badge-compact confidence">Zekerheid <?= $confidence ?>%</span>
                  <span class="issue-chevron" aria-hidden="true">▾</span>
                </span>
              </button>
            </h3>
            <div id="<?= $issueId ?>-panel" role="region" aria-labelledby="<?= $issueId ?>-btn" class="issue-body-region">
              <div class="issue-body-inner">
                <div class="issue-body-content">
                  <p><strong>Wat zien we</strong><br /><?= htmlspecialchars((string) ($issue['what_we_see'] ?? ''), ENT_QUOTES) ?></p>
                  <p><strong>Waarom risicant</strong><br /><?= htmlspecialchars((string) ($issue['why_it_matters'] ?? ''), ENT_QUOTES) ?></p>
                  <p style="margin-bottom: 0.35rem;"><strong>Aanbevolen acties</strong></p>
                  <ul>
                    <?php foreach (($issue['suggested_actions'] ?? []) as $action): ?>
                      <?php if (!is_array($action)) { continue; } ?>
                      <li>
                        <?= htmlspecialchars((string) ($action['action'] ?? ''), ENT_QUOTES) ?>
                        (<?= htmlspecialchars((string) ($action['effort'] ?? ''), ENT_QUOTES) ?>, kosten <?= htmlspecialchars((string) ($action['cost_band'] ?? ''), ENT_QUOTES) ?>)
                      </li>
                    <?php endforeach; ?>
                  </ul>
                  <?php if (($issue['needs_human_followup'] ?? false) === true): ?>
                    <p class="issue-followup">Dit punt vraagt mogelijk menselijke opvolging.</p>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <section class="card">
    <h2>Actieplan</h2>
    <h3>Vandaag</h3>
    <ul>
      <?php if (($actionPlan['today'] ?? []) === []): ?>
        <li>Geen directe acties.</li>
      <?php else: ?>
        <?php foreach ($actionPlan['today'] as $item): ?>
          <li><?= htmlspecialchars((string) $item, ENT_QUOTES) ?></li>
        <?php endforeach; ?>
      <?php endif; ?>
    </ul>

    <h3>Deze week</h3>
    <ul>
      <?php if (($actionPlan['week'] ?? []) === []): ?>
        <li>Geen aanvullende acties.</li>
      <?php else: ?>
        <?php foreach ($actionPlan['week'] as $item): ?>
          <li><?= htmlspecialchars((string) $item, ENT_QUOTES) ?></li>
        <?php endforeach; ?>
      <?php endif; ?>
    </ul>

    <h3>Binnen 1-3 maanden</h3>
    <ul>
      <?php if (($actionPlan['months'] ?? []) === []): ?>
        <li>Geen langetermijnacties.</li>
      <?php else: ?>
        <?php foreach ($actionPlan['months'] as $item): ?>
          <li><?= htmlspecialchars((string) $item, ENT_QUOTES) ?></li>
        <?php endforeach; ?>
      <?php endif; ?>
    </ul>
  </section>

  <?php if ($missingQuestions !== []): ?>
    <section class="card questions-card">
      <h2>Vragen ter aanvulling</h2>
      <ul>
        <?php foreach ($missingQuestions as $question): ?>
          <li><?= htmlspecialchars((string) $question, ENT_QUOTES) ?></li>
        <?php endforeach; ?>
      </ul>
      <div class="questions-card-cta">
        <p class="questions-card-cta-text">Samen lopen we deze vragen door en krijg je een persoonlijk advies voor jouw situatie.</p>
        <a href="/contact" class="btn btn-advice-cta">Plan een adviesgesprek</a>
      </div>
    </section>
  <?php endif; ?>

  <section class="card disclaimer-card">
    <p style="margin-top: 0;"><strong>Disclaimer:</strong> <?= htmlspecialchars((string) ($disclaimerParagraphs[0] ?? ''), ENT_QUOTES) ?></p>
    <p><?= htmlspecialchars((string) ($disclaimerParagraphs[1] ?? ''), ENT_QUOTES) ?></p>
    <p style="margin-bottom: 0;"><?= htmlspecialchars((string) ($disclaimerParagraphs[2] ?? ''), ENT_QUOTES) ?></p>
  </section>

  <section class="card no-print" style="display: flex; gap: 0.6rem; flex-wrap: wrap;">
    <a href="/assessment/result/<?= htmlspecialchars($assessmentId, ENT_QUOTES) ?>/print" class="btn btn-secondary">Print checklist</a>
    <a href="/contact" class="btn">Plan adviesgesprek</a>
    <button class="btn btn-secondary" type="button" data-delete-assessment data-assessment-id="<?= htmlspecialchars($assessmentId, ENT_QUOTES) ?>">Verwijder analyse en foto's</button>
  </section>

  <p class="error" id="delete-error" hidden></p>
</div>

<script src="/assets/js/result.js" defer></script>

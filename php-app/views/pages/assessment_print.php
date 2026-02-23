<?php

use App\Domain\Presentation;

$result = is_array($result ?? null) ? $result : [];
$hazards = is_array($result['hazards'] ?? null) ? $result['hazards'] : [];
?>
<div class="grid" style="gap: 1rem;">
  <section class="card">
    <h1>Checklist woonveiligheid</h1>
    <p class="muted">Assessment ID: <?= htmlspecialchars($assessmentId, ENT_QUOTES) ?></p>
    <p>
      Risicoscore: <strong><?= (int) ($result['overall_risk_score_0_100'] ?? 0) ?>/100</strong>
      <span class="badge <?= htmlspecialchars((string) ($risk['className'] ?? ''), ENT_QUOTES) ?>"><?= htmlspecialchars((string) ($risk['label'] ?? ''), ENT_QUOTES) ?></span>
    </p>
  </section>

  <section class="card">
    <h2>Issues en acties</h2>
    <ol>
      <?php foreach ($hazards as $index => $hazard): ?>
        <?php if (!is_array($hazard)) { continue; } ?>
        <li style="margin-bottom: 0.7rem;">
          <strong><?= htmlspecialchars(Presentation::categoryLabel((string) ($hazard['category'] ?? 'other')), ENT_QUOTES) ?></strong>
          - <?= htmlspecialchars((string) ($hazard['what_we_see'] ?? ''), ENT_QUOTES) ?>
          <ul>
            <?php foreach (($hazard['suggested_actions'] ?? []) as $action): ?>
              <?php if (!is_array($action)) { continue; } ?>
              <li><?= htmlspecialchars((string) ($action['action'] ?? ''), ENT_QUOTES) ?></li>
            <?php endforeach; ?>
          </ul>
        </li>
      <?php endforeach; ?>
    </ol>
  </section>

  <section class="card disclaimer-card">
    <p><strong>Disclaimer:</strong> <?= htmlspecialchars((string) ($disclaimerParagraphs[0] ?? ''), ENT_QUOTES) ?></p>
    <p><?= htmlspecialchars((string) ($disclaimerParagraphs[1] ?? ''), ENT_QUOTES) ?></p>
    <p style="margin-bottom: 0;"><?= htmlspecialchars((string) ($disclaimerParagraphs[2] ?? ''), ENT_QUOTES) ?></p>
  </section>

  <section class="no-print" style="display: flex; gap: 0.6rem;">
    <button class="btn" type="button" data-print-now>Print nu</button>
    <a href="/assessment/result/<?= htmlspecialchars($assessmentId, ENT_QUOTES) ?>" class="btn btn-secondary">Terug naar resultaat</a>
  </section>
</div>

<script src="/assets/js/result.js" defer></script>

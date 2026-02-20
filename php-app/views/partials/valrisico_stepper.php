<?php

$currentStep = isset($currentStep) ? (int) $currentStep : 1;
$totalSteps = isset($totalSteps) ? (int) $totalSteps : 5;
?>
<nav class="assessment-stepper" aria-label="Voortgang valrisico check">
  <ol class="assessment-stepper-list">
    <?php for ($index = 1; $index <= $totalSteps; $index++): ?>
      <?php
      $status = 'upcoming';
      if ($index < $currentStep) {
          $status = 'completed';
      } elseif ($index === $currentStep) {
          $status = 'active';
      }
      ?>
      <li class="assessment-step <?= $status ?>">
        <span class="assessment-step-index" aria-hidden="true"><?= $index ?></span>
        <span class="assessment-step-label">Stap <?= $index ?></span>
      </li>
    <?php endfor; ?>
  </ol>
</nav>

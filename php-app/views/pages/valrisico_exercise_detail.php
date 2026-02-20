<?php
$videoUrl = isset($exercise['video_url']) ? (string) $exercise['video_url'] : '';
?>
<div class="grid" style="gap: 1rem;">
  <section class="card">
    <p class="kicker"><?= htmlspecialchars((string) ucfirst((string) $exercise['categorie']), ENT_QUOTES) ?></p>
    <h1><?= htmlspecialchars((string) $exercise['titel'], ENT_QUOTES) ?></h1>
    <p><?= htmlspecialchars((string) $exercise['doel'], ENT_QUOTES) ?></p>

    <section class="valrisico-safety-callout" aria-label="Veiligheid">
      <p><strong>Veiligheid:</strong> Zet een stoel klaar. Stop bij duizeligheid of pijn.</p>
      <p class="muted"><?= htmlspecialchars((string) $exercise['veiligheid'], ENT_QUOTES) ?></p>
    </section>

    <?php if ($videoUrl !== ''): ?>
      <div class="valrisico-video-wrap" data-video-wrap>
        <video class="valrisico-video" controls preload="metadata" data-valrisico-video aria-label="Video van oefening <?= htmlspecialchars((string) $exercise['titel'], ENT_QUOTES) ?>">
          <source src="<?= htmlspecialchars($videoUrl, ENT_QUOTES) ?>" type="video/mp4" />
        </video>
        <div class="valrisico-video-controls no-print">
          <button type="button" class="btn btn-secondary" data-video-action="play">Play/Pause</button>
          <button type="button" class="btn btn-secondary" data-video-action="replay">Replay</button>
          <button type="button" class="btn btn-secondary" data-video-action="slow">Langzamer afspelen</button>
        </div>
      </div>
    <?php else: ?>
      <div class="valrisico-video-placeholder" role="img" aria-label="Video placeholder">
        <strong>Video volgt</strong>
        <p>Deze oefening is alvast beschikbaar met duidelijke stappen hieronder.</p>
      </div>
    <?php endif; ?>
  </section>

  <section class="card">
    <h2>Zo doet u deze oefening</h2>
    <ol>
      <?php foreach ($exercise['stappen'] as $step): ?>
        <li><?= htmlspecialchars((string) $step, ENT_QUOTES) ?></li>
      <?php endforeach; ?>
    </ol>
    <p><strong>Aanbevolen:</strong> <?= htmlspecialchars((string) $exercise['reps_of_time'], ENT_QUOTES) ?></p>
  </section>

  <section class="card no-print" style="display:flex; gap:0.6rem; flex-wrap:wrap;">
    <a href="/valrisico/oefeningen" class="btn btn-secondary">Terug naar oefeningen</a>
    <a href="/valrisico/resultaat" class="btn">Terug naar uitslag</a>
  </section>
</div>

<script src="/assets/js/valrisico.js" defer></script>

<?php
/** Platform brand marquee */
$platforms = ['instagram' => 'Instagram', 'youtube' => 'YouTube', 'facebook' => 'Facebook',
              'tiktok' => 'TikTok', 'spotify' => 'Spotify', 'apple' => 'Apple Music',
              'twitter' => 'X / Twitter', 'telegram' => 'Telegram'];
?>
<section class="marquee-section" aria-label="Platforms we work with">
  <div class="marquee">
    <div class="marquee__track">
      <?php for ($i = 0; $i < 2; $i++): ?>
        <?php foreach ($platforms as $ic => $label): ?>
          <span class="marquee__item"><?= icon($ic) ?><?= h($label) ?></span>
        <?php endforeach; ?>
      <?php endfor; ?>
    </div>
  </div>
</section>

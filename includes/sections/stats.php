<?php /** Animated counters strip */ ?>
<section class="stats" id="stats">
  <div class="container">
    <div class="stats__grid reveal-group">
      <?php foreach ($stats as $s): ?>
      <div class="stat-card reveal">
        <span class="stat-card__icon"><?= icon($s['icon'] ?: 'users') ?></span>
        <div class="stat-card__value">
          <span data-count="<?= h(preg_replace('/[^\d.]/', '', $s['value'])) ?>"><?= h($s['value']) ?></span><?= h($s['suffix']) ?>
        </div>
        <div class="stat-card__label"><?= h($s['label']) ?></div>
        <?php if ($s['sublabel']): ?><div class="stat-card__sub"><?= h($s['sublabel']) ?></div><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

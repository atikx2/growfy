<section class="section why" id="why">
  <div class="container">
    <div class="why__head reveal">
      <span class="kicker"><?= h(c('why_kicker')) ?></span>
      <h2 class="sec-title"><?= h(c('why_title')) ?></h2>
      <div class="why__leads">
        <p><?= h(c('why_lead1')) ?></p>
        <p><?= h(c('why_lead2')) ?></p>
      </div>
    </div>
    <div class="features__grid">
      <?php foreach ($features as $f): ?>
      <div class="feature-card reveal">
        <span class="feature-card__icon"><?= icon($f['icon'] ?: 'spark') ?></span>
        <h3><?= h($f['title']) ?></h3>
        <p><?= h($f['description']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

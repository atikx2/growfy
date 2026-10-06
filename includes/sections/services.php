<section class="section services" id="services">
  <div class="container">
    <div class="section-head reveal">
      <span class="kicker"><?= h(c('services_kicker')) ?></span>
      <h2 class="sec-title"><?= h(c('services_title')) ?></h2>
      <p class="sec-text"><?= h(c('services_text')) ?></p>
    </div>
    <div class="services__grid">
      <?php foreach ($services as $sv): ?>
      <article class="service-card reveal">
        <div class="service-card__top">
          <span class="service-card__icon service-card__icon--<?= h($sv['platform'] ?: 'spark') ?>"><?= icon($sv['icon'] ?: 'spark') ?></span>
          <?php if ($sv['tag']): ?><span class="chip"><?= h($sv['tag']) ?></span><?php endif; ?>
        </div>
        <h3 class="service-card__title"><?= h($sv['title']) ?></h3>
        <p class="service-card__text"><?= h($sv['description']) ?></p>
        <div class="service-card__foot">
          <?php if ($sv['price_from']): ?><span class="price">from <strong><?= h($sv['price_from']) ?></strong></span><?php endif; ?>
          <button type="button" class="order-link" data-order-open data-service-id="<?= (int) $sv['id'] ?>" data-service-name="<?= h($sv['title']) ?>">
            Order Now
            <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12h15M13 6l6 6-6 6"/></svg>
          </button>
        </div>
        <span class="service-card__glow" aria-hidden="true"></span>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

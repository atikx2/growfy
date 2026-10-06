<section class="section testimonials" id="reviews">
  <div class="container">
    <div class="section-head reveal">
      <span class="kicker"><?= h(c('testimonials_kicker')) ?></span>
      <h2 class="sec-title"><?= h(c('testimonials_title')) ?></h2>
    </div>
  </div>
  <div class="t-slider reveal" id="tSlider">
    <div class="t-slider__track" id="tTrack">
      <?php foreach (array_merge($testimonials, $testimonials) as $t): ?>
      <article class="t-card">
        <div class="t-card__stars"><?= stars((int) $t['stars']) ?></div>
        <p class="t-card__quote">“<?= h($t['quote']) ?>”</p>
        <div class="t-card__person">
          <?php if ($t['avatar']): ?>
            <img class="t-card__avatar" src="<?= h($t['avatar']) ?>" alt="<?= h($t['name']) ?>" width="52" height="52" loading="lazy">
          <?php else: ?>
            <span class="t-card__avatar t-card__avatar--initials"><?= h(initials($t['name'])) ?></span>
          <?php endif; ?>
          <div>
            <strong><?= h($t['name']) ?></strong>
            <small><?= h($t['role']) ?></small>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <div class="container t-slider__controls">
      <button class="t-arrow" id="tPrev" aria-label="Previous testimonials"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12H5M11 6l-6 6 6 6"/></svg></button>
      <div class="t-dots" id="tDots"></div>
      <button class="t-arrow" id="tNext" aria-label="Next testimonials"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12h15M13 6l6 6-6 6"/></svg></button>
    </div>
  </div>
</section>

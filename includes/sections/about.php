<?php $aboutPoints = array_filter(array_map('trim', preg_split('/\R/', c('about_points')))); ?>
<section class="section about" id="about">
  <div class="container about__grid">
    <div class="about__media reveal">
      <img src="assets/img/about.webp" alt="Growfy team crafting growth strategies" width="1100" height="600" loading="lazy">
      <div class="about__badge">
        <span class="about__badge-num">5+</span>
        <span class="about__badge-text">Years of<br>Experience</span>
      </div>
    </div>
    <div class="about__copy reveal">
      <span class="kicker"><?= h(c('about_kicker')) ?></span>
      <h2 class="sec-title"><?= h(c('about_title')) ?></h2>
      <p class="sec-text"><?= h(c('about_text')) ?></p>
      <ul class="check-list">
        <?php foreach ($aboutPoints as $point): ?>
          <li><?= icon('check') ?><span><?= h($point) ?></span></li>
        <?php endforeach; ?>
      </ul>
      <div class="about__actions">
        <a href="#contact" class="btn btn--primary">Get Started</a>
        <a href="#services" class="btn btn--ghost">View Services</a>
      </div>
    </div>
  </div>
</section>

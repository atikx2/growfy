<?php /** Hero section */ ?>
<section class="hero" id="home">
  <div class="hero__bg" aria-hidden="true">
    <div class="hero__glow hero__glow--1"></div>
    <div class="hero__glow hero__glow--2"></div>
    <div class="hero__grid-lines"></div>
  </div>
  <div class="container hero__grid">
    <div class="hero__copy reveal">
      <span class="badge"><span class="badge__dot"></span><?= h(c('hero_badge')) ?></span>
      <h1 class="hero__title">
        <?php $hl = c('hero_highlight');
        $title = c('hero_title');
        if ($hl && mb_stripos($title, $hl) !== false) {
            echo h(mb_substr($title, 0, mb_stripos($title, $hl)));
            echo '<span class="grad">' . h($hl) . '</span>';
            echo h(mb_substr($title, mb_stripos($title, $hl) + mb_strlen($hl)));
        } else {
            echo h($title);
        } ?>
      </h1>
      <p class="hero__sub"><?= h(c('hero_subtitle')) ?></p>
      <div class="hero__actions">
        <a href="#contact" class="btn btn--primary btn--lg"><?= h(c('hero_cta1_text')) ?> <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12h15M13 6l6 6-6 6"/></svg></a>
        <a href="#services" class="btn btn--ghost btn--lg"><?= h(c('hero_cta2_text')) ?></a>
      </div>
      <div class="hero__proof">
        <div class="avatar-stack">
          <?php foreach (array_slice($testimonials, 0, 4) as $t): ?>
            <span class="avatar" title="<?= h($t['name']) ?>"><?= h(initials($t['name'])) ?></span>
          <?php endforeach; ?>
          <span class="avatar avatar--more">+</span>
        </div>
        <div class="hero__proof-text">
          <?= stars(5) ?>
          <span>Trusted by <strong>10,000+</strong> creators &amp; brands</span>
        </div>
      </div>
    </div>

    <div class="hero__visual reveal">
      <div class="hero__img-wrap">
        <img src="assets/img/hero.webp" alt="Growfy analytics dashboard" width="1400" height="764" fetchpriority="high">
      </div>
      <div class="float-card float-card--1">
        <span class="float-card__icon float-card__icon--green"><?= icon('trend') ?></span>
        <div><strong><?= h(c('hero_card1_value')) ?></strong><small><?= h(c('hero_card1_label')) ?></small></div>
      </div>
      <div class="float-card float-card--2">
        <span class="float-card__icon float-card__icon--pink"><?= icon('heart') ?></span>
        <div><strong><?= h(c('hero_card2_value')) ?></strong><small><?= h(c('hero_card2_label')) ?></small></div>
      </div>
      <div class="float-card float-card--3">
        <span class="float-card__icon float-card__icon--violet"><?= icon('users') ?></span>
        <div><strong><?= h(c('hero_card3_value')) ?></strong><small><?= h(c('hero_card3_label')) ?></small></div>
      </div>
    </div>
  </div>
</section>

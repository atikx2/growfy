<section class="section faq" id="faq">
  <div class="container faq__grid">
    <div class="faq__side reveal">
      <span class="kicker"><?= h(c('faq_kicker')) ?></span>
      <h2 class="sec-title"><?= h(c('faq_title')) ?></h2>
      <div class="faq__help-card">
        <span class="faq__help-icon"><?= icon('headset') ?></span>
        <h3>Still have questions?</h3>
        <p>Talk to a growth strategist — free, no strings attached.</p>
        <a href="#contact" class="btn btn--primary btn--sm">Chat With Us</a>
      </div>
    </div>
    <div class="accordion reveal" id="accordion">
      <?php foreach ($faqs as $i => $faq): ?>
      <div class="accordion__item<?= $i === 0 ? ' is-open' : '' ?>">
        <button class="accordion__btn" type="button" aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>">
          <span><?= h($faq['question']) ?></span>
          <svg class="accordion__chev ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
        </button>
        <div class="accordion__panel">
          <div class="accordion__inner"><p><?= h($faq['answer']) ?></p></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section contact" id="contact">
  <div class="container contact__grid">
    <div class="contact__info reveal">
      <span class="kicker"><?= h(c('contact_kicker')) ?></span>
      <h2 class="sec-title"><?= h(c('contact_title')) ?></h2>
      <p class="sec-text"><?= h(c('contact_text')) ?></p>
      <ul class="contact__list">
        <li>
          <span class="contact__ic"><?= icon('mail') ?></span>
          <div><small>Email us</small><a href="mailto:<?= h(c('contact_email')) ?>"><?= h(c('contact_email')) ?></a></div>
        </li>
        <li>
          <span class="contact__ic"><?= icon('phone') ?></span>
          <div><small>Call / WhatsApp</small><a href="tel:<?= h(preg_replace('/[^+\d]/', '', c('contact_phone'))) ?>"><?= h(c('contact_phone')) ?></a></div>
        </li>
        <li>
          <span class="contact__ic"><?= icon('pin') ?></span>
          <div><small>Location</small><span><?= h(c('contact_address')) ?></span></div>
        </li>
        <li>
          <span class="contact__ic"><?= icon('clock') ?></span>
          <div><small>Working hours</small><span><?= h(c('business_hours')) ?></span></div>
        </li>
      </ul>
      <div class="contact__socials">
        <?php foreach (['facebook','instagram','twitter','linkedin','youtube','tiktok'] as $sn):
          $u = c('social_' . $sn); if (!$u || $u === '#') continue; ?>
          <a href="<?= h($u) ?>" target="_blank" rel="noopener" aria-label="<?= h($sn) ?>"><?= icon($sn) ?></a>
        <?php endforeach; ?>
      </div>
    </div>

    <form class="contact__form reveal" method="post" action="index.php#contact" data-ajax novalidate>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="contact">
      <div class="form-row">
        <div class="form-field">
          <label for="cf-name">Your Name *</label>
          <input type="text" id="cf-name" name="name" required maxlength="120" placeholder="John Carter">
        </div>
        <div class="form-field">
          <label for="cf-email">Email Address *</label>
          <input type="email" id="cf-email" name="email" required maxlength="150" placeholder="john@company.com">
        </div>
      </div>
      <div class="form-row">
        <div class="form-field">
          <label for="cf-phone">Phone / WhatsApp</label>
          <input type="text" id="cf-phone" name="phone" maxlength="60" placeholder="+1 555 000 0000">
        </div>
        <div class="form-field">
          <label for="cf-subject">Subject</label>
          <input type="text" id="cf-subject" name="subject" maxlength="200" placeholder="I want to grow my…">
        </div>
      </div>
      <div class="form-field">
        <label for="cf-message">Project Details *</label>
        <textarea id="cf-message" name="message" rows="5" required maxlength="3000" placeholder="Tell us about your goals, platforms and budget…"></textarea>
      </div>
      <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
      <button type="submit" class="btn btn--primary btn--lg btn--block" data-submit>
        <span class="btn__label">Send Message</span>
        <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4z"/><path d="M22 2 11 13"/></svg>
      </button>
      <p class="form-note">Average response time: under 3 hours.</p>
    </form>
  </div>
</section>
</main>

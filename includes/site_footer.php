<?php
/** Footer + floating UI + order modal + toasts. */
$siteName = c('site_name');
$wa = preg_replace('/\D/', '', c('whatsapp_number'));
$ver2 = fn(string $f) => file_exists(GROWFY_ROOT . '/' . $f) ? filemtime(GROWFY_ROOT . '/' . $f) : '1';
?>
<footer class="footer" id="footer">
  <div class="container footer__grid">
    <div class="footer__brand">
      <a class="brand" href="#home">
        <span class="brand__mark"><?php include __DIR__ . '/logo.php'; ?></span>
        <span class="brand__name"><?= h($siteName) ?></span>
      </a>
      <p><?= h(c('footer_about')) ?></p>
      <div class="footer__socials">
        <?php foreach (['facebook','instagram','twitter','linkedin','youtube','tiktok'] as $sn):
          $u = c('social_' . $sn); if (!$u || $u === '#') continue; ?>
          <a href="<?= h($u) ?>" target="_blank" rel="noopener" aria-label="<?= h($sn) ?>"><?= icon($sn) ?></a>
        <?php endforeach; ?>
      </div>
    </div>

    <nav class="footer__col" aria-label="Quick links">
      <h4>Quick Links</h4>
      <a href="#home">Home</a>
      <a href="#services">Services</a>
      <a href="#about">About Us</a>
      <a href="#reviews">Reviews</a>
      <a href="#faq">FAQ</a>
      <a href="#contact">Contact</a>
    </nav>

    <nav class="footer__col" aria-label="Services">
      <h4>Services</h4>
      <?php foreach (array_slice($services, 0, 6) as $sv): ?>
        <a href="#services"><?= h($sv['title']) ?></a>
      <?php endforeach; ?>
    </nav>

    <div class="footer__col footer__news">
      <h4>Growth Newsletter</h4>
      <p>One actionable growth tactic every week. No spam.</p>
      <form method="post" action="index.php#footer" data-ajax class="footer__news-form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="newsletter">
        <input type="email" name="email" required maxlength="150" placeholder="Your email" aria-label="Email address">
        <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
        <button type="submit" aria-label="Subscribe">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4z"/><path d="M22 2 11 13"/></svg>
        </button>
      </form>
      <div class="footer__contact-mini">
        <span><?= icon('mail') ?><?= h(c('contact_email')) ?></span>
        <span><?= icon('phone') ?><?= h(c('contact_phone')) ?></span>
      </div>
    </div>
  </div>
  <div class="footer__bar">
    <div class="container footer__bar-in">
      <span>© <?= date('Y') ?> <?= h(c('footer_copyright')) ?></span>
      <a href="admin/login.php" class="footer__admin" rel="nofollow">Admin</a>
    </div>
  </div>
</footer>

<?php if ($wa): ?>
<a class="wa-float" href="https://wa.me/<?= h($wa) ?>?text=Hi%20<?= rawurlencode($siteName) ?>!%20I%20want%20to%20grow%20my%20brand." target="_blank" rel="noopener" aria-label="Chat on WhatsApp">
  <?= icon('whatsapp') ?>
</a>
<?php endif; ?>

<button class="to-top" id="toTop" aria-label="Back to top">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M6 11l6-6 6 6"/></svg>
</button>

<!-- Order modal -->
<div class="modal" id="orderModal" aria-hidden="true" role="dialog" aria-label="Place an order">
  <div class="modal__backdrop" data-order-close></div>
  <div class="modal__card">
    <div class="modal__head">
      <div>
        <span class="kicker">Order Inquiry</span>
        <h3 id="orderModalTitle">Service</h3>
      </div>
      <button class="modal__close" type="button" data-order-close aria-label="Close">&times;</button>
    </div>
    <form method="post" action="index.php#services" data-ajax class="modal__form">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="order">
      <input type="hidden" name="service_id" id="orderServiceId" value="">
      <div class="form-field">
        <label for="od-package">Package</label>
        <select id="od-package" name="package">
          <option>Starter</option>
          <option selected>Growth</option>
          <option>Pro</option>
          <option>Custom</option>
        </select>
      </div>
      <div class="form-row">
        <div class="form-field">
          <label for="od-name">Your Name *</label>
          <input type="text" id="od-name" name="name" required maxlength="120" placeholder="John Carter">
        </div>
        <div class="form-field">
          <label for="od-contact">WhatsApp / Email *</label>
          <input type="text" id="od-contact" name="contact" required maxlength="150" placeholder="+1 555…  or  you@mail.com">
        </div>
      </div>
      <div class="form-field">
        <label for="od-link">Profile / Channel / Track Link</label>
        <input type="text" id="od-link" name="link" maxlength="255" placeholder="https://instagram.com/yourprofile">
      </div>
      <div class="form-field">
        <label for="od-notes">Notes</label>
        <textarea id="od-notes" name="notes" rows="3" maxlength="1000" placeholder="Anything we should know — targets, niche, timeline…"></textarea>
      </div>
      <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
      <button type="submit" class="btn btn--primary btn--block btn--lg" data-submit>
        <span class="btn__label">Place Order Request</span>
      </button>
    </form>
  </div>
</div>

<div class="toasts" id="toasts" aria-live="polite"></div>

<script>
window.GROWFY = <?= json_encode([
    'wa'      => $wa,
    'name'    => $siteName,
    'services' => array_map(fn($s) => ['id' => (int) $s['id'], 'name' => $s['title']], $services),
    'slides'  => count($testimonials),
], JSON_UNESCAPED_SLASHES) ?>;
</script>
<?php foreach (flash_get() as $fl): ?>
<script>window.__flash = <?= json_encode($fl) ?>;</script>
<?php endforeach; ?>
<script src="assets/js/site.js?v=<?= $ver2('assets/js/site.js') ?>" defer></script>
</body>
</html>

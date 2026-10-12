<?php
/**
 * Our Team — dedicated page.
 * Member data comes from the admin panel (admin/team.php).
 */
require_once __DIR__ . '/includes/bootstrap.php';

$teamMembers = q_all('team_members', 'active = 1');

$PAGE_TITLE = 'Meet Our Team — The Minds Behind the Growth · ' . c('site_name');
$PAGE_DESC  = 'Meet the strategists, creatives, ad specialists and support heroes of '
    . c('site_name') . ' — the people turning followers into fans and brands into category leaders.';

require __DIR__ . '/includes/site_header.php';
?>

<section class="section page-hero">
  <div class="container">
    <nav class="page-hero__crumbs reveal" aria-label="Breadcrumb">
      <a href="index.php">Home</a><span class="sep">/</span><span>Our Team</span>
    </nav>
    <span class="kicker reveal">Meet The Minds</span>
    <h1 class="page-hero__title reveal">The People Behind <span class="grad">Your Growth</span></h1>
    <p class="page-hero__lead reveal">Strategists, creatives, ad specialists and support heroes — a tight squad of <?= count($teamMembers) ?> experts obsessed with one metric: your result. Every campaign gets a real human brain, not a bot farm.</p>
  </div>
</section>

<section class="section team-sec" id="team">
  <div class="container">
    <div class="team-grid">
      <?php foreach ($teamMembers as $i => $m):
        $socials = [];
        foreach (['facebook', 'twitter', 'linkedin', 'instagram'] as $net) {
            if (!empty($m[$net]) && $m[$net] !== '#') {
                $socials[$net] = $m[$net];
            }
        }
      ?>
      <article class="team-card reveal" style="transition-delay: <?= (int) (($i % 3) * 90) ?>ms">
        <div class="team-card__media">
          <?php if ($m['avatar']): ?>
            <img src="<?= h($m['avatar']) ?>" alt="<?= h($m['name']) ?> — <?= h($m['role']) ?>" loading="lazy">
          <?php else: ?>
            <span class="team-card__initials" aria-hidden="true"><?= h(initials($m['name'])) ?></span>
          <?php endif; ?>
          <span class="team-card__num" aria-hidden="true">0<?= (int) $i + 1 ?></span>
          <?php if ($socials): ?>
          <div class="team-card__socials">
            <?php foreach ($socials as $net => $url): ?>
              <a href="<?= h($url) ?>" target="_blank" rel="noopener" aria-label="<?= h($m['name']) ?> on <?= h($net) ?> "><?= icon($net) ?></a>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
        <div class="team-card__body">
          <h3 class="team-card__name"><?= h($m['name']) ?></h3>
          <span class="team-card__role"><?= h($m['role']) ?></span>
          <?php if (trim((string) $m['bio']) !== ''): ?>
            <p class="team-card__bio"><?= h($m['bio']) ?></p>
          <?php endif; ?>
          <?php if ($m['email'] || $m['phone']): ?>
          <div class="team-card__contact">
            <?php if ($m['email']): ?>
              <a href="mailto:<?= h($m['email']) ?>" aria-label="Email <?= h($m['name']) ?>" title="<?= h($m['email']) ?>"><?= icon('mail') ?><span>Email</span></a>
            <?php endif; ?>
            <?php if ($m['phone']): ?>
              <a href="tel:<?= h(preg_replace('/[^\d+]/', '', $m['phone'])) ?>" aria-label="Call <?= h($m['name']) ?>" title="<?= h($m['phone']) ?>"><?= icon('phone') ?><span>Call</span></a>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
      </article>
      <?php endforeach; ?>
      <?php if (!$teamMembers): ?>
      <p class="team-empty">Team members will appear here soon — add them from the admin panel.</p>
      <?php endif; ?>
    </div>

    <div class="team-join reveal">
      <div class="team-join__txt">
        <span class="kicker">Want To Work With Us?</span>
        <h2>Your brand. Our brains. <span class="grad">One growth engine.</span></h2>
        <p>Tell us where you are and where you want to be — your dedicated growth manager will map the next 90 days for free.</p>
      </div>
      <a href="index.php#contact" class="btn btn--primary btn--lg">Start The Conversation</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/site_footer.php'; ?>

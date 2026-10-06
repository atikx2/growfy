<?php
/** Homepage text content editor */
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/_layout.php';
$user = admin_require_login();

function content_groups(): array
{
    return [
        'Hero section' => [
            ['hero_badge', 'Badge text', 'input'],
            ['hero_title', 'Main headline', 'input'],
            ['hero_highlight', 'Highlighted words (gradient part of headline — must appear inside headline)', 'input'],
            ['hero_subtitle', 'Sub-headline', 'textarea'],
            ['hero_cta1_text', 'Primary button text', 'input'],
            ['hero_cta2_text', 'Secondary button text', 'input'],
            ['hero_card1_label', 'Floating card 1 label', 'input'],
            ['hero_card1_value', 'Floating card 1 value', 'input'],
            ['hero_card2_label', 'Floating card 2 label', 'input'],
            ['hero_card2_value', 'Floating card 2 value', 'input'],
            ['hero_card3_label', 'Floating card 3 label', 'input'],
            ['hero_card3_value', 'Floating card 3 value', 'input'],
        ],
        'About / Welcome' => [
            ['about_kicker', 'Kicker (small top line)', 'input'],
            ['about_title', 'Title', 'input'],
            ['about_text', 'Paragraph', 'textarea'],
            ['about_points', 'Check list (one per line)', 'textarea'],
        ],
        'Services section header' => [
            ['services_kicker', 'Kicker', 'input'],
            ['services_title', 'Title', 'input'],
            ['services_text', 'Intro text', 'textarea'],
        ],
        'Process section header' => [
            ['steps_kicker', 'Kicker', 'input'],
            ['steps_title', 'Title', 'input'],
            ['steps_text', 'Intro text', 'textarea'],
        ],
        'Why choose us' => [
            ['why_kicker', 'Kicker', 'input'],
            ['why_title', 'Title', 'input'],
            ['why_lead1', 'Lead paragraph 1', 'textarea'],
            ['why_lead2', 'Lead paragraph 2', 'textarea'],
        ],
        'Testimonials & FAQ headers' => [
            ['testimonials_kicker', 'Testimonials kicker', 'input'],
            ['testimonials_title', 'Testimonials title', 'input'],
            ['faq_kicker', 'FAQ kicker', 'input'],
            ['faq_title', 'FAQ title', 'input'],
        ],
        'CTA banner' => [
            ['cta_title', 'Title', 'input'],
            ['cta_text', 'Text', 'textarea'],
            ['cta_button', 'Button text', 'input'],
        ],
        'Contact section header' => [
            ['contact_kicker', 'Kicker', 'input'],
            ['contact_title', 'Title', 'input'],
            ['contact_text', 'Intro text', 'textarea'],
        ],
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $pairs = [];
    foreach (content_groups() as $fields) {
        foreach ($fields as [$key, , $type]) {
            $pairs[$key] = mb_substr(trim((string) ($_POST[$key] ?? '')), 0, $type === 'textarea' ? 3000 : 300);
        }
    }
    settings_set($pairs);
    flash_set('success', 'Homepage content saved. Refresh the site to see changes.');
    redirect('content.php');
}

admin_header('Site Content', 'content.php', $user);
?>
<form method="post" class="admin-form">
  <?= csrf_field() ?>
  <?php foreach (content_groups() as $group => $fields): ?>
  <div class="panel">
    <div class="panel__head"><h2><?= h($group) ?></h2></div>
    <div class="form-grid">
      <?php foreach ($fields as [$key, $label, $type]): ?>
      <div class="form-field <?= $type === 'textarea' ? 'form-field--full' : '' ?>">
        <label><?= h($label) ?></label>
        <?php if ($type === 'textarea'): ?>
          <textarea name="<?= h($key) ?>" rows="3"><?= h(c($key)) ?></textarea>
        <?php else: ?>
          <input type="text" name="<?= h($key) ?>" maxlength="300" value="<?= h(c($key)) ?>">
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>
  <div class="form-actions form-actions--sticky">
    <button type="submit" class="btn btn--primary btn--lg">Save all content</button>
    <a class="btn btn--ghost btn--lg" href="../index.php" target="_blank">Preview site</a>
  </div>
</form>
<?php admin_footer(); ?>

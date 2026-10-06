<section class="section process" id="process">
  <div class="container process__grid">
    <div class="process__head reveal">
      <span class="kicker"><?= h(c('steps_kicker')) ?></span>
      <h2 class="sec-title"><?= h(c('steps_title')) ?></h2>
      <p class="sec-text"><?= h(c('steps_text')) ?></p>
      <a href="#contact" class="btn btn--primary">Start Your Project</a>
    </div>
    <ol class="timeline">
      <?php $n = 0; foreach ($steps as $step): $n++; ?>
      <li class="step-item reveal">
        <div class="step-item__marker">
          <span class="step-item__num"><?= str_pad((string) $n, 2, '0', STR_PAD_LEFT) ?></span>
          <span class="step-item__icon"><?= icon($step['icon'] ?: 'spark') ?></span>
        </div>
        <div class="step-item__body">
          <h3><?= h($step['title']) ?></h3>
          <p><?= h($step['description']) ?></p>
        </div>
      </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

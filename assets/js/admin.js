/* Growfy Admin — interactions */
(function () {
  'use strict';
  const $  = (s, c) => (c || document).querySelector(s);
  const $$ = (s, c) => Array.from((c || document).querySelectorAll(s));

  /* sidebar (mobile) */
  const burger = $('#adminBurger');
  const side = $('#adminSide');
  burger && burger.addEventListener('click', () => side.classList.toggle('is-open'));
  document.addEventListener('click', (e) => {
    if (side && side.classList.contains('is-open') && !side.contains(e.target) && !burger.contains(e.target)) {
      side.classList.remove('is-open');
    }
  });

  /* delete confirms */
  $$('form[data-confirm]').forEach(f => {
    f.addEventListener('submit', (e) => {
      if (!window.confirm(f.dataset.confirm || 'Are you sure?')) e.preventDefault();
    });
  });

  /* icon picker preview */
  const mapEl = $('#iconMap');
  const sel = $('#iconSel');
  const prev = $('#iconPrev');
  if (mapEl && sel && prev) {
    try {
      const map = JSON.parse(mapEl.textContent);
      sel.addEventListener('change', () => { prev.innerHTML = map[sel.value] || ''; });
    } catch (e) { /* noop */ }
  }

  /* file upload preview */
  const fileInput = $('#avatarFile');
  const preview = $('#uploadPreview');
  if (fileInput && preview) {
    fileInput.addEventListener('change', () => {
      const f = fileInput.files && fileInput.files[0];
      if (!f || !/^image\//.test(f.type)) return;
      const reader = new FileReader();
      reader.onload = ev => { preview.innerHTML = '<img src="' + ev.target.result + '" alt="">'; };
      reader.readAsDataURL(f);
    });
  }

  /* auto submit selects */
  $$('[data-autosubmit]').forEach(el => el.addEventListener('change', () => el.form.submit()));
})();

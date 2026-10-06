/* Growfy Agency — public site interactions */
(function () {
  'use strict';

  const $  = (s, c) => (c || document).querySelector(s);
  const $$ = (s, c) => Array.from((c || document).querySelectorAll(s));

  /* ---------------------- dark / light theme ---------------------- */
  const themeToggle = $('#themeToggle');
  const applyTheme = (t) => {
    document.documentElement.setAttribute('data-theme', t);
    if (themeToggle) {
      themeToggle.setAttribute('aria-label', t === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
    }
  };
  applyTheme(document.documentElement.getAttribute('data-theme') || 'dark');
  themeToggle && themeToggle.addEventListener('click', () => {
    const next = document.documentElement.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
    try { localStorage.setItem('growfy-theme', next); } catch (e) { /* private mode */ }
    applyTheme(next);
  });

  /* ------------------------- navbar ------------------------- */
  const navbar = $('#navbar');
  const navToggle = $('#navToggle');
  const navLinks = $('#navLinks');

  const onScrollNav = () => navbar && navbar.classList.toggle('is-scrolled', window.scrollY > 10);
  onScrollNav();
  window.addEventListener('scroll', onScrollNav, { passive: true });

  if (navToggle && navLinks) {
    navToggle.addEventListener('click', () => {
      const open = navLinks.classList.toggle('is-open');
      navToggle.classList.toggle('is-open', open);
      navToggle.setAttribute('aria-expanded', String(open));
      document.body.style.overflow = open ? 'hidden' : '';
    });
    $$('.nav-link, .nav-links__cta', navLinks).forEach(a => a.addEventListener('click', () => {
      navLinks.classList.remove('is-open');
      navToggle.classList.remove('is-open');
      document.body.style.overflow = '';
    }));
  }

  // active link on scroll
  const sections = ['home', 'services', 'about', 'process', 'why', 'reviews', 'faq']
    .map(id => document.getElementById(id)).filter(Boolean);
  const links = $$('.nav-link');
  const spy = () => {
    let current = 'home';
    const y = window.scrollY + 140;
    sections.forEach(sec => { if (sec.offsetTop <= y) current = sec.id; });
    links.forEach(l => l.classList.toggle('is-active', l.getAttribute('href') === '#' + current));
  };
  window.addEventListener('scroll', spy, { passive: true });
  spy();

  /* ------------------------- toasts ------------------------- */
  const toasts = $('#toasts');
  function toast(msg, type) {
    if (!toasts) return;
    type = type || 'success';
    const el = document.createElement('div');
    el.className = 'toast toast--' + type;
    const icon = type === 'success'
      ? '<svg class="toast__ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="m8 12.5 2.7 2.7L16 9.5"/></svg>'
      : '<svg class="toast__ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16.5v.5"/></svg>';
    el.innerHTML = icon + '<div>' + escapeHtml(msg) + '</div>';
    toasts.appendChild(el);
    setTimeout(() => { el.classList.add('is-out'); setTimeout(() => el.remove(), 380); }, 4200);
  }
  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  }
  if (window.__flash && window.__flash.msg) {
    toast(window.__flash.msg, window.__flash.type === 'error' ? 'error' : 'success');
  }

  /* --------------------- ajax form submits --------------------- */
  $$('form[data-ajax]').forEach(form => {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn = $('[data-submit]', form) || form.querySelector('button[type=submit]');
      const label = btn ? $('.btn__label', btn) : null;
      const oldText = label ? label.textContent : '';
      if (btn) { btn.disabled = true; if (label) label.textContent = 'Sending…'; }
      try {
        const fd = new FormData(form);
        fd.set('_ajax', '1');
        const res = await fetch(form.action.split('#')[0], {
          method: 'POST',
          body: fd,
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          credentials: 'same-origin'
        });
        const data = await res.json().catch(() => ({ ok: false, msg: 'Unexpected response. Please try again.' }));
        toast(data.msg || (data.ok ? 'Done!' : 'Something went wrong.'), data.ok ? 'success' : 'error');
        if (data.ok) { form.reset(); closeOrderModal(); }
      } catch (err) {
        toast('Network error — please try again.', 'error');
      } finally {
        if (btn) { btn.disabled = false; if (label) label.textContent = oldText; }
      }
    });
  });

  /* ---------------------- reveal on scroll ---------------------- */
  const io = 'IntersectionObserver' in window ? new IntersectionObserver((entries) => {
    entries.forEach(en => {
      if (en.isIntersecting) { en.target.classList.add('is-in'); io.unobserve(en.target); }
    });
  }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }) : null;

  $$('.reveal').forEach(el => {
    if (io) io.observe(el); else el.classList.add('is-in');
  });

  /* ------------------------ counters ------------------------ */
  const easeOut = t => 1 - Math.pow(1 - t, 3);
  const cio = 'IntersectionObserver' in window ? new IntersectionObserver((entries) => {
    entries.forEach(en => {
      if (!en.isIntersecting) return;
      cio.unobserve(en.target);
      const el = en.target;
      const target = parseFloat(el.dataset.count || '0');
      const decimals = String(el.dataset.count).split('.')[1] ? 1 : 0;
      const dur = 1800; const start = performance.now();
      const tick = now => {
        const p = Math.min((now - start) / dur, 1);
        const val = target * easeOut(p);
        el.textContent = decimals ? val.toFixed(1) : Math.round(val).toLocaleString('en-US');
        if (p < 1) requestAnimationFrame(tick);
      };
      requestAnimationFrame(tick);
    });
  }, { threshold: 0.5 }) : null;
  $$('[data-count]').forEach(el => { if (cio) cio.observe(el); });

  /* ------------------------ accordion ------------------------ */
  $$('.accordion__item').forEach(item => {
    const btn = $('.accordion__btn', item);
    const panel = $('.accordion__panel', item);
    const set = (open) => {
      item.classList.toggle('is-open', open);
      btn.setAttribute('aria-expanded', String(open));
      panel.style.maxHeight = open ? panel.scrollHeight + 'px' : '0px';
    };
    if (item.classList.contains('is-open')) {
      requestAnimationFrame(() => { panel.style.maxHeight = panel.scrollHeight + 'px'; });
    }
    btn.addEventListener('click', () => {
      const open = !item.classList.contains('is-open');
      $$('.accordion__item.is-open').forEach(o => {
        if (o !== item) {
          o.classList.remove('is-open');
          $('.accordion__panel', o).style.maxHeight = '0px';
          $('.accordion__btn', o).setAttribute('aria-expanded', 'false');
        }
      });
      set(open);
    });
  });

  /* ---------------------- testimonial slider ---------------------- */
  const track = $('#tTrack');
  const dotsWrap = $('#tDots');
  const total = (window.GROWFY && window.GROWFY.slides) || 0;
  if (track && total > 0) {
    let index = 0, offset = 0, startX = 0, dragging = false, cardW = 0, maxOffset = 0, timer = null;

    const measure = () => {
      const card = track.children[0];
      const gap = 20;
      cardW = card ? card.getBoundingClientRect().width + gap : 420;
      maxOffset = cardW * total;
    };
    measure();
    window.addEventListener('resize', measure);

    const buildDots = () => {
      dotsWrap.innerHTML = '';
      for (let i = 0; i < total; i++) {
        const b = document.createElement('button');
        b.type = 'button';
        b.setAttribute('aria-label', 'Go to slide ' + (i + 1));
        b.addEventListener('click', () => { index = i; apply(true); restart(); });
        dotsWrap.appendChild(b);
      }
    };
    buildDots();

    const apply = (smooth) => {
      offset = -index * cardW;
      track.style.transition = smooth ? 'transform .65s cubic-bezier(.2,.8,.25,1)' : 'none';
      track.style.transform = 'translateX(' + offset + 'px)';
      Array.from(dotsWrap.children).forEach((d, i) => d.classList.toggle('is-active', i === index));
    };

    // seamless loop: slides are duplicated in markup
    const fixLoop = () => {
      if (Math.abs(offset) >= maxOffset) {
        track.style.transition = 'none';
        index = index % total;
        offset = -index * cardW;
        track.style.transform = 'translateX(' + offset + 'px)';
      }
    };
    track.addEventListener('transitionend', fixLoop);

    const next = () => { index++; apply(true); };
    const prev = () => {
      if (index === 0) {
        // jump to the duplicate end instantly, then slide back
        index = total;
        track.style.transition = 'none';
        track.style.transform = 'translateX(' + (-index * cardW) + 'px)';
        requestAnimationFrame(() => requestAnimationFrame(() => { index = total - 1; apply(true); }));
        return;
      }
      index--; apply(true);
    };

    const restart = () => { clearInterval(timer); timer = setInterval(next, 4500); };
    restart();

    $('#tNext') && $('#tNext').addEventListener('click', () => { next(); restart(); });
    $('#tPrev') && $('#tPrev').addEventListener('click', () => { prev(); restart(); });

    // drag / swipe
    const x = e => ('touches' in e ? e.touches[0].clientX : e.clientX);
    const down = e => { dragging = true; startX = x(e) - offset; track.classList.add('is-drag'); track.style.transition = 'none'; clearInterval(timer); };
    const move = e => { if (!dragging) return; offset = x(e) - startX; track.style.transform = 'translateX(' + offset + 'px)'; };
    const up = () => {
      if (!dragging) return;
      dragging = false;
      track.classList.remove('is-drag');
      index = Math.min(Math.max(Math.round(-offset / cardW), 0), total * 2 - 1);
      if (index >= total) { index = index - total; apply(false); requestAnimationFrame(() => requestAnimationFrame(() => apply(true))); }
      else apply(true);
      restart();
    };
    track.addEventListener('mousedown', down);
    window.addEventListener('mousemove', move, { passive: true });
    window.addEventListener('mouseup', up);
    track.addEventListener('touchstart', down, { passive: true });
    track.addEventListener('touchmove', move, { passive: true });
    track.addEventListener('touchend', up);

    apply(false);
  }

  /* ------------------------ order modal ------------------------ */
  const modal = $('#orderModal');
  const modalTitle = $('#orderModalTitle');
  const serviceInput = $('#orderServiceId');

  function openOrderModal(id, name) {
    if (!modal) return;
    modalTitle.textContent = name;
    serviceInput.value = id;
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }
  function closeOrderModal() {
    if (!modal || !modal.classList.contains('is-open')) return;
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }
  window.closeOrderModal = closeOrderModal;

  $$('[data-order-open]').forEach(btn => btn.addEventListener('click', () => {
    openOrderModal(btn.dataset.serviceId, btn.dataset.serviceName);
  }));
  $$('[data-order-close]').forEach(el => el.addEventListener('click', closeOrderModal));
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeOrderModal(); });

  /* ------------------------ misc floats ------------------------ */
  const toTop = $('#toTop');
  window.addEventListener('scroll', () => {
    toTop && toTop.classList.toggle('is-visible', window.scrollY > 600);
  }, { passive: true });
  toTop && toTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

})();

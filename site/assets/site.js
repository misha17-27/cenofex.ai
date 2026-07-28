/* CENOFEX — публичный сайт: слайдер, меню, переключатель языка, анимации */
(function () {
  var q = function (s) { return document.querySelector(s); };

  /* ---- мобильное меню ---- */
  var nav = q('#nav'), menu = q('#menu');
  if (menu) {
    menu.addEventListener('click', function () {
      var open = nav.classList.toggle('open');
      menu.setAttribute('aria-expanded', open);
      document.body.classList.toggle('menu-open', open);
    });
    document.querySelectorAll('.links a').forEach(function (a) {
      a.addEventListener('click', function () {
        nav.classList.remove('open');
        menu.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('menu-open');
      });
    });
  }

  /* ---- hero-слайдер ---- */
  (function () {
    var hero = q('#home'); if (!hero) return;
    var track = q('#heroTrack'),
        slides = [].slice.call(hero.querySelectorAll('.hero-slide')),
        dots = q('#heroDots');
    if (!track || !slides.length) return;
    var cur = 0, timer;
    slides.forEach(function (_, i) {
      var b = document.createElement('button');
      b.setAttribute('aria-label', 'Slide ' + (i + 1));
      if (i === 0) b.className = 'active';
      b.addEventListener('click', function () { show(i); start(); });
      dots.appendChild(b);
    });
    var dotEls = [].slice.call(dots.children);
    function show(i) {
      cur = (i + slides.length) % slides.length;
      track.style.transform = 'translateX(' + (-cur * 100) + '%)';
      dotEls.forEach(function (d, n) { d.classList.toggle('active', n === cur); });
    }
    function start() { clearInterval(timer); timer = setInterval(function () { show(cur + 1); }, 6000); }
    var prev = hero.querySelector('.hero-arrow.prev'), next = hero.querySelector('.hero-arrow.next');
    if (prev) prev.addEventListener('click', function () { show(cur - 1); start(); });
    if (next) next.addEventListener('click', function () { show(cur + 1); start(); });

    /* листание пальцем */
    var vp = hero.querySelector('.hero-viewport') || track;
    var x0 = null, y0 = null, moved = false;
    vp.addEventListener('touchstart', function (ev) {
      var t = ev.touches[0];
      x0 = t.clientX; y0 = t.clientY; moved = false;
      clearInterval(timer);
    }, { passive: true });
    vp.addEventListener('touchmove', function (ev) {
      if (x0 === null) return;
      var t = ev.touches[0];
      if (Math.abs(t.clientX - x0) > Math.abs(t.clientY - y0)) moved = true;
    }, { passive: true });
    vp.addEventListener('touchend', function (ev) {
      if (x0 === null) return;
      var dx = ev.changedTouches[0].clientX - x0;
      if (moved && Math.abs(dx) > 45) show(dx < 0 ? cur + 1 : cur - 1);
      x0 = y0 = null;
      start();
    }, { passive: true });

    start();
  })();

  /* ---- концепция знака: подсветка сегмента ---- */
  (function () {
    var rows = q('#pillRows'), mark = q('#conceptMark');
    if (!rows || !mark) return;
    var items = [].slice.call(rows.querySelectorAll('.pill-row'));
    var timer, idx = 0;

    function activate(i) {
      idx = (i + items.length) % items.length;
      items.forEach(function (el, n) { el.classList.toggle('on', n === idx); });
      mark.setAttribute('data-active', items[idx].dataset.seg);
    }
    function autoplay() { clearInterval(timer); timer = setInterval(function () { activate(idx + 1); }, 3000); }
    function pick(i) { activate(i); autoplay(); }

    items.forEach(function (el, i) {
      el.addEventListener('mouseenter', function () { activate(i); clearInterval(timer); });
      el.addEventListener('focus', function () { activate(i); clearInterval(timer); });
      el.addEventListener('click', function () { pick(i); });
      el.addEventListener('mouseleave', autoplay);
    });

    activate(0);
    autoplay();
  })();

  /* ---- появление секций при скролле ---- */
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (en) {
        if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); }
      });
    }, { threshold: 0.12 });
    document.querySelectorAll('.reveal').forEach(function (el) { io.observe(el); });
  } else {
    document.querySelectorAll('.reveal').forEach(function (el) { el.classList.add('in'); });
  }
})();

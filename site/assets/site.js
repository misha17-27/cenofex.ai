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

  /* ---- переход по якорям: секция должна вставать под шапку ---- */
  (function () {
    var header = document.querySelector('header');

    /* У секций большой внутренний отступ (.wrap). Обычный якорь ставит
       ВЕРХ секции под шапку, и заголовок оказывается далеко внизу.
       Поэтому целимся в первый видимый блок содержимого. */
    function targetTop(sec) {
      var headerH = header ? header.getBoundingClientRect().height : 0;
      var wrap = sec.querySelector('.wrap') || sec;
      var pad = parseFloat(getComputedStyle(wrap).paddingTop) || 0;
      /* меряем по самой секции: у неё нет анимаций, в отличие от .reveal внутри */
      var top = sec.getBoundingClientRect().top + window.pageYOffset;
      return Math.max(0, Math.round(top + pad - headerH - 28));
    }

    function go(sec, smooth) {
      var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      window.scrollTo({ top: targetTop(sec), behavior: (smooth && !reduce) ? 'smooth' : 'auto' });
    }

    document.addEventListener('click', function (e) {
      var a = e.target.closest && e.target.closest('a[href^="#"]');
      if (!a) return;
      var id = a.getAttribute('href');
      if (!id || id === '#' || id.length < 2) return;

      var sec = document.querySelector(id);
      if (!sec) return;

      e.preventDefault();
      /* первый слайд — просто наверх, там шапка перекрывать нечего */
      if (id === '#home') {
        window.scrollTo({ top: 0, behavior: 'smooth' });
      } else {
        go(sec, true);
      }
      if (history.replaceState) history.replaceState(null, '', id);
    });

    /* если страницу открыли сразу со ссылкой вида /index7.php#solutions */
    if (location.hash.length > 1) {
      var sec = document.querySelector(location.hash);
      if (sec) {
        // ждём картинки: без этого высота ещё «плывёт» и промах неизбежен
        window.addEventListener('load', function () { setTimeout(function () { go(sec, false); }, 60); });
      }
    }
  })();

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
    /* слайд меняется раз в 10 секунд */
    function start() { clearInterval(timer); timer = setInterval(function () { show(cur + 1); }, 10000); }
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

  /* ---- кнопка «наверх» ---- */
  (function () {
    var btn = q('#toTop');
    if (!btn) return;

    function toggle() {
      btn.classList.toggle('show', window.pageYOffset > 400);
    }
    window.addEventListener('scroll', toggle, { passive: true });
    toggle();

    btn.addEventListener('click', function () {
      var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
    });
  })();

  /* ---- отправка формы без перезагрузки (страница не прыгает) ---- */
  (function () {
    var form = document.querySelector('form.form');
    if (!form || !window.fetch) return;                    // без fetch — обычная отправка

    var az = document.documentElement.lang === 'az';
    var TXT = {
      ok:  az ? 'Mesajınız göndərildi. Tezliklə əlaqə saxlayacağıq.'
              : 'Thank you — your message has been sent.',
      e1:  az ? 'Zəhmət olmasa ad və telefon xanalarını düzgün doldurun.'
              : 'Please fill in your name and phone correctly.',
      e2:  az ? 'Çox sayda müraciət göndərilib. Zəhmət olmasa bir azdan yenidən cəhd edin.'
              : 'Too many messages sent. Please try again later.',
      e3:  az ? '«Mən robot deyiləm» yoxlamasını tamamlayın.'
              : 'Please complete the “I’m not a robot” check.',
      net: az ? 'Əlaqə xətası. Zəhmət olmasa yenidən cəhd edin.'
              : 'Connection error. Please try again.'
    };

    function showMsg(type, text) {
      var box = form.querySelector('.form-msg');
      if (!box) {
        box = document.createElement('div');
        form.insertBefore(box, form.firstChild);
      }
      box.className = 'form-msg ' + (type === 'ok' ? 'ok' : 'err');
      box.textContent = text;
    }

    form.addEventListener('submit', function (ev) {
      ev.preventDefault();                                  // никакой перезагрузки и прыжка
      var btn = form.querySelector('button[type=submit]');
      if (btn) { btn.disabled = true; btn.style.opacity = '.7'; }

      fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res && res.ok) {
          showMsg('ok', TXT.ok);
          form.reset();
        } else {
          var code = res && res.code;
          showMsg('err', code === '2' ? TXT.e2 : code === '3' ? TXT.e3 : TXT.e1);
        }
        if (window.turnstile) { try { window.turnstile.reset(); } catch (e) {} }
      })
      .catch(function () { showMsg('err', TXT.net); })
      .then(function () {
        if (btn) { btn.disabled = false; btn.style.opacity = ''; }
      });
    });
  })();

  /* ---- поле телефона: только цифры, +, скобки, дефис и пробел ---- */
  (function () {
    var phone = document.getElementById('cf-phone');
    if (!phone) return;

    function clean(v) {
      var plus = v.trim().charAt(0) === '+';           // «+» разрешаем только первым
      var rest = v.replace(/[^\d()\-\s]/g, '');
      return (plus ? '+' : '') + rest;
    }
    phone.addEventListener('input', function () {
      var before = phone.value, pos = phone.selectionStart;
      var after = clean(before);
      if (after !== before) {
        phone.value = after;
        phone.setSelectionRange(Math.max(0, pos - (before.length - after.length)),
                                Math.max(0, pos - (before.length - after.length)));
      }
    });
    phone.addEventListener('keypress', function (ev) {
      if (ev.ctrlKey || ev.metaKey) return;
      var ok = /[\d()\-\s]/.test(ev.key) || (ev.key === '+' && phone.selectionStart === 0);
      if (!ok) ev.preventDefault();
    });
    phone.addEventListener('paste', function (ev) {
      var txt = (ev.clipboardData || window.clipboardData).getData('text');
      if (txt && clean(txt) !== txt) {
        ev.preventDefault();
        phone.value = clean(phone.value + txt);
      }
    });
  })();

  /* ---- поле e-mail: только допустимые в адресе символы ---- */
  (function () {
    var mail = document.getElementById('cf-email');
    if (!mail) return;

    // латиница, цифры и символы, разрешённые в адресе почты
    var ALLOWED = /[A-Za-z0-9@._+\-]/;
    function clean(v) { return v.replace(/[^A-Za-z0-9@._+\-]/g, '').toLowerCase(); }

    mail.addEventListener('keypress', function (ev) {
      if (ev.ctrlKey || ev.metaKey) return;
      if (!ALLOWED.test(ev.key)) ev.preventDefault();          // пробелы, кириллица и пр. — блокируем
    });
    mail.addEventListener('input', function () {
      var before = mail.value, pos = mail.selectionStart, after = clean(before);
      if (after !== before) {
        var shift = before.length - after.length;
        mail.value = after;
        mail.setSelectionRange(Math.max(0, pos - shift), Math.max(0, pos - shift));
      }
    });
    mail.addEventListener('paste', function (ev) {
      var txt = (ev.clipboardData || window.clipboardData).getData('text');
      if (txt && clean(txt) !== txt) {
        ev.preventDefault();
        mail.value = clean(mail.value + txt);
      }
    });
  })();

  /* ---- концепция знака: подсветка сегмента ---- */
  (function () {
    var rows = q('#pillRows'), mark = q('#conceptMark');
    if (!rows || !mark) return;
    var items = [].slice.call(rows.querySelectorAll('.pill-row'));

    /* По умолчанию знак без зелёного — ничего не выбрано и не крутится сам. */
    function activate(i) {
      items.forEach(function (el, n) { el.classList.toggle('on', n === i); });
      mark.setAttribute('data-active', items[i].dataset.seg);
    }
    function hoverOn(el)  { mark.setAttribute('data-hover', el.dataset.seg); }
    function hoverOff()   { mark.removeAttribute('data-hover'); }

    items.forEach(function (el, i) {
      el.addEventListener('mouseenter', function () { hoverOn(el); });
      el.addEventListener('focus',      function () { hoverOn(el); });
      el.addEventListener('mouseleave', hoverOff);
      el.addEventListener('blur',       hoverOff);
      el.addEventListener('click',      function () { activate(i); });
    });
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

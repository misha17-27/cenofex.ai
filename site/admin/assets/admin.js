/* Показать/скрыть пароль */
document.addEventListener('click', function (ev) {
  var btn = ev.target.closest('.pw-toggle');
  if (!btn) return;
  var input = document.getElementById(btn.dataset.target);
  if (!input) return;
  var show = input.type === 'password';
  input.type = show ? 'text' : 'password';
  btn.querySelector('.eye').style.display = show ? 'none' : '';
  btn.querySelector('.eye-off').style.display = show ? '' : 'none';
  btn.setAttribute('aria-label', show ? 'Скрыть пароль' : 'Показать пароль');
});

/* Боковое меню на мобильном */
var b = document.getElementById('burger'), s = document.getElementById('sidebar');
if (b) b.addEventListener('click', function () { s.classList.toggle('open'); });

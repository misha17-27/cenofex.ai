<?php
/**
 * Язык интерфейса админ-панели: RU / EN / AZ.
 * Панель написана по-русски; при выборе EN или AZ весь текст
 * переводится на лету по словарю — копий страниц не требуется.
 * Чтобы добавить новую надпись — впишите её в admin_phrases().
 */

function admin_lang(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();

    // смена языка по ссылке ?ui=en / ?ui=az / ?ui=ru
    if (isset($_GET['ui'])) {
        $l = in_array($_GET['ui'], ['en', 'az'], true) ? $_GET['ui'] : 'ru';
        $_SESSION['admin_ui'] = $l;
        setcookie('admin_ui', $l, time() + 31536000, '/');
        return $l;
    }
    if (!empty($_SESSION['admin_ui'])) return $_SESSION['admin_ui'];
    if (!empty($_COOKIE['admin_ui']))  return in_array($_COOKIE['admin_ui'], ['en', 'az'], true) ? $_COOKIE['admin_ui'] : 'ru';
    return 'ru';
}

/** Адрес текущей страницы с переключённым языком. */
function admin_lang_url(string $l): string
{
    $q = $_GET; $q['ui'] = $l;
    return strtok($_SERVER['REQUEST_URI'], '?') . '?' . http_build_query($q);
}

/** Кнопки переключения языка для верхней панели. */
function admin_lang_switch(): string
{
    $cur = admin_lang();
    $out = '<div class="ui-lang">';
    foreach (['ru' => 'RU', 'en' => 'EN', 'az' => 'AZ'] as $code => $label) {
        $cls = $code === $cur ? ' class="active"' : '';
        $out .= '<a href="' . e(admin_lang_url($code)) . '"' . $cls . '>' . $label . '</a>';
    }
    return $out . '</div>';
}

/** Перевод готового HTML панели на выбранный язык. */
function admin_translate(string $html): string
{
    $l = admin_lang();
    if ($l === 'ru') return $html;                 // панель написана по-русски
    return strtr($html, admin_dict($l));
}

/**
 * Словарь переводов: русская фраза => [en, az].
 * strtr сам подставляет самое длинное совпадение.
 */
function admin_dict(string $lang = 'en'): array
{
    $i = ($lang === 'az') ? 1 : 0;
    $out = [];
    foreach (admin_phrases() as $ru => $tr) {
        $out[$ru] = is_array($tr) ? ($tr[$i] ?? $tr[0]) : $tr;
    }
    return $out;
}

/** ru => [английский, азербайджанский] */
function admin_phrases(): array
{
    return [
        // ---------- меню и общее ----------
        'Основное' => ['General', 'Əsas'], 'Контент' => ['Content', 'Kontent'], 'Настройки' => ['Settings', 'Parametrlər'],
        'Обзор' => ['Dashboard', 'İcmal'], 'Тексты сайта' => ['Site texts', 'Sayt mətnləri'], 'Услуги и решения' => ['Services & solutions', 'Xidmətlər və həllər'],
        'Партнёры' => ['Partners', 'Tərəfdaşlar'], 'Изображения сайта' => ['Site images', 'Sayt şəkilləri'], 'Изображения' => ['Images', 'Şəkillər'],
        'Тег на фото 1' => ['Photo tag 1', 'Foto teqi 1'], 'Тег на фото 2' => ['Photo tag 2', 'Foto teqi 2'],
        'Тег на фото 3' => ['Photo tag 3', 'Foto teqi 3'], 'Тег на фото 4' => ['Photo tag 4', 'Foto teqi 4'],
        'Тег на фото 5' => ['Photo tag 5', 'Foto teqi 5'],
        'Больше сценариев' => ['More use cases', 'Daha çox ssenari'],
        'Форма: телефон' => ['Form: phone', 'Forma: telefon'],
        'Форма: подсказка телефона' => ['Form: phone hint', 'Forma: telefon ipucu'],
        'Сообщение после отправки' => ['Message after sending', 'Göndərişdən sonrakı mesaj'],
        'Контакты и соцсети' => ['Contacts & social', 'Əlaqə və sosial şəbəkələr'],
        'Контакты на сайте' => ['Site contacts', 'Saytdakı əlaqə məlumatları'],
        'Слева — английская версия, справа — азербайджанская.' => ['English version on the left, Azerbaijani on the right.', 'Solda ingilis, sağda Azərbaycan versiyası.'],
        'Эти данные показываются в блоке «Контакты» и попадают в разметку для поисковиков.' => ['This data appears in the Contact block and in the markup for search engines.', 'Bu məlumatlar «Əlaqə» blokunda və axtarış sistemləri üçün işarələmədə göstərilir.'],
        'Номер телефона' => ['Phone number', 'Telefon nömrəsi'],
        'Адрес почты' => ['E-mail address', 'E-poçt ünvanı'],
        'Адрес' => ['Address', 'Ünvan'],
        'Номер WhatsApp' => ['WhatsApp number', 'WhatsApp nömrəsi'],
        'Ссылка получится такой' => ['The resulting link', 'Alınacaq keçid'],
        'кнопка скрыта' => ['button hidden', 'düymə gizlidir'],
        'Номер для круглой кнопки в правом нижнем углу сайта.' => ['Number for the round button in the bottom-right corner of the site.', 'Saytın sağ aşağı küncündəki dəyirmi düymə üçün nömrə.'],
        'Пишите как удобно — в ссылку уйдут только цифры. Оставьте поле пустым,' => ['Type it any way you like — only digits go into the link. Leave it empty', 'İstədiyiniz kimi yazın — keçidə yalnız rəqəmlər düşür. Boş buraxsanız,'],
        'чтобы использовался телефон из контактов выше.' => ['to use the phone number from the contacts above.', 'yuxarıdakı telefon nömrəsi istifadə olunacaq.'],
        'В номере WhatsApp нет ни одной цифры.' => ['The WhatsApp number contains no digits.', 'WhatsApp nömrəsində heç bir rəqəm yoxdur.'],
        'Социальные сети' => ['Social networks', 'Sosial şəbəkələr'],
        'Иконки в блоке «Контакты» показываются только для заполненных полей.' => ['Icons in the Contact block appear only for filled-in fields.', '«Əlaqə» blokundakı ikonlar yalnız doldurulmuş sahələr üçün görünür.'],
        'Протокол можно не писать — добавим сами.' => ['You can omit the protocol — we will add it.', 'Protokolu yazmaya bilərsiniz — özümüz əlavə edəcəyik.'],
        'Заявки с сайта' => ['Messages', 'Müraciətlər'], 'Заявки' => ['Messages', 'Müraciətlər'],
        'Почта (SMTP)' => ['Email (SMTP)', 'Poçt (SMTP)'], 'Безопасность' => ['Security', 'Təhlükəsizlik'],
        'Пользователи' => ['Users', 'İstifadəçilər'], 'Мой профиль' => ['My profile', 'Profilim'],
        'Открыть сайт' => ['Open website', 'Saytı aç'], 'Выйти' => ['Log out', 'Çıxış'],
        'Вы вошли как' => ['Signed in as', 'Daxil olmusunuz:'], 'Меню' => ['Menu', 'Menyu'],
        'Сохранено. Изменения уже на сайте.' => ['Saved. Changes are live on the website.', 'Yadda saxlanıldı. Dəyişikliklər artıq saytdadır.'],

        // ---------- вход ----------
        'Вход — CENOFEX' => ['Sign in — CENOFEX', 'Giriş — CENOFEX'], 'Вход в панель' => ['Sign in', 'Panelə giriş'],
        'Управление контентом сайта' => ['Website content management', 'Sayt kontentinin idarəsi'],
        'Неверный e-mail или пароль.' => ['Wrong e-mail or password.', 'E-mail və ya şifrə yanlışdır.'],
        'Слишком много попыток. Попробуйте через 15 минут.' => ['Too many attempts. Please try again in 15 minutes.', 'Çox sayda cəhd. 15 dəqiqədən sonra yenidən yoxlayın.'],
        'Не пройдена проверка «я не робот».' => ['Please complete the “I’m not a robot” check.', '«Mən robot deyiləm» yoxlaması tamamlanmayıb.'],
        'Пароль изменён. Войдите с новым паролем.' => ['Password changed. Please sign in with the new one.', 'Şifrə dəyişdirildi. Yeni şifrə ilə daxil olun.'],
        'Вы вышли из панели.' => ['You have been logged out.', 'Paneldən çıxdınız.'],
        'Забыли пароль?' => ['Forgot your password?', 'Şifrəni unutmusunuz?'],
        'Войти' => ['Sign in', 'Daxil ol'], 'Пароль' => ['Password', 'Şifrə'],
        'Показать пароль' => ['Show password', 'Şifrəni göstər'], 'Показать ключ' => ['Show key', 'Açarı göstər'],

        // ---------- восстановление пароля ----------
        'Восстановление пароля — CENOFEX' => ['Password recovery — CENOFEX', 'Şifrənin bərpası — CENOFEX'],
        'Пришлём ссылку для восстановления' => ['We will send you a recovery link', 'Bərpa linkini göndərəcəyik'],
        'Если такой e-mail зарегистрирован, письмо со ссылкой отправлено.' => ['If this e-mail is registered, a link has been sent.', 'Bu e-mail qeydiyyatdadırsa, link göndərildi.'],
        'Ссылка действует 60 минут. Проверьте папку «Спам».' => ['The link is valid for 60 minutes. Check your Spam folder.', 'Link 60 dəqiqə etibarlıdır. «Spam» qovluğunu yoxlayın.'],
        'Введите корректный e-mail.' => ['Please enter a valid e-mail.', 'Düzgün e-mail daxil edin.'],
        'Слишком много запросов. Попробуйте позже.' => ['Too many requests. Please try again later.', 'Çox sayda sorğu. Bir azdan yenidən cəhd edin.'],
        'Отправить ссылку' => ['Send link', 'Link göndər'], 'Вернуться ко входу' => ['Back to sign in', 'Girişə qayıt'],
        'Новый пароль — CENOFEX' => ['New password — CENOFEX', 'Yeni şifrə — CENOFEX'], 'Новый пароль' => ['New password', 'Yeni şifrə'],
        'Ссылка недействительна или устарела. Запросите восстановление заново.' => ['This link is invalid or expired. Please request a new one.', 'Link etibarsızdır və ya vaxtı keçib. Yenidən sorğu göndərin.'],
        'Запросить новую ссылку' => ['Request a new link', 'Yeni link istə'],
        'Повторите пароль' => ['Repeat password', 'Şifrəni təkrarlayın'], 'Повторите' => ['Repeat', 'Təkrarlayın'],
        'Сохранить пароль' => ['Save password', 'Şifrəni yadda saxla'],
        'Пароль — минимум 8 символов.' => ['Password must be at least 8 characters.', 'Şifrə minimum 8 simvol olmalıdır.'],
        'Новый пароль — минимум 8 символов.' => ['New password must be at least 8 characters.', 'Yeni şifrə minimum 8 simvol olmalıdır.'],
        'Пароли не совпадают.' => ['Passwords do not match.', 'Şifrələr uyğun gəlmir.'],

        // ---------- обзор ----------
        'текстовых блоков' => ['text blocks', 'mətn bloku'], 'карточек услуг/решений' => ['service/solution cards', 'xidmət/həll kartı'],
        'партнёров' => ['partners', 'tərəfdaş'], 'новых заявок' => ['new messages', 'yeni müraciət'],
        'Быстрые действия' => ['Quick actions', 'Sürətli əməliyyatlar'], 'С чего обычно начинают.' => ['Where most people start.', 'Adətən buradan başlayırlar.'],
        'Редактировать тексты' => ['Edit texts', 'Mətnləri redaktə et'], 'Добавить партнёра' => ['Add a partner', 'Tərəfdaş əlavə et'],
        'Кэш страниц' => ['Page cache', 'Səhifə keşi'],
        'Сайт отдаётся как статика — это делает его быстрым.' => ['The site is served as static files — that keeps it fast.', 'Sayt statik fayl kimi verilir — bu, onu sürətli edir.'],
        'Кэш обновляется сам при сохранении.' => ['The cache refreshes automatically when you save.', 'Keş yadda saxlayanda avtomatik yenilənir.'],
        'Английская:' => ['English:', 'İngilis:'], 'Азербайджанская:' => ['Azerbaijani:', 'Azərbaycan:'],
        'Английская версия' => ['English version', 'İngilis versiyası'], 'Азербайджанская версия' => ['Azerbaijani version', 'Azərbaycan versiyası'],
        'готова' => ['ready', 'hazırdır'], 'будет создана' => ['will be created', 'yaradılacaq'],
        'Обновить кэш' => ['Refresh cache', 'Keşi yenilə'], 'Страницы сайта' => ['Website pages', 'Sayt səhifələri'],
        'Капча не настроена — формы работают без защиты Cloudflare.' => ['Captcha is not configured — forms work without Cloudflare protection.', 'Kapça qurulmayıb — formalar Cloudflare qorumasız işləyir.'],
        'Добавьте ключи в разделе' => ['Add the keys in', 'Açarları bu bölmədə əlavə edin:'],

        // ---------- тексты сайта ----------
        'Тексты на двух языках' => ['Texts in both languages', 'İki dildə mətnlər'],
        'Слева — английский (основной), справа — азербайджанский. После сохранения сайт обновляется сразу.' => ['English (primary) on the left, Azerbaijani on the right. The site updates as soon as you save.', 'Solda ingilis (əsas), sağda Azərbaycan dili. Yadda saxladıqdan sonra sayt dərhal yenilənir.'],
        'Сохранить всё' => ['Save all', 'Hamısını yadda saxla'], 'Меню и футер' => ['Menu and footer', 'Menyu və futer'],
        'Слайд 1 (Trust)' => ['Slide 1 (Trust)', 'Slayd 1 (Trust)'], 'Слайд 2 (Technology)' => ['Slide 2 (Technology)', 'Slayd 2 (Technology)'],
        'Слайд 3 (CoE)' => ['Slide 3 (CoE)', 'Slayd 3 (CoE)'], 'Кнопки' => ['Buttons', 'Düymələr'], 'О компании' => ['About', 'Şirkət haqqında'],
        'Знак: 4 части (блок «The mark»)' => ['The mark: 4 parts', 'Loqo: 4 hissə («The mark» bloku)'],
        'Услуги — заголовок' => ['Services — heading', 'Xidmətlər — başlıq'], 'Технологии' => ['Technology', 'Texnologiya'],
        'Готовые решения' => ['Ready Solutions', 'Hazır həllər'], 'Контакты' => ['Contact', 'Əlaqə'],
        'Надзаголовок блока' => ['Section kicker', 'Blok üst başlığı'], 'Надзаголовок' => ['Kicker', 'Üst başlıq'],
        'Заголовок блока партнёров' => ['Partners block heading', 'Tərəfdaşlar bloku başlığı'], 'Заголовок блока' => ['Section heading', 'Blok başlığı'],
        'Заголовок — часть 1' => ['Heading — part 1', 'Başlıq — 1-ci hissə'], 'Заголовок — часть 2 (зелёная)' => ['Heading — part 2 (green)', 'Başlıq — 2-ci hissə (yaşıl)'],
        'Заголовок «Ready to Deploy»' => ['“Ready to Deploy” heading', '«Ready to Deploy» başlığı'],
        'Описание «Ready to Deploy»' => ['“Ready to Deploy” description', '«Ready to Deploy» təsviri'],
        'Итоговая плашка (Together — Transformation)' => ['Summary bar (Together — Transformation)', 'Yekun zolaq (Together — Transformation)'],
        'Плашка — заголовок' => ['Chip — title', 'Zolaq — başlıq'], 'Плашка — подпись' => ['Chip — subtitle', 'Zolaq — alt yazı'],
        'Плашка — текст' => ['Bar — text', 'Zolaq — mətn'], 'Подзаголовок' => ['Subtitle', 'Alt başlıq'],
        'Заголовок' => ['Heading', 'Başlıq'], 'Вступление' => ['Intro', 'Giriş mətni'], 'Копирайт' => ['Copyright', 'Müəllif hüququ'],
        'Абзац' => ['Paragraph', 'Abzas'], 'название' => ['name', 'ad'], 'описание (необязательно)' => ['description (optional)', 'təsvir (könüllü)'],
        'Пункт: О нас' => ['Menu: Who We Are', 'Menyu: Biz kimik'], 'Пункт: Услуги' => ['Menu: What We Do', 'Menyu: Nə edirik'],
        'Пункт: Технологии' => ['Menu: Technology', 'Menyu: Texnologiya'], 'Пункт: Решения' => ['Menu: Ready Solutions', 'Menyu: Hazır həllər'],
        'Пункт: Контакты' => ['Menu: Contact', 'Menyu: Əlaqə'],
        'Связаться' => ['Contact us', 'Əlaqə saxla'], 'О нас' => ['About us', 'Haqqımızda'], 'Запросить демо' => ['Request a demo', 'Demo sifariş et'],
        'Кнопка формы' => ['Form button', 'Forma düyməsi'],
        'Группа: Финансы и налоги' => ['Group: Finance & Tax', 'Qrup: Maliyyə və Vergi'], 'Группа: HR' => ['Group: HR', 'Qrup: HR'],
        'Подпись «Телефон»' => ['“Phone” label', '«Telefon» yazısı'], 'Подпись «E-mail»' => ['“E-mail” label', '«E-mail» yazısı'],
        'Подпись «Адрес»' => ['“Address” label', '«Ünvan» yazısı'], 'Номер телефона' => ['Phone number', 'Telefon nömrəsi'],
        'Адрес почты' => ['E-mail address', 'E-mail ünvanı'], 'Адрес' => ['Address', 'Ünvan'],
        'Форма: имя' => ['Form: name', 'Forma: ad'], 'Форма: e-mail' => ['Form: e-mail', 'Forma: e-mail'], 'Форма: сообщение' => ['Form: message', 'Forma: mesaj'],
        'Форма: подсказка имени' => ['Form: name placeholder', 'Forma: ad üçün ipucu'],
        'Форма: подсказка почты' => ['Form: e-mail placeholder', 'Forma: e-mail üçün ipucu'],
        'Форма: подсказка сообщения' => ['Form: message placeholder', 'Forma: mesaj üçün ipucu'],

        // ---------- услуги и решения ----------
        'Услуги (What We Do)' => ['Services (What We Do)', 'Xidmətlər (What We Do)'],
        'Готовые решения (Ready Solutions)' => ['Ready Solutions', 'Hazır həllər (Ready Solutions)'],
        'Ready to Deploy — Финансы и налоги' => ['Ready to Deploy — Finance & Tax', 'Ready to Deploy — Maliyyə və Vergi'],
        'Ready to Deploy — HR' => ['Ready to Deploy — HR', 'Ready to Deploy — HR'],
        'Порядок задаётся числом: чем меньше, тем выше. Снимите галочку, чтобы временно скрыть пункт.' => ['Order is set by number: lower comes first. Uncheck to hide an item temporarily.', 'Sıra rəqəmlə verilir: kiçik olan yuxarıda. Bəndi müvəqqəti gizlətmək üçün işarəni götürün.'],
        'Пока пусто — добавьте первый пункт ниже.' => ['Empty for now — add the first item below.', 'Hələ boşdur — aşağıda ilk bəndi əlavə edin.'],
        'Сохранить изменения' => ['Save changes', 'Dəyişiklikləri yadda saxla'], 'Добавить пункт' => ['Add item', 'Bənd əlavə et'],
        'Порядок' => ['Order', 'Sıra'], 'Показывать' => ['Visible', 'Göstər'], 'на сайте' => ['on the site', 'saytda'],
        'Описание' => ['Description', 'Təsvir'], 'Удалить' => ['Delete', 'Sil'], 'Удалить:' => ['Delete:', 'Sil:'],
        'Новый пункт' => ['New item', 'Yeni bənd'],

        // ---------- партнёры ----------
        'Лучше всего — логотип на прозрачном фоне (PNG/SVG), высотой от 100 px. До 3 МБ.' => ['A logo on a transparent background (PNG/SVG), at least 100 px tall, works best. Up to 3 MB.', 'Ən yaxşısı şəffaf fonda loqodur (PNG/SVG), hündürlüyü 100 px-dən çox. 3 MB-a qədər.'],
        'Название' => ['Name', 'Ad'], 'Ссылка на сайт (необязательно)' => ['Website link (optional)', 'Sayt linki (könüllü)'],
        'Файл логотипа' => ['Logo file', 'Loqo faylı'], 'Список партнёров' => ['Partner list', 'Tərəfdaşlar siyahısı'],
        'Логотипы показываются бегущей лентой на сайте. Порядок — по числу.' => ['Logos are shown in a moving strip on the site. Order is by number.', 'Loqolar saytda hərəkət edən lentdə göstərilir. Sıra rəqəmə görədir.'],
        'Логотип' => ['Logo', 'Loqo'], 'Ссылка' => ['Link', 'Link'],
        'Укажите название партнёра.' => ['Please enter the partner name.', 'Tərəfdaşın adını daxil edin.'],
        'Выберите файл логотипа.' => ['Please choose a logo file.', 'Loqo faylını seçin.'],
        'Допустимы JPG, PNG, WEBP или SVG.' => ['JPG, PNG, WEBP or SVG only.', 'Yalnız JPG, PNG, WEBP və ya SVG.'],
        'Допустимы JPG, PNG или WEBP.' => ['JPG, PNG or WEBP only.', 'Yalnız JPG, PNG və ya WEBP.'],
        'Ошибка загрузки файла.' => ['File upload error.', 'Fayl yüklənməsi xətası.'], 'Ошибка загрузки.' => ['Upload error.', 'Yükləmə xətası.'],
        'Файл больше 3 МБ.' => ['File is larger than 3 MB.', 'Fayl 3 MB-dan böyükdür.'], 'Файл больше 6 МБ.' => ['File is larger than 6 MB.', 'Fayl 6 MB-dan böyükdür.'],
        'Не удалось сохранить файл.' => ['Could not save the file.', 'Faylı yadda saxlamaq mümkün olmadı.'], 'Не удалось сохранить.' => ['Could not save.', 'Yadda saxlamaq mümkün olmadı.'],
        'Например: SAP' => ['For example: SAP', 'Məsələn: SAP'],

        // ---------- изображения ----------
        'Фотографии страницы' => ['Page photos', 'Səhifə şəkilləri'],
        'Можно загрузить свой файл или указать ссылку на изображение.' => ['Upload your own file or provide an image link.', 'Öz faylınızı yükləyə və ya şəkil linki göstərə bilərsiniz.'],
        'Рекомендуемый размер — не меньше 1600 px по ширине.' => ['Recommended width: at least 1600 px.', 'Tövsiyə olunan en: ən azı 1600 px.'],
        'Слайд 1 — фото' => ['Slide 1 — photo', 'Slayd 1 — şəkil'], 'Слайд 2 — фото' => ['Slide 2 — photo', 'Slayd 2 — şəkil'],
        'Слайд 3 — фото' => ['Slide 3 — photo', 'Slayd 3 — şəkil'], 'Блок «О компании» — фото' => ['About block — photo', '«Şirkət haqqında» bloku — şəkil'],
        'Загрузить новый файл (заменит ссылку):' => ['Upload a new file (replaces the link):', 'Yeni fayl yüklə (linki əvəz edir):'],
        'нет фото' => ['no photo', 'şəkil yoxdur'], 'Логотипы и брендовые файлы' => ['Logos and brand files', 'Loqolar və brend faylları'],
        'Лежат в папке' => ['They live in the folder', 'Bu qovluqdadır:'], 'на сервере. Меняются загрузкой файла с тем же именем' => ['on the server. Replace by uploading a file with the same name', 'serverdə. Eyni adlı fayl yükləməklə dəyişdirilir'],
        'через Диспетчер файлов cPanel) — так логотип обновится сразу везде.' => ['via cPanel File Manager) — the logo then updates everywhere at once.', 'cPanel Fayl Meneceri vasitəsilə) — loqo dərhal hər yerdə yenilənir.'],

        // ---------- SEO ----------
        'Поисковая оптимизация' => ['Search engine optimisation', 'Axtarış optimizasiyası'],
        'Эти данные видят Google и соцсети при отправке ссылки.' => ['This is what Google and social networks show.', 'Bu məlumatları Google və sosial şəbəkələr göstərir.'],
        'Заголовок страницы (EN)' => ['Page title (EN)', 'Səhifə başlığı (EN)'], 'Заголовок страницы (AZ)' => ['Page title (AZ)', 'Səhifə başlığı (AZ)'],
        'Описание (EN)' => ['Description (EN)', 'Təsvir (EN)'], 'Описание (AZ)' => ['Description (AZ)', 'Təsvir (AZ)'],
        'в результатах поиска. Оптимально 50–60 символов.' => ['in search results. Ideally 50–60 characters.', 'axtarış nəticələrində. Optimal 50–60 simvol.'],
        'в поиске. Оптимально 140–160 символов.' => ['in search. Ideally 140–160 characters.', 'axtarışda. Optimal 140–160 simvol.'],
        'Картинка для соцсетей' => ['Social sharing image', 'Sosial şəbəkələr üçün şəkil'],
        'Путь от корня сайта, например /images/logo-white-text.png' => ['Path from site root, e.g. /images/logo-white-text.png', 'Sayt kökündən yol, məsələn /images/logo-white-text.png'],
        'Почта для заявок с формы' => ['E-mail for form submissions', 'Müraciətlər üçün e-mail'],
        'Куда приходят сообщения из формы обратной связи.' => ['Where contact form messages are delivered.', 'Əlaqə formasından mesajlar hara gəlir.'],
        'Полная ссылка или # если не нужно.' => ['Full link, or # if not needed.', 'Tam link və ya lazım deyilsə #.'],

        // ---------- заявки ----------
        'Сообщения из формы обратной связи' => ['Contact form messages', 'Əlaqə formasından mesajlar'],
        'Всего:' => ['Total:', 'Cəmi:'], 'Заявок пока нет.' => ['No messages yet.', 'Hələ müraciət yoxdur.'],
        'Заявки также дублируются на почту' => ['Messages are also sent to', 'Müraciətlər həm də bu ünvana göndərilir:'],
        'Отметить все прочитанными' => ['Mark all as read', 'Hamısını oxunmuş kimi işarələ'],
        'Пометить непрочитанной' => ['Mark as unread', 'Oxunmamış kimi işarələ'], 'Прочитано' => ['Mark as read', 'Oxundu'],
        'новая' => ['new', 'yeni'], 'Телефон:' => ['Phone:', 'Telefon:'], 'Удалить заявку?' => ['Delete this message?', 'Müraciəti silmək?'],
        'Таблица заявок ещё не создана. Запустите' => ['The messages table does not exist yet. Run', 'Müraciətlər cədvəli hələ yaradılmayıb. Bunu işə salın:'],
        'он добавит недостающие таблицы и не тронет существующие данные.' => ['— it adds the missing tables and leaves existing data untouched.', '— çatışmayan cədvəlləri əlavə edir, mövcud məlumatlara toxunmur.'],

        // ---------- безопасность ----------
        'Cloudflare Turnstile (капча)' => ['Cloudflare Turnstile (captcha)', 'Cloudflare Turnstile (kapça)'],
        'Защищает форму на сайте и вход в панель от ботов.' => ['Protects the website form and admin login from bots.', 'Sayt formasını və panelə girişi botlardan qoruyur.'],
        'Статус:' => ['Status:', 'Status:'], 'включена' => ['enabled', 'aktivdir'], 'выключена — ключи не заданы' => ['disabled — keys not set', 'deaktivdir — açarlar verilməyib'],
        'публичный ключ)' => ['public key)', 'ictimai açar)'], 'секретный ключ)' => ['secret key)', 'gizli açar)'],
        'Где взять ключи' => ['Where to get the keys', 'Açarları haradan almalı'],
        'бесплатен и не требует от посетителя разгадывать картинки.' => ['is free and does not ask visitors to solve puzzles.', 'pulsuzdur və ziyarətçidən şəkil tapmağı tələb etmir.'],
        'Зайдите в' => ['Go to', 'Daxil olun:'], 'раздел' => ['section', 'bölmə'], 'Нажмите' => ['Click', 'Basın'],
        'укажите' => ['enter', 'daxil edin'], 'Скопируйте' => ['Copy', 'Kopyalayın'], 'в поля выше' => ['into the fields above', 'yuxarıdakı xanalara'],
        'Что уже защищено' => ['What is already protected', 'Nə artıq qorunur'],
        'Пароли хранятся в виде необратимого хэша' => ['Passwords are stored as irreversible hashes', 'Şifrələr geri qaytarılmayan heş şəklində saxlanılır'],
        'Все формы защищены от CSRF (подделки запросов)' => ['All forms are protected against CSRF', 'Bütün formalar CSRF-dən qorunur'],
        'Ограничение попыток входа: 10 за 15 минут с одного IP' => ['Login limit: 10 attempts per 15 minutes per IP', 'Giriş limiti: bir IP-dən 15 dəqiqəyə 10 cəhd'],
        'Ограничение отправок формы: 5 за час с одного IP' => ['Form limit: 5 submissions per hour per IP', 'Forma limiti: bir IP-dən saatda 5 göndərmə'],
        'Скрытая ловушка для ботов в форме обратной связи' => ['Hidden honeypot field in the contact form', 'Əlaqə formasında botlar üçün gizli tələ'],
        'Проверка типа файлов при загрузке, запрет выполнения PHP в папке загрузок' => ['File type checks on upload; PHP execution disabled in the uploads folder', 'Yüklənən faylların tipi yoxlanılır, yükləmə qovluğunda PHP icrası qadağandır'],
        'Защита от подстановки заголовков в письмах' => ['Protection against e-mail header injection', 'Məktub başlıqlarının dəyişdirilməsindən qoruma'],

        // ---------- почта ----------
        'Настройки SMTP' => ['SMTP settings', 'SMTP parametrləri'],
        'Через SMTP письма доходят надёжнее, чем через стандартную функцию сервера.' => ['E-mails delivered over SMTP are more reliable than the server default.', 'SMTP ilə məktublar serverin standart funksiyasından daha etibarlı çatır.'],
        'Способ отправки сейчас:' => ['Current delivery method:', 'Hazırkı göndərmə üsulu:'],
        'стандартная функция mail()' => ['built-in mail() function', 'standart mail() funksiyası'],
        'SMTP-сервер' => ['SMTP host', 'SMTP server'], 'Порт' => ['Port', 'Port'], 'Шифрование' => ['Encryption', 'Şifrələmə'],
        'без шифрования' => ['no encryption', 'şifrələmə yoxdur'],
        'Пользователь (обычно полный адрес почты)' => ['Username (usually the full e-mail address)', 'İstifadəçi (adətən tam e-mail ünvanı)'],
        'сохранён — оставьте пустым' => ['saved — leave empty to keep', 'saxlanılıb — boş buraxın'],
        'Адрес отправителя' => ['From address', 'Göndərən ünvanı'], 'Имя отправителя' => ['From name', 'Göndərən adı'],
        'Проверка отправки' => ['Delivery test', 'Göndərmə yoxlaması'],
        'Отправим тестовое письмо, чтобы убедиться, что настройки верные.' => ['We will send a test e-mail to confirm the settings are correct.', 'Parametrlərin düzgünlüyünü yoxlamaq üçün test məktubu göndərəcəyik.'],
        'Адрес получателя' => ['Recipient address', 'Alıcı ünvanı'], 'Отправить тест' => ['Send test', 'Test göndər'],
        'Укажите корректный адрес для проверки.' => ['Please enter a valid address for the test.', 'Yoxlama üçün düzgün ünvan daxil edin.'],
        'Письмо отправлено на' => ['Test e-mail sent to', 'Məktub göndərildi:'], 'Проверьте входящие и «Спам».' => ['Check your inbox and Spam folder.', 'Gələnlər və «Spam» qovluğunu yoxlayın.'],
        'Не отправлено.' => ['Not sent.', 'Göndərilmədi.'], 'Где взять данные' => ['Where to find these details', 'Məlumatları haradan almalı'],
        'у нужного ящика нажмите' => ['and for the mailbox click', 'lazımi poçt qutusunda basın'],
        'Там указаны сервер, порт и способ шифрования.' => ['It lists the host, port and encryption method.', 'Orada server, port və şifrələmə üsulu göstərilib.'],
        'Пользователь — полный адрес почты, пароль — от этого ящика.' => ['Username is the full e-mail address; password is the mailbox password.', 'İstifadəçi — tam e-mail ünvanı, şifrə — həmin qutunun şifrəsi.'],

        // ---------- пользователи ----------
        'Добавить пользователя' => ['Add user', 'İstifadəçi əlavə et'],
        'Роль «Администратор» даёт доступ к управлению пользователями. «Редактор» — только контент.' => ['The Administrator role grants user management. Editors can only manage content.', '«Administrator» rolu istifadəçiləri idarə etməyə imkan verir. «Redaktor» yalnız kontenti idarə edir.'],
        'Роль' => ['Role', 'Rol'], 'Администратор' => ['Administrator', 'Administrator'], 'Редактор' => ['Editor', 'Redaktor'],
        'Добавить' => ['Add', 'Əlavə et'], 'Все пользователи' => ['All users', 'Bütün istifadəçilər'],
        'Поле пароля оставьте пустым, если менять его не нужно.' => ['Leave the password field empty to keep the current one.', 'Şifrəni dəyişmək lazım deyilsə, xananı boş buraxın.'],
        'Новый пароль' => ['New password', 'Yeni şifrə'], 'Активен' => ['Active', 'Aktiv'], 'Вход' => ['Last login', 'Son giriş'],
        'это вы' => ['you', 'bu sizsiniz'], 'Пользователь с таким e-mail уже есть.' => ['A user with this e-mail already exists.', 'Bu e-mail ilə istifadəçi artıq var.'],
        'Нельзя удалить самого себя.' => ['You cannot delete your own account.', 'Öz hesabınızı silə bilməzsiniz.'],
        'Укажите имя.' => ['Please enter a name.', 'Ad daxil edin.'], 'Некорректный e-mail.' => ['Invalid e-mail address.', 'Yanlış e-mail ünvanı.'],

        // ---------- профиль ----------
        'Профиль' => ['Profile', 'Profil'], 'E-mail (логин):' => ['E-mail (login):', 'E-mail (giriş):'], 'роль:' => ['role:', 'rol:'],
        'Смена пароля' => ['Change password', 'Şifrənin dəyişdirilməsi'],
        'Заполните, только если хотите изменить пароль.' => ['Fill in only if you want to change your password.', 'Yalnız şifrəni dəyişmək istəyirsinizsə doldurun.'],
        'Текущий пароль указан неверно.' => ['The current password is incorrect.', 'Cari şifrə yanlışdır.'],
        'Текущий пароль' => ['Current password', 'Cari şifrə'],

        // ---------- общие слова ----------
        'Сохранить' => ['Save', 'Yadda saxla'], 'Имя' => ['Name', 'Ad'], 'Пароль' => ['Password', 'Şifrə'],
    ];
}

<?php
/**
 * Язык интерфейса админ-панели (RU / EN).
 * Панель написана по-русски; при выборе EN весь текст переводится
 * на лету по словарю — отдельных копий страниц не требуется.
 */

function admin_lang(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();

    // смена языка по ссылке ?ui=en / ?ui=ru
    if (isset($_GET['ui'])) {
        $l = $_GET['ui'] === 'en' ? 'en' : 'ru';
        $_SESSION['admin_ui'] = $l;
        setcookie('admin_ui', $l, time() + 31536000, '/');
        return $l;
    }
    if (!empty($_SESSION['admin_ui'])) return $_SESSION['admin_ui'];
    if (!empty($_COOKIE['admin_ui']))  return $_COOKIE['admin_ui'] === 'en' ? 'en' : 'ru';
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
    foreach (['ru' => 'RU', 'en' => 'EN'] as $code => $label) {
        $cls = $code === $cur ? ' class="active"' : '';
        $out .= '<a href="' . e(admin_lang_url($code)) . '"' . $cls . '>' . $label . '</a>';
    }
    return $out . '</div>';
}

/** Перевод готового HTML панели на английский. */
function admin_translate(string $html): string
{
    if (admin_lang() !== 'en') return $html;
    return strtr($html, admin_dict());
}

/** Словарь: русская фраза => английская. strtr сам берёт самое длинное совпадение. */
function admin_dict(): array
{
    return [
        // ---------- меню и общее ----------
        'Основное' => 'General', 'Контент' => 'Content', 'Настройки' => 'Settings',
        'Обзор' => 'Dashboard', 'Тексты сайта' => 'Site texts', 'Услуги и решения' => 'Services & solutions',
        'Партнёры' => 'Partners', 'Изображения сайта' => 'Site images', 'Изображения' => 'Images',
        'Заявки с сайта' => 'Messages', 'Заявки' => 'Messages',
        'Почта (SMTP)' => 'Email (SMTP)', 'Безопасность' => 'Security',
        'Пользователи' => 'Users', 'Мой профиль' => 'My profile',
        'Открыть сайт' => 'Open website', 'Выйти' => 'Log out',
        'Вы вошли как' => 'Signed in as', 'Меню' => 'Menu',
        'Сохранено. Изменения уже на сайте.' => 'Saved. Changes are live on the website.',

        // ---------- вход ----------
        'Вход — CENOFEX' => 'Sign in — CENOFEX', 'Вход в панель' => 'Sign in',
        'Управление контентом сайта' => 'Website content management',
        'Неверный e-mail или пароль.' => 'Wrong e-mail or password.',
        'Слишком много попыток. Попробуйте через 15 минут.' => 'Too many attempts. Please try again in 15 minutes.',
        'Не пройдена проверка «я не робот».' => 'Please complete the “I’m not a robot” check.',
        'Пароль изменён. Войдите с новым паролем.' => 'Password changed. Please sign in with the new one.',
        'Вы вышли из панели.' => 'You have been logged out.',
        'Забыли пароль?' => 'Forgot your password?',
        'Войти' => 'Sign in', 'Пароль' => 'Password',
        'Показать пароль' => 'Show password', 'Показать ключ' => 'Show key',

        // ---------- восстановление пароля ----------
        'Восстановление пароля — CENOFEX' => 'Password recovery — CENOFEX',
        'Пришлём ссылку для восстановления' => 'We will send you a recovery link',
        'Если такой e-mail зарегистрирован, письмо со ссылкой отправлено.' => 'If this e-mail is registered, a link has been sent.',
        'Ссылка действует 60 минут. Проверьте папку «Спам».' => 'The link is valid for 60 minutes. Check your Spam folder.',
        'Введите корректный e-mail.' => 'Please enter a valid e-mail.',
        'Слишком много запросов. Попробуйте позже.' => 'Too many requests. Please try again later.',
        'Отправить ссылку' => 'Send link', 'Вернуться ко входу' => 'Back to sign in',
        'Новый пароль — CENOFEX' => 'New password — CENOFEX', 'Новый пароль' => 'New password',
        'Ссылка недействительна или устарела. Запросите восстановление заново.' => 'This link is invalid or expired. Please request a new one.',
        'Запросить новую ссылку' => 'Request a new link',
        'Повторите пароль' => 'Repeat password', 'Повторите' => 'Repeat',
        'Сохранить пароль' => 'Save password',
        'Пароль — минимум 8 символов.' => 'Password must be at least 8 characters.',
        'Новый пароль — минимум 8 символов.' => 'New password must be at least 8 characters.',
        'Пароли не совпадают.' => 'Passwords do not match.',

        // ---------- обзор ----------
        'текстовых блоков' => 'text blocks', 'карточек услуг/решений' => 'service/solution cards',
        'партнёров' => 'partners', 'новых заявок' => 'new messages',
        'Быстрые действия' => 'Quick actions', 'С чего обычно начинают.' => 'Where most people start.',
        'Редактировать тексты' => 'Edit texts', 'Добавить партнёра' => 'Add a partner',
        'Кэш страниц' => 'Page cache',
        'Сайт отдаётся как статика — это делает его быстрым.' => 'The site is served as static files — that keeps it fast.',
        'Кэш обновляется сам при сохранении.' => 'The cache refreshes automatically when you save.',
        'Английская:' => 'English:', 'Азербайджанская:' => 'Azerbaijani:',
        'Английская версия' => 'English version', 'Азербайджанская версия' => 'Azerbaijani version',
        'готова' => 'ready', 'будет создана' => 'will be created',
        'Обновить кэш' => 'Refresh cache', 'Страницы сайта' => 'Website pages',
        'Капча не настроена — формы работают без защиты Cloudflare.' => 'Captcha is not configured — forms work without Cloudflare protection.',
        'Добавьте ключи в разделе' => 'Add the keys in',

        // ---------- тексты сайта ----------
        'Тексты на двух языках' => 'Texts in both languages',
        'Слева — английский (основной), справа — азербайджанский. После сохранения сайт обновляется сразу.'
            => 'English (primary) on the left, Azerbaijani on the right. The site updates as soon as you save.',
        'Сохранить всё' => 'Save all', 'Меню и футер' => 'Menu and footer',
        'Слайд 1 (Trust)' => 'Slide 1 (Trust)', 'Слайд 2 (Technology)' => 'Slide 2 (Technology)',
        'Слайд 3 (CoE)' => 'Slide 3 (CoE)', 'Кнопки' => 'Buttons', 'О компании' => 'About',
        'Знак: 4 части (блок «The mark»)' => 'The mark: 4 parts',
        'Услуги — заголовок' => 'Services — heading', 'Технологии' => 'Technology',
        'Готовые решения' => 'Ready Solutions', 'Контакты' => 'Contact',
        'Надзаголовок блока' => 'Section kicker', 'Надзаголовок' => 'Kicker',
        'Заголовок блока партнёров' => 'Partners block heading', 'Заголовок блока' => 'Section heading',
        'Заголовок — часть 1' => 'Heading — part 1', 'Заголовок — часть 2 (зелёная)' => 'Heading — part 2 (green)',
        'Заголовок «Ready to Deploy»' => '“Ready to Deploy” heading',
        'Описание «Ready to Deploy»' => '“Ready to Deploy” description',
        'Итоговая плашка (Together — Transformation)' => 'Summary bar (Together — Transformation)',
        'Плашка — заголовок' => 'Chip — title', 'Плашка — подпись' => 'Chip — subtitle',
        'Плашка — текст' => 'Bar — text', 'Подзаголовок' => 'Subtitle',
        'Заголовок' => 'Heading', 'Вступление' => 'Intro', 'Копирайт' => 'Copyright',
        'Абзац' => 'Paragraph', 'название' => 'name', 'описание (необязательно)' => 'description (optional)',
        'Пункт: О нас' => 'Menu: Who We Are', 'Пункт: Услуги' => 'Menu: What We Do',
        'Пункт: Технологии' => 'Menu: Technology', 'Пункт: Решения' => 'Menu: Ready Solutions',
        'Пункт: Контакты' => 'Menu: Contact',
        'Связаться' => 'Contact us', 'О нас' => 'About us', 'Запросить демо' => 'Request a demo',
        'Кнопка формы' => 'Form button',
        'Группа: Финансы и налоги' => 'Group: Finance & Tax', 'Группа: HR' => 'Group: HR',
        'Подпись «Телефон»' => '“Phone” label', 'Подпись «E-mail»' => '“E-mail” label',
        'Подпись «Адрес»' => '“Address” label', 'Номер телефона' => 'Phone number',
        'Адрес почты' => 'E-mail address', 'Адрес' => 'Address',
        'Форма: имя' => 'Form: name', 'Форма: e-mail' => 'Form: e-mail', 'Форма: сообщение' => 'Form: message',
        'Форма: подсказка имени' => 'Form: name placeholder',
        'Форма: подсказка почты' => 'Form: e-mail placeholder',
        'Форма: подсказка сообщения' => 'Form: message placeholder',

        // ---------- услуги и решения ----------
        'Услуги (What We Do)' => 'Services (What We Do)',
        'Готовые решения (Ready Solutions)' => 'Ready Solutions',
        'Ready to Deploy — Финансы и налоги' => 'Ready to Deploy — Finance & Tax',
        'Ready to Deploy — HR' => 'Ready to Deploy — HR',
        'Порядок задаётся числом: чем меньше, тем выше. Снимите галочку, чтобы временно скрыть пункт.'
            => 'Order is set by number: lower comes first. Uncheck to hide an item temporarily.',
        'Пока пусто — добавьте первый пункт ниже.' => 'Empty for now — add the first item below.',
        'Сохранить изменения' => 'Save changes', 'Добавить пункт' => 'Add item',
        'Порядок' => 'Order', 'Показывать' => 'Visible', 'на сайте' => 'on the site',
        'Описание' => 'Description', 'Удалить' => 'Delete', 'Удалить:' => 'Delete:',
        'Новый пункт' => 'New item',

        // ---------- партнёры ----------
        'Лучше всего — логотип на прозрачном фоне (PNG/SVG), высотой от 100 px. До 3 МБ.'
            => 'A logo on a transparent background (PNG/SVG), at least 100 px tall, works best. Up to 3 MB.',
        'Название' => 'Name', 'Ссылка на сайт (необязательно)' => 'Website link (optional)',
        'Файл логотипа' => 'Logo file', 'Список партнёров' => 'Partner list',
        'Логотипы показываются бегущей лентой на сайте. Порядок — по числу.'
            => 'Logos are shown in a moving strip on the site. Order is by number.',
        'Логотип' => 'Logo', 'Ссылка' => 'Link',
        'Укажите название партнёра.' => 'Please enter the partner name.',
        'Выберите файл логотипа.' => 'Please choose a logo file.',
        'Допустимы JPG, PNG, WEBP или SVG.' => 'JPG, PNG, WEBP or SVG only.',
        'Допустимы JPG, PNG или WEBP.' => 'JPG, PNG or WEBP only.',
        'Ошибка загрузки файла.' => 'File upload error.', 'Ошибка загрузки.' => 'Upload error.',
        'Файл больше 3 МБ.' => 'File is larger than 3 MB.', 'Файл больше 6 МБ.' => 'File is larger than 6 MB.',
        'Не удалось сохранить файл.' => 'Could not save the file.', 'Не удалось сохранить.' => 'Could not save.',
        'Например: SAP' => 'For example: SAP',

        // ---------- изображения ----------
        'Фотографии страницы' => 'Page photos',
        'Можно загрузить свой файл или указать ссылку на изображение.' => 'Upload your own file or provide an image link.',
        'Рекомендуемый размер — не меньше 1600 px по ширине.' => 'Recommended width: at least 1600 px.',
        'Слайд 1 — фото' => 'Slide 1 — photo', 'Слайд 2 — фото' => 'Slide 2 — photo',
        'Слайд 3 — фото' => 'Slide 3 — photo', 'Блок «О компании» — фото' => 'About block — photo',
        'Загрузить новый файл (заменит ссылку):' => 'Upload a new file (replaces the link):',
        'нет фото' => 'no photo', 'Логотипы и брендовые файлы' => 'Logos and brand files',
        'Лежат в папке' => 'They live in the folder', 'на сервере. Меняются загрузкой файла с тем же именем'
            => 'on the server. Replace by uploading a file with the same name',
        'через Диспетчер файлов cPanel) — так логотип обновится сразу везде.'
            => 'via cPanel File Manager) — the logo then updates everywhere at once.',

        // ---------- SEO ----------
        'Поисковая оптимизация' => 'Search engine optimisation',
        'Эти данные видят Google и соцсети при отправке ссылки.' => 'This is what Google and social networks show.',
        'Заголовок страницы (EN)' => 'Page title (EN)', 'Заголовок страницы (AZ)' => 'Page title (AZ)',
        'Описание (EN)' => 'Description (EN)', 'Описание (AZ)' => 'Description (AZ)',
        'в результатах поиска. Оптимально 50–60 символов.' => 'in search results. Ideally 50–60 characters.',
        'в поиске. Оптимально 140–160 символов.' => 'in search. Ideally 140–160 characters.',
        'Картинка для соцсетей' => 'Social sharing image',
        'Путь от корня сайта, например /images/logo-white-text.png' => 'Path from site root, e.g. /images/logo-white-text.png',
        'Почта для заявок с формы' => 'E-mail for form submissions',
        'Куда приходят сообщения из формы обратной связи.' => 'Where contact form messages are delivered.',
        'Полная ссылка или # если не нужно.' => 'Full link, or # if not needed.',

        // ---------- заявки ----------
        'Сообщения из формы обратной связи' => 'Contact form messages',
        'Всего:' => 'Total:', 'Заявок пока нет.' => 'No messages yet.',
        'Заявки также дублируются на почту' => 'Messages are also sent to',
        'Отметить все прочитанными' => 'Mark all as read',
        'Пометить непрочитанной' => 'Mark as unread', 'Прочитано' => 'Mark as read',
        'новая' => 'new', 'Телефон:' => 'Phone:', 'Удалить заявку?' => 'Delete this message?',
        'Таблица заявок ещё не создана. Запустите' => 'The messages table does not exist yet. Run',
        'он добавит недостающие таблицы и не тронет существующие данные.'
            => '— it adds the missing tables and leaves existing data untouched.',

        // ---------- безопасность ----------
        'Cloudflare Turnstile (капча)' => 'Cloudflare Turnstile (captcha)',
        'Защищает форму на сайте и вход в панель от ботов.' => 'Protects the website form and admin login from bots.',
        'Статус:' => 'Status:', 'включена' => 'enabled', 'выключена — ключи не заданы' => 'disabled — keys not set',
        'публичный ключ)' => 'public key)', 'секретный ключ)' => 'secret key)',
        'Где взять ключи' => 'Where to get the keys',
        'бесплатен и не требует от посетителя разгадывать картинки.' => 'is free and does not ask visitors to solve puzzles.',
        'Зайдите в' => 'Go to', 'раздел' => 'section', 'Нажмите' => 'Click',
        'укажите' => 'enter', 'Скопируйте' => 'Copy', 'в поля выше' => 'into the fields above',
        'Что уже защищено' => 'What is already protected',
        'Пароли хранятся в виде необратимого хэша' => 'Passwords are stored as irreversible hashes',
        'Все формы защищены от CSRF (подделки запросов)' => 'All forms are protected against CSRF',
        'Ограничение попыток входа: 10 за 15 минут с одного IP' => 'Login limit: 10 attempts per 15 minutes per IP',
        'Ограничение отправок формы: 5 за час с одного IP' => 'Form limit: 5 submissions per hour per IP',
        'Скрытая ловушка для ботов в форме обратной связи' => 'Hidden honeypot field in the contact form',
        'Проверка типа файлов при загрузке, запрет выполнения PHP в папке загрузок'
            => 'File type checks on upload; PHP execution disabled in the uploads folder',
        'Защита от подстановки заголовков в письмах' => 'Protection against e-mail header injection',

        // ---------- почта ----------
        'Настройки SMTP' => 'SMTP settings',
        'Через SMTP письма доходят надёжнее, чем через стандартную функцию сервера.'
            => 'E-mails delivered over SMTP are more reliable than the server default.',
        'Способ отправки сейчас:' => 'Current delivery method:',
        'стандартная функция mail()' => 'built-in mail() function',
        'SMTP-сервер' => 'SMTP host', 'Порт' => 'Port', 'Шифрование' => 'Encryption',
        'без шифрования' => 'no encryption',
        'Пользователь (обычно полный адрес почты)' => 'Username (usually the full e-mail address)',
        'сохранён — оставьте пустым' => 'saved — leave empty to keep',
        'Адрес отправителя' => 'From address', 'Имя отправителя' => 'From name',
        'Проверка отправки' => 'Delivery test',
        'Отправим тестовое письмо, чтобы убедиться, что настройки верные.'
            => 'We will send a test e-mail to confirm the settings are correct.',
        'Адрес получателя' => 'Recipient address', 'Отправить тест' => 'Send test',
        'Укажите корректный адрес для проверки.' => 'Please enter a valid address for the test.',
        'Письмо отправлено на' => 'Test e-mail sent to', 'Проверьте входящие и «Спам».' => 'Check your inbox and Spam folder.',
        'Не отправлено.' => 'Not sent.', 'Где взять данные' => 'Where to find these details',
        'у нужного ящика нажмите' => 'and for the mailbox click',
        'Там указаны сервер, порт и способ шифрования.' => 'It lists the host, port and encryption method.',
        'Пользователь — полный адрес почты, пароль — от этого ящика.' => 'Username is the full e-mail address; password is the mailbox password.',

        // ---------- пользователи ----------
        'Добавить пользователя' => 'Add user',
        'Роль «Администратор» даёт доступ к управлению пользователями. «Редактор» — только контент.'
            => 'The Administrator role grants user management. Editors can only manage content.',
        'Роль' => 'Role', 'Администратор' => 'Administrator', 'Редактор' => 'Editor',
        'Добавить' => 'Add', 'Все пользователи' => 'All users',
        'Поле пароля оставьте пустым, если менять его не нужно.' => 'Leave the password field empty to keep the current one.',
        'Новый пароль' => 'New password', 'Активен' => 'Active', 'Вход' => 'Last login',
        'это вы' => 'you', 'Пользователь с таким e-mail уже есть.' => 'A user with this e-mail already exists.',
        'Нельзя удалить самого себя.' => 'You cannot delete your own account.',
        'Укажите имя.' => 'Please enter a name.', 'Некорректный e-mail.' => 'Invalid e-mail address.',

        // ---------- профиль ----------
        'Профиль' => 'Profile', 'E-mail (логин):' => 'E-mail (login):', 'роль:' => 'role:',
        'Смена пароля' => 'Change password',
        'Заполните, только если хотите изменить пароль.' => 'Fill in only if you want to change your password.',
        'Текущий пароль указан неверно.' => 'The current password is incorrect.',
        'Текущий пароль' => 'Current password',

        // ---------- общие слова ----------
        'Сохранить' => 'Save', 'Имя' => 'Name', 'Пароль' => 'Password',
    ];
}

# ТЗ для Codex — Одностраничный сайт CENOFEX

## 1. Задача
Создать **одностраничный сайт (landing page)** для компании **CENOFEX** в папке `D:\cenofex.ai`.
Сделать **3 разных варианта дизайна** одной и той же страницы (разная композиция, hero, расположение блоков — но единый бренд и одинаковое содержание).

Структура папок:
```
D:\cenofex.ai\
├── version-1\index.html   (+ css/js при необходимости)
├── version-2\index.html
├── version-3\index.html
└── images\                (уже существует — логотип, favicon, фото)
```
Все три версии должны корректно ссылаться на файлы из `D:\cenofex.ai\images`.

## 2. Технические требования
- Чистый **HTML + CSS + JS** (без сборщиков и фреймворков; допустимо подключение шрифтов и иконок через CDN). Каждая версия — самодостаточная.
- **Адаптивность (mobile-first):** корректно на телефоне, планшете, десктопе.
- **Two languages / İki dil:** переключатель языка **EN / AZ** в шапке. Язык по умолчанию — **English**.
  - Реализовать через `data-i18n` атрибуты + JSON-словарь (объект `translations`) и переключение без перезагрузки страницы. Выбор языка сохранять в `localStorage`.
- Плавная прокрутка к секциям по клику в меню (anchor-навигация).
- Семантическая вёрстка, alt у изображений, базовое SEO (`<title>`, meta description, favicon, Open Graph).
- Быстрая загрузка: оптимизировать/сжать крупные фото.

## 3. Фирменный стиль (из «Brand Guidelines CENOFEX.pdf»)

### Цвета
| Название | HEX |
|---|---|
| Cenofex Green (основной) | `#02A78E` |
| Deep Teal (акцент) | `#048282` |
| Light Gray (фон) | `#F6F6F6` |
| White | `#FFFFFF` |
| Dark Gray (текст) | `#1C1C1C` |
| Black | `#000000` |

Основной акцент — зелёный `#02A78E`; вторичный/hover — `#048282`. Фон светлый (`#FFFFFF` / `#F6F6F6`), текст `#1C1C1C`.

### Шрифты
- **Заголовки:** Gilroy (Light/Regular/Bold/Heavy). Gilroy — платный, в вебе подключить его при наличии; **fallback — Manrope ExtraBold** (Google Fonts).
- **Основной текст:** Manrope (Google Fonts) — Light/Regular/Bold/ExtraBold.
- **Fallback стек:** `'Manrope', Arial, sans-serif`.

### Логотип
- Файлы в `images\` (`LOGO.pdf`, `LOGO.ai` — для веба подготовить/использовать PNG-версию, напр. `1.png` или экспортировать SVG/PNG на прозрачном фоне).
- Favicon — использовать соответствующий файл из `images\`.
- **Не растягивать, не поворачивать, не отражать, не помещать в рамку** логотип (по правилам брендбука). Соблюдать защитное поле вокруг логотипа.

### Визуальный стиль
Минимализм, много воздуха, геометрия, зелёные акценты на светлом фоне. Использовать фирменный паттерн/геометрические элементы из брендбука как декоративный фон в hero и разделителях.

## 4. Навигация (шапка)
Логотип слева + меню + переключатель языка **EN/AZ**.

Пункты меню:

| EN | AZ |
|---|---|
| About Us | Haqqımızda |
| Services | Xidmətlər |
| Solutions | Həllər |
| Sectors | Sektorlar |
| Contact | Əlaqə |

Шапка фиксированная (sticky) при прокрутке.

## 5. Секции страницы (сверху вниз)

### 5.1 Hero (Логотип + слайд/баннер)
- Логотип и крупный слоган.
- **Слоган (EN):** «Your Outsourced Center of Excellence.»
  Подзаголовок: «Where Centre meets Excellence to create intelligent transformation.»
- **Слоган (AZ):** «Sizin Outsors Mükəmməllik Mərkəziniz.»
  Подзаголовок: «Mərkəz və Mükəmməlliyin birləşdiyi yerdə ağıllı transformasiya yaranır.»
- Кнопка CTA: EN «Contact Us» / AZ «Bizimlə əlaqə».
- Опционально: слайдер/слайд (можно использовать фото `1.jpg`, `2.jpg`, `Artboard 5.jpg` из `images\`).

### 5.2 About Us / Haqqımızda — **текст + фото**
- Заголовок EN «About Us» / AZ «Haqqımızda».
- Текст (черновик из брендбука, клиент может уточнить):
  - **EN:** "CENOFEX helps organizations unlock their full potential through intelligent outsourcing, AI-powered solutions, and operational excellence — enabling sustainable growth, innovation and measurable business impact."
  - **AZ:** «CENOFEX təşkilatlara ağıllı outsorsinq, süni intellektə əsaslanan həllər və əməliyyat mükəmməlliyi vasitəsilə tam potensiallarını açmağa kömək edir — dayanıqlı inkişaf, innovasiya və ölçülə bilən biznes nəticələri təmin edir.»
- Фото рядом с текстом (например `2.jpg` или `Artboard 5.jpg` из `images\`).
- *(Финальный текст «About» клиент предоставит отдельно — оставить эти как placeholder.)*

### 5.3 Services / Xidmətlər — **заголовок + описание**
- Заголовок EN «Services» / AZ «Xidmətlər».
- Общее описание блока + карточки услуг (3–6 карточек с иконкой, названием, кратким описанием).
- Тексты услуг — placeholder `[заполнить]`, клиент предоставит. Пример-заглушки: Intelligent Outsourcing, AI-Powered Solutions, Operational Excellence, Business Transformation.

### 5.4 Solutions / Həllər — **заголовок + описание**
- Заголовок EN «Solutions» / AZ «Həllər».
- Описание + карточки/список решений. Тексты — placeholder `[заполнить]`.

### 5.5 Sectors / Sektorlar — **названия + описания**
- Заголовок EN «Sectors» / AZ «Sektorlar».
- Сетка секторов: название + краткое описание каждого (иконка + заголовок + 1–2 предложения). Тексты/список секторов — placeholder `[заполнить]`, клиент предоставит.

### 5.6 Contact / Əlaqə — **контакты и адрес**
- Заголовок EN «Contact» / AZ «Əlaqə».
- Контактная информация: адрес, телефон, e-mail, ссылки на соцсети — placeholder `[заполнить]`.
- Форма обратной связи (Имя, E-mail, Сообщение, кнопка Send/Göndər) — без бэкенда, оформить фронтенд + `mailto:` или пометка «подключить позже».
- Опционально: встроенная карта Google (по адресу).

### 5.7 Footer
Логотип, копирайт «© 2026 CENOFEX. All rights reserved. / Bütün hüquqlar qorunur.», дубль навигации, соцсети.

## 6. Три версии — в чём различие
Единый бренд и контент, но разный layout:
- **Version 1 — Classic corporate:** центрированный hero, секции в одну колонку, крупная типографика.
- **Version 2 — Modern split:** hero со split-экраном (текст слева / фото или слайдер справа), карточки с тенями, зелёные акценты.
- **Version 3 — Bold / geometric:** тёмный или контрастный hero с фирменным геометрическим паттерном, крупные зелёные блоки, асимметричная сетка.

## 7. Что предоставит клиент отдельно (сейчас — placeholder)
- Финальные тексты About, Services, Solutions, Sectors.
- Контакты и адрес.
- Точная подпись/подбор фото для каждой секции.

## 8. Приёмка (definition of done)
- 3 рабочих `index.html` в `version-1/2/3`, открываются двойным кликом.
- Переключение EN/AZ работает на всех секциях, дефолт — English.
- Все ссылки на `images\` рабочие; логотип и favicon отображаются.
- Полная адаптивность (проверить 375px / 768px / 1440px).
- Соблюдены фирменные цвета и шрифты.

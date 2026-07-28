<?php
/**
 * CENOFEX — конфигурация.
 * ВАЖНО: заполните доступы к базе данных из cPanel → MySQL Databases.
 */

return [
    // --- База данных (cPanel → MySQL Databases) ---
    'db' => [
        'host'    => 'localhost',
        'name'    => 'cenofex_site',      // имя БД
        'user'    => 'cenofex_admin',     // пользователь БД
        'pass'    => 'ЗАМЕНИТЕ_ПАРОЛЬ',   // пароль пользователя БД
        'charset' => 'utf8mb4',
    ],

    // --- Сайт ---
    'site' => [
        // Полный адрес сайта без слэша в конце
        'url'        => 'https://cenofex.ai',
        // Страница английской версии (главная сейчас — coming soon)
        'en_path'    => '/index5.php',
        'az_path'    => '/az/',
        'from_email' => 'info@cenofex.ai',
        'from_name'  => 'CENOFEX',
    ],

    // --- Пути ---
    'paths' => [
        'cache'    => __DIR__ . '/../storage/cache',
        'uploads'  => __DIR__ . '/../uploads/partners',
        'uploads_url' => '/uploads/partners',
    ],

    // Кэш публичных страниц (сек). 0 = только ручная очистка при сохранении.
    'cache_ttl' => 0,
];

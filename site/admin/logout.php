<?php
require_once dirname(__DIR__) . '/app/auth.php';
logout();
header('Location: login');

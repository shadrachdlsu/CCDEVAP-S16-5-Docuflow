<?php
declare(strict_types=1);

session_start();
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $cookie = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $cookie['path'],
        $cookie['domain'],
        (bool) $cookie['secure'],
        (bool) $cookie['httponly']
    );
}

session_destroy();

header('Cache-Control: no-store');
header('Location: ../views/login.php');
exit;

<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function flashSet(string $type, string $msg): void {
    $_SESSION['_flash'][$type][] = $msg;
}

function flashGet(): array {
    $flash = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $flash;
}

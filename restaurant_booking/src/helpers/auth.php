<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: /restaurant_booking/public/login.php');
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if (!hasRole('ROLE_ADMIN')) {
        http_response_code(403);
        include __DIR__ . '/../../public/403.php';
        exit;
    }
}

function hasRole(string $role): bool {
    $roles = json_decode($_SESSION['roles'] ?? '[]', true);
    return in_array($role, $roles, true);
}

function currentUserId(): int {
    return (int)($_SESSION['user_id'] ?? 0);
}

/**
 * Owner-check: aborts with 403 if $ownerId !== current user AND not admin.
 */
function requireOwnerOrAdmin(int $ownerId): void {
    requireLogin();
    if ($ownerId !== currentUserId() && !hasRole('ROLE_ADMIN')) {
        http_response_code(403);
        include __DIR__ . '/../../public/403.php';
        exit;
    }
}

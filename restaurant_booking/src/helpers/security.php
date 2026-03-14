<?php
/**
 * Validate and move an uploaded file safely.
 */
function secureUpload(
    array $file,
    array $allowedMimes,
    array $allowedExts,
    int $maxBytes,
    string $destination
): string {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload error code: ' . $file['error']);
    }
    if ($file['size'] > $maxBytes) {
        throw new RuntimeException('File too large (max ' . ($maxBytes / 1024 / 1024) . ' MB).');
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, $allowedMimes, true)) {
        throw new RuntimeException('File type not allowed.');
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExts, true)) {
        throw new RuntimeException('File extension not allowed.');
    }
    $newName = bin2hex(random_bytes(16)) . '.' . $ext;
    $dest    = rtrim($destination, '/') . '/' . $newName;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('Could not move uploaded file.');
    }
    return $newName;
}

function h(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Generate or retrieve the CSRF token for the current session.
 */
function csrfToken(): string {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/**
 * Validate a submitted CSRF token. Aborts with 403 on mismatch.
 */
function csrfValidate(): void {
    if (!hash_equals(csrfToken(), $_POST['csrf'] ?? '')) {
        http_response_code(403);
        exit('CSRF token mismatch.');
    }
}

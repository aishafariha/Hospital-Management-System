<?php
declare(strict_types=1);

/** Blocks anyone who isn't a logged-in admin. Call after require_login(). */
function require_admin(): void {
    $user = current_user();
    if (!$user || $user['role'] !== 'admin') {
        header('Location: ../index.php');
        exit;
    }
}

<?php
// shared/csrf.php

// Start the session if it is not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Returns the CSRF token (generates a new one if none exists)
 */
function csrf_get_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Returns a ready-to-use hidden CSRF input field for HTML forms
 */
function csrf_field(): string
{
    $token = htmlspecialchars(csrf_get_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Validates the CSRF token sent from a POST request
 */
function csrf_verify(?string $token): bool
{
    if (empty($token)) {
        return false;
    }
    if (empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

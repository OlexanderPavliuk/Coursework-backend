<?php
namespace Core;

final class Csrf
{
    /** Put a token in the session if it does not exist */
    public static function boot(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['_csrf'] ??= bin2hex(random_bytes(32));
    }

    /** Return the current token (call boot() first) */
    public static function token(): string
    {
        self::boot();
        return $_SESSION['_csrf'];
    }

    /** Validate a token coming from a POST/PUT/DELETE form */
    public static function verify(?string $token): bool
    {
        self::boot();
        return hash_equals($_SESSION['_csrf'], (string)$token);
    }
}

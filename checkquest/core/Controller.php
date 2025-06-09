<?php
declare(strict_types=1);

namespace Core;

use PDO;
use Core\Csrf;

abstract class Controller
{
    protected \PDO $pdo;

    public function __construct()
    {
        // session + CSRF available for every child controller
        Csrf::boot();

        // DB connection (pulls helper from config/database.php)
        $this->pdo = \getPDO();
    }

    /** Simple auth helper */
    protected function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /** Abort if the user is not logged in */
    protected function requireAuth(): void
    {
        if (!$this->isAuthenticated()) {
            header('Location: /login');
            exit;
        }
    }
}

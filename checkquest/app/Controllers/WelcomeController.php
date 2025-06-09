<?php
namespace App\Controllers;

use Core\Csrf;

/**
 * Гостьовий лендинг. Успадковувати Core\Controller не обовʼязково –
 * тут не потрібні ані PDO, ані обовʼязковий Csrf::boot() (бо CSRF
 * токен зручно отримати й напряму).
 */
class WelcomeController
{
    public function index(): void
    {
        session_start();                 // потрібна сесія для флеша, CSRF тощо

        /* уже залогінений? – одразу на дашборд */
        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }

        $csrf = Csrf::token();           // якщо на лендингу є форма (логін)

        require __DIR__ . '/../Views/welcome/index.php';
    }
}

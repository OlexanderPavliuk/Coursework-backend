<?php
namespace App\Controllers;

use App\Models\User;

class AuthController {
    public function showLoginForm() {
        session_start();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $errors = [];
        $username = '';
        require_once __DIR__ . '/../Views/auth/login.php';
    }

    public function login() {
        session_start();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $errors = [];
        $username = $_POST['username'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                die("CSRF token validation failed");
            }

            $password = $_POST['password'] ?? '';
            $user = \App\Models\User::attemptLogin($username, $password);

            if ($user) {
                $_SESSION['user_id'] = $user['id'];

                // ✅ ОНОВЛЮЄМО last_login
                $pdo = \getPDO(); // або твій метод підключення
                $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);

                header('Location: /dashboard');
                exit;
            } else {
                $errors[] = "Invalid credentials";
            }
        }

        require_once __DIR__ . '/../Views/auth/login.php';
    }


    public function logout() {
        session_start();
        session_destroy();
        header('Location: /welcome');
        exit;
    }
}

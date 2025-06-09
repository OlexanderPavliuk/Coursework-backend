<?php

namespace App\Controllers;

use Models\Health;
use Core\Csrf;
use Core\Response;
use Exception;

class HealthController
{
    public function check(): void
    {
        header('Content-Type: application/json');
        session_start();

        $uid = $_SESSION['user_id'] ?? null;
        if (!$uid) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            return;
        }

        /* ---------- ① Який метод? ---------- */
        $isPost = ($_SERVER['REQUEST_METHOD'] === 'POST');

        /* ---------- ② Для POST читаємо body й перевіряємо CSRF ---------- */
        $data = $isPost
            ? json_decode(file_get_contents('php://input'), true)
            : [];

        if ($isPost && !\Core\Csrf::verify($data['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'error' => 'CSRF mismatch']);
            return;
        }

        /* ---------- ③ Делегуємо в модель ---------- */
        try {
            $model  = new \App\Models\Health();
            $result = $model->performHealthCheck($uid, $data); // тут $data містить revive
            echo json_encode(['success' => true] + $result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

}

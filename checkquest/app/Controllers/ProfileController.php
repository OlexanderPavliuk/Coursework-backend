<?php

namespace App\Controllers;

use Core\Controller;
use Core\Csrf;

class ProfileController extends Controller
{
    public function show()
    {
        $this->requireAuth();
        $uid = $_SESSION['user_id'];

        $stmtUser = $this->pdo->prepare("SELECT username, description, created_at, last_login FROM users WHERE id = ?");
        $stmtUser->execute([$uid]);
        $userData = $stmtUser->fetch();

        $stmtChar = $this->pdo->prepare("SELECT level, xp, health AS hp, avatar, gold FROM characters WHERE user_id = ?");
        $stmtChar->execute([$uid]);
        $charData = $stmtChar->fetch();

        if (!$userData || !$charData) {
            die('User or character not found');
        }

        $user = array_merge($userData, $charData);
        $user['max_hp'] = 100;
        $user['xp_to_next_level'] = 100;

        require_once __DIR__ . '/../Views/profile/index.php';
    }

    public function changeAvatar()
    {
        header('Content-Type: application/json');
        $this->requireAuth();

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['avatarUrl'])) {
                throw new \Exception("Missing avatarUrl");
            }

            $stmt = $this->pdo->prepare("UPDATE characters SET avatar = ? WHERE user_id = ?");
            $stmt->execute([$data['avatarUrl'], $_SESSION['user_id']]);

            echo json_encode([
                'success' => true,
                'newAvatarUrl' => $data['avatarUrl']
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function edit()
    {
        $this->requireAuth();

        $stmt = $this->pdo->prepare("SELECT description FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        require_once __DIR__ . '/../Views/profile/edit.php';
    }

    public function updateAccount(): void
    {
        header('Content-Type: application/json');
        $this->requireAuth();

        $json  = json_decode(file_get_contents('php://input'), true) ?? [];
        $token = $json['csrf_token'] ?? '';

        if (!Csrf::verify($token)) {
            echo json_encode(['success' => false, 'error' => 'CSRF mismatch']);
            return;
        }

        $uid      = $_SESSION['user_id'];
        $username = trim($json['username'] ?? '');
        $email    = trim($json['email'] ?? '');
        $passNew  = $json['password'] ?? '';

        $errors = [];
        if ($username === '' || !preg_match('/^\w{3,30}$/', $username))
            $errors[] = 'Invalid username';
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL))
            $errors[] = 'Invalid email';
        if ($passNew !== '' && strlen($passNew) < 8)
            $errors[] = 'Password too short';

        if ($errors) {
            echo json_encode(['success' => false, 'error' => implode('; ', $errors)]);
            return;
        }

        $this->pdo->beginTransaction();
        $this->pdo->prepare('UPDATE users SET username = ?, email = ? WHERE id = ?')
            ->execute([$username ?: null, $email ?: null, $uid]);

        if ($passNew !== '') {
            $this->pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
                ->execute([password_hash($passNew, PASSWORD_DEFAULT), $uid]);
        }

        $this->pdo->commit();
        echo json_encode(['success' => true]);
    }

    public function getProfileData()
    {
        header('Content-Type: application/json');
        $this->requireAuth();

        $uid = $_SESSION['user_id'];

        $stmtUser = $this->pdo->prepare("SELECT username, description, email FROM users WHERE id = ?");
        $stmtUser->execute([$uid]);
        $userData = $stmtUser->fetch();

        $stmtChar = $this->pdo->prepare("SELECT level, xp, health, avatar, gold FROM characters WHERE user_id = ?");
        $stmtChar->execute([$uid]);
        $charData = $stmtChar->fetch();

        echo json_encode(array_merge($userData, $charData));
    }

    public function updateDescription()
    {
        header('Content-Type: application/json');
        $this->requireAuth();

        $data = json_decode(file_get_contents('php://input'), true);
        $desc = $data['description'] ?? '';

        $stmt = $this->pdo->prepare("UPDATE users SET description = ? WHERE id = ?");
        $stmt->execute([$desc, $_SESSION['user_id']]);

        echo json_encode(['success' => true]);
    }
}

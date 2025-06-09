<?php
namespace App\Controllers;

use Core\Controller;
use Core\Csrf;
use Core\Response;
use PDO;

class TaskController extends Controller
{
    public function update(): void
    {
        $this->requireAuth();

        $id  = $_POST['task_id'] ?? null;
        $uid = $_SESSION['user_id'];
        if (!$id) Response::json(['success' => false, 'error' => 'Missing id'], 422);

        // update task core info
        $stmt = $this->pdo->prepare(
            'UPDATE tasks
     SET title = :t, type = :ty, priority = :p,
         notes = :n, deadline = :d, updated_at = NOW()
     WHERE id = :id AND user_id = :uid'
        );
        $stmt->execute([
            't'   => $_POST['title'],
            'ty'  => $_POST['type'],
            'p'   => $_POST['priority'],
            'n'   => isset($_POST['notes']) && !is_array($_POST['notes']) ? trim($_POST['notes']) : null,
            'd'   => $_POST['deadline'] ?: null,
            'id'  => $id,
            'uid' => $uid
        ]);

// update categories
        $cats = json_decode($_POST['categories'] ?? '[]', true);


// гарантуємо, що це масив і фільтруємо тільки числові ID > 0
        if (!is_array($cats)) $cats = [];
        $cats = array_filter($cats, fn($cid) => is_numeric($cid) && $cid > 0);

// очищаємо старі зв’язки
        $this->pdo->prepare(
            'DELETE FROM task_category_relations WHERE task_id = :tid'
        )->execute(['tid' => $id]);

// вставляємо тільки валідні категорії
        if ($cats) {
            $rel = $this->pdo->prepare(
                'INSERT INTO task_category_relations (task_id, category_id)
         VALUES (:tid, :cid)'
            );

            foreach ($cats as $cid) {
                $rel->execute(['tid' => $id, 'cid' => $cid]);
            }
        }

        Response::json(['success' => true]);

    }

    public function add(): void
    {
        $this->requireAuth();

        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            Response::json(['error' => 'CSRF token mismatch'], 419);
        }

        $title    = trim($_POST['title'] ?? '');
        $type     = $_POST['type'] ?? 'todo';
        $priority = $_POST['priority'] ?? 'medium';
        $deadline = $_POST['deadline'] ?: null;
        $notes    = $_POST['notes'] ?? '';
        $cats     = json_decode($_POST['categories'] ?? '[]', true);

        if ($title === '') {
            Response::json(['error' => 'Title required'], 422);
        }

        $this->pdo->beginTransaction();

        $this->pdo->prepare(
            'INSERT INTO tasks (user_id, title, type, priority, deadline, notes, created_at)
             VALUES (:uid, :t, :ty, :p, :d, :n, NOW())'
        )->execute([
            'uid' => $_SESSION['user_id'],
            't'   => $title,
            'ty'  => $type,
            'p'   => $priority,
            'd'   => $deadline,
            'n'   => $notes,
        ]);

        $taskId = (int) $this->pdo->lastInsertId();

        if (is_array($cats) && $cats) {
            $rel = $this->pdo->prepare(
                'INSERT INTO task_category_relations (task_id, category_id)
                 VALUES (:tid, :cid)'
            );
            foreach ($cats as $cid) {
                $rel->execute(['tid' => $taskId, 'cid' => $cid]);
            }
        }

        $this->pdo->commit();

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            Response::json([
                'success' => true,
                'taskId'  => $taskId
            ]);
        }

        header('Location: /dashboard?success=task_created');
        exit;
    }

    public function complete(): void
    {
        $this->requireAuth();
        $data = json_decode(file_get_contents('php://input'), true);

        if (!Csrf::verify($data['csrf_token'] ?? '')) {
            Response::json(['success' => false, 'error' => 'CSRF mismatch'], 419);
        }

        $taskId = $data['task_id'] ?? null;
        if (!$taskId) {
            Response::json(['success' => false, 'error' => 'Missing task_id'], 422);
        }

        $uid = $_SESSION['user_id'];

        // 1. Позначаємо задачу як завершену
        $this->pdo->prepare(
            'UPDATE tasks
         SET completed = 1,
             completed_at = NOW()
         WHERE id = :id AND user_id = :uid'
        )->execute(['id' => $taskId, 'uid' => $uid]);

        // 2. Отримуємо інформацію про користувача
        $char = $this->pdo->query(
            "SELECT xp, level, gold, xp_boost_remaining FROM characters WHERE user_id = $uid"
        )->fetch(PDO::FETCH_ASSOC);

        // 3. Визначаємо бонус за буст
        $xpGain = 10;
        if ($char['xp_boost_remaining'] > 0) {
            $xpGain *= 2;

            // зменшуємо залишок бусту
            $this->pdo->prepare("UPDATE characters SET xp_boost_remaining = xp_boost_remaining - 1 WHERE user_id = ?")
                ->execute([$uid]);
        }

        // 4. Нараховуємо XP і золото
        $this->pdo->prepare(
            "UPDATE characters SET xp = xp + ?, gold = gold + 1 WHERE user_id = ?"
        )->execute([$xpGain, $uid]);

        // 🔁 ОНОВЛЮЄМО СТАН ПІСЛЯ НАРАХУВАННЯ
        $char = $this->pdo->query(
            "SELECT xp, level, gold FROM characters WHERE user_id = $uid"
        )->fetch(PDO::FETCH_ASSOC);

        // 5. Перевірка на level up
        $msg = "+{$xpGain} XP";
        if ($char['xp'] >= 100) {
            $this->pdo->exec("UPDATE characters SET level = level + 1, xp = 0 WHERE user_id = $uid");
            $char['level'] += 1;
            $char['xp'] = 0;
            $msg = "Level up! 🎉 You're now level {$char['level']}!";
        }

        // 6. Відповідь
        Response::json([
            'success'  => true,
            'message'  => $msg,
            'newXP'    => $char['xp'],
            'newLevel' => $char['level'],
            'newGold'  => $char['gold'],
        ]);

    }



    public function delete(): void
    {
        $this->requireAuth();

        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            Response::json(['success' => false, 'error' => 'CSRF mismatch'], 419);
        }

        $taskId = $_POST['task_id'] ?? null;
        if (!$taskId) {
            Response::json(['success' => false, 'error' => 'Invalid task_id'], 400);
        }

        $this->pdo->prepare(
            'DELETE FROM tasks WHERE id = :tid AND user_id = :uid'
        )->execute(['tid' => $taskId, 'uid' => $_SESSION['user_id']]);

        Response::json(['success' => true]);
    }
}

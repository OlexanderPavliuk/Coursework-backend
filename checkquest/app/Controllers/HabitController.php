<?php
namespace App\Controllers;

use Core\Controller;
use Core\Csrf;
use Exception;
use PDO;

class HabitController extends Controller
{
    private int $uid;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->uid = $_SESSION['user_id'];
    }

    public function track(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Invalid method']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $token = $data['csrf_token'] ?? '';

        if (!Csrf::verify($token)) {
            echo json_encode(['success' => false, 'error' => 'CSRF mismatch']);
            return;
        }

        $taskId = $data['task_id'] ?? null;
        $delta = $data['delta'] ?? null;

        if (!$taskId || !is_numeric($delta)) {
            echo json_encode(['success' => false, 'error' => 'Missing parameters']);
            return;
        }

        try {
            $this->pdo->beginTransaction();

            /* record action */
            $this->pdo->prepare(
                'INSERT INTO habit_actions (user_id,task_id,delta,created_at)
                 VALUES (:uid,:tid,:d,NOW())'
            )->execute(['uid' => $this->uid, 'tid' => $taskId, 'd' => $delta]);

            if ($delta > 0) {
                /* positive habit → XP + Gold */
                $this->pdo->prepare(
                    'UPDATE characters
                     SET xp = xp + 25, gold = gold + 50
                     WHERE user_id = :uid'
                )->execute(['uid' => $this->uid]);

                $char = $this->pdo->query(
                    'SELECT xp,level,gold FROM characters WHERE user_id = ' . $this->uid
                )->fetch(PDO::FETCH_ASSOC);

                /* handle level-ups */
                $leveled = false;
                while ($char['xp'] >= 100) {
                    $char['xp'] -= 100;
                    $char['level']++;
                    $leveled = true;
                }
                if ($leveled) {
                    $this->pdo->prepare(
                        'UPDATE characters SET xp=:xp,level=:lv WHERE user_id=:uid'
                    )->execute([
                        'xp' => $char['xp'], 'lv' => $char['level'], 'uid' => $this->uid
                    ]);
                }

                $this->pdo->commit();
                echo json_encode([
                    'success' => true,
                    'message' => $leveled
                        ? "Level up! You're now level {$char['level']} 🎉 +25 XP, +50 Gold"
                        : 'Positive habit completed! +25 XP, +50 Gold',
                    'newXP' => $char['xp'],
                    'newGold' => $char['gold'],
                    'newLevel' => $char['level'],
                    'xpPercent' => $char['xp'],
                ]);
            } else {
                /* negative habit → HP loss */
                $damage = abs((int)$delta) * 20;
                $this->pdo->prepare(
                    'UPDATE characters
                             SET health = GREATEST(health - :d,0)
                             WHERE user_id = :uid'
                )->execute(['d' => $damage, 'uid' => $this->uid]);

                // Отримаємо нове значення HP
                $char = $this->pdo->query(
                    'SELECT health FROM characters WHERE user_id = ' . $this->uid
                )->fetch(PDO::FETCH_ASSOC);

                $this->pdo->commit();

                $response = [
                    'success' => true,
                    'message' => "Negative habit recorded. -$damage Health",
                    'healthLost' => $damage,
                    'newHealth' => $char['health'],
                ];

                if ($char['health'] <= 0) {
                    // Знімаємо 20% XP та 25% золота
                    $charRow = $this->pdo->query(
                        'SELECT xp, gold FROM characters WHERE user_id = ' . $this->uid
                    )->fetch(PDO::FETCH_ASSOC);

                    $lostXp = (int)($charRow['xp'] * 0.2);
                    $lostGold = (int)($charRow['gold'] * 0.25);

                    $this->pdo->prepare(
                        'UPDATE characters SET xp = GREATEST(xp - :xp, 0), gold = GREATEST(gold - :gold, 0) WHERE user_id = :uid'
                    )->execute([
                        'xp' => $lostXp,
                        'gold' => $lostGold,
                        'uid' => $this->uid
                    ]);

                    $response['death'] = true;
                    $response['message'] = "You died... Return by Death?";
                    $response['lostXp'] = $lostXp;
                    $response['lostGold'] = $lostGold;
                }


                echo json_encode($response);


            }
        } catch (Exception $e) {
            $this->pdo->rollBack();
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

<?php
namespace App\Models;

use PDO;
use function getPDO;

class Health
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = getPDO();
    }

    /**
     * Returns an associative array describing the result of the health check.
     */
    public function performHealthCheck(int $userId, array $data = []): array
    {
        $this->pdo->beginTransaction();
        $now = date('Y-m-d H:i:s');

        // 🔄 Revive логіка
        if (!empty($data['revive'])) {
            $this->pdo->prepare(
                'UPDATE characters SET health = 100 WHERE user_id = :uid'
            )->execute(['uid' => $userId]);

            $this->pdo->commit();
            return ['revived' => true, 'newHealth' => 100];
        }

        // 🔁 Стандартний health-check
        $last = $this->pdo->prepare(
            'SELECT last_health_check FROM characters WHERE user_id = :uid'
        );
        $last->execute(['uid' => $userId]);
        $lastCheck = $last->fetchColumn();

        if ($lastCheck && strtotime($lastCheck) > strtotime('-1 hour')) {
            return [
                'noChange'    => true,
                'nextCheckIn' => strtotime($lastCheck) + 3600 - time(),
            ];
        }

        $overdue = $this->pdo->prepare(
            "SELECT COUNT(*) FROM tasks
         WHERE user_id = :uid
           AND deadline < :now
           AND completed = 0
           AND type IN ('daily','todo')"
        );
        $overdue->execute(['uid' => $userId, 'now' => $now]);
        $overdueCount = (int) $overdue->fetchColumn();

        $negative = $this->pdo->prepare(
            "SELECT COUNT(*) FROM habit_actions
         WHERE user_id = :uid
           AND delta < 0
           AND created_at > COALESCE(
                 (SELECT last_health_check FROM characters WHERE user_id = :uid),
                 DATE_SUB(NOW(), INTERVAL 24 HOUR)
             )"
        );
        $negative->execute(['uid' => $userId]);
        $negativeCount = (int) $negative->fetchColumn();

        $hpLoss = $overdueCount * 5 + $negativeCount * 3;

        if ($hpLoss > 0) {
            $current = $this->pdo->prepare(
                'SELECT health FROM characters WHERE user_id = :uid'
            );
            $current->execute(['uid' => $userId]);
            $newHealth = max(0, (int) $current->fetchColumn() - $hpLoss);

            $this->pdo->prepare(
                'UPDATE characters
             SET health = :hp, last_health_check = :now
             WHERE user_id = :uid'
            )->execute(['hp' => $newHealth, 'now' => $now, 'uid' => $userId]);

            $this->pdo->prepare(
                "UPDATE tasks
             SET last_health_check = :now
             WHERE user_id = :uid
               AND type IN ('daily','todo')
               AND completed = 0"
            )->execute(['now' => $now, 'uid' => $userId]);

            $this->pdo->commit();
            return [
                'newHealth'      => $newHealth,
                'hpLoss'         => $hpLoss,
                'overdueTasks'   => $overdueCount,
                'negativeHabits' => $negativeCount,
            ];
        }

        $this->pdo->prepare(
            'UPDATE characters SET last_health_check = :now WHERE user_id = :uid'
        )->execute(['now' => $now, 'uid' => $userId]);

        $this->pdo->commit();
        return ['noChange' => true];
    }
}

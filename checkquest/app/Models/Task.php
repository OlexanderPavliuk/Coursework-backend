<?php
namespace App\Models;

use PDO;
use function getPDO;

class Task
{
    /**
     * Fetch all tasks for a given user.
     *
     * @return array<array<string,mixed>>
     */
    public static function getByUserId(int $userId): array
    {
        $pdo = getPDO();

        $stmt = $pdo->prepare(
            'SELECT *
             FROM tasks
             WHERE user_id = :uid
             ORDER BY created_at DESC'
        );
        $stmt->execute(['uid' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

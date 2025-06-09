<?php
namespace App\Models;

use Exception;
use PDO;
use function getPDO;

class Store
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = getPDO();
    }

    /**
     * Handle an item purchase and apply its effect.
     *
     * @throws Exception
     */
    public function handlePurchase(int $userId, int $itemId): array
    {
        try {
            $this->pdo->beginTransaction();

            $item       = $this->fetchItem($itemId);
            $character  = $this->fetchCharacter($userId);

            if ($character['gold'] < $item['price']) {
                throw new Exception('Not enough gold');
            }

            $this->deductGold($userId, (int) $item['price']);
            $this->recordPurchase($userId, $itemId);

            $response = [
                'newGold' => $character['gold'] - $item['price'],
            ];

            // Only set message if NOT mystery
            if ($item['type'] !== 'mystery') {
                $response['message'] = "Successfully purchased {$item['name']}!";
            }

            $this->applyItemEffect($item, $character, $userId, $response);

            $this->pdo->commit();
            return $response;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;      // bubble up for controller to JSON-encode
        }
    }

    /* ---------- helpers -------------------------------------------------- */

    private function fetchItem(int $itemId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM store_items WHERE id = :id');
        $stmt->execute(['id' => $itemId]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            throw new Exception('Item not found');
        }
        return $item;
    }

    private function fetchCharacter(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT gold, health FROM characters WHERE user_id = :uid');
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function deductGold(int $userId, int $amount): void
    {
        $this->pdo
            ->prepare('UPDATE characters SET gold = gold - :amt WHERE user_id = :uid')
            ->execute(['amt' => $amount, 'uid' => $userId]);
    }

    private function recordPurchase(int $userId, int $itemId): void
    {
        $this->pdo
            ->prepare('INSERT INTO store_purchases (user_id, item_id) VALUES (:uid, :iid)')
            ->execute(['uid' => $userId, 'iid' => $itemId]);
    }

    private function applyItemEffect(array $item, array $character, int $userId, array &$response): void
    {
        switch ($item['type']) {
            case 'potion':
                $this->applyPotion($item, $character, $userId, $response);
                break;

            case 'boost':
                if ($item['name'] === 'XP Boost') {
                    $_SESSION['xp_boost'] = ($_SESSION['xp_boost'] ?? 0) + $item['effect_value'];
                    $response['xpBoost']  = $_SESSION['xp_boost'];

                }
                break;

            case 'cosmetic':
                if ($item['name'] === 'Custom Avatar Frame') {
                    $this->pdo->prepare(
                        "UPDATE characters SET avatar_frame = 'gold' WHERE user_id = :uid"
                    )->execute(['uid' => $userId]);

                    $response['avatarFrame'] = 'gold';
                    $response['message'] = 'Golden frame applied to your avatar!';
                }
                break;

            case 'mystery':
                $this->applyMystery($character, $userId, $response);
                break;
        }
    }

    private function applyPotion(array $item, array $character, int $userId, array &$response): void
    {
        if ($item['name'] === 'Health Potion') {
            $newHealth = min($character['health'] + $item['effect_value'], 100);
            $this->pdo->prepare(
                'UPDATE characters SET health = :hp WHERE user_id = :uid'
            )->execute(['hp' => $newHealth, 'uid' => $userId]);

            $response += [
                'newHealth' => $newHealth,
                'message'   => "Used Health Potion! Restored {$item['effect_value']} health.",
            ];
        } elseif ($item['name'] === 'Energy Elixir') {
            $stmt = $this->pdo->prepare(
                "UPDATE tasks SET completed = 1
                 WHERE user_id = :uid AND type = 'daily' AND completed = 0"
            );
            $stmt->execute(['uid' => $userId]);
            $response['message'] = 'Energy Elixir used! Completed '.$stmt->rowCount().' daily tasks.';
        }
    }

    private function applyMystery(array $character, int $userId, array &$response): void
    {
        $rewards = [
            ['type' => 'gold',     'amount' => rand(100, 1000)],
            ['type' => 'health',   'amount' => rand(10,  50)],
            ['type' => 'xp_boost', 'amount' => rand(1,    3)],
        ];
        $reward = $rewards[array_rand($rewards)];

        switch ($reward['type']) {
            case 'gold':
                $this->pdo->prepare(
                    'UPDATE characters SET gold = gold + :amt WHERE user_id = :uid'
                )->execute(['amt' => $reward['amount'], 'uid' => $userId]);
                $response['newGold'] += $reward['amount'];
                $response['message']  = "Mystery Box contained {$reward['amount']} gold!";
                break;

            case 'health':
                $newHealth = min($character['health'] + $reward['amount'], 100);
                $this->pdo->prepare(
                    'UPDATE characters SET health = :hp WHERE user_id = :uid'
                )->execute(['hp' => $newHealth, 'uid' => $userId]);
                $response += [
                    'newHealth' => $newHealth,
                    'message'   => "Mystery Box restored {$reward['amount']} health!",
                ];
                break;

            case 'xp_boost':
                $_SESSION['xp_boost'] = ($_SESSION['xp_boost'] ?? 0) + $reward['amount'];
                $response += [
                    'xpBoost' => $_SESSION['xp_boost'],
                    'message' => "Mystery Box gave XP Boost for {$reward['amount']} tasks!",
                ];
                break;
        }
    }
}

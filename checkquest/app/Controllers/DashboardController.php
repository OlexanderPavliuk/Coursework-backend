<?php
namespace App\Controllers;

use App\Models\Task;

class DashboardController {
    public function index() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit;
        }

        $user_id = $_SESSION['user_id'];

        require __DIR__ . '/../../config/database.php';
        $pdo = getPDO();

        // Redirect first-time visitors
        if (!isset($_COOKIE['visitedDashboard']) && !isset($_GET['first'])) {
            setcookie('visitedDashboard', 'yes', time() + (86400 * 30), "/");
            header('Location: /dashboard?first=1');
            exit;
        }

        // Get character data
        $stmt = $pdo->prepare("SELECT level, xp, health, avatar, IFNULL(gold,0) AS gold, avatar_frame FROM characters WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $char = $stmt->fetch() ?: [
            'level' => 1,
            'xp' => 0,
            'health' => 100,
            'avatar' => 'https://api.dicebear.com/7.x/pixel-art/svg?seed=hero',
            'gold' => 0,
            'avatar_frame' => null
        ];

        // Username
        $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $username = $stmt->fetchColumn() ?: 'Adventurer';

        // Load tasks with categories
        $stmt = $pdo->prepare("
            SELECT t.*, 
                   GROUP_CONCAT(DISTINCT c.id) as category_ids,
                   GROUP_CONCAT(DISTINCT c.name) as category_names,
                   GROUP_CONCAT(DISTINCT c.color) as category_colors
            FROM tasks t
            LEFT JOIN task_category_relations tcr ON t.id = tcr.task_id
            LEFT JOIN task_categories c ON tcr.category_id = c.id
            WHERE t.user_id = ?
            GROUP BY t.id
            ORDER BY t.position ASC
        ");
        $stmt->execute([$user_id]);
        $tasks = $stmt->fetchAll();

        // Convert categories
        foreach ($tasks as &$task) {
            $task['categories'] = [];
            if ($task['category_ids']) {
                $ids = explode(',', $task['category_ids']);
                $names = explode(',', $task['category_names']);
                $colors = explode(',', $task['category_colors']);
                for ($i = 0; $i < count($ids); $i++) {
                    $task['categories'][] = [
                        'id' => $ids[$i],
                        'name' => $names[$i],
                        'color' => $colors[$i]
                    ];
                }
            }
            unset($task['category_ids'], $task['category_names'], $task['category_colors']);
        }
        unset($task); // break reference

        // Add habit counts
        $habitLogStmt = $pdo->prepare("
            SELECT 
                task_id,
                SUM(CASE WHEN delta > 0 THEN 1 ELSE 0 END) AS plus_count,
                SUM(CASE WHEN delta < 0 THEN 1 ELSE 0 END) AS minus_count
            FROM habit_actions
            WHERE user_id = ?
            GROUP BY task_id
        ");
        $habitLogStmt->execute([$user_id]);
        $habitCounts = $habitLogStmt->fetchAll(\PDO::FETCH_ASSOC);

        // Map counts by task_id
        $habitMap = [];
        foreach ($habitCounts as $row) {
            $habitMap[$row['task_id']] = [
                'plus' => $row['plus_count'],
                'minus' => $row['minus_count'],
            ];
        }

        // Inject counts into tasks
        foreach ($tasks as &$task) {
            if ($task['type'] === 'habit') {
                $task['plus_count'] = $habitMap[$task['id']]['plus'] ?? 0;
                $task['minus_count'] = $habitMap[$task['id']]['minus'] ?? 0;
            }
        }

        $now = date('Y-m-d H:i:s');

        require_once __DIR__ . '/../Views/dashboard/index.php';
    }
}

<?php
namespace App\Controllers;

use Core\Controller;

class StatisticsController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $uid = $_SESSION['user_id'];

        /* ---------------- aggregate stats ---------------- */
        $stats = $this->pdo->prepare(
            'SELECT
                 SUM(completed = 1) AS completed_tasks,
                 SUM(completed = 0) AS pending_tasks,
                 COUNT(*)          AS total_tasks,
                 SUM(type = "daily")  AS daily_count,
                 SUM(type = "habit")  AS habit_count,
                 SUM(type = "todo")   AS todo_count
             FROM tasks
             WHERE user_id = :uid'
        );
        $stats->execute(['uid' => $uid]);
        $stats = $stats->fetch();

        /* ------------- category breakdown ---------------- */
        $cat = $this->pdo->prepare(
            'SELECT c.name AS category_name,c.color AS category_color,
                    COUNT(t.id)                       AS total_tasks,
                    SUM(t.completed = 1)              AS completed_tasks
             FROM task_categories c
             LEFT JOIN task_category_relations tcr ON c.id = tcr.category_id
             LEFT JOIN tasks t ON tcr.task_id = t.id
             WHERE c.user_id = :uid
             GROUP BY c.id'
        );
        $cat->execute(['uid' => $uid]);
        $categoryStats = $cat->fetchAll();

        /* -------------- 7-day completion trend ----------- */
        $trend = $this->pdo->prepare(
            'SELECT DATE(completed_at) AS d, COUNT(*) AS c
             FROM tasks
             WHERE user_id = :uid
               AND completed = 1
               AND completed_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
             GROUP BY d'
        );
        $trend->execute(['uid' => $uid]);
        $rows = $trend->fetchAll();

        $dates = [];
        $completions = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-$i days"));
            $dates[]  = date('D', strtotime($day));
            $completions[] = 0;
            foreach ($rows as $r) {
                if ($r['d'] === $day) {
                    $completions[6 - $i] = (int) $r['c'];
                    break;
                }
            }
        }

        // fallback if no data at all (safety)
        if (empty($dates)) {
            $dates = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            $completions = array_fill(0, 7, 0);
        }

        require __DIR__.'/../Views/statistics/index.php';
    }
}

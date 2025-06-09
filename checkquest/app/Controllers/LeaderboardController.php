<?php
namespace App\Controllers;

use Core\Controller;


class LeaderboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $uid = $_SESSION['user_id'];

        $players = $this->pdo->query(
            'SELECT u.id,u.username,
                    c.level,c.xp,c.health,c.gold,c.avatar,c.avatar_frame,
                    (SELECT COUNT(*) FROM tasks
                     WHERE user_id=u.id AND completed=1) AS tasks_completed
             FROM users u
             JOIN characters c ON c.user_id=u.id
             ORDER BY c.level DESC,c.xp DESC'
        )->fetchAll();

        $currentUserRank = null;
        foreach ($players as $i=>$p) {
            if ($p['id']===$uid){ $currentUserRank=$i+1; break; }
        }

        require __DIR__.'/../Views/leaderboard/index.php';
    }
}

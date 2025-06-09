<?php
namespace App\Controllers;

use Core\Controller;   // extends if you already have base helpers
use Core\Response;
use PDO;

class SuggestionController extends Controller
{

    public function __construct()
    {
        parent::__construct();     // ← only if you extend Core\Controller
        $this->pdo = $GLOBALS['pdo'] ?? getPDO(); // fallback helper
    }

    /* -------------------------------------------------------------- */
    public function generate(): void
    {
        /* 0️⃣  CSRF & session (optional) ---------------------------- */
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $uid = $_SESSION['user_id'] ?? null;
        if (!$uid) {
            Response::json(['error' => 'Unauthenticated'], 401);
        }

        /* 1️⃣  Validate input --------------------------------------- */
        $userAsk = trim($_POST['prompt'] ?? '');
        if ($userAsk === '') {
            Response::json(['error' => 'No prompt provided'], 422);
        }

        /* 2️⃣  Gather game-state ------------------------------------ */
        $charStmt = $this->pdo->prepare(
            'SELECT level,xp,health,gold FROM characters WHERE user_id = ?'
        );
        $charStmt->execute([$uid]);
        $char = $charStmt->fetch() ?: ['level'=>1,'xp'=>0,'health'=>100,'gold'=>0];

        $taskStmt = $this->pdo->prepare(
            "SELECT title,type,priority,deadline
             FROM tasks
             WHERE user_id = ? AND completed = 0
             ORDER BY priority='high' DESC, deadline IS NULL, deadline ASC
             LIMIT 10"
        );
        $taskStmt->execute([$uid]);
        $tasks = $taskStmt->fetchAll(PDO::FETCH_ASSOC);

        /* 3️⃣  Build prompts ---------------------------------------- */
        $systemPrompt = <<<SYS
You are CheckQuest Assistant — an in-game coach for a gamified task manager.

Game logic:
 • Types: Habit (±), Daily (recurring), To-Do (one-shot), Reward (spend gold)
 • Completing tasks → XP+Gold. 100 XP ⇒ level up.
 • Overdue Daily/To-Do or negative Habit → lose Health (0-100).

Return a numbered list of **1-3 concise, actionable suggestions** tailored to
the JSON context I provide. Never wrap suggestions in markdown.
SYS;

        $fewShotUser = json_encode([
            'level'=>2,'xp'=>30,'health'=>60,'gold'=>12,
            'todos'=>['Clean desk (todo/high)','Morning run (daily/high)'],
            'ask'  =>'Need quick tips'
        ], JSON_UNESCAPED_UNICODE);

        $fewShotAssistant = "1. Заверши «Clean desk» сьогодні — отримаєш XP та чистий простір.\n"
            ."2. Додай Habit «5-хв розтяжка» для +XP і Health щодня.\n"
            ."3. Збережи золото на Reward «Weekend movie» після Level 3.";

        $realContext = json_encode([
            'level' => (int)$char['level'],
            'xp'    => (int)$char['xp'],
            'health'=> (int)$char['health'],
            'gold'  => (int)$char['gold'],
            'todos' => array_map(
                fn($t)=>$t['title'].' ('.$t['type'].'/'.$t['priority'].')',
                $tasks
            ),
            'ask'   => $userAsk
        ], JSON_UNESCAPED_UNICODE);

        /* 4️⃣  Call OpenAI ----------------------------------------- */
        $config  = require __DIR__.'/../../config/openai.php';
        $apiKey  = $config['api_key'];

        $body = [
            'model'       => 'gpt-4o-mini',
            'messages'    => [
                ['role'=>'system',   'content'=>$systemPrompt],
                ['role'=>'user',     'content'=>$fewShotUser],
                ['role'=>'assistant','content'=>$fewShotAssistant],
                ['role'=>'user',     'content'=>$realContext],
            ],
            'temperature' => 0.6,
            'max_tokens'  => 180,
        ];
        $headers = [
            'Content-Type: application/json',
            'Authorization: '."Bearer {$apiKey}",
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($body),
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 20,
        ]);

        $raw  = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            Response::json(['error'=>"Curl error: $err"], 502);
        }

        $json = json_decode($raw, true);
        $text = $json['choices'][0]['message']['content'] ?? '';

        if ($code !== 200 || $text === '') {
            Response::json(['error'=>'API error','raw'=>$raw], 502);
        }

        /* 5️⃣  Fallback numbering if model forgot ------------------- */
        if (!preg_match('/^\d\./', $text)) {
            $lines = array_filter(array_map('trim', explode("\n", $text)));
            foreach ($lines as $i => &$ln) {
                if (!preg_match('/^\d+\./', $ln)) $ln = ($i+1).'. '.$ln;
            }
            $text = implode("\n", $lines);
        }

        Response::json(['success'=>true, 'suggestion'=>$text]);
    }
}

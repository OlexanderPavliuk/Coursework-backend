<?php
namespace App\Controllers;

use Core\Controller;
use Core\Csrf;
use PDO;
use Exception;
use function getPDO;

class RegisterController extends Controller
{
    /** @var PDO */
    protected PDO $pdo;

    public function __construct()
    {
        parent::__construct();   // стартує сесію + Csrf + $this->pdo
        $this->pdo = getPDO();   // (у батька вже є — лишив для наочності)
    }

    public function index(): void
    {
        $errors = [];

        /* ─────────────────────────  POST (submit)  ───────────────────────── */
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            /* ① перевіряємо CSRF токен */
            if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
                $errors[] = 'CSRF token validation failed.';
            }

            /* ② зчитуємо поля */
            $username        = trim($_POST['username'] ?? '');
            $email           = $_POST['email'] ? trim($_POST['email']) : null;
            $password        = $_POST['password']         ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';
            $agreeTerms      = isset($_POST['agree_terms']);

            /* ③ валідації */
            if (!preg_match('/^[\w]{3,30}$/', $username)) {
                $errors[] = 'Username must be 3-30 chars, letters/numbers/_ only.';
            }
            if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email address.';
            }
            if (
                strlen($password) < 8 ||
                !preg_match('/[A-Z]/', $password) ||
                !preg_match('/[a-z]/', $password) ||
                !preg_match('/\d/',   $password)
            ) {
                $errors[] = 'Password ≥ 8 chars, upper, lower, number.';
            }
            if ($password !== $passwordConfirm) {
                $errors[] = 'Passwords do not match.';
            }
            if (!$agreeTerms) {
                $errors[] = 'You must agree to the Terms of Service.';
            }

            /* ④ перевірка унікальності логіна / email */
            if (empty($errors)) {
                $q = $this->pdo->prepare(
                    'SELECT id FROM users WHERE username = :u OR (email = :e AND email IS NOT NULL)'
                );
                $q->execute(['u'=>$username,'e'=>$email]);
                if ($q->fetch()) {
                    $errors[] = 'Username or email already taken.';
                }
            }

            /* ⑤ створюємо користувача + початкові дані */
            if (empty($errors)) {
                try {
                    $this->pdo->beginTransaction();

                    /* users */
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $this->pdo->prepare(
                        'INSERT INTO users (username,email,password_hash,created_at)
                         VALUES (?,?,?,NOW())'
                    )->execute([$username,$email,$hash]);
                    $uid = (int)$this->pdo->lastInsertId();

                    /* characters */
                    $avatar = 'https://api.dicebear.com/7.x/pixel-art/svg?seed='.uniqid();
                    $this->pdo->prepare(
                        'INSERT INTO characters (user_id,avatar)
                         VALUES (?,?)'
                    )->execute([$uid,$avatar]);

                    /* ───── дефолтні категорії ───── */
                    $cats = [
                        ['Work',     '#ef4444'],
                        ['Personal', '#22c55e'],
                        ['Health',   '#3b82f6'],
                        ['Study',    '#f59e0b'],
                        ['Shopping', '#8b5cf6'],
                    ];
                    $catIns = $this->pdo->prepare(
                        'INSERT INTO task_categories (user_id,name,color)
                         VALUES (?,?,?)'
                    );
                    foreach ($cats as [$n,$c]) {
                        $catIns->execute([$uid,$n,$c]);
                    }

                    /* map назва→id щойно створених категорій */
                    $map = $this->pdo->prepare(
                        'SELECT id,name FROM task_categories WHERE user_id = ?'
                    );
                    $map->execute([$uid]);
                    $catId = [];
                    foreach ($map as $row) {
                        $catId[$row['name']] = $row['id'];
                    }

                    /* ───── дефолтні задачі ───── */
                    $tasks = [
                        ['Complete your profile','todo','Add a profile picture','high','Personal',date('Y-m-d',strtotime('+1 day'))],
                        ['Set up your first health goal','habit','Create a health goal','medium','Health',null],
                        ['Explore the task system','todo','Try creating tasks','medium','Personal',date('Y-m-d',strtotime('+2 days'))],
                        ['Create your shopping list','todo','Organize shopping needs','low','Shopping',date('Y-m-d',strtotime('+3 days'))],
                        ['Daily water intake','daily','Drink 8 glasses','high','Health',null],
                        ['Exercise routine','daily','30 min activity','high','Health',null],
                        ['Daily meditation','daily','10 min mindfulness','medium','Health',null],
                    ];

                    $taskIns = $this->pdo->prepare(
                        'INSERT INTO tasks
                         (user_id,title,type,notes,priority,deadline,created_at,position)
                         VALUES (?,?,?,?,?,?,NOW(),?)'
                    );
                    $relIns = $this->pdo->prepare(
                        'INSERT INTO task_category_relations (task_id,category_id)
                         VALUES (?,?)'
                    );

                    foreach ($tasks as $i => [$title,$type,$notes,$prio,$catName,$deadline]) {
                        $taskIns->execute([
                            $uid,$title,$type,$notes,$prio,$deadline,($i+1)*1024
                        ]);
                        $relIns->execute([
                            $this->pdo->lastInsertId(),
                            $catId[$catName]
                        ]);
                    }

                    $this->pdo->commit();
                    $_SESSION['user_id'] = $uid;
                    header('Location: /dashboard');
                    exit;
                } catch (Exception $e) {
                    $this->pdo->rollBack();
                    $errors[] = 'Registration failed. Please try again.';
                }
            }
        }

        /* ─────────────────────────  GET (форма) ───────────────────────── */
        $token = Csrf::token();   // гарантовано є у сесії + повертаємо у view
        require __DIR__.'/../Views/auth/register.php';
    }
}

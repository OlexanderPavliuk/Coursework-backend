<?php
namespace App\Models;

use PDO;
class User
{
    /**
     * Attempt to log a user in.
     *
     * @return array|false  User row on success, false on failure
     */
    public static function attemptLogin(string $usernameOrEmail, string $password): array|false
    {
        // database.php was required once in public/index.php,
        // so the helper already exists in the global namespace.
        $pdo = \getPDO();               // or getPDO() if you added 'use function'

        $stmt = $pdo->prepare(
            "SELECT * FROM users
             WHERE username = :ue OR email = :ue
             LIMIT 1"
        );
        $stmt->execute(['ue' => $usernameOrEmail]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user && password_verify($password, $user['password_hash'])
            ? $user
            : false;
    }
}

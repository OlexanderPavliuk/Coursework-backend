
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <script src="/assets/js/theme.js"></script>
    <link rel="icon" type="image/png" href="/logo/favicon.png">
    <title>Register • CheckQuest</title>
</head>
<body id="pageBody" class="min-h-screen flex items-center justify-center p-4">

<div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
    <h2 class="text-2xl font-bold mb-6 text-center text-purple-700">Create Your Account</h2>

    <?php if (!empty($errors)): ?>
        <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6">
            <ul class="list-disc list-inside space-y-1">
                <?php foreach ($errors as $error): ?>
                    <li class="text-sm"><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="space-y-4">
        <input type="hidden" name="csrf_token"
               value="<?= htmlspecialchars($token ?? ($_SESSION['csrf_token'] ?? '')) ?>">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
            <input name="username" type="text" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                   class="w-full px-4 py-2 border text-gray-700 rounded focus:ring-purple-500 focus:border-purple-500"
                   required pattern="[a-zA-Z0-9_]+" minlength="3" maxlength="30">
            <p class="mt-1 text-xs text-gray-500">Letters, numbers, and underscores only (3-30 characters)</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address (Optional)</label>
            <input name="email" type="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                   class="w-full px-4 py-2 border text-gray-700 rounded focus:ring-purple-500 focus:border-purple-500">
            <p class="mt-1 text-xs text-gray-500">Required for password recovery</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input name="password" type="password"
                   class="w-full px-4 py-2 border text-gray-700 rounded focus:ring-purple-500 focus:border-purple-500"
                   required minlength="8">
            <p class="mt-1 text-xs text-gray-500">At least 8 characters with uppercase, lowercase, and numbers</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
            <input name="password_confirm" type="password"
                   class="w-full px-4 py-2 border text-gray-700 rounded focus:ring-purple-500 focus:border-purple-500"
                   required>
        </div>

        <div class="flex items-start space-x-2">
            <input type="checkbox" name="agree_terms" id="agree_terms" class="mt-1">
            <label for="agree_terms" class="text-sm text-gray-600">
                I agree to the <a href="#" class="text-purple-600 hover:underline">Terms of Service</a> and
                <a href="#" class="text-purple-600 hover:underline">Privacy Policy</a>
            </label>
        </div>

        <button class="w-full bg-purple-700 text-white py-2 rounded hover:bg-purple-800 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
            Create Account
        </button>
    </form>

    <p class="mt-4 text-sm text-center text-gray-600">
        Already have an account? <a href="login" class="text-purple-600 hover:underline">Login</a>
    </p>

    <p class="mt-4 text-sm text-center text-gray-500">
        ← <a href="/welcome" class="text-purple-600 hover:underline">Back to Welcome</a>
    </p>

</div>

<script>
    document.querySelector('form').addEventListener('submit', function(e) {
        const password = document.querySelector('input[name="password"]').value;
        const confirmPassword = document.querySelector('input[name="password_confirm"]').value;

        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match!');
        }
    });
</script>

</body>
</html>
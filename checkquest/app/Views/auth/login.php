<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="icon" type="image/png" href="/logo/favicon.png">
    <script src="/assets/js/theme.js"></script>
    <title>Login • CheckQuest</title>
</head>
<body id="pageBody" class="min-h-screen flex items-center justify-center p-4">

<div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
    <h2 class="text-2xl font-bold mb-6 text-center text-purple-700">Login to CheckQuest</h2>

    <?php if (isset($_SESSION['registration_success'])): ?>
        <div class="bg-green-50 text-green-600 p-4 rounded-lg mb-6">
            <p class="text-sm">Registration successful! Please check your email to verify your account.</p>
        </div>
        <?php unset($_SESSION['registration_success']); ?>
    <?php endif; ?>

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
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Username or Email</label>
            <input name="username" type="text" value="<?= htmlspecialchars($username) ?>"
                   class="w-full px-4 py-2 border rounded text-black focus:ring-purple-500 focus:border-purple-500"
                   required autofocus>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input name="password" type="password"
                   class="w-full px-4 py-2 border rounded text-black focus:ring-purple-500 focus:border-purple-500"
                   required>
        </div>

        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <input type="checkbox" name="remember_me" id="remember_me" class="h-4 w-4 text-purple-600">
                <label for="remember_me" class="ml-2 text-sm text-gray-600">Remember me</label>
            </div>
            <a class="text-sm text-purple-600 hover:underline">Forgot password?</a>
        </div>

        <button class="w-full bg-purple-700 text-white py-2 rounded hover:bg-purple-800 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
            Login
        </button>
    </form>

    <p class="mt-4 text-sm text-center text-gray-600">
        Don't have an account? <a href="/register" class="text-purple-600 hover:underline">Register</a>
    </p>

    <p class="mt-4 text-sm text-center text-gray-500">
        ← <a href="/welcome" class="text-purple-600 hover:underline">Back to Welcome</a>
    </p>

</div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>CheckQuest • Welcome</title>
    <link rel="icon" type="image/png" href="/logo/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts (як і в інших сторінках) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Nunito:wght@700&display=swap"
          rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3 { font-family: 'Nunito', sans-serif; }
    </style>
</head>

<body class="min-h-screen flex flex-col bg-gradient-to-br from-purple-900 via-purple-800 to-purple-900 text-white">

<!-- Hero-section --------------------------------------------------- -->
<section class="flex-1 flex flex-col items-center justify-center text-center px-4">
    <h1 class="text-4xl md:text-6xl font-extrabold drop-shadow mb-6">
        Turn Tasks into <span class="text-purple-300">Quests</span>
    </h1>

    <p class="max-w-2xl mx-auto text-lg md:text-xl text-purple-200 mb-10">
        Gamified productivity tracker that rewards you for finishing tasks,
        habits and dailies. Level-up your character while getting things done!
    </p>

    <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="/register"
           class="px-8 py-3 rounded-xl bg-purple-600 hover:bg-purple-500 transition shadow-lg font-semibold">
            Get Started — It’s Free
        </a>

        <a href="/login"
           class="px-8 py-3 rounded-xl bg-white/10 hover:bg-white/20 transition shadow-lg font-semibold">
            I already have an account
        </a>
    </div>
</section>

<!-- Footer --------------------------------------------------------- -->
<footer class="text-center py-6 text-xs text-purple-300">
    © <?= date('Y') ?> CheckQuest. Built with 💜
</footer>
</body>
</html>

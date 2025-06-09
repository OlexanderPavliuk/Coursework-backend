<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CheckQuest • Leaderboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/leaderboard.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="icon" type="image/png" href="/logo/favicon.png">
    <script src="/assets/js/loading_screen.js" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Nunito:wght@400;700&display=swap" rel="stylesheet">

</head>
<body id="pageBody" class="min-h-screen">

<!-- 🔄 Loading Screen -->
<div id="loading-screen"
     style="position:fixed;inset:0;background:#4c1d95;z-index:9999;color:white;display:flex;align-items:center;justify-content:center;flex-direction:column;text-align:center;"
     class="fixed inset-0 bg-purple-900 z-50 flex flex-col items-center justify-center text-white text-center">

    <h1 class="text-3xl font-bold mb-2 animate-pulse">CHECKQUEST</h1>
    <p class="text-sm opacity-75">Preparing your adventure...</p>
</div>

<!-- Navigation -->
<nav class="bg-purple-950/80 backdrop-blur sticky top-0 z-20 px-4 py-3 flex justify-between items-center shadow animate-slide-in">
    <h1 class="text-lg font-bold">CHECKQUEST</h1>
    <div class="flex items-center space-x-6 text-sm font-semibold tracking-wide">
        <a href="dashboard" class="hover:text-purple-300 transition">Dashboard</a>
        <a href="statistics" class="relative navbar-link select-none border-b-2 border-purple-400">Statistics</a>
        <a href="leaderboard" class="text-purple-300 border-b-2 border-purple-400">Leaderboard</a>
        <a href="logout" class="hover:text-purple-300 transition">Logout</a>
    </div>
</nav>

<!-- Main Content -->
<main class="container mx-auto px-4 py-8 animate-fade-in">
    <!-- Your Rank Section -->
    <div class="bg-white/10 backdrop-blur-lg rounded-xl p-6 mb-8 animate-scale-in">
        <h2 class="text-2xl font-bold mb-4">Your Ranking</h2>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <span class="text-4xl font-bold text-yellow-400">#<?= $currentUserRank ?></span>
                <div>
                    <p class="text-lg">Keep pushing forward!</p>
                    <p class="text-sm text-purple-300">
                        <?php if ($currentUserRank <= 3): ?>
                            Amazing work! You're among the top players!
                        <?php elseif ($currentUserRank <= 10): ?>
                            Great job! You're in the top 10!
                        <?php else: ?>
                            Keep completing tasks to climb the ranks!
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaderboard Table -->
    <div class="bg-white/10 backdrop-blur-lg rounded-xl p-6">
        <h2 class="text-2xl font-bold mb-6">Top Players</h2>
        <div class="grid gap-4">
            <?php foreach ($players as $index => $player): ?>
                <div class="player-card bg-white/5 rounded-lg p-4 flex items-center gap-4 hover:bg-white/10 transition-all"
                     style="animation-delay: <?= $index * 0.1 ?>s">
                    <!-- Rank -->
                    <div class="flex-none w-12 text-center">
                        <?php if ($index === 0): ?>
                            <span class="text-2xl trophy-gold">🏆</span>
                        <?php elseif ($index === 1): ?>
                            <span class="text-2xl trophy-silver">🥈</span>
                        <?php elseif ($index === 2): ?>
                            <span class="text-2xl trophy-bronze">🥉</span>
                        <?php else: ?>
                            <span class="text-xl font-bold">#<?= $index + 1 ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Avatar -->
                    <?php
                    $avatar = $player['avatar'] ?? 'https://api.dicebear.com/7.x/pixel-art/svg?seed=Hero';
                    $avatarFrame = $player['avatar_frame'] ?? null;
                    ?>
                    <div class="flex-none relative">
                        <?php if ($avatarFrame === 'gold'): ?>
                            <div class="absolute inset-0 w-[calc(100%+8px)] h-[calc(100%+8px)] -m-1 rounded-full bg-gradient-to-r from-yellow-300 via-yellow-500 to-yellow-300 animate-pulse"></div>
                        <?php endif; ?>
                        <img src="<?= htmlspecialchars($avatar) ?>"
                             class="w-12 h-12 rounded-full ring-2 <?= $avatarFrame === 'gold' ? 'ring-yellow-400' : 'ring-purple-400' ?> ring-offset-2 ring-offset-purple-900 relative"
                             alt="Avatar">
                    </div>


                    <!-- Player Info -->
                    <div class="flex-grow">
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold"><?= htmlspecialchars($player['username']) ?></h3>
                            <span class="bg-purple-500/20 text-purple-300 px-2 py-0.5 rounded-full text-xs">
                                    Level <?= $player['level'] ?>
                                </span>
                        </div>

                        <!-- Stats Bars -->
                        <div class="grid grid-cols-2 gap-4 mt-2">
                            <div class="text-sm">
                                <div class="flex justify-between mb-1">
                                    <span class="text-purple-300">XP</span>
                                    <span class="text-purple-300"><?= min($player['xp'], 100) ?>%</span>
                                </div>
                                <div class="w-full h-2 bg-white/10 rounded-full">
                                    <div class="stat-bar" style="width: <?= min($player['xp'], 100) ?>%"></div>
                                </div>
                            </div>
                            <div class="text-sm">
                                <div class="flex justify-between mb-1">
                                    <span class="text-purple-300">Health</span>
                                    <span class="text-purple-300"><?= $player['health'] ?>%</span>
                                </div>
                                <div class="w-full h-2 bg-white/10 rounded-full">
                                    <div class="stat-bar from-red-500 to-pink-500" style="width: <?= $player['health'] ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Stats -->
                    <div class="flex-none grid grid-cols-2 gap-4 text-center">
                        <div class="px-4">
                            <div class="text-yellow-400 text-xl font-bold"><?= $player['gold'] ?></div>
                            <div class="text-xs text-purple-300">Gold</div>
                        </div>
                        <div class="px-4 border-l border-white/10">
                            <div class="text-green-400 text-xl font-bold"><?= $player['tasks_completed'] ?></div>
                            <div class="text-xs text-purple-300">Tasks</div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const body = document.getElementById('pageBody');
        const savedTheme = localStorage.getItem('selectedTheme') || 'soft-neon';
        if (body) {
            body.className = `min-h-screen theme-${savedTheme}`;
        }
    })
    // Intersection Observer for smooth animations
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
            }
        });
    }, {
        threshold: 0.1
    });

    // Observe all player cards
    document.querySelectorAll('.player-card').forEach(card => {
        observer.observe(card);
    });
</script>
</body>
</html>

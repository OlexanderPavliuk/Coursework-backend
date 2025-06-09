<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics • CheckQuest</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <script src="/assets/js/loading_screen.js" defer></script>
    <link rel="icon" type="image/png" href="/logo/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Nunito:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4 { font-family: 'Nunito', sans-serif; }
        .chart-container {
            position: relative;
            margin: auto;
            height: 300px;
        }
    </style>
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
<nav class="bg-purple-950/80 backdrop-blur sticky top-0 z-20 px-4 py-3 flex justify-between items-center shadow">
    <h1 class="text-lg select-none drop-shadow">CHECKQUEST</h1>
    <div class="flex items-center space-x-6 text-sm font-semibold tracking-wide">
        <a href="dashboard" class="relative navbar-link select-none">Dashboard</a>
        <a href="statistics" class="relative navbar-link select-none border-b-2 border-purple-400">Statistics</a>
        <a href="leaderboard" class="relative navbar-link select-none">Leaderboard</a>
        <a href="logout" class="relative navbar-link select-none">Logout</a>
    </div>
</nav>

<main class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-center mb-8">Task Statistics</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Task Distribution -->
        <div class="bg-white/10 backdrop-blur rounded-xl p-6 shadow-lg">
            <h2 class="text-xl font-bold mb-4">Task Distribution</h2>
            <div class="chart-container">
                <canvas id="taskStatsChart"
                        data-completed="<?= $stats['completed_tasks'] ?? 0 ?>"
                        data-pending="<?= $stats['pending_tasks'] ?? 0 ?>"
                        class="w-full h-64"></canvas>
            </div>
        </div>

        <!-- Task Types -->
        <div class="bg-white/10 backdrop-blur rounded-xl p-6 shadow-lg">
            <h2 class="text-xl font-bold mb-4">Task Types</h2>
            <div class="chart-container">
                <canvas id="taskTypesChart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6">
        <!-- Completion Trend -->
        <div class="bg-white/10 backdrop-blur rounded-xl p-6 shadow-lg">
            <h2 class="text-xl font-bold mb-4">Completion Trend (Last 7 Days)</h2>
            <div class="chart-container">
                <canvas id="completionTrendChart"></canvas>
            </div>
        </div>

        <!-- Category Performance -->
        <div class="bg-white/10 backdrop-blur rounded-xl p-6 shadow-lg">
            <h2 class="text-xl font-bold mb-4">Category Performance</h2>
            <div class="chart-container">
                <canvas id="categoryPerformanceChart"></canvas>
            </div>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const body = document.getElementById('pageBody');
        const savedTheme = localStorage.getItem('selectedTheme') || 'soft-neon';
        if (body) {
            body.className = `min-h-screen theme-${savedTheme}`;
        }
        const canvas = document.getElementById('taskStatsChart');
        if (canvas) {
            const completed = parseInt(canvas.dataset.completed || '0');
            const pending = parseInt(canvas.dataset.pending || '0');

            new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: ['Completed', 'Pending'],
                    datasets: [{
                        data: [completed, pending],
                        backgroundColor: [
                            'rgba(34, 197, 94, 0.8)',
                            'rgba(249, 115, 22, 0.8)'
                        ],
                        borderColor: [
                            'rgba(34, 197, 94, 1)',
                            'rgba(249, 115, 22, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: 'white' }
                        }
                    }
                }
            });
        }

        // Task Types Chart
        new Chart(document.getElementById('taskTypesChart'), {
            type: 'polarArea',
            data: {
                labels: ['Daily Tasks', 'Habits', 'To-Dos'],
                datasets: [{
                    data: [
                        <?= $stats['daily_count'] ?>,
                        <?= $stats['habit_count'] ?>,
                        <?= $stats['todo_count'] ?>
                    ],
                    backgroundColor: [
                        'rgba(99, 102, 241, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(234, 179, 8, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: 'white' }
                    }
                }
            }
        });

        // Completion Trend Chart
        new Chart(document.getElementById('completionTrendChart'), {
            type: 'line',
            data: {
                labels: <?= json_encode($dates) ?>,
                datasets: [{
                    label: 'Tasks Completed',
                    data: <?= json_encode($completions) ?>,
                    borderColor: 'rgba(147, 51, 234, 1)',
                    backgroundColor: 'rgba(147, 51, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: 'white' }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: 'white' },
                        grid: { color: 'rgba(255, 255, 255, 0.1)' }
                    },
                    x: {
                        ticks: { color: 'white' },
                        grid: { color: 'rgba(255, 255, 255, 0.1)' }
                    }
                }
            }
        });


        // Category Performance Chart
        new Chart(document.getElementById('categoryPerformanceChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($categoryStats, 'category_name')) ?>,
                datasets: [
                    {
                        label: 'Total Tasks',
                        data: <?= json_encode(array_column($categoryStats, 'total_tasks')) ?>,
                        backgroundColor: <?= json_encode(array_map(fn($cat) => $cat['category_color'] . '80', $categoryStats)) ?>,
                        borderColor: <?= json_encode(array_column($categoryStats, 'category_color')) ?>,
                        borderWidth: 1
                    },
                    {
                        label: 'Completed Tasks',
                        data: <?= json_encode(array_column($categoryStats, 'completed_tasks')) ?>,
                        backgroundColor: <?= json_encode(array_map(fn($cat) => $cat['category_color'] . '40', $categoryStats)) ?>,
                        borderColor: <?= json_encode(array_column($categoryStats, 'category_color')) ?>,
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: 'white' }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: 'white' },
                        grid: { color: 'rgba(255, 255, 255, 0.1)' }
                    },
                    x: {
                        ticks: { color: 'white' },
                        grid: { color: 'rgba(255, 255, 255, 0.1)' }
                    }
                }
            }
        });
    });
</script>
</body>
</html>

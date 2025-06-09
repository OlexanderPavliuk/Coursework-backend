
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta name="csrf" content="<?= \Core\Csrf::token() ?>">

    <title>CheckQuest • Dashboard</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/assets/js/dashboard.js" defer></script>
    <script src="/assets/js/loading_screen.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <link rel="icon" type="image/png" href="/logo/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Nunito:wght@400;700&display=swap" rel="stylesheet">

</head>



<body id="pageBody" class="min-h-screen flex flex-col">
<?php

// Handle URL parameters for messages
if (isset($_GET['success'])) {
    $successMessage = match($_GET['success']) {
        'task_created' => 'Task created successfully!',
        default => null
    };
}
if (isset($_GET['error'])) {
    $errorMessage = match($_GET['error']) {
        'title_required' => 'Task title is required.',
        'task_creation_failed' => 'Failed to create task. Please try again.',
        default => null
    };
}
?>

<!-- Show success/error messages if they exist -->
<?php if (isset($successMessage)): ?>
    <div id="successMessage" class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50">
        <span class="block sm:inline"><?= htmlspecialchars($successMessage) ?></span>
        <button onclick="this.parentElement.remove()" class="absolute top-0 bottom-0 right-0 px-4 py-3">
            <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <title>Close</title>
                <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
            </svg>
        </button>
    </div>
    <script>
        setTimeout(() => {
            const msg = document.getElementById('successMessage');
            if (msg) msg.remove();
        }, 5000);
    </script>
<?php endif; ?>

<?php if (isset($errorMessage)): ?>
    <div id="errorMessage" class="fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded z-50">
        <span class="block sm:inline"><?= htmlspecialchars($errorMessage) ?></span>
        <button onclick="this.parentElement.remove()" class="absolute top-0 bottom-0 right-0 px-4 py-3">
            <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <title>Close</title>
                <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
            </svg>
        </button>
    </div>
    <script>
        setTimeout(() => {
            const msg = document.getElementById('errorMessage');
            if (msg) msg.remove();
        }, 5000);
    </script>
<?php endif; ?>

<!-- ➕ Floating Add Button -->
<button id="openTaskModal" aria-label="Add new task" class="fixed bottom-12 right-6 z-50 bg-purple-700 hover:bg-purple-800 text-white px-5 py-3 rounded-full shadow-lg">
    + Add Task
</button>

<!-- 🔄 Loading Screen -->
<div id="loading-screen"
     style="position:fixed;inset:0;background:#4c1d95;z-index:9999;color:white;display:flex;align-items:center;justify-content:center;flex-direction:column;text-align:center;"
     class="fixed inset-0 bg-purple-900 z-50 flex flex-col items-center justify-center text-white text-center">

    <h1 class="text-3xl font-bold mb-2 animate-pulse">CHECKQUEST</h1>
    <p class="text-sm opacity-75">Preparing your adventure...</p>
</div>

<!-- ✦ NAVBAR ✦ -->
<nav class="bg-purple-950/80 backdrop-blur sticky top-0 z-20 px-4 py-3 flex justify-between items-center shadow">
    <h1 class="text-lg select-none drop-shadow">CHECKQUEST</h1>

    <div class="flex items-center space-x-6 text-sm font-semibold tracking-wide">
        <a id="openProfileModal" class="relative navbar-link select-none">Profile</a>
        <a href="/statistics" class="relative navbar-link select-none">Statistics</a>
        <a href="/leaderboard" class="relative navbar-link select-none">Leaderboard</a>
        <a href="/logout" class="relative navbar-link select-none">Logout</a>


    </div>
</nav>




<!-- ✦ CHARACTER PANEL (Top) ✦ -->
<section class="w-full flex flex-col items-center py-6 px-4 bg-purple-950/80 backdrop-blur border-b border-white/10">
    <div id="avatarFramePulse" class="relative group">
        <div id="avatarPulseEffect">
            <?php if ($char['avatar_frame'] === 'gold'): ?>
                <div class="absolute inset-0 w-[calc(100%+8px)] h-[calc(100%+8px)] -m-1 rounded-full bg-gradient-to-r from-yellow-300 via-yellow-500 to-yellow-300 animate-pulse"></div>
            <?php endif; ?>
        </div>

        <img id="avatarImage" src="<?= htmlspecialchars($char['avatar']) ?>"
             class="w-20 h-20 rounded-full ring-4 <?= $char['avatar_frame'] === 'gold' ? 'ring-yellow-400' : 'ring-purple-400' ?> ring-offset-2 ring-offset-purple-950 shadow-lg transition-all duration-300 group-hover:scale-105 character-avatar relative"
             alt="Character Avatar">

        <div id="characterLevel" class="absolute -bottom-1 right-0 bg-purple-500 text-white text-xs px-2 py-1 rounded-full shadow-lg transform transition-all duration-300 group-hover:scale-110">
            LVL <?= $char['level'] ?>
        </div>
    </div>

    <h2 class="text-base font-semibold mt-4 mb-2 character-username"><?= htmlspecialchars($username) ?></h2>

    <div class="w-full max-w-xl space-y-4 text-sm">
        <div>
            <div class="flex justify-between mb-1">
                <label class="opacity-70">XP</label>
                <span class="text-xs opacity-70" id="xpPercent"><?= min($char['xp'], 100) ?>%</span>
            </div>
            <div class="w-full h-2 bg-white/20 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-green-400 to-lime-400 progress-bar xp-bar"
                     style="--target-width: <?= min($char['xp'], 100) ?>%"></div>
            </div>
        </div>
        <div>
            <div class="flex justify-between mb-1">
                <label class="opacity-70">HEALTH</label>
                <span class="text-xs opacity-70" id="healthPercent"><?= min($char['health'], 100) ?>%</span>
            </div>
            <div class="w-full h-2 bg-white/20 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-red-500 to-pink-500 progress-bar health-bar"
                     style="--target-width: <?= min($char['health'], 100) ?>%"></div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="opacity-70">GOLD</span>
            <button onclick="openStore()" class="bg-yellow-400/20 text-yellow-200 px-2 py-0.5 rounded-full text-xs hover:bg-yellow-400/30 transition-colors">
                <span id="goldAmount"><?= $char['gold'] ?></span>
            </button>
        </div>
    </div>
</section>



<!-- ✦ AI SUGGESTIONS ✦ -->
<section class="w-full flex flex-col items-center py-6 px-4 bg-purple-900/50 backdrop-blur border-b border-white/10">
    <div class="w-full max-w-xl">
        <div class="flex flex-col gap-4">
            <div class="flex gap-3">
                <input type="text" id="aiPrompt"
                       placeholder="Feeling stuck? Ask for suggestions..."
                       class="flex-1 px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:outline-none focus:border-purple-400">
                <button onclick="getAISuggestion()"
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors disabled:opacity-50"
                        id="aiSuggestBtn">
                    Get Ideas
                </button>
            </div>

            <!-- AI Response Container -->
            <div id="aiResponse" class="hidden">
                <div class="bg-white/10 backdrop-blur rounded-xl p-6 animate-float">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-purple-400 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        <div class="flex-1">
                            <div id="aiSuggestion" class="text-white"></div>
                            <div class="mt-4 flex justify-end">
                                <button onclick="addSuggestedTasks()"
                                        class="text-sm px-3 py-1.5 bg-purple-500/20 text-purple-300 rounded-lg hover:bg-purple-500/30 transition-colors">
                                    Add These Tasks →
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ✦ TASK SECTIONS VERTICALLY STACKED ✦ -->
<main class="flex-1 px-4 sm:px-8 py-8">

    <!-- ✦ Tabs -->
    <div class="flex flex-col items-center gap-4 mb-8">
        <div class="flex justify-center gap-8 text-sm font-semibold tracking-wide text-purple-300" role="tablist">
            <button onclick="switchTab('all')" id="tab-all" role="tab" aria-selected="true" aria-controls="section-all" class="pb-1 border-b-2 border-purple-400">All Tasks</button>
            <button onclick="switchTab('habit')" id="tab-habit" role="tab" aria-selected="false" aria-controls="section-habit" class="pb-1 hover:text-white">Habits</button>
            <button onclick="switchTab('daily')" id="tab-daily" role="tab" aria-selected="false" aria-controls="section-daily" class="pb-1 hover:text-white">Dailies</button>
            <button onclick="switchTab('todo')" id="tab-todo" role="tab" aria-selected="false" aria-controls="section-todo" class="pb-1 hover:text-white">To‑Dos</button>
        </div>
        <div class="w-full max-w-md">
            <input type="text" id="taskSearch" aria-label="Search tasks" placeholder="Search tasks..." class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:border-purple-400">
        </div>
    </div>

    <?php
    $sections = [
        'all' => ['todo' => 'To‑Dos', 'daily' => 'Dailies', 'habit' => 'Habits'],
        'habit' => ['habit' => 'Habits'],
        'daily' => ['daily' => 'Dailies'],
        'todo' => ['todo' => 'To‑Dos']
    ];
    ?>

    <?php
    $priorityColor = [
        'low' => 'bg-green-400 text-black',
        'medium' => 'bg-yellow-400 text-black',
        'high' => 'bg-red-600 text-white',
    ];


    ?>

    <?php foreach ($sections as $tabId => $section): ?>
        <section id="section-<?= $tabId ?>" class="<?= $tabId !== 'all' ? 'hidden' : '' ?>">
            <div class="<?= $tabId === 'all' ? 'grid grid-cols-1 lg:grid-cols-3 gap-6' : 'space-y-6' ?> animate-float">
                <?php foreach ($section as $type => $label): ?>
                    <div class="card-glass p-4 <?= $tabId === 'all' ? '' : '' ?>">
                        <h2 class="text-xl font-bold mb-4"><?= $label ?></h2>
                        <div class="flex flex-col gap-4 max-h-[400px] overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-purple-600 scrollbar-track-transparent task-container" data-type="<?= $type ?>">
                            <?php
                            $filteredTasks = array_filter($tasks, fn($t) => $t['type'] === $type);
                            foreach ($filteredTasks as $task):
                                ?>
                                <div class="bg-white text-gray-900 rounded-lg border-l-8 border-purple-400 flex items-start justify-between p-4 shadow hover:shadow-md transition task-card group"
                                     data-id="<?= $task['id'] ?>" draggable="true">
                                    <div class="flex-1">
                                        <h4 class="text-base font-semibold group-hover:text-purple-600 transition-colors"><?= htmlspecialchars($task['title']) ?></h4>
                                        <?php if (!empty($task['notes'])): ?>
                                            <p class="text-xs text-gray-600 mt-1 italic"><?= htmlspecialchars($task['notes']) ?></p>
                                        <?php endif; ?>

                                        <!-- Display categories -->
                                        <?php if (!empty($task['categories'])): ?>
                                            <div class="flex flex-wrap gap-1.5 mt-2">
                                                <?php foreach ($task['categories'] as $category): ?>
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium transition-all hover:scale-105"
                                                          style="background-color: <?= htmlspecialchars($category['color']) ?>15; color: <?= htmlspecialchars($category['color']) ?>; border: 1px solid <?= htmlspecialchars($category['color']) ?>50;">
                                                                <svg class="w-2 h-2 mr-1.5" fill="currentColor" viewBox="0 0 8 8">
                                                                    <circle cx="4" cy="4" r="3"/>
                                                                </svg>
                                                                <?= htmlspecialchars($category['name']) ?>
                                                            </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($task['deadline']): ?>
                                            <p class="text-xs text-gray-500 flex items-center gap-1 mt-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <?= $task['deadline'] ?>
                                            </p>
                                        <?php endif; ?>

                                        <?php
                                        $isOverdue = !$task['completed'] && $task['deadline'] && strtotime($task['deadline']) < strtotime($now);
                                        ?>
                                        <?php if ($type !== 'habit'): ?>
                                            <p class="text-sm mt-2 flex items-center gap-2 status-wrapper" data-task-id="<?= $task['id'] ?>">
                                                Status:
                                                <?php if ($task['completed']): ?>
                                                    <span class="text-green-600 font-semibold flex items-center gap-1">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                        Done
                                                    </span>
                                                <?php elseif ($isOverdue && $type !== 'reward'): ?>
                                                    <span class="text-red-500 font-semibold flex items-center gap-1">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        Overdue
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-yellow-600 font-semibold flex items-center gap-1">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        Pending
                                                    </span>
                                                <?php endif; ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>

                                    <div class="flex flex-col items-end gap-2">
                                                <span class="inline-block text-xs font-semibold uppercase rounded-full px-2 py-1 <?= $priorityColor[strtolower($task['priority'])] ?? 'bg-gray-400 text-black' ?>
                                                      transform transition-all duration-300 group-hover:scale-105">
                                        <?= ucfirst(strtolower($task['priority'])) ?>
                                    </span>

                                        <?php if ($type === 'habit'): ?>
                                            <div class="flex gap-1">
                                                <button type="button"
                                                        onclick="trackHabit(<?= $task['id'] ?>, 1)"
                                                        class="bg-green-500 hover:bg-green-400 text-white w-8 h-8 rounded-full flex items-center justify-center transition-all hover:scale-110">
                                                    +
                                                </button>
                                                <button type="button"
                                                        onclick="trackHabit(<?= $task['id'] ?>, -1)"
                                                        class="bg-red-500 hover:bg-red-400 text-white w-8 h-8 rounded-full flex items-center justify-center transition-all hover:scale-110">
                                                    −
                                                </button>
                                            </div>

                                            <!-- Render habit count -->
                                            <div class="text-xs mt-1 text-gray-700">
                                                <span class="text-green-600 font-semibold habit-plus">+<?= $task['plus_count'] ?? 0 ?></span>
                                                /
                                                <span class="text-red-500 font-semibold habit-minus">−<?= $task['minus_count'] ?? 0 ?></span>
                                            </div>

                                        <?php endif; ?>




                                        <?php if (!$task['completed'] && $type !== 'habit'): ?>
                                            <button id="complete-btn-<?= $task['id'] ?>" onclick="completeTask(<?= $task['id'] ?>)"
                                                    class="bg-purple-600 hover:bg-purple-500 text-white px-3 py-1 rounded-lg shadow transition-all hover:scale-105 flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                Complete
                                            </button>
                                        <?php endif; ?>


                                        <div class="flex gap-1 group-hover:opacity-100 opacity-0 transition-opacity">
                                            <button onclick='openEditTaskModal(<?= json_encode($task) ?>)'
                                                    class="bg-blue-100 hover:bg-blue-200 text-blue-600 p-1.5 rounded-lg"
                                                    title="Edit Task">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h2m-1 0v14m-7-7h14"/>
                                                </svg>
                                            </button>

                                            <button onclick="openDeleteConfirmModal(<?= $task['id'] ?>)"
                                                    class="bg-red-100 hover:bg-red-200 text-red-600 p-1.5 rounded-lg"
                                                    title="Delete task">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>

                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($filteredTasks)): ?>
                                <div class="text-center text-gray-400 py-8">No tasks found.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>

</main>

<!-- ✨ Task Modal -->
<div id="taskModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white text-gray-800 p-6 rounded-xl w-full max-w-md shadow-xl relative">
        <!-- ✖ close button -->
        <button onclick="closeTaskModal()"
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <div class="text-center mb-6">
            <h3 id="taskModalTitle" class="text-2xl font-bold text-purple-700">Create New Task</h3>
            <p class="text-gray-500 text-sm mt-1">Add a new quest to your adventure!</p>
        </div>

        <!-- ***************************************************************** -->
        <!--  NOTE: keep action="/task/add" – dashboard.js intercepts via Fetch -->
        <!-- ***************************************************************** -->
        <form action="/task/add" method="post" class="space-y-6">
            <!-- CSRF token (required for every POST) -->
            <input type="hidden" name="csrf_token" value="<?= \Core\Csrf::token() ?>">
            <input type="hidden" name="task_id" value="">

            <!-- Task Title -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Task Title</label>
                <input name="title" type="text" required
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-shadow"
                       placeholder="Enter your task title">
            </div>

            <!-- Task Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Task Type</label>
                <div class="grid grid-cols-3 gap-3">
                    <!-- To-Do -->
                    <label class="task-type-option">
                        <input type="radio" name="type" value="todo" class="sr-only" checked>
                        <div class="border rounded-lg p-3 cursor-pointer hover:border-purple-500 transition-all text-center">
                            <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <span class="text-sm">To-Do</span>
                        </div>
                    </label>
                    <!-- Daily -->
                    <label class="task-type-option">
                        <input type="radio" name="type" value="daily" class="sr-only">
                        <div class="border rounded-lg p-3 cursor-pointer hover:border-purple-500 transition-all text-center">
                            <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-sm">Daily</span>
                        </div>
                    </label>
                    <!-- Habit -->
                    <label class="task-type-option">
                        <input type="radio" name="type" value="habit" class="sr-only">
                        <div class="border rounded-lg p-3 cursor-pointer hover:border-purple-500 transition-all text-center">
                            <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                            </svg>
                            <span class="text-sm">Habit</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Priority -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Priority Level</label>
                <div class="flex justify-center gap-6">
                    <!-- Low -->
                    <label class="priority-option">
                        <input type="radio" name="priority" value="low" class="sr-only">
                        <div class="w-8 h-8 rounded-full bg-green-500 cursor-pointer hover:scale-110 transition-transform duration-200 hover:ring-4 hover:ring-green-200"></div>
                    </label>
                    <!-- Med -->
                    <label class="priority-option">
                        <input type="radio" name="priority" value="medium" class="sr-only" checked>
                        <div class="w-8 h-8 rounded-full bg-yellow-500 cursor-pointer hover:scale-110 transition-transform duration-200 hover:ring-4 hover:ring-yellow-200"></div>
                    </label>
                    <!-- High -->
                    <label class="priority-option">
                        <input type="radio" name="priority" value="high" class="sr-only">
                        <div class="w-8 h-8 rounded-full bg-red-500 cursor-pointer hover:scale-110 transition-transform duration-200 hover:ring-4 hover:ring-red-200"></div>
                    </label>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                <textarea name="notes" rows="3"
                          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-shadow"
                          placeholder="Add any additional details..."></textarea>
            </div>

            <!-- Categories -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Categories</label>
                <div class="relative">
                    <div id="selectedCategories" class="flex flex-wrap gap-2 mb-2"></div>

                    <div class="flex gap-2">
                        <select id="categorySelect"
                                class="flex-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-shadow">
                            <option value="">Select a category...</option>
                            <?php
                            $stmt = $pdo->prepare("
                                SELECT id,name,color
                                FROM task_categories
                                WHERE user_id = ?
                                ORDER BY name
                            ");
                            $stmt->execute([$user_id]);
                            while ($cat = $stmt->fetch()) {
                                echo '<option value="'.$cat['id'].'" data-color="'.$cat['color'].'">'
                                    .htmlspecialchars($cat['name']).'</option>';
                            }
                            ?>
                        </select>
                        <button type="button" onclick="addSelectedCategory()"
                                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors">
                            Add
                        </button>
                    </div>

                    <input type="hidden" name="categories" id="categoriesInput" value="[]">
                </div>
            </div>

            <!-- Deadline -->
            <div class="deadline-field">
                <label class="block text-sm font-medium text-gray-700 mb-1">Deadline</label>
                <input type="datetime-local" name="deadline"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-shadow">
                <p class="mt-1 text-xs text-gray-500">Optional for To-Dos and Dailies</p>
            </div>

            <!-- Submit -->
            <button type="submit"
                    class="w-full bg-purple-600 text-white py-3 rounded-lg hover:bg-purple-700 focus:ring-4 focus:ring-purple-500 focus:ring-opacity-50 transition-all">
                Create Task
            </button>
        </form>
    </div>
</div>



<!-- Store Modal -->
<div id="storeModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden overflow-y-auto py-8">
    <div class="bg-white text-gray-800 p-6 rounded-xl w-full max-w-4xl shadow-xl relative animate-float mx-4 my-auto">
        <div class="absolute top-4 right-4">
            <button onclick="closeStore()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div class="text-center mb-6">
            <h3 class="text-2xl font-bold text-purple-700">Store</h3>
            <p class="text-gray-500 text-sm mt-1">Enhance your adventure with magical items!</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-h-[60vh] overflow-y-auto p-2 store-items-container">
            <?php
            // Get all store items and user's purchases
            $stmt = $pdo->prepare("
                    SELECT 
                        si.*,
                        CASE 
                            WHEN si.type IN ('potion', 'boost', 'mystery') THEN TRUE
                            WHEN sp.id IS NOT NULL THEN FALSE  -- Already purchased non-consumable
                            ELSE TRUE  -- Available for purchase
                        END as can_purchase
                    FROM store_items si
                    LEFT JOIN store_purchases sp ON si.id = sp.item_id 
                        AND sp.user_id = ? 
                        AND si.type = 'cosmetic'  -- Only check purchases for cosmetic items
                    GROUP BY si.id
                    ORDER BY si.price ASC
                ");
            $stmt->execute([$user_id]);
            $items = $stmt->fetchAll();

            foreach ($items as $item):
                $itemTypeClass = match($item['type']) {
                    'potion' => 'from-red-500 to-pink-500',
                    'boost' => 'from-green-500 to-emerald-500',
                    'cosmetic' => 'from-yellow-500 to-amber-500',
                    'mystery' => 'from-blue-500 to-indigo-500',
                    default => 'from-purple-500 to-indigo-500'
                };

                $buttonClass = $item['can_purchase']
                    ? 'bg-purple-600 hover:bg-purple-700 cursor-pointer'
                    : 'bg-gray-400 cursor-not-allowed';
                ?>
                <div class="bg-white rounded-xl overflow-hidden shadow-lg border border-gray-200 hover:border-purple-300 transition-all hover:shadow-xl group">
                    <div class="h-32 bg-gradient-to-br <?= $itemTypeClass ?> p-4 flex items-center justify-center">
                        <img src="<?= htmlspecialchars($item['image_url']) ?>"
                             class="w-20 h-20 object-contain filter drop-shadow-lg transform group-hover:scale-110 transition-transform duration-300"
                             alt="<?= htmlspecialchars($item['name']) ?>">
                    </div>

                    <div class="p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-lg font-bold text-gray-800"><?= htmlspecialchars($item['name']) ?></h4>
                            <span class="flex items-center gap-1 bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full text-sm font-semibold">
                                <?= $item['price'] ?> 🪙
                            </span>
                        </div>

                        <p class="text-gray-600 text-sm mb-4"><?= htmlspecialchars($item['description']) ?></p>

                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500 capitalize">Type: <?= htmlspecialchars($item['type']) ?></span>
                            <?php if (!$item['can_purchase']): ?>
                                <button disabled class="<?= $buttonClass ?> text-white px-4 py-2 rounded-lg transition-colors">
                                    Owned
                                </button>
                            <?php else: ?>
                                <button onclick="purchaseItem(
                                <?= $item['id'] ?>,
                                <?= $item['price'] ?>,
                                        '<?= htmlspecialchars($item['name']) ?>',
                                        this,
                                        '<?= $item['type'] ?>'
                                        )" class="<?= $buttonClass ?> text-white px-4 py-2 rounded-lg transition-colors">
                                    <?= $item['can_purchase'] ? 'Purchase' : 'Owned' ?>
                                </button>

                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="hidden fixed bottom-4 right-4 bg-white text-gray-800 px-6 py-3 rounded-lg shadow-lg transform translate-y-full transition-transform duration-300 z-50 flex items-center gap-3"></div>

<!-- Add avatar modal -->
<div id="avatarModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white text-gray-800 p-6 rounded-xl w-full max-w-2xl shadow-xl relative">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-bold">Choose Your Avatar</h3>
            <button onclick="closeAvatarModal()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
            <?php
            $avatarFiles = glob($_SERVER['DOCUMENT_ROOT'] . '/icons/*.{jpg,jpeg,png,svg}', GLOB_BRACE);
            sort($avatarFiles); // optional: alphabetical or numeric order

            foreach ($avatarFiles as $index => $file):
                $filename = basename($file);
                $url = "/icons/$filename";
                ?>

                <div class="avatar-option cursor-pointer transform transition-all duration-200 hover:scale-105"
                     onclick="selectAvatar('<?= $url ?>', this)"
                     data-url="<?= $url ?>">
                    <img src="<?= $url ?>"
                         alt="Avatar option <?= $index + 1 ?>"
                         class="w-full aspect-square rounded-lg border-4 border-transparent hover:border-purple-400">
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <button onclick="closeAvatarModal()"
                    class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition-colors">
                Cancel
            </button>
            <button onclick="saveAvatar()"
                    class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-500 transition-colors">
                Save Changes
            </button>
        </div>
    </div>
</div>

<!-- Add confirmation modal -->
<div id="deleteConfirmModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white text-gray-800 p-6 rounded-xl w-full max-w-md shadow-xl relative animate-float">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Delete Task</h3>
        <p class="text-gray-600 mb-6">Are you sure you want to delete this task? This action cannot be undone.</p>

        <div class="flex justify-end gap-3">
            <button onclick="closeDeleteConfirmModal()"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg transition-colors">
                Cancel
            </button>
            <button id="confirmDeleteBtn"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                Delete
            </button>
        </div>
    </div>
</div>

<!-- 🧍 Profile Modal -->
<div id="profileModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-white/10 text-white rounded-2xl p-6 w-[420px] max-w-full relative border border-white/10 shadow-xl">

        <button id="closeProfileModal" class="absolute top-4 right-4 text-white hover:text-purple-300 text-xl">&times;</button>

        <!-- 🔁 PROFILE VIEW -->
        <div id="profileView">

            <!-- 🎨 Theme Selector -->
            <div class="mb-6">
                <label for="themeSelector" class="block text-sm font-medium text-white/70 mb-1">Theme</label>
                <select id="themeSelector" class="w-full px-4 py-2 bg-black/30 text-white border border-white/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="soft-neon">Soft Neon</option>
                    <option value="sunset-glow">Sunset Glow</option>
                    <option value="cyber-minimalist">Cyber Minimalist</option>
                    <option value="forest-productivity">Forest Productivity</option>
                </select>
            </div>

            <!-- 🧍 Profile Info -->
            <div id="profileContent" class="text-center space-y-4">
                <img id="profileAvatar" src="/icons/1.jpg" alt="Avatar" class="w-24 h-24 mx-auto rounded-full border-4 border-purple-500 shadow-md">
                <h2 id="profileUsername" class="text-2xl font-bold">Loading...</h2>
                <p class="text-sm text-purple-300" id="profileLevel">Level 0</p>

                <!-- 📊 XP Bar -->
                <div class="text-left space-y-1">
                    <label class="text-xs text-white/60">XP</label>
                    <div class="w-full h-2 bg-white/10 rounded-full overflow-hidden">
                        <div id="xpBar" class="bg-yellow-400 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>

                <!-- ❤️ HP Bar -->
                <div class="text-left space-y-1">
                    <label class="text-xs text-white/60">Health</label>
                    <div class="w-full h-2 bg-white/10 rounded-full overflow-hidden">
                        <div id="hpBar" class="bg-red-400 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>

                <p class="text-sm text-white/80"><strong>Gold:</strong> <span id="profileGold">0</span></p>

                <!-- 📝 About Me -->
                <div class="text-left mt-4">
                    <label for="profileDescription" class="block text-sm text-white/70 mb-1">📝 About Me</label>
                    <textarea id="profileDescription" class="w-full p-2 bg-black/30 border border-white/20 text-white rounded-lg resize-none focus:outline-none focus:ring-2 focus:ring-purple-500" rows="3" placeholder="Tell something about yourself..."></textarea>

                    <!-- 🔘 Save + Switch -->
                    <div class="flex justify-end mt-3 space-x-2">
                        <button id="saveProfileDescription" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                            Save
                        </button>
                        <button id="openEditAccount" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                            Edit Account
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ✏️ EDIT ACCOUNT VIEW -->
        <div id="editAccountView" class="hidden">
            <div class="text-left mt-6 space-y-4">
                <h3 class="text-sm text-white/70 font-semibold">🛠 Edit Account</h3>

                <div>
                    <label for="editUsername" class="block text-sm text-white/60 mb-1">Username</label>
                    <input id="editUsername" type="text" class="w-full p-2 bg-black/30 border border-white/20 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Your username">
                </div>

                <div>
                    <label for="editEmail" class="block text-sm text-white/60 mb-1">Email (optional)</label>
                    <input id="editEmail" type="email" class="w-full p-2 bg-black/30 border border-white/20 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Your email">
                </div>

                <div>
                    <label for="editPassword" class="block text-sm text-white/60 mb-1">New Password</label>
                    <input id="editPassword" type="password" class="w-full p-2 bg-black/30 border border-white/20 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="••••••••">
                </div>

                <!-- 🔁 Switch Back + Save -->
                <div class="flex justify-between mt-4">
                    <button id="backToProfile" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                        ← Back
                    </button>
                    <button id="saveAccountSettings" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                        Update Account
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>




<!-- ✦ FOOTER ✦ -->
<footer class="bg-purple-950/80 backdrop-blur static bottom-0 z-20 px-4 py-3 flex justify-between items-center text-sm text-white shadow-inner">
    <span class="select-none opacity-80">© <?= date('Y') ?> CheckQuest</span>

    <div class="flex gap-4 items-center text-xs opacity-80">
        <span class="hidden sm:inline">Made with 💜 by Alexander Pavliuk</span>
        <a href="https://github.com/your-github" target="_blank" class="hover:underline">GitHub</a>
        <a href="#" class="hover:underline">About</a>
        <span class="text-gray-500 select-none">v1.34</span>
    </div>
</footer>


<div id="deathScreen" class="fixed inset-0 bg-black bg-opacity-80 text-white flex flex-col items-center justify-center z-[9999] hidden">
    <h1 class="text-4xl font-extrabold mb-4 animate-float">You Died</h1>
    <button onclick="revivePlayer()" class="px-6 py-3 bg-purple-600 hover:bg-purple-500 rounded-lg text-lg shadow animate-pulse">
        Return by Death
    </button>
    <audio id="deathAudio" src="/audio/rezero-death.mp3" preload="auto"></audio>
</div>

</body>
</html>
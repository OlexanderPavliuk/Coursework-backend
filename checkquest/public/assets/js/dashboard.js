// 🔐 global token
const CSRF = document.querySelector('meta[name="csrf"]').content;

/** ==================== 🌄 Theme & Layout ==================== **/
document.addEventListener('DOMContentLoaded', () => {
    const themeSelector = document.getElementById('themeSelector');
    const body = document.getElementById('pageBody');

    function applyTheme(theme) {
        if (body) {
            body.className = `min-h-screen flex flex-col theme-${theme}`;
        }
    }

    const savedTheme = localStorage.getItem('selectedTheme') || 'soft-neon';
    applyTheme(savedTheme);

    if (themeSelector) {
        themeSelector.value = savedTheme;
        themeSelector.addEventListener('change', (e) => {
            const selected = e.target.value;
            localStorage.setItem('selectedTheme', selected);
            applyTheme(selected);
        });
    }
});


/** ==================== 🌠 Stars Background ==================== **/
document.addEventListener('DOMContentLoaded', () => {
    const STAR_COUNT = 60;
    for (let i = 0; i < STAR_COUNT; i++) {
        const s = document.createElement('div');
        s.className = 'fixed w-1 h-1 bg-white/80 rounded-full animate-pulse';
        s.style.top = Math.random() * 100 + '%';
        s.style.left = Math.random() * 100 + '%';
        s.style.animationDelay = Math.random() * 2 + 's';
        document.body.appendChild(s); // ← тепер точно існує
    }
});





/** ==================== 🧭 Tabs & Search ==================== **/
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('taskSearch');
    const urlParams = new URLSearchParams(window.location.search);
    let currentTab = 'all';

    function filterTasks(searchTerm) {
        const searchLower = searchTerm.toLowerCase().trim();
        const activeSection = document.querySelector(`#section-${currentTab}`);
        if (!activeSection) return;

        const taskContainers = activeSection.querySelectorAll('.card-glass');
        taskContainers.forEach(container => {
            const taskCards = container.querySelectorAll('.task-card');
            let hasVisibleTasks = false;

            taskCards.forEach(card => {
                const title = card.querySelector('h4')?.textContent.toLowerCase() || '';
                const notes = card.querySelector('.text-xs')?.textContent.toLowerCase() || '';
                const deadline = card.querySelector('.text-xs')?.textContent.toLowerCase() || '';
                const priority = card.querySelector('.rounded-full')?.textContent.toLowerCase() || '';
                const status = card.querySelector('.text-sm')?.textContent.toLowerCase() || '';

                const isVisible = !searchTerm || title.includes(searchLower) ||
                    notes.includes(searchLower) || deadline.includes(searchLower) ||
                    priority.includes(searchLower) || status.includes(searchLower);

                if (isVisible) {
                    card.style.display = 'flex';
                    card.style.opacity = '1';
                    hasVisibleTasks = true;
                } else {
                    card.style.opacity = '0';
                    setTimeout(() => card.style.display = 'none', 200);
                }
            });

            const emptyMessage = container.querySelector('.empty-state');
            if (emptyMessage) {
                emptyMessage.style.display = hasVisibleTasks ? 'none' : 'block';
            }
            container.style.display = hasVisibleTasks ? 'block' : 'none';
        });

        const url = new URL(window.location);
        if (searchTerm) {
            url.searchParams.set('search', searchTerm);
        } else {
            url.searchParams.delete('search');
        }
        window.history.pushState({}, '', url);
    }

    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func(...args), wait);
        };
    }

    const debouncedFilter = debounce((value) => filterTasks(value), 150);
    searchInput.addEventListener('input', (e) => debouncedFilter(e.target.value));

    window.switchTab = function (tab) {
        const tabs = ['all', 'habit', 'daily', 'todo'];
        tabs.forEach(id => {
            document.getElementById('section-' + id).classList.add('hidden');
            document.getElementById('tab-' + id).classList.remove('text-white', 'border-purple-400');
        });
        document.getElementById('section-' + tab).classList.remove('hidden');
        document.getElementById('tab-' + tab).classList.add('text-white', 'border-purple-400');
        currentTab = tab;
        if (searchInput.value) filterTasks(searchInput.value);
    };

    const initialSearch = urlParams.get('search');
    if (initialSearch) {
        searchInput.value = initialSearch;
        filterTasks(initialSearch);
    }

    window.addEventListener('popstate', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const searchTerm = urlParams.get('search') || '';
        searchInput.value = searchTerm;
        filterTasks(searchTerm);
    });
});

/** ==================== 🪟 Task Modal ==================== **/
document.addEventListener('DOMContentLoaded', () => {
    const openModalBtn  = document.getElementById('openTaskModal');
    const closeModalBtn = document.getElementById('closeTaskModal');
    const taskModal     = document.getElementById('taskModal');

    /* +++++++++++++ ➊  DEFINE GLOBAL HELPER +++++++++++++ */
    window.closeTaskModal = () => {
        taskModal?.classList.add('hidden');
    };
    /* +++++++++++++++++++++++++++++++++++++++++++++++++++ */

    openModalBtn?.addEventListener('click', () => {
        taskModal?.classList.remove('hidden');

        const form = document.querySelector('#taskModal form');

        // Reset all fields
        form.reset();
        form.action = '/task/add';
        form.querySelector('input[name="task_id"]').value = '';

        // Reset modal title and button
        document.getElementById('taskModalTitle').textContent = 'Create New Task';
        form.querySelector('button[type="submit"]').textContent = 'Create Task';

        // Reset priority to medium
        document.querySelectorAll('input[name="priority"]').forEach(r => {
            r.checked = r.value === 'medium';
        });

        // Reset task type to To-Do
        document.querySelectorAll('input[name="type"]').forEach(r => {
            r.checked = r.value === 'todo';
        });

        // Reset deadline visibility
        updateDeadlineVisibility('todo');

        // Reset selected categories
        selectedCategoryIds = new Set();
        updateSelectedCategoriesDisplay();
        updateCategoriesInput();
    });

    closeModalBtn?.addEventListener('click', () => taskModal?.classList.add('hidden'));

    taskModal?.addEventListener('click', (e) => {
        if (e.target === taskModal) closeTaskModal();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !taskModal?.classList.contains('hidden')) {
            closeTaskModal();
        }
    });

    // 📌 Attach deadline visibility toggle
    document.querySelectorAll('input[name="type"]').forEach(radio => {
        radio.addEventListener('change', () => {
            updateDeadlineVisibility(radio.value);
        });
    });
});

function updateDeadlineVisibility(type) {
    const deadlineField = document.querySelector('.deadline-field');
    if (deadlineField) {
        deadlineField.style.display = (type === 'habit') ? 'none' : 'block';
    }
}




/* ==========================================================
   COMPLETE TASK  (async + UI updates)
   ========================================================== */
async function completeTask(id) {
    try {
        const res = await fetch('/task/complete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'fetch',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ task_id: id, csrf_token: CSRF })
        });

        const j = await res.json();
        if (!j.success) {
            showToast(j.error || 'Error completing task', true);
            return;
        }

        /* toast */
        showToast(j.message || 'Task completed');

        /* XP bar */
        if (j.newXP !== undefined) {
            const xpBar   = document.querySelector('.xp-bar');
            const xpLabel = document.getElementById('xpPercent');
            if (xpBar)   xpBar.style.setProperty('--target-width', `${j.newXP}%`);
            if (xpLabel) xpLabel.textContent = `${j.newXP}%`;
        }

        /* level */
        if (j.newLevel !== undefined) {
            const lvl = document.getElementById('characterLevel');
            if (lvl) lvl.textContent = `LVL ${j.newLevel}`;
        }

        /* gold */
        if (j.newGold !== undefined) {
            const goldEl = document.getElementById('goldAmount');
            if (goldEl) goldEl.textContent = j.newGold;
        }

        /* fade card + change status */
        const card = document.querySelector(`.task-card[data-id="${id}"]`);
        if (card) card.classList.add('opacity-100');

        const wrapper = document.querySelector(
            `.status-wrapper[data-task-id="${id}"]`
        );
        if (wrapper) {
            wrapper.innerHTML = `
                Status:
                <span class="status-text text-green-600 font-semibold flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M5 13l4 4L19 7"></path>
                    </svg>
                    Done
                </span>`;
        }

        /* remove Complete button */
        const btn = document.getElementById(`complete-btn-${id}`);
        if (btn) btn.remove();

    } catch (err) {
        console.error(err);
        showToast('Network error', true);
    }
}

/* ==========================================================
   DELETE TASK  (async + UI updates)
   ========================================================== */
let taskToDelete = null;

function openDeleteConfirmModal(id) {
    taskToDelete = id;
    document.getElementById('deleteConfirmModal')?.classList.remove('hidden');
}

function closeDeleteConfirmModal() {
    document.getElementById('deleteConfirmModal')?.classList.add('hidden');
    taskToDelete = null;
}

document.getElementById('confirmDeleteBtn')?.addEventListener('click', async () => {
    if (!taskToDelete) return;

    try {
        const fd = new FormData();
        fd.append('task_id', taskToDelete);
        fd.append('csrf_token', CSRF);

        const res = await fetch('/task/delete', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'fetch',
                'Accept': 'application/json'
            },
            body: fd
        });

        const j = await res.json();
        if (!j.success) {
            showToast(j.error || 'Delete failed', true);
            return;
        }

        const card = document.querySelector(`.task-card[data-id="${taskToDelete}"]`);
        if (card) card.remove();
        showToast('Task deleted');
        closeDeleteConfirmModal();
    } catch (err) {
        console.error(err);
        showToast('Network error', true);
    }
});


/* simple reusable toast */
function showToast(msg, isErr = false) {
    const toast = document.getElementById('toast');
    toast.textContent = msg;
    toast.className = (isErr ? 'bg-red-600 text-white' : 'bg-green-600 text-white')
        + ' fixed bottom-4 right-4 px-4 py-2 rounded shadow-lg';
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 4000);
}


/** ==================== 📊 Chart Initialization ==================== **/
document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('taskStatsChart');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const completed = parseInt(canvas.dataset.completed || '0');
    const pending = parseInt(canvas.dataset.pending || '0');

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Completed', 'Pending'],
            datasets: [{
                data: [completed, pending],
                backgroundColor: ['rgba(34, 197, 94, 0.8)', 'rgba(249, 115, 22, 0.8)'],
                borderColor: ['rgba(34, 197, 94, 1)', 'rgba(249, 115, 22, 1)'],
                borderWidth: 1,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: 'white',
                        font: { size: 14 },
                        padding: 20
                    }
                },
                title: {
                    display: true,
                    text: 'Task Distribution',
                    color: 'white',
                    font: { size: 16, weight: 'bold' },
                    padding: { bottom: 20 }
                }
            }
        }
    });
});
/** ==================== 🗂️ Category Logic ==================== **/
let selectedCategoryIds = new Set();

function addSelectedCategory() {
    const select = document.getElementById('categorySelect');
    const option = select.options[select.selectedIndex];
    if (!option.value || selectedCategoryIds.has(option.value)) return;
    selectedCategoryIds.add(option.value);
    updateSelectedCategoriesDisplay();
    updateCategoriesInput();
}

function removeCategory(categoryId) {
    selectedCategoryIds.delete(categoryId);
    updateSelectedCategoriesDisplay();
    updateCategoriesInput();
}

function updateSelectedCategoriesDisplay() {
    const container = document.getElementById('selectedCategories');
    const select = document.getElementById('categorySelect');
    container.innerHTML = '';

    selectedCategoryIds.forEach(id => {
        const option = select.querySelector(`option[value="${id}"]`);
        if (!option) return;
        const color = option.dataset.color;
        const name = option.textContent;

        const tag = document.createElement('div');
        tag.className = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium';
        tag.style.backgroundColor = `${color}15`;
        tag.style.color = color;
        tag.style.border = `1px solid ${color}50`;

        tag.innerHTML = `
            <svg class="w-2 h-2 mr-1.5" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
            ${name}
            <button type="button" onclick="removeCategory('${id}')" class="ml-1 hover:text-red-500">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        `;
        container.appendChild(tag);
    });
}

function updateCategoriesInput() {
    document.getElementById('categoriesInput').value = JSON.stringify([...selectedCategoryIds]);
}

document.getElementById('openTaskModal')?.addEventListener('click', () => {
    selectedCategoryIds.clear();
    updateSelectedCategoriesDisplay();
    updateCategoriesInput();
});

/** ==================== 🧲 Sortable Drag & Drop ==================== **/
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.task-container').forEach(container => {
        new Sortable(container, {
            animation: 150,
            ghostClass: 'bg-purple-100',
            dragClass: 'shadow-lg',
            group: 'tasks',
            onEnd: function(evt) {
                const taskId = evt.item.dataset.id;
                const newType = evt.to.dataset.type;
                const newIndex = evt.newIndex;
                const prevTaskId = evt.to.children[newIndex - 1]?.dataset.id || null;
                const nextTaskId = evt.to.children[newIndex + 1]?.dataset.id || null;

                evt.item.classList.add('scale-105');
                setTimeout(() => evt.item.classList.remove('scale-105'), 300);
                updateTaskPosition(taskId, newType, prevTaskId, nextTaskId);
            }
        });
    });
});

function updateTaskPosition(taskId, newType, prevTaskId, nextTaskId) {
    fetch('update_task_position.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ taskId, newType, prevTaskId, nextTaskId })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const task = document.querySelector(`[data-id="${taskId}"]`);
                task?.classList.add('border-green-400');
                setTimeout(() => task?.classList.remove('border-green-400'), 1000);
            } else {
                console.error('Failed to update task position');
            }
        })
        .catch(err => console.error('Error:', err));
}

/** ==================== 🛒 Store Functions ==================== **/
function openStore() {
    document.getElementById('storeModal')?.classList.remove('hidden');
}
function closeStore() {
    document.getElementById('storeModal')?.classList.add('hidden');
}

function purchaseItem(itemId, price, itemName, buttonElement, itemType) {
    const gold = parseInt(document.getElementById('goldAmount').textContent);
    if (gold < price) {
        showToast(`Not enough gold to purchase ${itemName}!`, true);
        return;
    }

    fetch('/store/purchase', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'fetch', 'Accept': 'application/json'},
        body: JSON.stringify({ itemId, csrf_token: CSRF })
    })
        .then(async res => {
            const text = await res.text();
            try {
                return JSON.parse(text);
            } catch {
                throw new Error("Invalid response: " + text);
            }
        })
        .then(data => {
            if (data.success) {
                document.getElementById('goldAmount').textContent = data.newGold;
                showToast(data.message);

                // Only disable button for cosmetic items
                if (itemType === 'cosmetic' && buttonElement) {
                    buttonElement.disabled = true;
                    buttonElement.textContent = "Owned";
                    buttonElement.classList.remove('bg-purple-600', 'hover:bg-purple-700', 'cursor-pointer');
                    buttonElement.classList.add('bg-gray-400', 'cursor-not-allowed');
                }
                if (data.avatarFrame) {
                    const avatar = document.getElementById('avatarImage');
                    if (avatar) {
                        avatar.classList.remove('ring-purple-400', 'ring-yellow-400');
                        avatar.classList.add(`ring-${data.avatarFrame}-400`);
                    }
                    const pulseWrapper = document.getElementById('avatarPulseEffect');
                    if (pulseWrapper) {
                        if (data.avatarFrame === 'yellow' || data.avatarFrame === 'gold') {
                            pulseWrapper.innerHTML = `
                <div class="absolute inset-0 w-[calc(100%+8px)] h-[calc(100%+8px)] -m-1 rounded-full bg-gradient-to-r from-yellow-300 via-yellow-500 to-yellow-300 animate-pulse"></div>
            `;
                        } else {
                            pulseWrapper.innerHTML = ''; // Очистити, якщо інший фрейм
                        }
                    }
                }





                if (data.newHealth !== undefined) {
                    const bar   = document.querySelector('.health-bar');
                    const label = document.getElementById('healthPercent');

                    const safeHP = Math.min(data.newHealth, 100);

                    if (bar) {
                        bar.style.width = `${safeHP}%`;
                        bar.style.setProperty('--target-width', `${safeHP}%`);
                    }
                    if (label) {
                        label.textContent = `${safeHP}%`;
                    }
                }



                if (data.xpBoost !== undefined) {
                    showXPBoostIndicator(data.xpBoost);
                }
            } else {
                showToast(data.error || 'Purchase failed.', true);
            }
        })
        .catch(err => {
            console.error("❌ Purchase error:", err);
            showToast('An error occurred during purchase.', true);
        });
}



function showXPBoostIndicator(boostAmount) {
    const el = document.createElement('div');
    el.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 animate-float';
    el.textContent = `XP Boost Active: Next ${boostAmount} tasks give 2x XP!`;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 5000);
}


/** ==================== 🧍 Avatar Picker ==================== **/
let selectedAvatarUrl = null;

function openAvatarModal() {
    document.getElementById('avatarModal').classList.remove('hidden');
    selectedAvatarUrl = null;
    document.querySelectorAll('.avatar-option img').forEach(img => {
        img.classList.remove('border-purple-600');
        img.classList.add('border-transparent');
    });
}

function closeAvatarModal() {
    document.getElementById('avatarModal').classList.add('hidden');
}

function selectAvatar(url, element) {
    selectedAvatarUrl = url;
    document.querySelectorAll('.avatar-option img').forEach(img => {
        img.classList.remove('border-purple-600');
        img.classList.add('border-transparent');
    });
    element.querySelector('img').classList.add('border-purple-600');
}

function saveAvatar() {
    if (!selectedAvatarUrl) {
        showToast('Please select an avatar first', true);
        return;
    }

    fetch('/avatar/change', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ avatarUrl: selectedAvatarUrl })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.querySelector('.character-avatar').src = data.newAvatarUrl;
                showToast('Avatar updated successfully');
                closeAvatarModal();
            } else {
                showToast(data.error || 'Failed to update avatar', true);
            }
        })
        .catch(err => {
            showToast('An error occurred', true);
            console.error(err);
        });
}

// Inject avatar overlay
document.addEventListener('DOMContentLoaded', () => {
    const avatarImg = document.querySelector('.character-avatar');
    if (avatarImg) {
        const wrapper = document.createElement('div');
        wrapper.className = 'relative group cursor-pointer';
        avatarImg.parentNode.insertBefore(wrapper, avatarImg);
        wrapper.appendChild(avatarImg);

        const btn = document.createElement('div');
        btn.className = 'absolute inset-0 bg-black/50 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity rounded-full';
        btn.innerHTML = '<span class="text-sm font-semibold">Change Avatar</span>';
        btn.onclick = openAvatarModal;
        wrapper.appendChild(btn);
    }
});
// -------------------- PART 3: Health Check --------------------
function checkHealthLoss() {
    fetch('/health-check', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'fetch',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ csrf_token: CSRF })   // 🔑
    })
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;

            /* ---- смерть ---- */
            if (data.death) {
                showReturnByDeathScreen();      // ⬅ викликаємо екран смерті
                return;                         // не показуємо toast про втрату HP
            }

            /* ---- звичайна втрата HP ---- */
            if (data.noChange) return;

            const bar = document.querySelector('.health-bar');
            if (bar) bar.style.width = `${Math.min(data.newHealth,100)}%`;

            let msg = `You lost ${data.hpLoss} HP!`;
            if (data.overdueTasks)   msg += ` (${data.overdueTasks} overdue tasks)`;
            if (data.negativeHabits) msg += ` (${data.negativeHabits} negative habits)`;
            showToast(msg, true);
        })
        .catch(console.error);
}

document.addEventListener('DOMContentLoaded', () => {
    checkHealthLoss();
    setInterval(checkHealthLoss, 300000); // 5 minutes
});



// -------------------- PART 5: AI Suggestion --------------------
function getAISuggestion() {
    const prompt = document.getElementById('aiPrompt').value.trim();
    if (!prompt) return;

    const btn = document.getElementById('aiSuggestBtn');
    const originalText = btn.innerText;
    btn.disabled = true;
    btn.innerHTML = `<svg class="animate-spin -ml-1 mr-2 h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Thinking...`;

    fetch('/suggest', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'prompt=' + encodeURIComponent(prompt)
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('aiResponse').classList.remove('hidden');
                document.getElementById('aiSuggestion').innerHTML = data.suggestion.replace(/\n/g, '<br>');
            } else {
                throw new Error(data.error || 'Failed to get suggestion');
            }
        })
        .catch(error => {
            showToast(error.message, true);
            console.error('Error:', error);
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerText = originalText;
        });
}

function addSuggestedTasks() {
    const suggestion = document.getElementById('aiSuggestion').innerText;
    const tasks = suggestion.match(/\d\.\s+([^\n]+)/g) || [];

    if (tasks.length === 0) {
        showToast('No tasks found in the suggestion', true);
        return;
    }

    document.getElementById('taskModal')?.classList.remove('hidden');
    const titleInput = document.querySelector('#taskModal input[name="title"]');
    if (titleInput) {
        titleInput.value = tasks[0].replace(/^\d\.\s+/, '');
    }
}

document.getElementById('aiPrompt')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') getAISuggestion();
});

function showToast(message, isError = false) {
    const toast = document.createElement('div');
    toast.innerText = message;
    toast.className = 'fixed bottom-4 right-4 px-4 py-2 rounded-lg shadow-lg text-white z-50 ' +
        (isError ? 'bg-red-500' : 'bg-green-500');
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('opacity-0');
        setTimeout(() => toast.remove(), 500);
    }, 3000);
}
function trackHabit(taskId, delta) {
    fetch('/habit/track', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'fetch', 'Accept': 'application/json' },
        body: JSON.stringify({ task_id: taskId, delta: delta, csrf_token: CSRF })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const card = document.querySelector(`[data-id="${taskId}"]`);
                if (card) {
                    const plusEl = card.querySelector('.habit-plus');
                    const minusEl = card.querySelector('.habit-minus');

                    if (delta > 0 && plusEl) {
                        plusEl.textContent = '+' + (parseInt(plusEl.textContent.replace('+', '')) + 1);
                    }
                    if (delta < 0 && minusEl) {
                        minusEl.textContent = '−' + (parseInt(minusEl.textContent.replace('−', '')) + 1);
                    }
                }
                showToast(data.message);

                if (data.death) {
                    showReturnByDeathScreen(); // функція для екрану смерті
                }

                if (data.newLevel !== undefined) {
                    document.getElementById('characterLevel').textContent = `LVL ${data.newLevel}`;
                }

                if (data.newXP !== undefined) {
                    const xpBar = document.querySelector('.xp-bar');
                    const xpLabel = document.getElementById('xpPercent');

                    if (xpBar) {
                        xpBar.style.setProperty('--target-width', `${Math.min(data.newXP, 100)}%`);
                    }
                    if (xpLabel) {
                        xpLabel.textContent = `${Math.min(data.newXP, 100)}%`;
                    }
                }

                if (data.newGold !== undefined) {
                    document.getElementById('goldAmount').textContent = data.newGold;
                }
                if (data.newHealth !== undefined) {
                    const bar   = document.querySelector('.health-bar');
                    const label = document.getElementById('healthPercent');

                    const safeHP = Math.min(data.newHealth, 100);

                    if (bar) {
                        bar.style.width = `${safeHP}%`;
                        bar.style.setProperty('--target-width', `${safeHP}%`);
                    }
                    if (label) {
                        label.textContent = `${safeHP}%`;
                    }
                }




            } else {
                showToast(data.error || 'Error', true);
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Network error occurred', true);
        });
}

// -------------------- PART 7: ProfileModal --------------------
document.addEventListener('DOMContentLoaded', () => {
    const profileModal = document.getElementById('profileModal');
    const closeBtn = document.getElementById('closeProfileModal');
    const saveBtn = document.getElementById('saveProfileDescription');

    document.getElementById('openProfileModal')?.addEventListener('click', async () => {
        const res = await fetch('/api/profile-data');
        const data = await res.json();

        document.getElementById('profileAvatar').src = data.avatar;
        document.getElementById('profileUsername').textContent = data.username;
        document.getElementById('profileLevel').textContent = 'Level ' + data.level;
        document.getElementById('xpBar').style.width = `${Math.min(100, (data.xp / 100) * 100)}%`;
        document.getElementById('hpBar').style.width = `${Math.min(100, (data.health / 100) * 100)}%`;
        document.getElementById('profileGold').textContent = data.gold;
        document.getElementById('editPassword').value  = '';    // очищаємо
        document.getElementById('editAccountView').dataset.csrf = data.csrf; // пришлемо токен
        document.getElementById('profileDescription').value = data.description || '';
        document.getElementById('editUsername').value = data.username || '';
        document.getElementById('editEmail').value = data.email || '';

        profileModal.classList.remove('hidden');
    });

    closeBtn.addEventListener('click', () => {
        profileModal.classList.add('hidden');
    });

    saveBtn.addEventListener('click', async () => {
        const description = document.getElementById('profileDescription').value;
        await fetch('/api/update-description', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ description })
        });
        // 🎉 Підтвердження
        showToast('Description updated');
    });

    // ✅ перемикання між вкладками
    document.getElementById('openEditAccount')?.addEventListener('click', () => {
        document.getElementById('profileView')?.classList.add('hidden');
        document.getElementById('editAccountView')?.classList.remove('hidden');
    });

    document.getElementById('backToProfile')?.addEventListener('click', () => {
        document.getElementById('editAccountView')?.classList.add('hidden');
        document.getElementById('profileView')?.classList.remove('hidden');
    });
    document.getElementById('saveAccountSettings')?.addEventListener('click', async () => {
        const username = document.getElementById('editUsername').value.trim();
        const email = document.getElementById('editEmail').value.trim();
        const password = document.getElementById('editPassword').value;

        const body = {
            username,
            email,
            password,
            csrf_token: CSRF // глобальний токен, встановлюється в <meta name="csrf-token" content="...">
        };

        try {
            const res = await fetch('/api/update-account', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            });

            const data = await res.json();
            if (!data.success) throw new Error(data.error || 'Update failed');

            // 🎉 Підтвердження
            showToast('Account updated');

            // 👤 Оновлюємо ім’я на профілі
            document.getElementById('profileUsername').textContent = username;
            // також оновлюємо ім’я на дашборді (поза модалкою)
            document.querySelector('.character-username') &&
            (document.querySelector('.character-username').textContent = body.username);

            // 🧹 Очищення поля пароля
            document.getElementById('editPassword').value = '';

            // 🔁 Повертаємось до головного виду профілю
            document.getElementById('editAccountView').classList.add('hidden');
            document.getElementById('profileView').classList.remove('hidden');
        } catch (err) {
            console.error(err);
            showToast(err.message, true);
        }
    });


});
// -------------------- PART 8: EditModal --------------------
function openEditTaskModal(task) {
    const modal = document.getElementById('taskModal');
    const form  = modal.querySelector('form');

    document.getElementById('taskModalTitle').textContent = 'Edit Task';
    document.querySelector('button[type="submit"]').textContent = 'Update Task';

    /* 1 — switch the form to “update” mode */
    form.action = '/task/update';

    /* 2 — change modal heading + submit label */
    modal.querySelector('h3').textContent               = 'Edit Task';
    modal.querySelector('button[type="submit"]').textContent = 'Update Task';

    /* 3 — fill the fields with the task data */
    form.querySelector('input[name="task_id"]').value       = task.id;
    form.querySelector('input[name="title"]').value         = task.title;
    form.querySelector('textarea[name="notes"]').value      = task.notes ?? '';
    form.querySelector('input[name="deadline"]').value      = task.deadline ?? '';

    // task type radios
    form.querySelectorAll('input[name="type"]').forEach(r => {
        r.checked = r.value === task.type;
    });

    // priority radios
    form.querySelectorAll('input[name="priority"]').forEach(r => {
        r.checked = r.value === task.priority;
    });

    // categories (helper defined in your code)
    setSelectedCategories(task.categories ?? []);

    /* 4 — finally show the modal */
    modal.classList.remove('hidden');
}

/* make it accessible to inline onclick="" */
window.openEditTaskModal = openEditTaskModal;
// helper (see #3)
function setSelectedCategories(ids) {
    selectedCategoryIds = new Set(ids);
    updateSelectedCategoriesDisplay();
    updateCategoriesInput();
}

// intercept BOTH add & update
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('#taskModal form');
    if (!form) return;

    form.addEventListener('submit', async e => {
        e.preventDefault();

        const formData = new FormData(form);
        const url = form.action; // /task/add OR /task/update
        const isUpdate = url.endsWith('/update');

        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'fetch',
                'Accept': 'application/json'
            },
            body: formData
        });

        const j = await res.json();
        if (!j.success) {
            showToast(j.error, true);
            return;
        }

        closeTaskModal();
        form.reset();
        showToast(isUpdate ? 'Task updated' : 'Task created');

        // always reload after submit to avoid sync issues or duplication
        sessionStorage.setItem('skipLoader', 'true');
        setTimeout(() => location.reload(), 300);
    });
});

// -------------------- PART 9: Death Screen --------------------
function showReturnByDeathScreen() {
    const screen = document.getElementById('deathScreen');
    screen.classList.remove('hidden');
    document.getElementById('deathAudio').play();
}

function revivePlayer() {
    fetch('/health-check', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'fetch',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ revive: true, csrf_token: CSRF })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                showToast('Failed to revive', true);
            }
        });
}

document.addEventListener('DOMContentLoaded', () => {
    // вже є в тебе
    checkHealthLoss();
    setInterval(checkHealthLoss, 300000);

    // нова перевірка смерті одразу при вході
    fetch('/api/profile-data')
        .then(res => res.json())
        .then(data => {
            if (data.health === 0) {
                showReturnByDeathScreen();
            }
        })
        .catch(console.error);
});

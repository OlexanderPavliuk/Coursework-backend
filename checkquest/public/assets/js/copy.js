// -------------------- PART 6: Async --------------------
function completeTask(taskId) {
    fetch('/task/complete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ task_id: taskId })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message);

                // Update XP bar
                if (data.newXP !== undefined) {
                    const xpBar = document.querySelector('.xp-bar');
                    const xpLabel = document.getElementById('xpPercent');
                    if (xpBar) xpBar.style.setProperty('--target-width', `${data.newXP}%`);
                    if (xpLabel) xpLabel.textContent = `${data.newXP}%`;
                }

                // Update level
                if (data.newLevel !== undefined) {
                    const lvl = document.getElementById('characterLevel');
                    if (lvl) lvl.textContent = `LVL ${data.newLevel}`;
                }

                // Update gold
                if (data.newGold !== undefined) {
                    const goldEl = document.getElementById('goldAmount');
                    if (goldEl) goldEl.textContent = data.newGold;
                }

                // ✅ Update task block UI
                const wrapper = document.querySelector(`.status-wrapper[data-task-id="${taskId}"]`);
                if (wrapper) {
                    wrapper.innerHTML = `
                        Status:
                        <span class="status-text text-green-600 font-semibold flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Done
                        </span>
                    `;
                }

                // ✅ Remove Complete button
                const btn = document.getElementById(`complete-btn-${taskId}`);
                if (btn) btn.remove();

            } else {
                showToast(data.error || 'Error completing task', true);
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Network error', true);
        });
}

// -------------------- PART 4: Task Deletion --------------------
let taskToDelete = null;

function confirmDeleteTask(taskId) {
    taskToDelete = taskId;
    document.getElementById('deleteConfirmModal')?.classList.remove('hidden');
}

function closeDeleteConfirmModal() {
    document.getElementById('deleteConfirmModal')?.classList.add('hidden');
    taskToDelete = null;
}

document.getElementById('confirmDeleteBtn')?.addEventListener('click', () => {
    if (!taskToDelete) return;

    const taskElement = document.querySelector(`[data-id="${taskToDelete}"]`);
    if (!taskElement) return;

    taskElement.style.transition = 'all 0.3s ease-out';
    taskElement.style.opacity = '0';
    taskElement.style.transform = 'scale(0.95)';

    fetch('/task/delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `task_id=${taskToDelete}`
    })
        .then(async res => {
            console.log('[res status]', res.status);
            if (!res.ok) {
                const err = await res.text();
                throw new Error(err || 'Request failed');
            }
            return res.json();
        })
        .then(data => {
            console.log('[res data]', data);
            if (data.success) {
                setTimeout(() => {
                    taskElement.remove();
                    const container = taskElement.closest('.task-container');
                    if (container && !container.querySelector('.task-card')) {
                        container.innerHTML = '<div class="text-center text-gray-400 py-8">No tasks found.</div>';
                    }
                }, 300);
                showToast('Task deleted successfully');
            } else {
                throw new Error(data.error || 'Failed to delete task');
            }
        })
        .catch(error => {
            console.error('[DELETE ERROR]', error);
            taskElement.style.opacity = '1';
            taskElement.style.transform = 'scale(1)';
            showToast(error.message || 'An error occurred while deleting the task', true);
        })
        .finally(closeDeleteConfirmModal);
});


document.getElementById('deleteConfirmModal')?.addEventListener('click', (e) => {
    if (e.target === document.getElementById('deleteConfirmModal')) {
        closeDeleteConfirmModal();
    }
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !document.getElementById('deleteConfirmModal')?.classList.contains('hidden')) {
        closeDeleteConfirmModal();
    }
});
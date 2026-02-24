<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12">
        <h2>Time Tracking</h2>
        <p class="text-muted">Track time with live timers or log manual entries</p>
    </div>
</div>

<?php if (!empty($is_admin) && !empty($users)): ?>
<div class="row mb-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex flex-wrap align-items-center gap-3">
                <div>
                    <strong>Viewing tracker for:</strong> <?= esc($selected_user_name) ?>
                </div>
                <form id="userFilterForm" class="d-flex align-items-center gap-2" method="get" action="<?= base_url('time/tracker') ?>">
                    <label class="mb-0" for="userFilter">Select developer</label>
                    <select id="userFilter" name="user" class="form-select form-select-sm" onchange="document.getElementById('userFilterForm').submit()">
                        <option value="" <?= !empty($is_viewing_all) ? 'selected' : '' ?>>All Developers</option>
                        <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>" <?= (!empty($selected_user_id) && (int)$selected_user_id === (int)$user['id']) ? 'selected' : '' ?>>
                            <?= esc($user['username']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <div class="text-muted">
                    Admins can monitor any developer; switch to your own account to start or stop timers.
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-stopwatch"></i> Active Timer
            </div>
            <div class="card-body text-center">
                <div id="timerDisplay" class="display-3 mb-4 font-monospace">00:00:00</div>
                
                <div class="mb-3">
                    <select id="timerTask" class="form-select form-select-lg">
                        <option value="">Select a task...</option>
                        <?php if (!empty($my_tasks)): ?>
                        <?php foreach ($my_tasks as $task): ?>
                        <option value="<?= $task['id'] ?>" data-project="<?= $task['project_id'] ?>">
                            <?= esc($task['title']) ?> (<?= esc($task['project_name']) ?>)
                        </option>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <small class="form-text text-muted">Leave blank to track general work not tied to a task.</small>
                </div>
                
                <div class="mb-3">
                    <textarea id="timerDescription" class="form-control" rows="2" placeholder="What are you working on?"></textarea>
                </div>
                
                <div class="btn-group" role="group">
                    <button id="startTimer" class="btn btn-success btn-lg" onclick="startTimer()">
                        <i class="bi bi-play-fill"></i> Start
                    </button>
                    <button id="pauseTimer" class="btn btn-warning btn-lg" onclick="pauseTimer()" disabled>
                        <i class="bi bi-pause-fill"></i> Pause
                    </button>
                    <button id="stopTimer" class="btn btn-danger btn-lg" onclick="stopTimer()" disabled>
                        <i class="bi bi-stop-fill"></i> Stop & Save
                    </button>
                </div>
                
                <div id="timerStatus" class="mt-3 text-muted"></div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-plus-circle"></i> Manual Time Entry
            </div>
            <div class="card-body">
                <form action="<?= base_url('time/store') ?>" method="post">
                    <?= csrf_field() ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Task</label>
                        <select name="task_id" class="form-select" required>
                            <option value="">Select a task...</option>
                            <?php if (!empty($my_tasks)): ?>
                            <?php foreach ($my_tasks as $task): ?>
                            <option value="<?= $task['id'] ?>">
                                <?= esc($task['title']) ?> (<?= esc($task['project_name']) ?>)
                            </option>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Hours</label>
                        <input type="number" name="hours" class="form-control" step="0.05" min="0.05" max="24" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_billable" value="1" class="form-check-input" id="billable" checked>
                            <label class="form-check-label" for="billable">Billable</label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-save"></i> Save Entry
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history"></i> Recent Time Entries</span>
                <span class="badge bg-primary">Today: <?= number_format($today_hours ?? 0, 2) ?>h</span>
            </div>
            <div class="card-body">
                <?php if (empty($recent_entries)): ?>
                <p class="text-muted text-center py-4">No time entries yet</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>User</th>
                                <th>Task</th>
                                <th>Project</th>
                                <th>Hours</th>
                                <th>Description</th>
                                <th>Billable</th>
                                <?php if (!empty($is_admin)): ?>
                                <th class="text-end">Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_entries as $entry): ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($entry['date'])) ?></td>
                                <td><?= esc($entry['user_name'] ?? 'N/A') ?></td>
                                <td><?= esc($entry['task_title'] ?? 'N/A') ?></td>
                                <td><small class="text-muted"><?= esc($entry['project_name'] ?? 'N/A') ?></small></td>
                                <td><strong><?= number_format($entry['hours'], 2) ?>h</strong></td>
                                <td><small><?= esc($entry['description']) ?></small></td>
                                <td>
                                    <?php if ($entry['is_billable']): ?>
                                    <span class="badge bg-success">Yes</span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">No</span>
                                    <?php endif; ?>
                                </td>
                                <?php if (!empty($is_admin)): ?>
                                <td class="text-end">
                                    <button type="button"
                                        class="btn btn-sm btn-outline-primary edit-entry-btn"
                                        data-entry-id="<?= esc($entry['id'], 'attr') ?>"
                                        data-task-id="<?= esc($entry['task_id'] ?? '', 'attr') ?>"
                                        data-date="<?= esc($entry['date'], 'attr') ?>"
                                        data-hours="<?= esc(number_format($entry['hours'], 2, '.', ''), 'attr') ?>"
                                        data-description="<?= esc($entry['description'] ?? '', 'attr') ?>"
                                        data-billable="<?= (int)($entry['is_billable'] ?? 0) ?>">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </button>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($is_admin)): ?>
<div class="modal fade" id="editEntryModal" tabindex="-1" aria-labelledby="editEntryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEntryModalLabel">Edit Time Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editEntryForm">
                    <input type="hidden" name="entry_id" id="editEntryId">

                    <div class="mb-3">
                        <label class="form-label">Task</label>
                        <select name="task_id" id="editTaskId" class="form-select">
                            <option value="">No task / general work</option>
                            <?php if (!empty($task_options)): ?>
                            <?php foreach ($task_options as $task): ?>
                            <option value="<?= esc($task['id'], 'attr') ?>">
                                <?= esc($task['title']) ?><?= !empty($task['project_name']) ? ' (' . esc($task['project_name']) . ')' : '' ?>
                            </option>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="date" id="editDate" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Hours</label>
                            <input type="number" step="0.05" min="0.05" max="24" name="hours" id="editHours" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="editDescription" class="form-control" rows="3" required></textarea>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_billable" id="editBillable" value="1">
                        <label class="form-check-label" for="editBillable">
                            Billable
                        </label>
                    </div>
                </form>
                <div class="alert alert-danger d-none" id="editEntryError"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveEntryChanges">
                    <i class="bi bi-save"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
<?php if (!empty($is_admin)): ?>
const editEntryModalEl = document.getElementById('editEntryModal');
const bootstrapLib = window.bootstrap || null;
const editEntryModal = (editEntryModalEl && bootstrapLib) ? new bootstrapLib.Modal(editEntryModalEl) : null;
const editEntryForm = document.getElementById('editEntryForm');
const editEntryError = document.getElementById('editEntryError');

document.querySelectorAll('.edit-entry-btn').forEach((button) => {
    button.addEventListener('click', () => {
        const entryId = button.getAttribute('data-entry-id');
        const taskId = button.getAttribute('data-task-id');
        const date = button.getAttribute('data-date');
        const hours = button.getAttribute('data-hours');
        const description = button.getAttribute('data-description') || '';
        const billable = button.getAttribute('data-billable') === '1';

        editEntryForm.reset();
        editEntryError.classList.add('d-none');
        document.getElementById('editEntryId').value = entryId;
        document.getElementById('editTaskId').value = taskId || '';
        document.getElementById('editDate').value = date;
        document.getElementById('editHours').value = hours;
        document.getElementById('editDescription').value = description;
        document.getElementById('editBillable').checked = billable;

        if (editEntryModal) {
            editEntryModal.show();
        } else {
            alert('Unable to open edit dialog because Bootstrap JS is unavailable. Please reload the page.');
        }
    });
});

document.getElementById('saveEntryChanges')?.addEventListener('click', async () => {
    if (!editEntryForm.reportValidity()) {
        return;
    }

    const entryId = document.getElementById('editEntryId').value;
    const payload = {
        task_id: editEntryForm.task_id.value || null,
        date: editEntryForm.date.value,
        hours: parseFloat(editEntryForm.hours.value),
        description: editEntryForm.description.value,
        is_billable: editEntryForm.is_billable.checked ? 1 : 0,
    };

    try {
        const response = await fetch('<?= base_url('api/time-entries') ?>/' + entryId, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || 'Failed to update entry');
        }

        editEntryModal?.hide();
        window.location.reload();
    } catch (error) {
        editEntryError.textContent = error.message;
        editEntryError.classList.remove('d-none');
    }
});
<?php endif; ?>

let timerInterval = null;
let timerSeconds = 0;
let timerRunning = false;
let timerStartTime = null;

function updateTimerDisplay() {
    const hours = Math.floor(timerSeconds / 3600);
    const minutes = Math.floor((timerSeconds % 3600) / 60);
    const seconds = timerSeconds % 60;
    
    document.getElementById('timerDisplay').textContent = 
        String(hours).padStart(2, '0') + ':' + 
        String(minutes).padStart(2, '0') + ':' + 
        String(seconds).padStart(2, '0');
}

function startTimer() {
    const taskSelect = document.getElementById('timerTask');
    document.getElementById('startTimer').disabled = true;
    document.getElementById('pauseTimer').disabled = false;
    document.getElementById('stopTimer').disabled = false;
    
    timerRunning = true;
    timerStartTime = Date.now() - (timerSeconds * 1000);
    
    timerInterval = setInterval(() => {
        timerSeconds = Math.floor((Date.now() - timerStartTime) / 1000);
        updateTimerDisplay();
    }, 1000);
    
    document.getElementById('timerTask').disabled = true;
    document.getElementById('timerStatus').innerHTML = '<span class="text-success"><i class="bi bi-circle-fill"></i> Timer running...</span>';
}

function pauseTimer() {
    if (timerInterval) {
        clearInterval(timerInterval);
        timerInterval = null;
        timerRunning = false;
        
        document.getElementById('startTimer').disabled = false;
        document.getElementById('startTimer').innerHTML = '<i class="bi bi-play-fill"></i> Resume';
        document.getElementById('pauseTimer').disabled = true;
        document.getElementById('timerStatus').innerHTML = '<span class="text-warning"><i class="bi bi-pause-circle-fill"></i> Timer paused</span>';
    }
}

async function stopTimer() {
    if (timerInterval) {
        clearInterval(timerInterval);
        timerInterval = null;
    }
    
    const selectedTask = document.getElementById('timerTask').value;
    const taskId = selectedTask ? selectedTask : null;
    const description = document.getElementById('timerDescription').value || 'Timed work session';
    const hours = (timerSeconds / 3600).toFixed(2);
    
    if (hours < 0.01) {
        alert('Timer must run for at least 1 minute');
        resetTimer();
        return;
    }
    
    try {
        const response = await fetch('<?= base_url('api/time-entries') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                task_id: taskId === "" ? null : taskId,
                date: new Date().toISOString().split('T')[0],
                hours: parseFloat(hours),
                description: description,
                is_billable: 1
            })
        });
        
        if (response.ok) {
            alert(`Time entry saved: ${hours} hours`);
            document.getElementById('timerStatus').innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill"></i> Saved</span>';
        } else {
            const data = await response.json();
            alert('Failed to save: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
    
    resetTimer();
}

function resetTimer() {
    timerSeconds = 0;
    timerRunning = false;
    timerStartTime = null;
    updateTimerDisplay();
    
    document.getElementById('startTimer').disabled = false;
    document.getElementById('startTimer').innerHTML = '<i class="bi bi-play-fill"></i> Start';
    document.getElementById('pauseTimer').disabled = true;
    document.getElementById('stopTimer').disabled = true;
    document.getElementById('timerTask').disabled = false;
    document.getElementById('timerTask').value = '';
    document.getElementById('timerDescription').value = '';
    document.getElementById('timerStatus').textContent = '';
}

window.addEventListener('beforeunload', (e) => {
    if (timerRunning) {
        e.preventDefault();
        e.returnValue = 'Timer is running. Are you sure you want to leave?';
    }
});
</script>
<?= $this->endSection() ?>

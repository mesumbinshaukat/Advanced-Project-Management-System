<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Task</h5>
            </div>
            <div class="card-body">
                <form id="taskForm">
                    <div class="mb-3">
                        <label for="title" class="form-label">Task Title *</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?= esc($task['title']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"><?= esc($task['description'] ?? '') ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="project_id" class="form-label">Project *</label>
                            <select class="form-select" id="project_id" name="project_id" required>
                                <?php foreach ($projects as $project): ?>
                                <option value="<?= $project['id'] ?>" <?= $project['id'] == $task['project_id'] ? 'selected' : '' ?>>
                                    <?= esc($project['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Assign To (Multiple Developers)</label>
                            <div id="selectedDevelopers" class="mb-2 p-2 bg-light rounded" style="min-height: 40px; display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
                                <span class="text-muted" id="noDevelopersText">No developers selected</span>
                            </div>
                            
                            <div class="border rounded p-3" style="max-height: 250px; overflow-y: auto;">
                                <?php foreach ($users as $user): ?>
                                <div class="form-check mb-2">
                                    <input 
                                        class="form-check-input developer-checkbox" 
                                        type="checkbox" 
                                        id="developer_<?= $user['id'] ?>" 
                                        name="assigned_to[]" 
                                        value="<?= $user['id'] ?>"
                                        data-username="<?= esc($user['username']) ?>"
                                        <?= in_array($user['id'], $assigned_user_ids ?? []) ? 'checked' : '' ?>
                                    >
                                    <label class="form-check-label" for="developer_<?= $user['id'] ?>">
                                        <?= esc($user['username']) ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <small class="form-text text-muted d-block mt-2">Select one or more team members to assign this task.</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="backlog" <?= $task['status'] == 'backlog' ? 'selected' : '' ?>>Backlog</option>
                                <option value="todo" <?= $task['status'] == 'todo' ? 'selected' : '' ?>>Todo</option>
                                <option value="in_progress" <?= $task['status'] == 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="review" <?= $task['status'] == 'review' ? 'selected' : '' ?>>Review</option>
                                <option value="done" <?= $task['status'] == 'done' ? 'selected' : '' ?>>Done</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-select" id="priority" name="priority">
                                <option value="low" <?= $task['priority'] == 'low' ? 'selected' : '' ?>>Low</option>
                                <option value="medium" <?= $task['priority'] == 'medium' ? 'selected' : '' ?>>Medium</option>
                                <option value="high" <?= $task['priority'] == 'high' ? 'selected' : '' ?>>High</option>
                                <option value="urgent" <?= $task['priority'] == 'urgent' ? 'selected' : '' ?>>Urgent</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="estimated_hours" class="form-label">Estimated Hours</label>
                            <input type="number" class="form-control" id="estimated_hours" name="estimated_hours" step="0.5" value="<?= $task['estimated_hours'] ?? '' ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $task['start_date'] ?? '' ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="due_date" class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="due_date" name="due_date" value="<?= $task['deadline'] ? date('Y-m-d', strtotime($task['deadline'])) : '' ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_blocked" name="is_blocked" value="1" <?= $task['is_blocked'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_blocked">
                                Task is Blocked
                            </label>
                        </div>
                    </div>

                    <div class="mb-3" id="blockerReasonDiv" style="display: <?= $task['is_blocked'] ? 'block' : 'none' ?>;">
                        <label for="blocker_reason" class="form-label">Blocker Reason</label>
                        <textarea class="form-control" id="blocker_reason" name="blocker_reason" rows="2"><?= esc($task['blocker_reason'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Update Task
                        </button>
                        <a href="<?= base_url('tasks/kanban/' . $task['project_id']) ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Handle developer checkbox selection and display
function updateSelectedDevelopers() {
    const checkboxes = document.querySelectorAll('.developer-checkbox:checked');
    const selectedDevelopersDiv = document.getElementById('selectedDevelopers');
    
    if (checkboxes.length === 0) {
        selectedDevelopersDiv.innerHTML = '<span class="text-muted" id="noDevelopersText">No developers selected</span>';
    } else {
        let html = '';
        checkboxes.forEach(checkbox => {
            const username = checkbox.dataset.username;
            const userId = checkbox.value;
            html += `<span class="badge bg-primary d-flex align-items-center gap-2">
                ${username}
                <button type="button" class="btn-close btn-close-white" data-user-id="${userId}" style="font-size: 0.7rem;"></button>
            </span>`;
        });
        selectedDevelopersDiv.innerHTML = html;
        
        // Add click handlers to remove badges
        selectedDevelopersDiv.querySelectorAll('.btn-close').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const userId = btn.dataset.userId;
                document.getElementById(`developer_${userId}`).checked = false;
                updateSelectedDevelopers();
            });
        });
    }
}

// Add event listeners to all checkboxes
document.querySelectorAll('.developer-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateSelectedDevelopers);
});

// Initialize selected developers display
updateSelectedDevelopers();

// Show/hide blocker reason field
const isBlockedCheckbox = document.getElementById('is_blocked');
if (isBlockedCheckbox) {
    isBlockedCheckbox.addEventListener('change', function() {
        const blockerReasonDiv = document.getElementById('blockerReasonDiv');
        if (blockerReasonDiv) {
            blockerReasonDiv.style.display = this.checked ? 'block' : 'none';
        }
    });
}

document.getElementById('taskForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    // Handle multiple assignees from checkboxes
    const checkedBoxes = document.querySelectorAll('.developer-checkbox:checked');
    const assignedUserIds = Array.from(checkedBoxes).map(checkbox => checkbox.value);
    data.assigned_to = assignedUserIds.length > 0 ? assignedUserIds : [];
    
    // Handle checkbox
    data.is_blocked = formData.get('is_blocked') ? 1 : 0;
    
    // Convert empty strings to null for optional fields
    ['description', 'estimated_hours', 'start_date', 'due_date', 'blocker_reason'].forEach(field => {
        if (data[field] === '') data[field] = null;
    });
    
    try {
        const response = await fetch('<?= base_url('api/tasks/' . $task['id']) ?>', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (response.ok) {
            window.location.href = '<?= base_url('tasks/kanban/' . $task['project_id']) ?>';
        } else {
            alert('Error: ' + (result.message || 'Failed to update task'));
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
});
</script>
<?= $this->endSection() ?>

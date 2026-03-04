<?= $this->extend('layouts/main') ?>

<?php
$user_skills = $user_skills ?? [];
$skill_options = $skill_options ?? [];
$is_admin = $is_admin ?? false;
?>

<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Create New Task</h5>
            </div>
            <div class="card-body">
                <form id="taskForm">
                    <div class="mb-3">
                        <label for="project_id" class="form-label">Project *</label>
                        <select class="form-select" id="project_id" name="project_id" required>
                            <option value="">Select Project</option>
                            <?php foreach ($projects as $project): ?>
                            <option value="<?= $project['id'] ?>" 
                                data-priority="<?= esc($project['priority'] ?? 'medium') ?>"
                                data-deadline="<?= esc($project['deadline'] ?? '') ?>"
                                <?= ($selected_project_id == $project['id']) ? 'selected' : '' ?>>
                                <?= esc($project['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label">Task Title *</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                    </div>

                    <?php if (!empty($is_admin) && !empty($skill_options)): ?>
                    <div class="mb-3">
                        <label for="skill_filter" class="form-label">Filter Developers by Skill</label>
                        <select class="form-select" id="skill_filter">
                            <option value="">All Skills</option>
                            <?php foreach ($skill_options as $skill): ?>
                            <option value="<?= esc($skill) ?>"><?= esc(ucfirst($skill)) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">Choose a skill to narrow the assignee list. Only admins see this filter.</small>
                    </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Assign To (Multiple Developers)</label>
                        <div id="selectedDevelopers" class="mb-2 p-2 bg-light rounded" style="min-height: 40px; display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
                            <span class="text-muted" id="noDevelopersText">No developers selected</span>
                        </div>
                        
                        <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                            <?php foreach ($users as $user): ?>
                            <?php 
                                $skillsList = $user_skills[$user['id']] ?? [];
                                $skillsLabel = empty($skillsList) ? '' : ' • ' . implode(', ', $skillsList);
                                $skillKey = implode('|', array_map('strtolower', $skillsList));
                            ?>
                            <div class="form-check mb-2">
                                <input 
                                    class="form-check-input developer-checkbox" 
                                    type="checkbox" 
                                    id="developer_<?= $user['id'] ?>" 
                                    name="assigned_to[]" 
                                    value="<?= $user['id'] ?>"
                                    data-username="<?= esc($user['username']) ?>"
                                    data-skills="<?= esc($skillKey, 'attr') ?>"
                                >
                                <label class="form-check-label" for="developer_<?= $user['id'] ?>">
                                    <strong><?= esc($user['username']) ?></strong>
                                    <?php if (!empty($skillsLabel)): ?>
                                        <span class="text-muted small"><?= esc($skillsLabel) ?></span>
                                    <?php endif; ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <small class="form-text text-muted d-block mt-2">Select one or more team members. Usernames show relevant skills to help pick the right teammates.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="backlog" selected>Backlog</option>
                                <option value="todo">To Do</option>
                                <option value="in_progress">In Progress</option>
                                <option value="review">Review</option>
                                <option value="done">Done</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-select" id="priority" name="priority">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="deadline" class="form-label">Deadline</label>
                            <input type="date" class="form-control" id="deadline" name="deadline">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="estimated_hours" class="form-label">Estimated Hours</label>
                        <input type="number" step="0.5" class="form-control" id="estimated_hours" name="estimated_hours">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Create Task
                        </button>
                        <a href="<?= base_url('tasks') ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const projectSelect = document.getElementById('project_id');
const prioritySelect = document.getElementById('priority');
const deadlineSelect = document.getElementById('deadline');
const assignmentSelect = document.getElementById('assigned_to');
const skillFilterSelect = document.getElementById('skill_filter');

function setProjectDefaults() {
    if (!projectSelect) return;
    const selected = projectSelect.options[projectSelect.selectedIndex];
    if (!selected) return;
    
    // Set priority from project
    const projectPriority = selected.getAttribute('data-priority');
    if (projectPriority && prioritySelect) {
        prioritySelect.value = projectPriority;
    }
    
    // Set deadline from project
    const projectDeadline = selected.getAttribute('data-deadline');
    if (projectDeadline && deadlineSelect) {
        deadlineSelect.value = projectDeadline;
    }
}

// Apply default values on load if a project is pre-selected
setProjectDefaults();

projectSelect.addEventListener('change', () => {
    setProjectDefaults();
});

function filterUsersBySkill() {
    if (!skillFilterSelect || !assignmentSelect) return;
    const selectedSkill = skillFilterSelect.value.toLowerCase();
    const options = assignmentSelect.querySelectorAll('option');

    options.forEach(option => {
        if (!option.value) {
            option.hidden = false;
            return;
        }

        const optionSkills = (option.dataset.skills || '');
        const matches = !selectedSkill || optionSkills.split('|').includes(selectedSkill);
        option.hidden = !matches;

        if (!matches && option.selected) {
            assignmentSelect.value = '';
        }
    });
}

// Handle developer checkbox selection and display
function updateSelectedDevelopers() {
    const checkboxes = document.querySelectorAll('.developer-checkbox:checked');
    const selectedDevelopersDiv = document.getElementById('selectedDevelopers');
    const noDevelopersText = document.getElementById('noDevelopersText');
    
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

if (skillFilterSelect) {
    skillFilterSelect.addEventListener('change', filterUsersBySkill);
    filterUsersBySkill();
}

document.getElementById('taskForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    // Handle multiple assignees from checkboxes
    const checkedBoxes = document.querySelectorAll('.developer-checkbox:checked');
    const assignedUserIds = Array.from(checkedBoxes).map(checkbox => checkbox.value);
    data.assigned_to = assignedUserIds.length > 0 ? assignedUserIds : [];
    
    // Convert empty strings to null for optional fields
    ['description', 'start_date', 'deadline', 'estimated_hours'].forEach(field => {
        if (data[field] === '') data[field] = null;
    });
    
    try {
        const response = await fetch('<?= base_url('api/tasks') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (response.ok) {
            window.location.href = '<?= base_url('tasks') ?>';
        } else {
            alert('Error: ' + (result.message || 'Failed to create task'));
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
});
</script>
<?= $this->endSection() ?>

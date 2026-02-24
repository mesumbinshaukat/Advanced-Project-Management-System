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
                        <label for="assigned_to" class="form-label">Assign To</label>
                        <select class="form-select" id="assigned_to" name="assigned_to">
                            <option value="">Unassigned</option>
                            <?php foreach ($users as $user): ?>
                            <?php 
                                $skillsList = $user_skills[$user['id']] ?? [];
                                $skillsLabel = empty($skillsList) ? '' : ' • ' . implode(', ', $skillsList);
                                $skillKey = implode('|', array_map('strtolower', $skillsList));
                            ?>
                            <option value="<?= $user['id'] ?>" data-skills="<?= esc($skillKey, 'attr') ?>">
                                <?= esc($user['username'] . $skillsLabel) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">Usernames show relevant skills to help pick the right teammate.</small>
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
const assignmentSelect = document.getElementById('assigned_to');
const skillFilterSelect = document.getElementById('skill_filter');

function setPriorityFromProject() {
    if (!projectSelect) return;
    const selected = projectSelect.options[projectSelect.selectedIndex];
    if (!selected) return;
    const projectPriority = selected.getAttribute('data-priority');
    if (projectPriority) {
        prioritySelect.value = projectPriority;
    }
}

// Apply default priority on load if a project is pre-selected
setPriorityFromProject();

projectSelect.addEventListener('change', () => {
    setPriorityFromProject();
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

if (skillFilterSelect) {
    skillFilterSelect.addEventListener('change', filterUsersBySkill);
    filterUsersBySkill();
}

document.getElementById('taskForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    // Convert empty strings to null for optional fields
    ['description', 'assigned_to', 'start_date', 'deadline', 'estimated_hours'].forEach(field => {
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

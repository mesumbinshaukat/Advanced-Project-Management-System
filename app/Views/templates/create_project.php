<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2>Create Project Template</h2>
                <p class="text-muted">Define a reusable project template with tasks</p>
            </div>
            <a href="<?= base_url('templates') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Templates
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form id="templateForm">
                    <?= csrf_field() ?>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Template Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="default_priority" class="form-label">Default Priority</label>
                            <select class="form-select" id="default_priority" name="default_priority">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="default_budget" class="form-label">Default Budget</label>
                            <input type="number" class="form-control" id="default_budget" name="default_budget" step="0.01" min="0">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Task Templates</label>
                        <div id="taskTemplates">
                            <div class="task-template-item border rounded p-3 mb-2">
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <input type="text" class="form-control task-title" placeholder="Task title" required>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <select class="form-select task-priority">
                                            <option value="low">Low</option>
                                            <option value="medium" selected>Medium</option>
                                            <option value="high">High</option>
                                            <option value="urgent">Urgent</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <input type="number" class="form-control task-hours" placeholder="Est. hours" min="0" step="0.5">
                                    </div>
                                    <div class="col-12">
                                        <textarea class="form-control task-description" placeholder="Task description" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addTask">
                            <i class="bi bi-plus-lg"></i> Add Task
                        </button>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                        <label class="form-check-label" for="is_active">
                            Active Template
                        </label>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Template
                        </button>
                        <a href="<?= base_url('templates') ?>" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('addTask').addEventListener('click', () => {
    const template = document.querySelector('.task-template-item').cloneNode(true);
    template.querySelectorAll('input, textarea').forEach(el => el.value = '');
    template.querySelector('.task-priority').value = 'medium';
    document.getElementById('taskTemplates').appendChild(template);
});

document.getElementById('templateForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    // Collect task templates
    const taskTemplates = [];
    document.querySelectorAll('.task-template-item').forEach(item => {
        const title = item.querySelector('.task-title').value.trim();
        if (title) {
            taskTemplates.push({
                title: title,
                description: item.querySelector('.task-description').value.trim() || null,
                priority: item.querySelector('.task-priority').value,
                estimated_hours: item.querySelector('.task-hours').value || null
            });
        }
    });
    
    data.task_templates_json = JSON.stringify(taskTemplates);
    data.is_active = document.getElementById('is_active').checked ? 1 : 0;
    
    try {
        const response = await fetch('<?= base_url('templates/store-project') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(data)
        });
        
        if (response.redirected) {
            window.location.href = response.url;
        } else {
            const result = await response.json();
            if (result.errors) {
                alert('Validation errors: ' + JSON.stringify(result.errors));
            }
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while saving the template');
    }
});
</script>

<?= $this->endSection() ?>

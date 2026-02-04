<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2>Create Task Template</h2>
                <p class="text-muted">Define a reusable task template with checklist</p>
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
                <form id="taskTemplateForm">
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
                            <label for="estimated_hours" class="form-label">Estimated Hours</label>
                            <input type="number" class="form-control" id="estimated_hours" name="estimated_hours" step="0.5" min="0">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Checklist Items</label>
                        <div id="checklistItems">
                            <div class="input-group mb-2 checklist-item">
                                <input type="text" class="form-control checklist-text" placeholder="Checklist item">
                                <button type="button" class="btn btn-outline-danger remove-item" style="display: none;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addChecklistItem">
                            <i class="bi bi-plus-lg"></i> Add Checklist Item
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
document.getElementById('addChecklistItem').addEventListener('click', () => {
    const template = document.querySelector('.checklist-item').cloneNode(true);
    template.querySelector('.checklist-text').value = '';
    template.querySelector('.remove-item').style.display = 'block';
    document.getElementById('checklistItems').appendChild(template);
    
    updateRemoveButtons();
});

document.getElementById('checklistItems').addEventListener('click', (e) => {
    if (e.target.closest('.remove-item')) {
        e.target.closest('.checklist-item').remove();
        updateRemoveButtons();
    }
});

function updateRemoveButtons() {
    const items = document.querySelectorAll('.checklist-item');
    items.forEach((item, index) => {
        const btn = item.querySelector('.remove-item');
        btn.style.display = items.length > 1 ? 'block' : 'none';
    });
}

document.getElementById('taskTemplateForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    // Collect checklist items
    const checklistItems = [];
    document.querySelectorAll('.checklist-text').forEach(input => {
        const text = input.value.trim();
        if (text) {
            checklistItems.push({ text: text, completed: false });
        }
    });
    
    data.checklist_json = JSON.stringify(checklistItems);
    data.is_active = document.getElementById('is_active').checked ? 1 : 0;
    
    // Convert empty strings to null
    ['description', 'estimated_hours'].forEach(field => {
        if (data[field] === '') data[field] = null;
    });
    
    try {
        const response = await fetch('<?= base_url('templates/store-task') ?>', {
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

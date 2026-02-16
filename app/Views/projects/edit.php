<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Project</h5>
            </div>
            <div class="card-body">
                <form id="projectForm">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="name" class="form-label">Project Name *</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?= esc($project['name']) ?>" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="client_id" class="form-label">Client <span class="text-muted fw-normal">(optional)</span></label>
                            <select class="form-select" id="client_id" name="client_id">
                                <option value="">No client assigned</option>
                                <?php foreach ($clients as $client): ?>
                                <option value="<?= $client['id'] ?>" <?= $client['id'] == $project['client_id'] ? 'selected' : '' ?>>
                                    <?= esc($client['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= esc($project['description'] ?? '') ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active" <?= $project['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="on_hold" <?= $project['status'] == 'on_hold' ? 'selected' : '' ?>>On Hold</option>
                                <option value="completed" <?= $project['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="archived" <?= $project['status'] == 'archived' ? 'selected' : '' ?>>Archived</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-select" id="priority" name="priority">
                                <option value="low" <?= $project['priority'] == 'low' ? 'selected' : '' ?>>Low</option>
                                <option value="medium" <?= $project['priority'] == 'medium' ? 'selected' : '' ?>>Medium</option>
                                <option value="high" <?= $project['priority'] == 'high' ? 'selected' : '' ?>>High</option>
                                <option value="urgent" <?= $project['priority'] == 'urgent' ? 'selected' : '' ?>>Urgent</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $project['start_date'] ?? '' ?>">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="deadline" class="form-label">Deadline</label>
                            <input type="date" class="form-control" id="deadline" name="deadline" value="<?= $project['deadline'] ?? '' ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="estimated_hours" class="form-label">Estimated Hours</label>
                        <input type="number" class="form-control" id="estimated_hours" name="estimated_hours" step="0.5" value="<?= $project['estimated_hours'] ?? '' ?>">
                    </div>

                    <div class="mb-3">
                        <label for="documentation_url" class="form-label">Documentation URL</label>
                        <input type="url" class="form-control" id="documentation_url" name="documentation_url" value="<?= esc($project['documentation_url'] ?? '') ?>">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Update Project
                        </button>
                        <a href="<?= base_url('projects/view/' . $project['id']) ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.getElementById('projectForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    // Convert empty strings to null for optional fields
    ['client_id', 'description', 'start_date', 'deadline', 'estimated_hours', 'documentation_url'].forEach(field => {
        if (data[field] === '') data[field] = null;
    });
    
    try {
        const response = await fetch('<?= base_url('api/projects/' . $project['id']) ?>', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (response.ok) {
            window.location.href = '<?= base_url('projects/view/' . $project['id']) ?>';
        } else {
            alert('Error: ' + (result.message || 'Failed to update project'));
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
});
</script>
<?= $this->endSection() ?>

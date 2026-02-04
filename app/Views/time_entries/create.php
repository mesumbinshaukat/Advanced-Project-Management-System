<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Log Time</h5>
            </div>
            <div class="card-body">
                <form id="timeForm">
                    <div class="mb-3">
                        <label for="task_id" class="form-label">Task *</label>
                        <select class="form-select" id="task_id" name="task_id" required>
                            <option value="">Select Task</option>
                            <?php foreach ($tasks as $task): ?>
                            <option value="<?= $task['id'] ?>"><?= esc($task['title']) ?> (<?= esc($task['project_name'] ?? '') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="date" class="form-label">Date *</label>
                            <input type="date" class="form-control" id="date" name="date" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="hours" class="form-label">Hours *</label>
                            <input type="number" step="0.25" class="form-control" id="hours" name="hours" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_billable" name="is_billable" value="1" checked>
                            <label class="form-check-label" for="is_billable">
                                Billable
                            </label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Log Time
                        </button>
                        <a href="<?= base_url('time') ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.getElementById('timeForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    data.is_billable = formData.get('is_billable') ? 1 : 0;
    
    try {
        const response = await fetch('<?= base_url('api/time') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (response.ok) {
            window.location.href = '<?= base_url('time') ?>';
        } else {
            alert('Error: ' + (result.message || 'Failed to log time'));
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
});
</script>
<?= $this->endSection() ?>

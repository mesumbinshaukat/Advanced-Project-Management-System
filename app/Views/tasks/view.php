<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><?= esc($task['title']) ?></h5>
                <div>
                    <?php if ($isAdmin): ?>
                        <a href="<?= base_url('tasks/edit/' . $task['id']) ?>" class="btn btn-sm btn-primary">Edit</a>
                    <?php else: ?>
                        <?php if (in_array($task['status'], ['todo', 'in_progress'])): ?>
                            <button class="btn btn-sm btn-success" onclick="submitTaskForReview(<?= $task['id'] ?>)">
                                <i class="bi bi-check-circle"></i> Request Review
                            </button>
                        <?php elseif ($task['status'] === 'submitted_for_review'): ?>
                            <span class="badge bg-info">Review Requested</span>
                        <?php elseif ($task['status'] === 'needs_revision'): ?>
                            <span class="badge bg-warning">Needs Revision</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    <a href="<?= base_url('tasks') ?>" class="btn btn-sm btn-secondary">Back</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted">Project</h6>
                        <p><a href="<?= base_url('projects/view/' . $project['id']) ?>"><?= esc($project['name']) ?></a></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Status</h6>
                        <p>
                            <span class="badge bg-<?= $task['status'] === 'done' ? 'success' : ($task['status'] === 'in_progress' ? 'info' : 'warning') ?>">
                                <?= ucfirst(str_replace('_', ' ', $task['status'])) ?>
                            </span>
                        </p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted">Priority</h6>
                        <p>
                            <span class="badge bg-<?= $task['priority'] === 'high' ? 'danger' : ($task['priority'] === 'medium' ? 'warning' : 'info') ?>">
                                <?= ucfirst($task['priority']) ?>
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Assigned To</h6>
                        <p>
                            <?php if (!empty($assigned_developers)): ?>
                                <?php foreach ($assigned_developers as $developer): ?>
                                    <span class="badge bg-primary"><?= esc($developer['username']) ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-muted">Unassigned</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted">Deadline</h6>
                        <p><?= $task['deadline'] ? date('M d, Y', strtotime($task['deadline'])) : 'No deadline' ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Estimated Hours</h6>
                        <p><?= $task['estimated_hours'] ?? 'Not set' ?></p>
                    </div>
                </div>

                <?php if ($task['is_blocked']): ?>
                <div class="alert alert-danger" role="alert">
                    <strong>Blocked:</strong> <?= esc($task['blocker_reason'] ?? 'No reason provided') ?>
                </div>
                <?php endif; ?>

                <div class="mb-4">
                    <h6 class="text-muted">Description</h6>
                    <div class="card-text">
                        <?= $task['description'] ? nl2br(esc($task['description'])) : '<em>No description</em>' ?>
                    </div>
                </div>

                <?php if ($task['tags']): ?>
                <div class="mb-4">
                    <h6 class="text-muted">Tags</h6>
                    <p>
                        <?php foreach (explode(',', $task['tags']) as $tag): ?>
                        <span class="badge bg-secondary"><?= esc(trim($tag)) ?></span>
                        <?php endforeach; ?>
                    </p>
                </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">Created</h6>
                        <p><?= date('M d, Y H:i', strtotime($task['created_at'])) ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Updated</h6>
                        <p><?= date('M d, Y H:i', strtotime($task['updated_at'])) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Task Details</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">Task ID</small>
                    <p class="mb-0">#<?= $task['id'] ?></p>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Status</small>
                    <p class="mb-0"><?= ucfirst(str_replace('_', ' ', $task['status'])) ?></p>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Priority</small>
                    <p class="mb-0"><?= ucfirst($task['priority']) ?></p>
                </div>
                <?php if ($task['completed_at']): ?>
                <div class="mb-3">
                    <small class="text-muted">Completed</small>
                    <p class="mb-0"><?= date('M d, Y H:i', strtotime($task['completed_at'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Submit task for review function
async function submitTaskForReview(taskId) {
    if (!confirm('Are you sure you want to submit this task for review?')) {
        return;
    }
    
    try {
        const response = await fetch(`<?= base_url('api/tasks/') ?>${taskId}/submit-review`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (response.ok) {
            alert('Task submitted for review successfully!');
            location.reload();
        } else {
            alert(data.message || 'Failed to submit task for review');
        }
    } catch (error) {
        console.error('Error submitting task for review:', error);
        alert('Error submitting task for review');
    }
}
</script>
<?= $this->endSection() ?>

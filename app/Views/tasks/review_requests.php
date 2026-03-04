<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>Task Review Requests</h2>
        <p class="text-muted mb-0">Review and manage task submissions from developers</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('tasks') ?>" class="btn btn-outline-primary">
            <i class="bi bi-list-task"></i> All Tasks
        </a>
        <a href="<?= base_url('tasks/review-requests') ?>" class="btn btn-primary">
            <i class="bi bi-clipboard-check"></i> Review Requests
        </a>
    </div>
</div>

<?php if (empty($reviewTasks)): ?>
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-clipboard-check display-1 text-muted mb-3"></i>
        <h4 class="text-muted">No Review Requests</h4>
        <p class="text-muted">There are currently no tasks waiting for review.</p>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Tasks Awaiting Review (<?= count($reviewTasks) ?>)</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Task</th>
                        <th>Project</th>
                        <th>Assigned To</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reviewTasks as $task): ?>
                    <tr>
                        <td>
                            <div>
                                <strong><?= esc($task['title']) ?></strong>
                                <?php if ($task['priority']): ?>
                                <span class="badge bg-<?= $task['priority'] === 'high' ? 'danger' : ($task['priority'] === 'medium' ? 'warning' : 'info') ?> ms-2">
                                    <?= ucfirst($task['priority']) ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <?php if ($task['description']): ?>
                            <small class="text-muted"><?= esc(substr($task['description'], 0, 100)) ?><?= strlen($task['description']) > 100 ? '...' : '' ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= base_url('projects/view/' . $task['project_id']) ?>" class="text-decoration-none">
                                <?= esc($task['project_name']) ?>
                            </a>
                        </td>
                        <td>
                            <?php if ($task['assigned_username']): ?>
                                <span class="badge bg-primary"><?= esc($task['assigned_username']) ?></span>
                            <?php elseif (!empty($task['assigned_developers'])): ?>
                                <?php foreach ($task['assigned_developers'] as $dev): ?>
                                    <span class="badge bg-primary me-1"><?= esc($dev['username']) ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-muted">Unassigned</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($task['status'] === 'submitted_for_review'): ?>
                                <span class="badge bg-info">Submitted for Review</span>
                            <?php elseif ($task['status'] === 'needs_revision'): ?>
                                <span class="badge bg-warning">Needs Revision</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($task['submitted_for_review_at']): ?>
                                <small class="text-muted">
                                    <?= date('M d, Y H:i', strtotime($task['submitted_for_review_at'])) ?>
                                </small>
                            <?php else: ?>
                                <small class="text-muted">-</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="<?= base_url('tasks/view/' . $task['id']) ?>" class="btn btn-sm btn-outline-primary" title="View Task">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if ($task['status'] === 'submitted_for_review'): ?>
                                    <button class="btn btn-sm btn-success" onclick="reviewTask(<?= $task['id'] ?>, 'done')" title="Approve Task">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning" onclick="reviewTask(<?= $task['id'] ?>, 'needs_revision')" title="Request Revision">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                <?php elseif ($task['status'] === 'needs_revision'): ?>
                                    <span class="badge bg-secondary">Awaiting Developer</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Review task function for admins
async function reviewTask(taskId, status) {
    const statusText = status === 'done' ? 'approve' : 'request revision for';
    const comments = status === 'needs_revision' ? prompt('Please provide revision comments (optional):') : '';
    
    if (!confirm(`Are you sure you want to ${statusText} this task?`)) {
        return;
    }
    
    try {
        const response = await fetch(`<?= base_url('api/tasks/') ?>${taskId}/review`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                status: status,
                comments: comments || ''
            })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            const action = status === 'done' ? 'approved' : 'marked for revision';
            alert(`Task ${action} successfully!`);
            location.reload();
        } else {
            alert(data.message || 'Failed to review task');
        }
    } catch (error) {
        console.error('Error reviewing task:', error);
        alert('Error reviewing task');
    }
}
</script>
<?= $this->endSection() ?>

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
                        <th>Quality Checklist</th>
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
                            <?php if (isset($task['checklist']) && $task['checklist']): ?>
                                <div class="d-flex flex-column gap-1">
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php if ($task['checklist']['is_responsive']): ?>
                                            <span class="badge bg-success" title="Responsive Design"><i class="bi bi-phone"></i></span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark" title="Responsive Design - Not Checked"><i class="bi bi-phone"></i></span>
                                        <?php endif; ?>
                                        
                                        <?php if ($task['checklist']['no_ai_generated_text']): ?>
                                            <span class="badge bg-success" title="No AI Text"><i class="bi bi-robot"></i></span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark" title="No AI Text - Not Checked"><i class="bi bi-robot"></i></span>
                                        <?php endif; ?>
                                        
                                        <?php if ($task['checklist']['all_links_working']): ?>
                                            <span class="badge bg-success" title="Links Working"><i class="bi bi-link-45deg"></i></span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark" title="Links Working - Not Checked"><i class="bi bi-link-45deg"></i></span>
                                        <?php endif; ?>
                                        
                                        <?php if ($task['checklist']['code_reviewed']): ?>
                                            <span class="badge bg-success" title="Code Reviewed"><i class="bi bi-code-slash"></i></span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark" title="Code Reviewed - Not Checked"><i class="bi bi-code-slash"></i></span>
                                        <?php endif; ?>
                                        
                                        <?php if ($task['checklist']['functionality_tested']): ?>
                                            <span class="badge bg-success" title="Functionality Tested"><i class="bi bi-gear"></i></span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark" title="Functionality Tested - Not Checked"><i class="bi bi-gear"></i></span>
                                        <?php endif; ?>
                                        
                                        <?php if ($task['checklist']['cross_browser_tested']): ?>
                                            <span class="badge bg-success" title="Cross-browser Tested"><i class="bi bi-browser-chrome"></i></span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark" title="Cross-browser Tested - Not Checked"><i class="bi bi-browser-chrome"></i></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (!empty($task['checklist']['additional_notes'])): ?>
                                        <button class="btn btn-sm btn-outline-info" onclick="showChecklistNotes(<?= $task['id'] ?>, '<?= esc($task['checklist']['additional_notes']) ?>')" title="View Notes">
                                            <i class="bi bi-chat-text"></i> Notes
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">No checklist</span>
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

// Show checklist notes modal
function showChecklistNotes(taskId, notes) {
    const modalHtml = `
        <div class="modal fade" id="checklistNotesModal" tabindex="-1" aria-labelledby="checklistNotesModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="checklistNotesModalLabel">
                            <i class="bi bi-chat-text"></i> Developer Notes - Task #${taskId}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Additional Notes from Developer:</strong>
                        </div>
                        <div class="p-3 bg-light rounded">
                            ${notes.replace(/\n/g, '<br>')}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('checklistNotesModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('checklistNotesModal'));
    modal.show();
    
    // Clean up after modal is hidden
    document.getElementById('checklistNotesModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}
</script>
<?= $this->endSection() ?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
    .kanban-board {
        display: flex;
        gap: 1rem;
        overflow-x: auto;
        padding-bottom: 1rem;
    }
    
    .kanban-column {
        flex: 0 0 280px;
        background: #f8fafc;
        border-radius: 0.75rem;
        padding: 1rem;
    }
    
    .kanban-column-header {
        font-weight: 600;
        margin-bottom: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .kanban-cards {
        min-height: 200px;
    }
    
    .kanban-card {
        background: #fff;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 0.75rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        cursor: move;
        transition: all 0.2s;
    }
    
    .kanban-card:hover {
        box-shadow: 0 4px 6px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }
    
    .kanban-card-title {
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .kanban-card-meta {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        font-size: 0.875rem;
    }
    
    .sortable-ghost {
        opacity: 0.4;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><?= esc($project['name']) ?> - Kanban Board</h2>
        <a href="<?= base_url('projects/view/' . $project['id']) ?>" class="text-decoration-none">
            <i class="bi bi-arrow-left"></i> Back to Project
        </a>
    </div>
    <?php if ($isAdmin): ?>
    <a href="<?= base_url('tasks/create/' . $project['id']) ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> New Task
    </a>
    <?php endif; ?>
</div>

<div class="kanban-board">
    <?php
    $columns = [
        'backlog' => ['title' => 'Backlog', 'color' => 'secondary'],
        'todo' => ['title' => 'To Do', 'color' => 'primary'],
        'in_progress' => ['title' => 'In Progress', 'color' => 'warning'],
        'submitted_for_review' => ['title' => 'Submitted for Review', 'color' => 'info'],
        'needs_revision' => ['title' => 'Needs Revision', 'color' => 'danger'],
        'review' => ['title' => 'Review', 'color' => 'info'],
        'done' => ['title' => 'Done', 'color' => 'success']
    ];
    
    foreach ($columns as $status => $column):
        $tasks = $tasks_by_status[$status] ?? [];
    ?>
    <div class="kanban-column">
        <div class="kanban-column-header">
            <span><?= $column['title'] ?></span>
            <span class="badge bg-<?= $column['color'] ?>"><?= count($tasks) ?></span>
        </div>
        <div class="kanban-cards" data-status="<?= $status ?>">
            <?php foreach ($tasks as $task): ?>
            <div class="kanban-card <?= $task['is_blocked'] ? 'border-danger' : '' ?>" data-task-id="<?= $task['id'] ?>">
                <?php if ($task['is_blocked']): ?>
                <div class="mb-2">
                    <span class="badge bg-danger"><i class="bi bi-exclamation-triangle-fill"></i> BLOCKED</span>
                </div>
                <?php endif; ?>
                <div class="kanban-card-title" onclick="window.location='<?= base_url('tasks/view/' . $task['id']) ?>'" style="cursor: pointer;"><?= esc($task['title']) ?></div>
                <?php if ($task['assigned_to']): ?>
                <div class="mb-2">
                    <small class="text-muted">
                        <i class="bi bi-person"></i> <?= esc($task['assigned_username'] ?? 'Assigned') ?>
                    </small>
                </div>
                <?php else: ?>
                <div class="mb-2">
                    <small class="text-warning">
                        <i class="bi bi-person-x"></i> Unassigned
                    </small>
                </div>
                <?php endif; ?>
                <div class="kanban-card-meta mb-2">
                    <?php
                    $priorityColors = [
                        'low' => 'secondary',
                        'medium' => 'primary',
                        'high' => 'warning',
                        'urgent' => 'danger'
                    ];
                    ?>
                    <span class="badge bg-<?= $priorityColors[$task['priority']] ?? 'secondary' ?>">
                        <?= ucfirst($task['priority']) ?>
                    </span>
                    <?php if ($task['deadline']): ?>
                    <span class="text-muted">
                        <i class="bi bi-calendar"></i> <?= date('M d', strtotime($task['deadline'])) ?>
                    </span>
                    <?php endif; ?>
                    <?php if ($task['estimated_hours']): ?>
                    <span class="text-muted">
                        <i class="bi bi-clock"></i> <?= $task['estimated_hours'] ?>h
                    </span>
                    <?php endif; ?>
                </div>
                
                <!-- Task Action Buttons -->
                <div class="d-flex gap-1 flex-wrap">
                    <?php if (!$isAdmin && $task['status'] === 'in_progress'): ?>
                    <button class="btn btn-sm btn-success" onclick="event.stopPropagation(); submitTaskForReview(<?= $task['id'] ?>)" title="Submit for Review">
                        <i class="bi bi-check-circle"></i> Submit for Review
                    </button>
                    <?php endif; ?>
                    
                    <?php if ($isAdmin && $task['status'] === 'submitted_for_review'): ?>
                    <button class="btn btn-sm btn-success" onclick="event.stopPropagation(); reviewTask(<?= $task['id'] ?>, 'done')" title="Approve Task">
                        <i class="bi bi-check-circle"></i> Approve
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="event.stopPropagation(); reviewTask(<?= $task['id'] ?>, 'needs_revision')" title="Request Revision">
                        <i class="bi bi-arrow-clockwise"></i> Revise
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?= $this->endSection() ?>

<!-- Include Task Submission Checklist Modal -->
<?= $this->include('components/task_submission_checklist_modal') ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const columns = document.querySelectorAll('.kanban-cards');
    
    columns.forEach(column => {
        new Sortable(column, {
            group: 'kanban',
            animation: 150,
            ghostClass: 'sortable-ghost',
            onEnd: async function(evt) {
                const taskId = evt.item.dataset.taskId;
                const newStatus = evt.to.dataset.status;
                const newPosition = evt.newIndex;
                
                const badge = evt.to.parentElement.querySelector('.badge');
                const currentCount = parseInt(badge.textContent);
                badge.textContent = currentCount + 1;
                
                const oldBadge = evt.from.parentElement.querySelector('.badge');
                const oldCount = parseInt(oldBadge.textContent);
                oldBadge.textContent = Math.max(0, oldCount - 1);
                
                try {
                    const response = await fetch(`<?= base_url('api/tasks/') ?>${taskId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            status: newStatus,
                            order_position: newPosition
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (!response.ok) {
                        if (response.status === 403) {
                            alert('Permission denied: You cannot update this task');
                        } else {
                            alert(data.message || 'Failed to update task status');
                        }
                        location.reload();
                    } else {
                        console.log('Task status updated successfully');
                    }
                } catch (error) {
                    console.error('Error updating task status:', error);
                    alert('Error updating task status');
                    location.reload();
                }
            }
        });
    });
});

// Submit task for review function - now shows checklist modal
function submitTaskForReview(taskId) {
    // Show quality checklist modal instead of direct submission
    showTaskSubmissionChecklist(taskId);
}

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

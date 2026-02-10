<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><?= esc($project['name']) ?></h2>
        <p class="text-muted mb-0"><?= esc($project['description'] ?? '') ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('tasks/kanban/' . $project['id']) ?>" class="btn btn-primary">
            <i class="bi bi-kanban"></i> Kanban Board
        </a>
        <?php if ($isAdmin): ?>
        <a href="<?= base_url('projects/edit/' . $project['id']) ?>" class="btn btn-outline-secondary">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">Project Details</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Status</label>
                        <?php
                        $statusColors = [
                            'active' => 'success',
                            'on_hold' => 'warning',
                            'completed' => 'info',
                            'archived' => 'secondary'
                        ];
                        ?>
                        <div>
                            <span class="badge bg-<?= $statusColors[$project['status']] ?? 'secondary' ?>">
                                <?= ucfirst(str_replace('_', ' ', $project['status'])) ?>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Priority</label>
                        <?php
                        $priorityColors = [
                            'low' => 'secondary',
                            'medium' => 'primary',
                            'high' => 'warning',
                            'urgent' => 'danger'
                        ];
                        ?>
                        <div>
                            <span class="badge bg-<?= $priorityColors[$project['priority']] ?? 'secondary' ?>">
                                <?= ucfirst($project['priority']) ?>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Start Date</label>
                        <div><?= $project['start_date'] ? date('M d, Y', strtotime($project['start_date'])) : '-' ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Deadline</label>
                        <div><?= $project['deadline'] ? date('M d, Y', strtotime($project['deadline'])) : '-' ?></div>
                    </div>
                    <?php if ($project['budget']): ?>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Budget</label>
                        <div>$<?= number_format($project['budget'], 2) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Project Health</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center mb-3">
                        <h3 class="mb-0"><?= $health['total_tasks'] ?></h3>
                        <small class="text-muted">Total Tasks</small>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                        <h3 class="mb-0 text-success"><?= $health['completed_tasks'] ?></h3>
                        <small class="text-muted">Completed</small>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                        <h3 class="mb-0 text-danger"><?= $health['overdue_tasks'] ?></h3>
                        <small class="text-muted">Overdue</small>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                        <h3 class="mb-0 text-primary"><?= $health['completion_rate'] ?>%</h3>
                        <small class="text-muted">Completion Rate</small>
                    </div>
                </div>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= $health['completion_rate'] ?>%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Team Members</span>
                <?php if ($isAdmin): ?>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignDeveloperModal">
                    <i class="bi bi-plus-lg"></i> Assign
                </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($assigned_users)): ?>
                <p class="text-muted text-center py-3">No team members assigned</p>
                <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($assigned_users as $user): ?>
                    <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?= esc($user['username']) ?></strong>
                            <br><small class="text-muted"><?= esc($user['email']) ?></small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-secondary"><?= esc($user['role']) ?></span>
                            <?php if ($isAdmin): ?>
                            <button class="btn btn-sm btn-outline-danger" onclick="removeUser(<?= $user['user_id'] ?>)">
                                <i class="bi bi-x"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- MILESTONE 2+ FEATURES -->
        <?php if ($isAdmin): ?>
        <div class="card mt-3">
            <div class="card-header">Quick Actions</div>
            <div class="card-body">
                <a href="<?= base_url('notes?project_id=' . $project['id']) ?>" class="btn btn-outline-primary w-100 mb-2">
                    <i class="bi bi-journal-text"></i> View Notes
                </a>
                <a href="<?= base_url('messages/' . $project['id']) ?>" class="btn btn-outline-primary w-100 mb-2">
                    <i class="bi bi-chat-dots"></i> View Messages
                </a>
                <a href="<?= base_url('profitability/project/' . $project['id']) ?>" class="btn btn-outline-success w-100">
                    <i class="bi bi-graph-up"></i> View Profitability
                </a>
            </div>
        </div>
        <?php else: ?>
        <div class="card mt-3">
            <div class="card-header">Quick Actions</div>
            <div class="card-body">
                <a href="<?= base_url('notes?project_id=' . $project['id']) ?>" class="btn btn-outline-primary w-100 mb-2">
                    <i class="bi bi-journal-text"></i> View Notes
                </a>
                <a href="<?= base_url('messages/' . $project['id']) ?>" class="btn btn-outline-primary w-100">
                    <i class="bi bi-chat-dots"></i> View Messages
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($isAdmin): ?>
<!-- Assign Developer Modal -->
<div class="modal fade" id="assignDeveloperModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Developer to Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="assignDeveloperForm">
                    <div class="mb-3">
                        <label class="form-label">Select Developer</label>
                        <select class="form-select" id="developer_id" required>
                            <option value="">Choose a developer...</option>
                            <?php if (!empty($available_developers)): ?>
                                <?php foreach ($available_developers as $dev): ?>
                                <option value="<?= $dev['id'] ?>"><?= esc($dev['username']) ?> (<?= esc($dev['email']) ?>)</option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role on Project</label>
                        <select class="form-select" id="project_role">
                            <option value="developer">Developer</option>
                            <option value="lead">Lead Developer</option>
                            <option value="reviewer">Reviewer</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="assignDeveloper()">Assign Developer</button>
            </div>
        </div>
    </div>
</div>

<script>
async function assignDeveloper() {
    const developerId = document.getElementById('developer_id').value;
    const projectRole = document.getElementById('project_role').value;
    
    if (!developerId) {
        alert('Please select a developer');
        return;
    }
    
    try {
        const response = await fetch('<?= base_url('api/projects/' . $project['id'] . '/assign') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                user_id: developerId,
                role: projectRole
            })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            location.reload();
        } else {
            alert(data.message || 'Failed to assign developer');
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

async function removeUser(userId) {
    if (!confirm('Are you sure you want to remove this user from the project?')) {
        return;
    }
    
    try {
        const response = await fetch(`<?= base_url('api/projects/' . $project['id'] . '/users/') ?>${userId}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (response.ok) {
            location.reload();
        } else {
            alert(data.message || 'Failed to remove user');
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}
</script>
<?php endif; ?>
</div>

<?= $this->endSection() ?>

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
            <div class="card-header">Team Members</div>
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
                        <span class="badge bg-secondary"><?= esc($user['role']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

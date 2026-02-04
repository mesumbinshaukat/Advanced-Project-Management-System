<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row">
    <?php if ($isAdmin): ?>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-value"><?= $stats['total_projects'] ?></div>
            <div class="stat-label">Total Projects</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card success">
            <div class="stat-value"><?= $stats['active_projects'] ?></div>
            <div class="stat-label">Active Projects</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card warning">
            <div class="stat-value"><?= $stats['total_tasks'] ?></div>
            <div class="stat-label">Total Tasks</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card danger">
            <div class="stat-value"><?= $stats['overdue_tasks'] ?></div>
            <div class="stat-label">Overdue Tasks</div>
        </div>
    </div>
    <?php else: ?>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-value"><?= $stats['my_projects'] ?></div>
            <div class="stat-label">My Projects</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card warning">
            <div class="stat-value"><?= $stats['my_tasks'] ?></div>
            <div class="stat-label">My Tasks</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card success">
            <div class="stat-value"><?= $stats['completed_today'] ?></div>
            <div class="stat-label">Completed Today</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-value"><?= number_format($stats['hours_today'], 1) ?></div>
            <div class="stat-label">Hours Today</div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><?= $isAdmin ? 'Recent Projects' : 'My Projects' ?></span>
                <?php if ($isAdmin): ?>
                <a href="<?= base_url('projects/create') ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg"></i> New Project
                </a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($projects)): ?>
                <p class="text-muted text-center py-4">No projects found</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <?php if ($isAdmin): ?>
                                <th>Health</th>
                                <?php endif; ?>
                                <th>Deadline</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $project): ?>
                            <tr>
                                <td>
                                    <strong><?= esc($project['name']) ?></strong>
                                    <?php if (isset($project['client_name'])): ?>
                                    <br><small class="text-muted"><?= esc($project['client_name']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusColors = [
                                        'active' => 'success',
                                        'on_hold' => 'warning',
                                        'completed' => 'info',
                                        'archived' => 'secondary'
                                    ];
                                    ?>
                                    <span class="badge bg-<?= $statusColors[$project['status']] ?? 'secondary' ?>">
                                        <?= ucfirst(str_replace('_', ' ', $project['status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $priorityColors = [
                                        'low' => 'secondary',
                                        'medium' => 'primary',
                                        'high' => 'warning',
                                        'urgent' => 'danger'
                                    ];
                                    ?>
                                    <span class="badge bg-<?= $priorityColors[$project['priority']] ?? 'secondary' ?>">
                                        <?= ucfirst($project['priority']) ?>
                                    </span>
                                </td>
                                <?php if ($isAdmin && isset($project['health'])): ?>
                                <td>
                                    <small class="text-muted">
                                        <?= $project['health']['completed_tasks'] ?>/<?= $project['health']['total_tasks'] ?> tasks
                                        (<?= $project['health']['completion_rate'] ?>%)
                                    </small>
                                </td>
                                <?php endif; ?>
                                <td><?= $project['deadline'] ? date('M d, Y', strtotime($project['deadline'])) : '-' ?></td>
                                <td>
                                    <a href="<?= base_url('projects/view/' . $project['id']) ?>" class="btn btn-sm btn-outline-primary">
                                        View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <?php if ($isAdmin): ?>
        <div class="card">
            <div class="card-header">Recent Activity</div>
            <div class="card-body">
                <?php if (empty($recent_activity)): ?>
                <p class="text-muted text-center py-4">No recent activity</p>
                <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($recent_activity as $activity): ?>
                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong><?= esc($activity['username'] ?? 'System') ?></strong>
                                <p class="mb-0 small text-muted"><?= esc($activity['description']) ?></p>
                            </div>
                            <small class="text-muted"><?= date('H:i', strtotime($activity['created_at'])) ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="card">
            <div class="card-header">My Tasks by Status</div>
            <div class="card-body">
                <?php foreach ($tasks_by_status as $status => $count): ?>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span><?= ucfirst(str_replace('_', ' ', $status)) ?></span>
                    <span class="badge bg-primary"><?= $count ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">Quick Actions</div>
            <div class="card-body">
                <a href="<?= base_url('time/create') ?>" class="btn btn-primary w-100 mb-2">
                    <i class="bi bi-clock"></i> Log Time
                </a>
                <a href="<?= base_url('tasks') ?>" class="btn btn-outline-primary w-100">
                    <i class="bi bi-list-task"></i> View All Tasks
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

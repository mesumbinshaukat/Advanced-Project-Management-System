<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php if ($isAdmin): ?>
    <?php if (!empty($critical_alerts)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div class="flex-grow-1">
                    <strong><?= count($critical_alerts) ?> Critical Alert<?= count($critical_alerts) > 1 ? 's' : '' ?></strong> require immediate attention
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <span><i class="bi bi-heart-pulse"></i> Project Health Overview</span>
                        <div class="small text-muted">Collapsed by default to keep focus on alerts</div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#projectHealthCollapse" aria-expanded="false" aria-controls="projectHealthCollapse">
                            <i class="bi bi-chevron-down"></i> Show/Hide
                        </button>
                        <a href="<?= base_url('projects/create') ?>" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-lg"></i> New Project
                        </a>
                    </div>
                </div>
                <div class="collapse" id="projectHealthCollapse">
                    <div class="card-body">
                        <?php if (empty($project_health)): ?>
                        <p class="text-muted text-center py-4">No active projects</p>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Project</th>
                                        <th>Health</th>
                                        <th>Progress</th>
                                        <th>Issues</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($project_health, 0, 10) as $project): ?>
                                    <tr>
                                        <td>
                                            <strong><?= esc($project['name']) ?></strong>
                                            <br><small class="text-muted"><?= ucfirst(str_replace('_', ' ', $project['status'])) ?></small>
                                        </td>
                                        <td>
                                            <?php
                                            $healthColors = ['healthy' => 'success', 'warning' => 'warning', 'critical' => 'danger'];
                                            $healthIcons = ['healthy' => 'check-circle-fill', 'warning' => 'exclamation-triangle-fill', 'critical' => 'x-circle-fill'];
                                            ?>
                                            <span class="badge bg-<?= $healthColors[$project['health_status']] ?>">
                                                <i class="bi bi-<?= $healthIcons[$project['health_status']] ?>"></i>
                                                <?= ucfirst($project['health_status']) ?>
                                            </span>
                                            <?php if ($project['health_status'] === 'warning'): ?>
                                            <div class="small text-warning mt-1">
                                                Warning reason: <?= esc($project['health_reason'] ?? $project['health_detail'] ?? 'Needs review') ?>
                                            </div>
                                            <?php else: ?>
                                            <div class="small text-muted mt-1">
                                                <?= esc($project['health_detail'] ?? 'Status signal') ?>
                                            </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 20px; min-width: 100px;">
                                                <div class="progress-bar bg-<?= $project['completion_rate'] < 30 ? 'danger' : ($project['completion_rate'] < 70 ? 'warning' : 'success') ?>" 
                                                     style="width: <?= $project['completion_rate'] ?>%">
                                                    <?= $project['completion_rate'] ?>%
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($project['blocked_tasks'] > 0): ?>
                                            <span class="badge bg-danger"><?= $project['blocked_tasks'] ?> blocked</span>
                                            <?php endif; ?>
                                            <?php if ($project['overdue_tasks'] > 0): ?>
                                            <span class="badge bg-warning"><?= $project['overdue_tasks'] ?> overdue</span>
                                            <?php endif; ?>
                                            <?php if ($project['blocked_tasks'] == 0 && $project['overdue_tasks'] == 0): ?>
                                            <span class="text-muted">None</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?= base_url('projects/view/' . $project['id']) ?>" class="btn btn-sm btn-outline-primary">View</a>
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
        </div>
        <?php if (!empty($delayed_projects)): ?>
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning bg-opacity-10">
                    <i class="bi bi-clock-history"></i> Delayed Projects
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Projects listed below are past their deadline; review the delay reason and assign resources.</p>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Project</th>
                                    <th>Owner</th>
                                    <th>Status</th>
                                    <th>Delay</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($delayed_projects as $project): ?>
                                <tr>
                                    <td>
                                        <strong><?= esc($project['name']) ?></strong>
                                        <br><small class="text-muted">Deadline: <?= date('M d, Y', strtotime($project['deadline'])) ?></small>
                                    </td>
                                    <td><?= esc($project['owner_name'] ?? 'Unassigned') ?></td>
                                    <td class="text-capitalize">
                                        <span class="badge bg-warning text-dark">Delayed</span>
                                    </td>
                                    <td><?= esc($project['delay_reason']) ?></td>
                                    <td>
                                        <a href="<?= base_url('projects/view/' . $project['id']) ?>" class="btn btn-sm btn-outline-primary">View</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><i class="bi bi-exclamation-triangle"></i> Critical Alerts</div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <?php if (empty($critical_alerts)): ?>
                    <p class="text-muted text-center py-4">No critical alerts</p>
                    <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($critical_alerts as $alert): ?>
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <span class="badge bg-<?= $alert['severity'] === 'critical' ? 'danger' : ($alert['severity'] === 'warning' ? 'warning' : 'info') ?> me-2">
                                        <?= ucfirst($alert['type']) ?>
                                    </span>
                                    <span><?= esc($alert['message']) ?></span>
                                </div>
                                <a href="<?= base_url($alert['link']) ?>" class="btn btn-sm btn-outline-primary">View</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><i class="bi bi-activity"></i> Recent Activity</div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <?php if (empty($recent_activity)): ?>
                    <p class="text-muted text-center py-4">No recent activity</p>
                    <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_activity as $activity): ?>
                        <div class="list-group-item px-0 py-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong><?= esc($activity['username'] ?? 'System') ?></strong>
                                    <small class="text-muted d-block"><?= esc($activity['description']) ?></small>
                                </div>
                                <small class="text-muted"><?= date('H:i', strtotime($activity['created_at'])) ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>

<div class="row">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-value"><?= count($my_projects ?? []) ?></div>
            <div class="stat-label">My Projects</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card warning">
            <div class="stat-value"><?= count($my_tasks ?? []) ?></div>
            <div class="stat-label">My Active Tasks</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card success">
            <div class="stat-value"><?= count(array_filter($my_tasks ?? [], fn($t) => $t['status'] === 'done' && date('Y-m-d', strtotime($t['completed_at'] ?? '')) === date('Y-m-d'))) ?></div>
            <div class="stat-label">Completed Today</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-value"><?= count(array_filter($my_tasks ?? [], fn($t) => $t['status'] === 'in_progress')) ?></div>
            <div class="stat-label">In Progress</div>
        </div>
    </div>
</div>

<?php if (!empty($my_alerts)): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div class="flex-grow-1">
                <strong><?= count($my_alerts) ?> Active Alert<?= count($my_alerts) > 1 ? 's' : '' ?></strong> require your attention
            </div>
            <a href="<?= base_url('alerts') ?>" class="btn btn-sm btn-outline-dark">View Alerts</a>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row mt-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-list-task"></i> My Active Tasks</span>
                <a href="<?= base_url('tasks') ?>" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($my_tasks)): ?>
                <p class="text-muted text-center py-4">No active tasks</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Project</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Deadline</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($my_tasks, 0, 10) as $task): ?>
                            <tr>
                                <td>
                                    <strong><?= esc($task['title']) ?></strong>
                                    <?php if ($task['is_blocked'] ?? false): ?>
                                    <span class="badge bg-danger ms-2">Blocked</span>
                                    <?php endif; ?>
                                </td>
                                <td><small class="text-muted"><?= esc($task['project_name'] ?? '') ?></small></td>
                                <td>
                                    <?php
                                    $statusColors = ['backlog' => 'secondary', 'todo' => 'primary', 'in_progress' => 'warning', 'review' => 'info', 'done' => 'success'];
                                    ?>
                                    <span class="badge bg-<?= $statusColors[$task['status']] ?? 'secondary' ?>">
                                        <?= ucfirst(str_replace('_', ' ', $task['status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $priorityColors = ['low' => 'secondary', 'medium' => 'primary', 'high' => 'warning', 'urgent' => 'danger'];
                                    ?>
                                    <span class="badge bg-<?= $priorityColors[$task['priority']] ?? 'secondary' ?>">
                                        <?= ucfirst($task['priority']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($task['deadline']): ?>
                                        <?php
                                        $isOverdue = strtotime($task['deadline']) < time() && $task['status'] !== 'done';
                                        ?>
                                        <span class="<?= $isOverdue ? 'text-danger' : '' ?>">
                                            <?= date('M d', strtotime($task['deadline'])) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= base_url('tasks/view/' . $task['id']) ?>" class="btn btn-sm btn-outline-primary">View</a>
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
        <div class="card">
            <div class="card-header"><i class="bi bi-folder"></i> My Projects</div>
            <div class="card-body">
                <?php if (empty($my_projects)): ?>
                <p class="text-muted text-center py-4">No assigned projects</p>
                <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach (array_slice($my_projects, 0, 5) as $project): ?>
                    <a href="<?= base_url('projects/view/' . $project['id']) ?>" class="list-group-item list-group-item-action px-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= esc($project['name']) ?></strong>
                                <br><small class="text-muted"><?= ucfirst(str_replace('_', ' ', $project['status'])) ?></small>
                            </div>
                            <i class="bi bi-chevron-right"></i>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header"><i class="bi bi-lightning"></i> Quick Actions</div>
            <div class="card-body">
                <a href="<?= base_url('time/create') ?>" class="btn btn-primary w-100 mb-2">
                    <i class="bi bi-clock"></i> Log Time
                </a>
                <a href="<?= base_url('tasks') ?>" class="btn btn-outline-primary w-100 mb-2">
                    <i class="bi bi-list-task"></i> View All Tasks
                </a>
                <?php if (!empty($my_projects)): ?>
                <a href="<?= base_url('tasks/kanban/' . $my_projects[0]['id']) ?>" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-kanban"></i> Kanban Board
                </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header"><i class="bi bi-activity"></i> Recent Activity</div>
            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                <?php if (empty($recent_activity)): ?>
                <p class="text-muted text-center py-4">No recent activity</p>
                <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach (array_slice($recent_activity, 0, 10) as $activity): ?>
                    <div class="list-group-item px-0 py-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong><?= esc($activity['username'] ?? 'System') ?></strong>
                                <small class="text-muted d-block"><?= esc($activity['description']) ?></small>
                            </div>
                            <small class="text-muted"><?= date('H:i', strtotime($activity['created_at'])) ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<?= $this->endSection() ?>

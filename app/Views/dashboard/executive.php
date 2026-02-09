<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12">
        <h2>Executive Dashboard</h2>
        <p class="text-muted">Real-time project health monitoring and critical alerts</p>
    </div>
</div>

<?php if (!empty($critical_alerts)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <i class="bi bi-exclamation-triangle-fill"></i> Critical Alerts (<?= count($critical_alerts) ?>)
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php foreach ($critical_alerts as $alert): ?>
                    <div class="list-group-item">
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
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-heart-pulse"></i> Project Health Indicators</span>
                <a href="<?= base_url('projects/create') ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg"></i> New Project
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($project_health)): ?>
                <p class="text-muted text-center py-4">No active projects</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>Health Status</th>
                                <th>Progress</th>
                                <th>Blocked Tasks</th>
                                <th>Overdue Tasks</th>
                                <th>Deadline</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($project_health as $project): ?>
                            <tr>
                                <td>
                                    <strong><?= esc($project['name']) ?></strong>
                                    <br><small class="text-muted"><?= ucfirst(str_replace('_', ' ', $project['status'])) ?></small>
                                </td>
                                <td>
                                    <?php
                                    $healthColors = [
                                        'healthy' => 'success',
                                        'warning' => 'warning',
                                        'critical' => 'danger'
                                    ];
                                    $healthIcons = [
                                        'healthy' => 'check-circle-fill',
                                        'warning' => 'exclamation-triangle-fill',
                                        'critical' => 'x-circle-fill'
                                    ];
                                    ?>
                                    <span class="badge bg-<?= $healthColors[$project['health_status']] ?>">
                                        <i class="bi bi-<?= $healthIcons[$project['health_status']] ?>"></i>
                                        <?= ucfirst($project['health_status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-<?= $project['completion_rate'] < 30 ? 'danger' : ($project['completion_rate'] < 70 ? 'warning' : 'success') ?>" 
                                             role="progressbar" 
                                             style="width: <?= $project['completion_rate'] ?>%"
                                             aria-valuenow="<?= $project['completion_rate'] ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            <?= $project['completion_rate'] ?>%
                                        </div>
                                    </div>
                                    <small class="text-muted"><?= $project['completed_tasks'] ?>/<?= $project['total_tasks'] ?> tasks</small>
                                </td>
                                <td>
                                    <?php if ($project['blocked_tasks'] > 0): ?>
                                    <span class="badge bg-danger"><?= $project['blocked_tasks'] ?></span>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($project['overdue_tasks'] > 0): ?>
                                    <span class="badge bg-warning"><?= $project['overdue_tasks'] ?></span>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($project['deadline']): ?>
                                    <?= date('M d, Y', strtotime($project['deadline'])) ?>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= base_url('projects/view/' . $project['id']) ?>" class="btn btn-sm btn-outline-primary">View</a>
                                    <!-- MILESTONE 2+ FEATURE - Hidden for Milestone 1
                                    <a href="<?= base_url('tasks/kanban/' . $project['id']) ?>" class="btn btn-sm btn-outline-secondary">Kanban</a>
                                    -->
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

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-calendar-check"></i> Upcoming Deadlines (Next 7 Days)
            </div>
            <div class="card-body">
                <?php if (empty($deadline_overview)): ?>
                <p class="text-muted text-center py-4">No upcoming deadlines</p>
                <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($deadline_overview as $task): ?>
                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <strong><?= esc($task['title']) ?></strong>
                                <br><small class="text-muted"><?= esc($task['project_name']) ?></small>
                                <br><small class="text-muted">Assigned to: <?= esc($task['username'] ?? 'Unassigned') ?></small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-<?= strtotime($task['deadline']) - time() < 86400 * 2 ? 'danger' : 'warning' ?>">
                                    <?= date('M d', strtotime($task['deadline'])) ?>
                                </span>
                            </div>
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
            <div class="card-header">
                <i class="bi bi-people"></i> Team Performance (Last 30 Days)
            </div>
            <div class="card-body">
                <?php if (empty($team_performance)): ?>
                <p class="text-muted text-center py-4">No team data available</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Developer</th>
                                <th>Tasks Completed</th>
                                <th>Hours Logged</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($team_performance as $perf): ?>
                            <tr>
                                <td><?= esc($perf['username']) ?></td>
                                <td><span class="badge bg-success"><?= $perf['tasks_completed_30d'] ?></span></td>
                                <td><?= number_format($perf['hours_logged_30d'], 1) ?>h</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="<?= base_url('developers') ?>" class="btn btn-sm btn-outline-primary">View All Developers</a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <i class="bi bi-activity"></i> Recent Activity
            </div>
            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
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

<?= $this->endSection() ?>

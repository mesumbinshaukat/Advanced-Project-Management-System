<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Tasks</h2>
    <?php if ($isAdmin): ?>
    <a href="<?= base_url('tasks/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> New Task
    </a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($tasks)): ?>
        <p class="text-muted text-center py-5">No tasks found</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Project</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Assigned To</th>
                        <th>Deadline</th>
                        <th>Hours</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td>
                            <strong><?= esc($task['title']) ?></strong>
                            <?php if (!empty($task['description'])): ?>
                            <br><small class="text-muted"><?= esc(substr($task['description'], 0, 60)) ?>...</small>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($task['project_name'] ?? 'N/A') ?></td>
                        <td>
                            <?php
                            $statusColors = [
                                'backlog' => 'secondary',
                                'todo' => 'primary',
                                'in_progress' => 'warning',
                                'review' => 'info',
                                'done' => 'success'
                            ];
                            ?>
                            <span class="badge bg-<?= $statusColors[$task['status']] ?? 'secondary' ?>">
                                <?= ucfirst(str_replace('_', ' ', $task['status'])) ?>
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
                            <span class="badge bg-<?= $priorityColors[$task['priority']] ?? 'secondary' ?>">
                                <?= ucfirst($task['priority']) ?>
                            </span>
                        </td>
                        <td><?= esc($task['assigned_to_name'] ?? 'Unassigned') ?></td>
                        <td>
                            <?php if ($task['deadline']): ?>
                                <?php
                                $deadline = strtotime($task['deadline']);
                                $isOverdue = $deadline < time() && $task['status'] !== 'done';
                                ?>
                                <span class="<?= $isOverdue ? 'text-danger' : '' ?>">
                                    <?= date('M d, Y', $deadline) ?>
                                    <?php if ($isOverdue): ?>
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <?php endif; ?>
                                </span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <small class="text-muted">
                                <?= number_format($task['actual_hours'] ?? 0, 1) ?>
                                <?php if ($task['estimated_hours']): ?>
                                / <?= number_format($task['estimated_hours'], 1) ?>
                                <?php endif; ?>
                                h
                            </small>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

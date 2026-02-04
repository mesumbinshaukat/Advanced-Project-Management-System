<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2>Project & Task Templates</h2>
                <p class="text-muted">Reusable templates for faster project setup</p>
            </div>
            <div>
                <a href="<?= base_url('templates/create-project') ?>" class="btn btn-primary me-2">
                    <i class="bi bi-plus-lg"></i> New Project Template
                </a>
                <a href="<?= base_url('templates/create-task') ?>" class="btn btn-outline-primary">
                    <i class="bi bi-plus-lg"></i> New Task Template
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-folder"></i> Project Templates
            </div>
            <div class="card-body">
                <?php if (empty($project_templates)): ?>
                <p class="text-muted text-center py-4">No project templates yet. Create one to get started!</p>
                <?php else: ?>
                <div class="row">
                    <?php foreach ($project_templates as $template): ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?= esc($template['name']) ?></h5>
                                <p class="card-text text-muted small"><?= esc($template['description']) ?></p>
                                <div class="mb-2">
                                    <span class="badge bg-secondary"><?= ucfirst($template['default_priority']) ?></span>
                                    <?php if ($template['estimated_duration_days']): ?>
                                    <span class="badge bg-info"><?= $template['estimated_duration_days'] ?> days</span>
                                    <?php endif; ?>
                                    <?php if ($template['default_budget']): ?>
                                    <span class="badge bg-success">$<?= number_format($template['default_budget'], 0) ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($template['task_templates'])): ?>
                                <small class="text-muted">
                                    <i class="bi bi-list-check"></i> <?= count($template['task_templates']) ?> tasks included
                                </small>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="<?= base_url('templates/use-project/' . $template['id']) ?>" class="btn btn-sm btn-primary w-100">
                                    <i class="bi bi-play"></i> Use Template
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-list-task"></i> Task Templates
            </div>
            <div class="card-body">
                <?php if (empty($task_templates)): ?>
                <p class="text-muted text-center py-4">No task templates yet. Create one to get started!</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Priority</th>
                                <th>Estimated Hours</th>
                                <th>Checklist Items</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($task_templates as $template): ?>
                            <tr>
                                <td><strong><?= esc($template['name']) ?></strong></td>
                                <td><small class="text-muted"><?= esc(substr($template['description'] ?? '', 0, 100)) ?></small></td>
                                <td>
                                    <?php
                                    $priorityColors = ['low' => 'secondary', 'medium' => 'primary', 'high' => 'warning', 'urgent' => 'danger'];
                                    ?>
                                    <span class="badge bg-<?= $priorityColors[$template['default_priority']] ?>">
                                        <?= ucfirst($template['default_priority']) ?>
                                    </span>
                                </td>
                                <td><?= $template['estimated_hours'] ?? '-' ?>h</td>
                                <td>
                                    <?php if (!empty($template['checklist_items'])): ?>
                                    <span class="badge bg-info"><?= count($template['checklist_items']) ?> items</span>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
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

<?= $this->endSection() ?>

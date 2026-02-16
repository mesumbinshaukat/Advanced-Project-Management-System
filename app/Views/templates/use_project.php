<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2>Use Project Template: <?= esc($template['name']) ?></h2>
                <p class="text-muted">Create a new project from this template</p>
            </div>
            <a href="<?= base_url('templates') ?>" class="btn btn-secondary">Back to Templates</a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Project Details</h5>
            </div>
            <div class="card-body">
                <form id="useTemplateForm" method="POST" action="<?= base_url('templates/apply-project') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="template_id" value="<?= $template['id'] ?>">

                    <div class="mb-3">
                        <label for="name" class="form-label">Project Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required placeholder="Enter project name">
                    </div>

                    <div class="mb-3">
                        <label for="client_id" class="form-label">Client <span class="text-muted fw-normal">(optional)</span></label>
                        <select class="form-select" id="client_id" name="client_id">
                            <option value="">No client assigned</option>
                            <?php
                            $db = \Config\Database::connect();
                            $clients = $db->table('clients')->where('deleted_at', null)->get()->getResultArray();
                            foreach ($clients as $client):
                            ?>
                            <option value="<?= $client['id'] ?>"><?= esc($client['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="deadline" class="form-label">Deadline</label>
                            <input type="date" class="form-control" id="deadline" name="deadline">
                        </div>
                    </div>

                    <div class="alert alert-info" role="alert">
                        <strong>Template Info:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Priority: <strong><?= ucfirst($template['default_priority']) ?></strong></li>
                            <?php if (!empty($template['estimated_duration_days'])): ?>
                            <li>Estimated Duration: <strong><?= $template['estimated_duration_days'] ?> days</strong></li>
                            <?php endif; ?>
                            <?php 
                            $taskTemplates = $template['task_templates'];
                            if (is_string($taskTemplates)) {
                                $taskTemplates = json_decode($taskTemplates, true);
                            }
                            if (!empty($taskTemplates)): 
                            ?>
                            <li>Included Tasks: <strong><?= count($taskTemplates) ?> tasks</strong></li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-play"></i> Create Project from Template
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Template Summary</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">Description</small>
                    <p class="mb-0"><?= esc($template['description']) ?: '<em>No description</em>' ?></p>
                </div>

                <div class="mb-3">
                    <small class="text-muted">Default Priority</small>
                    <p class="mb-0">
                        <span class="badge bg-<?= $template['default_priority'] === 'high' ? 'danger' : ($template['default_priority'] === 'medium' ? 'warning' : 'info') ?>">
                            <?= ucfirst($template['default_priority']) ?>
                        </span>
                    </p>
                </div>

                <?php if (!empty($template['estimated_duration_days'])): ?>
                <div class="mb-3">
                    <small class="text-muted">Estimated Duration</small>
                    <p class="mb-0"><?= $template['estimated_duration_days'] ?> days</p>
                </div>
                <?php endif; ?>

                <?php 
                $taskTemplates = $template['task_templates'];
                if (is_string($taskTemplates)) {
                    $taskTemplates = json_decode($taskTemplates, true);
                }
                if (!empty($taskTemplates)): 
                ?>
                <div class="mb-3">
                    <small class="text-muted">Included Tasks</small>
                    <p class="mb-0"><?= count($taskTemplates) ?> tasks</p>
                    <ul class="small mt-2 mb-0">
                        <?php foreach ($taskTemplates as $task): ?>
                        <li><?= esc($task['title'] ?? 'Untitled') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

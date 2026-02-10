<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>Use Project Template</h2>
        <p class="text-muted">Create a new project from template: <?= esc($template['name']) ?></p>
    </div>
    <a href="<?= base_url('templates') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Templates
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Template Details</div>
            <div class="card-body">
                <h5><?= esc($template['name']) ?></h5>
                <p class="text-muted"><?= esc($template['description'] ?? '') ?></p>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Default Priority</label>
                        <p><?= ucfirst($template['default_priority'] ?? 'Not set') ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Default Budget</label>
                        <p><?= $template['default_budget'] ? '$' . number_format($template['default_budget'], 2) : 'Not set' ?></p>
                    </div>
                </div>

                <?php if (!empty($template['task_templates'])): ?>
                <hr>
                <h6>Included Task Templates</h6>
                <ul class="list-group">
                    <?php foreach ($template['task_templates'] as $taskTemplate): ?>
                    <li class="list-group-item">
                        <strong><?= esc($taskTemplate['name'] ?? 'Task') ?></strong>
                        <?php if (!empty($taskTemplate['description'])): ?>
                        <br>
                        <small class="text-muted"><?= esc($taskTemplate['description']) ?></small>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Create Project from Template</div>
            <div class="card-body">
                <form action="<?= base_url('templates/apply-project') ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="template_id" value="<?= $template['id'] ?>">

                    <div class="mb-3">
                        <label for="name" class="form-label">Project Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required placeholder="Enter project name">
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Project description"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="client_id" class="form-label">Client</label>
                        <select class="form-control" id="client_id" name="client_id">
                            <option value="">-- Select Client --</option>
                            <?php if (!empty($clients)): ?>
                                <?php foreach ($clients as $client): ?>
                                <option value="<?= $client['id'] ?>"><?= esc($client['name']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="deadline" class="form-label">Deadline</label>
                        <input type="date" class="form-control" id="deadline" name="deadline">
                    </div>

                    <div class="mb-3">
                        <label for="priority" class="form-label">Priority</label>
                        <select class="form-control" id="priority" name="priority">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-check-lg"></i> Create Project
                        </button>
                        <a href="<?= base_url('templates') ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

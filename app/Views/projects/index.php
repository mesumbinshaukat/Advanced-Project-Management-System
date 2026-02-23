<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <h2 class="mb-0">Projects</h2>
    <?php if ($isAdmin): ?>
    <a href="<?= base_url('projects/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> New Project
    </a>
    <?php endif; ?>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="clientFilter" class="form-label">Client</label>
                <select id="clientFilter" name="client_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Clients</option>
                    <?php foreach ($clients as $client): ?>
                    <option value="<?= $client['id'] ?>" <?= ($selectedClientId === (int) $client['id']) ? 'selected' : '' ?>>
                        <?= esc($client['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if (!empty($selectedClientId)): ?>
            <div class="col-md-2 d-flex align-items-end">
                <a href="<?= current_url() ?>" class="btn btn-outline-secondary w-100">Clear Filter</a>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($projects)): ?>
        <p class="text-muted text-center py-5">No projects found</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Project Name</th>
                        <th>Client</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Start Date</th>
                        <th>Deadline</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): ?>
                    <tr>
                        <td>
                            <strong><?= esc($project['name']) ?></strong>
                            <?php if (!empty($project['description'])): ?>
                            <br><small class="text-muted"><?= esc(substr($project['description'], 0, 60)) ?>...</small>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($project['client_name'] ?? 'N/A') ?></td>
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
                        <td><?= $project['start_date'] ? date('M d, Y', strtotime($project['start_date'])) : '-' ?></td>
                        <td><?= $project['deadline'] ? date('M d, Y', strtotime($project['deadline'])) : '-' ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?= base_url('projects/view/' . $project['id']) ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?= base_url('tasks/kanban/' . $project['id']) ?>" class="btn btn-outline-info">
                                    <i class="bi bi-kanban"></i>
                                </a>
                                <?php if ($isAdmin): ?>
                                <a href="<?= base_url('projects/edit/' . $project['id']) ?>" class="btn btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="<?= base_url('projects/delete/' . $project['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this project?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <?php endif; ?>
                            </div>
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

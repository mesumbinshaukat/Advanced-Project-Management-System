<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Time Tracking</h2>
    <a href="<?= base_url('time/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Log Time
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($entries)): ?>
        <p class="text-muted text-center py-5">No time entries found</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Task</th>
                        <th>Project</th>
                        <th>Hours</th>
                        <th>Description</th>
                        <th>Billable</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $entry): ?>
                    <tr>
                        <td><?= date('M d, Y', strtotime($entry['date'])) ?></td>
                        <td><?= esc($entry['task_title'] ?? 'N/A') ?></td>
                        <td><?= esc($entry['project_name'] ?? 'N/A') ?></td>
                        <td><strong><?= number_format($entry['hours'], 2) ?>h</strong></td>
                        <td><?= esc($entry['description'] ?? '-') ?></td>
                        <td>
                            <?php if ($entry['is_billable']): ?>
                            <span class="badge bg-success">Billable</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Non-billable</span>
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

<?= $this->endSection() ?>

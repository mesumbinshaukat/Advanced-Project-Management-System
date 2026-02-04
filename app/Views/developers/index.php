<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12">
        <h2>Developer Management</h2>
        <p class="text-muted">Monitor team workload and performance</p>
    </div>
</div>

<div class="row">
    <?php if (empty($developers)): ?>
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <p class="text-muted">No developers found</p>
            </div>
        </div>
    </div>
    <?php else: ?>
    <?php foreach ($developers as $dev): ?>
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="card-title mb-1"><?= esc($dev['username']) ?></h5>
                        <span class="badge bg-secondary">Developer</span>
                    </div>
                    <a href="<?= base_url('developers/workload/' . $dev['id']) ?>" class="btn btn-sm btn-outline-primary">
                        Details
                    </a>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Active Tasks</span>
                        <strong class="text-primary"><?= $dev['workload']['active_tasks'] ?></strong>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar <?= $dev['workload']['active_tasks'] > 10 ? 'bg-danger' : ($dev['workload']['active_tasks'] > 5 ? 'bg-warning' : 'bg-success') ?>" 
                             style="width: <?= min(100, $dev['workload']['active_tasks'] * 10) ?>%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Hours This Week</span>
                        <strong><?= number_format($dev['workload']['hours_this_week'], 1) ?>h</strong>
                    </div>
                </div>
                
                <div class="mt-3">
                    <small class="text-muted d-block mb-2">Tasks by Status:</small>
                    <div class="d-flex justify-content-between flex-wrap gap-2">
                        <?php foreach ($dev['workload']['tasks_by_status'] as $status => $count): ?>
                        <?php if ($count > 0): ?>
                        <span class="badge bg-secondary"><?= ucfirst(str_replace('_', ' ', $status)) ?>: <?= $count ?></span>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="mt-3">
                    <?php
                    $workloadScore = ($dev['workload']['active_tasks'] * 10) + ($dev['workload']['hours_this_week'] * 0.5);
                    $workloadStatus = $workloadScore > 100 ? 'Overloaded' : ($workloadScore > 60 ? 'Busy' : 'Available');
                    $workloadColor = $workloadScore > 100 ? 'danger' : ($workloadScore > 60 ? 'warning' : 'success');
                    ?>
                    <div class="alert alert-<?= $workloadColor ?> py-2 mb-0">
                        <small><strong>Status:</strong> <?= $workloadStatus ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

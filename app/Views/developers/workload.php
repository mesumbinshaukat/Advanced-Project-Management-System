<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><?= esc($user->username ?? 'Developer') ?> - Workload</h2>
        <p class="text-muted">Current task assignments and workload analysis</p>
    </div>
    <a href="<?= base_url('developers') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Developers
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Tasks by Status -->
        <div class="card mb-4">
            <div class="card-header">Tasks by Status</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center mb-3">
                        <div style="font-size: 2rem; font-weight: bold; color: #6c757d;">
                            <?= $workload['tasks_by_status']['backlog'] ?? 0 ?>
                        </div>
                        <small class="text-muted">Backlog</small>
                    </div>
                    <div class="col-md-4 text-center mb-3">
                        <div style="font-size: 2rem; font-weight: bold; color: #0d6efd;">
                            <?= $workload['tasks_by_status']['todo'] ?? 0 ?>
                        </div>
                        <small class="text-muted">To Do</small>
                    </div>
                    <div class="col-md-4 text-center mb-3">
                        <div style="font-size: 2rem; font-weight: bold; color: #ffc107;">
                            <?= $workload['tasks_by_status']['in_progress'] ?? 0 ?>
                        </div>
                        <small class="text-muted">In Progress</small>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 text-center mb-3">
                        <div style="font-size: 2rem; font-weight: bold; color: #17a2b8;">
                            <?= $workload['tasks_by_status']['review'] ?? 0 ?>
                        </div>
                        <small class="text-muted">Review</small>
                    </div>
                    <div class="col-md-4 text-center mb-3">
                        <div style="font-size: 2rem; font-weight: bold; color: #28a745;">
                            <?= $workload['tasks_by_status']['done'] ?? 0 ?>
                        </div>
                        <small class="text-muted">Done</small>
                    </div>
                    <div class="col-md-4 text-center mb-3">
                        <div style="font-size: 2rem; font-weight: bold; color: #0d6efd;">
                            <?= ($workload['tasks_by_status']['todo'] ?? 0) + ($workload['tasks_by_status']['in_progress'] ?? 0) + ($workload['tasks_by_status']['review'] ?? 0) ?>
                        </div>
                        <small class="text-muted">Active Tasks</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Workload Summary -->
        <div class="card">
            <div class="card-header">Workload Summary</div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Active Tasks</span>
                        <strong><?= $workload['active_tasks'] ?? 0 ?></strong>
                    </div>
                    <small class="text-muted">Tasks in todo, in progress, or review status</small>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Hours This Week</span>
                        <strong><?= number_format($workload['hours_this_week'] ?? 0, 1) ?>h</strong>
                    </div>
                    <small class="text-muted">Total time logged this week</small>
                </div>

                <hr>

                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle"></i>
                    <strong>Workload Status:</strong>
                    <?php
                    $activeCount = $workload['active_tasks'] ?? 0;
                    if ($activeCount > 10) {
                        echo '<span class="text-danger">High - ' . $activeCount . ' active tasks</span>';
                    } elseif ($activeCount > 5) {
                        echo '<span class="text-warning">Medium - ' . $activeCount . ' active tasks</span>';
                    } else {
                        echo '<span class="text-success">Low - ' . $activeCount . ' active tasks</span>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Developer Info -->
        <div class="card">
            <div class="card-header">Developer Information</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">Username</label>
                    <p><?= esc($user->username ?? 'N/A') ?></p>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">Email</label>
                    <p><?= esc($user->email ?? 'N/A') ?></p>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">Status</label>
                    <p>
                        <?php if ($user->active): ?>
                        <span class="badge bg-success">Active</span>
                        <?php else: ?>
                        <span class="badge bg-secondary">Inactive</span>
                        <?php endif; ?>
                    </p>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">Last Active</label>
                    <p><?= $user->last_active ? date('M d, Y H:i', strtotime($user->last_active)) : 'Never' ?></p>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="card mt-3">
            <div class="card-header">Quick Stats</div>
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted">Workload Level</small>
                    <div class="progress" style="height: 20px;">
                        <?php
                        $activeCount = $workload['active_tasks'] ?? 0;
                        $percentage = min(100, ($activeCount / 15) * 100);
                        $color = $percentage > 66 ? 'danger' : ($percentage > 33 ? 'warning' : 'success');
                        ?>
                        <div class="progress-bar bg-<?= $color ?>" role="progressbar" style="width: <?= $percentage ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

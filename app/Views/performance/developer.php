<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>Developer Performance</h2>
        <p class="text-muted">Performance metrics and trends</p>
    </div>
    <a href="<?= base_url('performance') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Performance
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Performance Scores -->
        <div class="card mb-4">
            <div class="card-header">Performance Scores</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="text-center">
                            <h5>Overall Score</h5>
                            <div style="font-size: 2.5rem; font-weight: bold; color: #0d6efd;">
                                <?= number_format($scores['overall'] ?? 0, 1) ?>
                            </div>
                            <small class="text-muted">out of 100</small>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="text-center">
                            <h5>Deadline Score</h5>
                            <div style="font-size: 2.5rem; font-weight: bold; color: #198754;">
                                <?= number_format($scores['deadline'] ?? 0, 1) ?>
                            </div>
                            <small class="text-muted">On-time delivery</small>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="text-center">
                            <h5>Speed Score</h5>
                            <div style="font-size: 2.5rem; font-weight: bold; color: #0dcaf0;">
                                <?= number_format($scores['speed'] ?? 0, 1) ?>
                            </div>
                            <small class="text-muted">Task efficiency</small>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="text-center">
                            <h5>Engagement Score</h5>
                            <div style="font-size: 2.5rem; font-weight: bold; color: #fd7e14;">
                                <?= number_format($scores['engagement'] ?? 0, 1) ?>
                            </div>
                            <small class="text-muted">Activity & participation</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Trend -->
        <div class="card">
            <div class="card-header">Performance Trend (Last 6 Months)</div>
            <div class="card-body">
                <?php if (!empty($trend)): ?>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Tasks Completed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trend as $month): ?>
                        <tr>
                            <td><?= esc($month['month']) ?></td>
                            <td>
                                <span class="badge bg-primary"><?= $month['tasks_completed'] ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="text-muted text-center py-4">No trend data available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Score Breakdown -->
        <div class="card">
            <div class="card-header">Score Breakdown</div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Deadline Score</span>
                        <span class="badge bg-success"><?= number_format($scores['deadline'] ?? 0, 1) ?>%</span>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $scores['deadline'] ?? 0 ?>%"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Speed Score</span>
                        <span class="badge bg-info"><?= number_format($scores['speed'] ?? 0, 1) ?>%</span>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: <?= $scores['speed'] ?? 0 ?>%"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Engagement Score</span>
                        <span class="badge bg-warning"><?= number_format($scores['engagement'] ?? 0, 1) ?>%</span>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $scores['engagement'] ?? 0 ?>%"></div>
                    </div>
                </div>

                <hr>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span><strong>Overall Score</strong></span>
                        <span class="badge bg-primary"><?= number_format($scores['overall'] ?? 0, 1) ?>%</span>
                    </div>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $scores['overall'] ?? 0 ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="card mt-3">
            <div class="card-header">Score Legend</div>
            <div class="card-body small">
                <p class="mb-2">
                    <span class="badge bg-success">Deadline Score</span>
                    <br>
                    <small>Percentage of tasks completed on time</small>
                </p>
                <p class="mb-2">
                    <span class="badge bg-info">Speed Score</span>
                    <br>
                    <small>Task completion efficiency ratio</small>
                </p>
                <p class="mb-0">
                    <span class="badge bg-warning">Engagement Score</span>
                    <br>
                    <small>Activity, check-ins, and time tracking</small>
                </p>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

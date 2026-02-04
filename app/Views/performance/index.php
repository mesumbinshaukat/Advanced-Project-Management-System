<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2>Developer Performance</h2>
                <p class="text-muted">Performance scores based on deadlines, speed, and engagement</p>
            </div>
            <a href="<?= base_url('performance/update-all') ?>" class="btn btn-primary">
                <i class="bi bi-arrow-clockwise"></i> Update All Scores
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Developer</th>
                                <th>Overall Score</th>
                                <th>Deadline Score</th>
                                <th>Speed Score</th>
                                <th>Engagement Score</th>
                                <th>Tasks (30d)</th>
                                <th>Last Check-In</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($developers as $dev): ?>
                            <tr>
                                <td><strong><?= esc($dev['username']) ?></strong></td>
                                <td>
                                    <?php
                                    $scoreColor = $dev['performance_score'] >= 80 ? 'success' : ($dev['performance_score'] >= 60 ? 'warning' : 'danger');
                                    ?>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 20px; min-width: 100px;">
                                            <div class="progress-bar bg-<?= $scoreColor ?>" style="width: <?= $dev['performance_score'] ?>%">
                                                <?= round($dev['performance_score'], 1) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $dev['deadline_score'] >= 80 ? 'success' : ($dev['deadline_score'] >= 60 ? 'warning' : 'danger') ?>">
                                        <?= round($dev['deadline_score'], 1) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $dev['speed_score'] >= 80 ? 'success' : ($dev['speed_score'] >= 60 ? 'warning' : 'danger') ?>">
                                        <?= round($dev['speed_score'], 1) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $dev['engagement_score'] >= 80 ? 'success' : ($dev['engagement_score'] >= 60 ? 'warning' : 'danger') ?>">
                                        <?= round($dev['engagement_score'], 1) ?>
                                    </span>
                                </td>
                                <td><?= $dev['tasks_completed_30d'] ?></td>
                                <td>
                                    <?php if ($dev['last_check_in']): ?>
                                    <small><?= date('M d', strtotime($dev['last_check_in'])) ?></small>
                                    <?php else: ?>
                                    <small class="text-muted">Never</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= base_url('performance/developer/' . $dev['id']) ?>" class="btn btn-sm btn-outline-primary">
                                        Details
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle"></i> Performance Score Calculation
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6>Deadline Score (40%)</h6>
                        <p class="small text-muted">Based on on-time task completion rate over the last 30 days. Higher score for consistently meeting deadlines.</p>
                    </div>
                    <div class="col-md-4">
                        <h6>Speed Score (30%)</h6>
                        <p class="small text-muted">Measures efficiency by comparing estimated vs actual hours. Higher score for completing tasks faster than estimated.</p>
                    </div>
                    <div class="col-md-4">
                        <h6>Engagement Score (30%)</h6>
                        <p class="small text-muted">Based on daily check-ins, activity logs, and time entries. Higher score for consistent engagement.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

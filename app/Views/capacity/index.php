<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12">
        <h2>Capacity Forecasting</h2>
        <p class="text-muted">Team capacity analysis and hiring recommendations</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Current Team Size</h6>
                <h2 class="mb-0"><?= $forecast['current_capacity']['developer_count'] ?> <small class="text-muted">developers</small></h2>
                <small class="text-muted"><?= $forecast['current_capacity']['available_hours'] ?>h/week capacity</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Current Utilization</h6>
                <h2 class="mb-0"><?= $forecast['utilization']['utilization_rate'] ?>%</h2>
                <div class="progress mt-2" style="height: 10px;">
                    <div class="progress-bar bg-<?= $forecast['utilization']['utilization_rate'] > 90 ? 'danger' : ($forecast['utilization']['utilization_rate'] > 70 ? 'warning' : 'success') ?>" 
                         style="width: <?= min(100, $forecast['utilization']['utilization_rate']) ?>%"></div>
                </div>
                <small class="text-muted"><?= number_format($forecast['utilization']['hours_logged_this_week'], 1) ?>h logged this week</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Demand</h6>
                <h2 class="mb-0"><?= $forecast['demand']['active_tasks'] ?> <small class="text-muted">tasks</small></h2>
                <small class="text-muted"><?= number_format($forecast['demand']['total_hours_needed'], 0) ?>h estimated work</small>
            </div>
        </div>
    </div>
</div>

<?php if ($forecast['hiring_recommendation']['should_hire']): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-<?= $forecast['hiring_recommendation']['urgency'] === 'high' ? 'danger' : 'warning' ?> d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-3" style="font-size: 2rem;"></i>
            <div class="flex-grow-1">
                <h5 class="alert-heading mb-2">Hiring Recommendation</h5>
                <p class="mb-1"><strong>Recommended:</strong> Hire <?= $forecast['hiring_recommendation']['developers_needed'] ?> developer(s)</p>
                <p class="mb-0"><strong>Reason:</strong> <?= $forecast['hiring_recommendation']['reason'] ?></p>
                <p class="mb-0"><small><strong>Urgency:</strong> <?= ucfirst($forecast['hiring_recommendation']['urgency']) ?></small></p>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-success d-flex align-items-center">
            <i class="bi bi-check-circle-fill me-3" style="font-size: 2rem;"></i>
            <div>
                <h5 class="alert-heading mb-1">Capacity Status: Good</h5>
                <p class="mb-0"><?= $forecast['hiring_recommendation']['reason'] ?></p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-bar-chart"></i> Capacity Gap Analysis
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Available Capacity (per week)</h6>
                        <div class="progress mb-3" style="height: 30px;">
                            <div class="progress-bar bg-success" style="width: 100%">
                                <?= $forecast['current_capacity']['available_hours'] ?>h
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Required Capacity (estimated)</h6>
                        <div class="progress mb-3" style="height: 30px;">
                            <div class="progress-bar bg-<?= $forecast['capacity_gap'] > 0 ? 'danger' : 'success' ?>" 
                                 style="width: <?= min(100, ($forecast['demand']['total_hours_needed'] / max(1, $forecast['current_capacity']['available_hours'])) * 100) ?>%">
                                <?= number_format($forecast['demand']['total_hours_needed'], 0) ?>h
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <?php if ($forecast['capacity_gap'] > 0): ?>
                    <p class="text-danger mb-0">
                        <strong>Capacity Gap:</strong> <?= number_format($forecast['capacity_gap'], 0) ?> hours short
                    </p>
                    <?php else: ?>
                    <p class="text-success mb-0">
                        <strong>Capacity Surplus:</strong> <?= number_format(abs($forecast['capacity_gap']), 0) ?> hours available
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-folder"></i> Project Capacity Allocation
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>Active Tasks</th>
                                <th>Hours Needed</th>
                                <th>Weeks of Work</th>
                                <th>Capacity %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allocations as $allocation): ?>
                            <tr>
                                <td>
                                    <a href="<?= base_url('projects/view/' . $allocation['project_id']) ?>">
                                        <?= esc($allocation['project_name']) ?>
                                    </a>
                                </td>
                                <td><?= $allocation['active_tasks'] ?></td>
                                <td><?= number_format($allocation['hours_needed'], 0) ?>h</td>
                                <td><?= $allocation['weeks_of_work'] ?> weeks</td>
                                <td>
                                    <?php
                                    $percentage = ($allocation['hours_needed'] / max(1, $forecast['demand']['total_hours_needed'])) * 100;
                                    ?>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar" style="width: <?= $percentage ?>%">
                                            <?= round($percentage, 1) ?>%
                                        </div>
                                    </div>
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

<?= $this->endSection() ?>

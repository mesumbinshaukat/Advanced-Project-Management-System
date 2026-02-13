<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2><?= esc($project['name']) ?></h2>
                <p class="text-muted">Project Profitability Analysis</p>
            </div>
            <a href="<?= base_url('profitability') ?>" class="btn btn-secondary">Back to Overview</a>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h6 class="card-title">Total Revenue</h6>
                <h3 class="mb-0">$<?= number_format($profitability['total_revenue'] ?? 0, 2) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <h6 class="card-title">Total Cost</h6>
                <h3 class="mb-0">$<?= number_format($profitability['total_cost'] ?? 0, 2) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h6 class="card-title">Total Profit</h6>
                <h3 class="mb-0">$<?= number_format($profitability['total_profit'] ?? 0, 2) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-<?= ($profitability['profit_margin'] ?? 0) > 30 ? 'success' : (($profitability['profit_margin'] ?? 0) > 15 ? 'warning' : 'danger') ?>">
            <div class="card-body">
                <h6 class="card-title">Profit Margin</h6>
                <h3 class="mb-0"><?= number_format($profitability['profit_margin'] ?? 0, 1) ?>%</h3>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Project Details</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">Client</small>
                    <p class="mb-0"><?= $project['client_id'] ? esc($project['client_name'] ?? 'Unknown') : 'N/A' ?></p>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Status</small>
                    <p class="mb-0">
                        <span class="badge bg-<?= $project['status'] === 'completed' ? 'success' : ($project['status'] === 'active' ? 'info' : 'warning') ?>">
                            <?= ucfirst($project['status']) ?>
                        </span>
                    </p>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Start Date</small>
                    <p class="mb-0"><?= $project['start_date'] ? date('M d, Y', strtotime($project['start_date'])) : 'N/A' ?></p>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Deadline</small>
                    <p class="mb-0"><?= $project['deadline'] ? date('M d, Y', strtotime($project['deadline'])) : 'N/A' ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Financial Summary</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">Billing Type</small>
                    <p class="mb-0"><?= ucfirst($profitability['billing_type'] ?? 'N/A') ?></p>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Hourly Rate</small>
                    <p class="mb-0">$<?= number_format($profitability['hourly_rate'] ?? 0, 2) ?>/hr</p>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Total Hours Logged</small>
                    <p class="mb-0"><?= number_format($profitability['total_hours'] ?? 0, 1) ?> hours</p>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Billable Hours</small>
                    <p class="mb-0"><?= number_format($profitability['billable_hours'] ?? 0, 1) ?> hours</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Profitability Breakdown</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Metric</th>
                                <th class="text-end">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Total Revenue</td>
                                <td class="text-end text-primary fw-bold">$<?= number_format($profitability['total_revenue'] ?? 0, 2) ?></td>
                            </tr>
                            <tr>
                                <td>Total Cost</td>
                                <td class="text-end text-danger fw-bold">$<?= number_format($profitability['total_cost'] ?? 0, 2) ?></td>
                            </tr>
                            <tr>
                                <td>Gross Profit</td>
                                <td class="text-end text-success fw-bold">$<?= number_format($profitability['total_profit'] ?? 0, 2) ?></td>
                            </tr>
                            <tr>
                                <td>Profit Margin</td>
                                <td class="text-end fw-bold"><?= number_format($profitability['profit_margin'] ?? 0, 1) ?>%</td>
                            </tr>
                            <tr>
                                <td>ROI</td>
                                <td class="text-end fw-bold"><?= number_format($profitability['roi'] ?? 0, 1) ?>%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

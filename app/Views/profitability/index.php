<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12">
        <h2>Profitability Dashboard</h2>
        <p class="text-muted">Financial performance and project profitability analysis</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h6 class="card-title">Total Revenue</h6>
                <h3 class="mb-0">$<?= number_format($overall['total_revenue'], 2) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <h6 class="card-title">Total Cost</h6>
                <h3 class="mb-0">$<?= number_format($overall['total_cost'], 2) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h6 class="card-title">Total Profit</h6>
                <h3 class="mb-0">$<?= number_format($overall['total_profit'], 2) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-<?= $overall['profit_margin'] > 30 ? 'success' : ($overall['profit_margin'] > 15 ? 'warning' : 'danger') ?>">
            <div class="card-body">
                <h6 class="card-title">Profit Margin</h6>
                <h3 class="mb-0"><?= $overall['profit_margin'] ?>%</h3>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-graph-up"></i> Profitability Trend (Last 6 Months)
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Hours Logged</th>
                                <th>Billable Hours</th>
                                <th>Revenue</th>
                                <th>Cost</th>
                                <th>Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($trend as $month): ?>
                            <tr>
                                <td><?= $month['month'] ?></td>
                                <td><?= number_format($month['hours_logged'], 1) ?>h</td>
                                <td><?= number_format($month['billable_hours'], 1) ?>h</td>
                                <td class="text-primary">$<?= number_format($month['revenue'], 2) ?></td>
                                <td class="text-danger">$<?= number_format($month['cost'], 2) ?></td>
                                <td class="fw-bold <?= $month['profit'] > 0 ? 'text-success' : 'text-danger' ?>">
                                    $<?= number_format($month['profit'], 2) ?>
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

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-trophy"></i> Top Profitable Projects
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>Billing Type</th>
                                <th>Hours Logged</th>
                                <th>Billable Hours</th>
                                <th>Revenue</th>
                                <th>Cost</th>
                                <th>Profit</th>
                                <th>Margin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_projects as $project): ?>
                            <tr>
                                <td>
                                    <a href="<?= base_url('projects/view/' . $project['id']) ?>">
                                        <?= esc($project['name']) ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= ucfirst($project['billing_type']) ?></span>
                                </td>
                                <td><?= number_format($project['hours_logged'], 1) ?>h</td>
                                <td><?= number_format($project['billable_hours'], 1) ?>h</td>
                                <td class="text-primary">$<?= number_format($project['revenue'], 2) ?></td>
                                <td class="text-danger">$<?= number_format($project['cost'], 2) ?></td>
                                <td class="fw-bold <?= $project['profit'] > 0 ? 'text-success' : 'text-danger' ?>">
                                    $<?= number_format($project['profit'], 2) ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $project['profit_margin'] > 30 ? 'success' : ($project['profit_margin'] > 15 ? 'warning' : 'danger') ?>">
                                        <?= $project['profit_margin'] ?>%
                                    </span>
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

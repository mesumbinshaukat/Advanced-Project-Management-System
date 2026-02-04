<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2>Automated Alerts</h2>
                <p class="text-muted">System-generated alerts for risks and inactivity</p>
            </div>
            <?php if (auth()->user()->inGroup('admin')): ?>
            <a href="<?= base_url('alerts/generate') ?>" class="btn btn-primary">
                <i class="bi bi-arrow-clockwise"></i> Generate Alerts
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (empty($alerts)): ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                <h4 class="mt-3">No Active Alerts</h4>
                <p class="text-muted">Everything is running smoothly!</p>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="row">
    <div class="col-12">
        <?php
        $groupedAlerts = [];
        foreach ($alerts as $alert) {
            $groupedAlerts[$alert['severity']][] = $alert;
        }
        ?>
        
        <?php foreach (['critical', 'high', 'medium', 'low'] as $severity): ?>
            <?php if (isset($groupedAlerts[$severity])): ?>
            <div class="card mb-3">
                <div class="card-header bg-<?= $severity === 'critical' ? 'danger' : ($severity === 'high' ? 'warning' : ($severity === 'medium' ? 'info' : 'secondary')) ?> text-white">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?= ucfirst($severity) ?> Alerts (<?= count($groupedAlerts[$severity]) ?>)
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php foreach ($groupedAlerts[$severity] as $alert): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <?php
                                        $typeIcons = [
                                            'deadline_risk' => 'calendar-x',
                                            'inactivity' => 'person-x',
                                            'overload' => 'exclamation-triangle',
                                            'budget_risk' => 'currency-dollar',
                                            'performance_drop' => 'graph-down',
                                            'blocker' => 'stop-circle',
                                        ];
                                        ?>
                                        <i class="bi bi-<?= $typeIcons[$alert['type']] ?? 'bell' ?> me-2"></i>
                                        <strong><?= esc($alert['title']) ?></strong>
                                        <span class="badge bg-secondary ms-2"><?= ucfirst(str_replace('_', ' ', $alert['type'])) ?></span>
                                    </div>
                                    <p class="mb-2"><?= esc($alert['message']) ?></p>
                                    <small class="text-muted">
                                        <i class="bi bi-clock"></i> <?= date('M d, Y H:i', strtotime($alert['created_at'])) ?>
                                    </small>
                                </div>
                                <div class="ms-3">
                                    <?php if ($alert['action_url']): ?>
                                    <a href="<?= base_url($alert['action_url']) ?>" class="btn btn-sm btn-outline-primary mb-2">
                                        <i class="bi bi-arrow-right"></i> View
                                    </a>
                                    <?php endif; ?>
                                    <a href="<?= base_url('alerts/resolve/' . $alert['id']) ?>" class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-check"></i> Resolve
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

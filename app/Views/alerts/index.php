<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<script>
console.log('=== ALERTS DEBUG SCRIPT ===');
console.log('Current user is admin:', <?= auth()->user()->inGroup('admin') ? 'true' : 'false' ?>);
console.log('Current user ID:', <?= auth()->id() ?>);
console.log('Current user username:', '<?= auth()->user()->username ?>');
console.log('Alerts count received:', <?= count($alerts) ?>);
console.log('Alerts data:', <?= json_encode($alerts) ?>);

// Detailed alert inspection
const alertsData = <?= json_encode($alerts) ?>;
console.log('Detailed alert inspection:');
if (alertsData && alertsData.length > 0) {
    alertsData.forEach((alert, index) => {
        console.log(`Alert ${index + 1}:`, {
            id: alert.id,
            type: alert.type,
            severity: alert.severity,
            title: alert.title,
            message: alert.message,
            entity_type: alert.entity_type,
            entity_id: alert.entity_id,
            is_resolved: alert.is_resolved
        });
    });
} else {
    console.log('No alerts in array or alerts is null/undefined');
}

// Check database queries for developer alerts
console.log('Checking developer alert access...');
console.log('User should see alerts for:');
console.log('1. Alerts directly assigned to user_id:', <?= auth()->id() ?>);
console.log('2. Alerts for projects where user is assigned');
console.log('3. Alerts for tasks where user is assigned');

// Test if there are any alerts in the system at all
fetch('<?= base_url('alerts/generate') ?>', {
    method: 'GET',
    headers: {
        'X-Requested-With': 'XMLHttpRequest'
    },
    credentials: 'same-origin'
})
.then(response => {
    console.log('Generate alerts response status:', response.status);
    if (response.status === 403) {
        console.log('User cannot generate alerts (expected for developers)');
    } else {
        console.log('User can generate alerts or other response');
    }
})
.catch(error => {
    console.log('Generate alerts test error (expected):', error.message);
});

console.log('=== END ALERTS DEBUG SCRIPT ===');
</script>

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

<?php 
// Debug output to understand the issue
echo "<!-- DEBUG: alerts variable type: " . gettype($alerts) . " -->";
echo "<!-- DEBUG: alerts count: " . count($alerts) . " -->";
echo "<!-- DEBUG: alerts empty check: " . (empty($alerts) ? 'true' : 'false') . " -->";
echo "<!-- DEBUG: alerts is_array: " . (is_array($alerts) ? 'true' : 'false') . " -->";
if (is_array($alerts) && !empty($alerts)) {
    echo "<!-- DEBUG: First alert keys: " . implode(', ', array_keys($alerts[0])) . " -->";
}
?>
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
            // Handle empty or null severity by defaulting to 'info'
            $severity = !empty($alert['severity']) ? $alert['severity'] : 'info';
            $groupedAlerts[$severity][] = $alert;
        }
        ?>
        
        <?php foreach (['critical', 'high', 'medium', 'low', 'info'] as $severity): ?>
            <?php if (isset($groupedAlerts[$severity])): ?>
            <div class="card mb-3">
                <?php
                $headerClass = 'secondary';
                if ($severity === 'critical') {
                    $headerClass = 'danger';
                } elseif ($severity === 'high') {
                    $headerClass = 'warning';
                } elseif ($severity === 'medium') {
                    $headerClass = 'info';
                } elseif ($severity === 'info') {
                    $headerClass = 'primary';
                }
                ?>
                <div class="card-header bg-<?= $headerClass ?> text-white">
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

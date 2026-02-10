<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>Team Check-Ins</h2>
        <p class="text-muted">Daily standup for <?= date('M d, Y', strtotime($date)) ?></p>
    </div>
    <a href="<?= base_url('check-in') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> My Check-In
    </a>
</div>

<div class="row mb-4">
    <div class="col-12">
        <form method="get" action="<?= base_url('check-in/team') ?>" class="d-flex gap-2">
            <input type="date" name="date" value="<?= $date ?>" class="form-control" style="max-width: 200px;">
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>
</div>

<?php if (empty($check_ins)): ?>
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-chat-left-text text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mt-3">No check-ins for this date</p>
    </div>
</div>
<?php else: ?>
<div class="row">
    <?php foreach ($check_ins as $checkIn): ?>
    <div class="col-lg-6 mb-3">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <strong><?= esc($checkIn['username'] ?? 'Unknown') ?></strong>
                    <span class="badge bg-<?= $checkIn['mood'] === 'great' ? 'success' : ($checkIn['mood'] === 'good' ? 'info' : ($checkIn['mood'] === 'okay' ? 'warning' : 'danger')) ?>">
                        <?= ucfirst($checkIn['mood'] ?? 'unknown') ?>
                    </span>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($checkIn['achievements'])): ?>
                <div class="mb-3">
                    <h6 class="text-success">✓ Achievements</h6>
                    <p class="mb-0"><?= nl2br(esc($checkIn['achievements'])) ?></p>
                </div>
                <?php endif; ?>

                <?php if (!empty($checkIn['plans'])): ?>
                <div class="mb-3">
                    <h6 class="text-primary">→ Plans</h6>
                    <p class="mb-0"><?= nl2br(esc($checkIn['plans'])) ?></p>
                </div>
                <?php endif; ?>

                <?php if (!empty($checkIn['blockers'])): ?>
                <div class="mb-3">
                    <h6 class="text-danger">⚠ Blockers</h6>
                    <p class="mb-0"><?= nl2br(esc($checkIn['blockers'])) ?></p>
                </div>
                <?php endif; ?>

                <small class="text-muted d-block mt-3">
                    <i class="bi bi-clock"></i> <?= date('H:i', strtotime($checkIn['created_at'])) ?>
                </small>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

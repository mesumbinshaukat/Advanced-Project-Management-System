<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>Team Check-Ins</h2>
        <p class="text-muted mb-0">Review who has checked in today and fix times when needed.</p>
    </div>
    <form class="d-flex align-items-center gap-2" method="get" action="<?= base_url('check-in/team') ?>">
        <label for="checkin_date" class="form-label mb-0">Date</label>
        <input type="date" id="checkin_date" name="date" class="form-control" value="<?= esc($date) ?>">
        <button type="submit" class="btn btn-outline-primary">Go</button>
    </form>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card success">
            <div class="stat-value"><?= $stats['checked_in'] ?></div>
            <div class="stat-label">Checked In</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card warning">
            <div class="stat-value"><?= $stats['checked_out'] ?></div>
            <div class="stat-label">Checked Out</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card danger">
            <div class="stat-value"><?= $stats['missing'] ?></div>
            <div class="stat-label">Missing</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-value"><?= $stats['total'] ?></div>
            <div class="stat-label">Developers</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-people"></i> <?= esc(date('l, F j, Y', strtotime($date))) ?></span>
        <span class="text-muted">Times are local server time (<?= date('T') ?>)</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Developer</th>
                        <th>Status</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th>Mood</th>
                        <th class="text-end">Adjust Times</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($developers as $developer): ?>
                        <?php $entry = $check_ins[$developer['id']] ?? null; ?>
                        <?php
                        $status = 'Not Checked In';
                        $badge = 'secondary';
                        if ($entry) {
                            if (!empty($entry['checked_out_at'])) {
                                $status = 'Checked Out';
                                $badge = 'success';
                            } elseif (!empty($entry['checked_in_at'])) {
                                $status = !empty($entry['checkout_ready']) ? 'Ready to Check Out' : 'Checked In';
                                $badge = !empty($entry['checkout_ready']) ? 'warning' : 'info';
                            }
                        }
                        ?>
                        <tr>
                            <td><strong><?= esc($developer['username']) ?></strong></td>
                            <td>
                                <span class="badge bg-<?= $badge ?>"><?= esc($status) ?></span>
                            </td>
                            <td><?= !empty($entry['checked_in_at']) ? date('g:i A', strtotime($entry['checked_in_at'])) : '<span class="text-muted">-</span>' ?></td>
                            <td><?= !empty($entry['checked_out_at']) ? date('g:i A', strtotime($entry['checked_out_at'])) : '<span class="text-muted">-</span>' ?></td>
                            <td><?= !empty($entry['mood']) ? ucfirst($entry['mood']) : '<span class="text-muted">n/a</span>' ?></td>
                            <td class="text-end">
                                <form class="row g-2 align-items-center" method="post" action="<?= base_url('check-in/update-times') ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="check_in_id" value="<?= esc($entry['id'] ?? '') ?>">
                                    <input type="hidden" name="user_id" value="<?= esc($developer['id']) ?>">
                                    <input type="hidden" name="check_in_date" value="<?= esc($date) ?>">
                                    <div class="col-md-4">
                                        <label class="form-label small mb-0">Check-In</label>
                                        <input type="datetime-local" name="checked_in_at" class="form-control form-control-sm" value="<?= !empty($entry['checked_in_at']) ? date('Y-m-d\TH:i', strtotime($entry['checked_in_at'])) : '' ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small mb-0">Check-Out</label>
                                        <input type="datetime-local" name="checked_out_at" class="form-control form-control-sm" value="<?= !empty($entry['checked_out_at']) ? date('Y-m-d\TH:i', strtotime($entry['checked_out_at'])) : '' ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-check mt-4">
                                            <input class="form-check-input" type="checkbox" name="checkout_ready" value="1" id="checkout_ready_<?= $developer['id'] ?>" <?= !empty($entry['checkout_ready']) ? 'checked' : '' ?>>
                                            <label class="form-check-label small" for="checkout_ready_<?= $developer['id'] ?>">
                                                Ready
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <button type="submit" class="btn btn-sm btn-outline-primary mt-4">
                                            Save
                                        </button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

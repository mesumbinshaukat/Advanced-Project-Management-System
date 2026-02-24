<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2>Daily Check-In</h2>
                <p class="text-muted">Share your progress and blockers</p>
            </div>
            <div>
                <span class="badge bg-primary" style="font-size: 1.2rem;">
                    <i class="bi bi-fire"></i> <?= $streak ?> day streak
                </span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar-check"></i> Today's Check-In (<?= date('l, F j, Y') ?>)</span>
                <?php if (!empty($today_check_in['checked_in_at'])): ?>
                <span class="badge bg-light text-primary">
                    Checked in at <?= date('g:i A', strtotime($today_check_in['checked_in_at'])) ?>
                </span>
                <?php endif; ?>

<?= $this->section('scripts') ?>
<script>
    (function () {
        const pad = (value) => String(value).padStart(2, '0');
        const formatLocalDateTime = (date) => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} ${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(date.getSeconds())}`;

        const checkInForm = document.getElementById('checkInForm');
        if (checkInForm) {
            checkInForm.addEventListener('submit', () => {
                const hidden = document.getElementById('client_checked_in_at');
                if (hidden) {
                    hidden.value = formatLocalDateTime(new Date());
                }
            });
        }

        document.querySelectorAll('.checkout-form').forEach((form) => {
            form.addEventListener('submit', () => {
                const hidden = form.querySelector('input[name="client_checked_out_at"]');
                if (hidden) {
                    hidden.value = formatLocalDateTime(new Date());
                }
            });
        });
    })();
</script>
<?= $this->endSection() ?>
            </div>
            <div class="card-body">
                <?php if (!empty($has_checked_out)): ?>
                <div class="alert alert-success d-flex justify-content-between align-items-center">
                    <div>
                        <strong>You're all set!</strong> Checked out at <?= date('g:i A', strtotime($today_check_in['checked_out_at'])) ?>.
                        <div class="small text-muted">Come back tomorrow to check in again.</div>
                    </div>
                    <i class="bi bi-check-circle-fill fs-3"></i>
                </div>
                <?php elseif (!empty($today_check_in)): ?>
                <div class="alert alert-info d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Checked in at <?= date('g:i A', strtotime($today_check_in['checked_in_at'])) ?></strong>
                        <?php if (!empty($today_check_in['checkout_ready']) && empty($today_check_in['checked_out_at'])): ?>
                        <div class="small text-muted">Update your notes any time, then check out when you're done.</div>
                        <?php else: ?>
                        <div class="small text-muted">Checkout becomes available on your next check-in cycle.</div>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($can_checkout)): ?>
                    <form class="ms-3 checkout-form" action="<?= base_url('check-in/checkout') ?>" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="client_checked_out_at" value="">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-box-arrow-right"></i> Check Out
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (empty($has_checked_out)): ?>
                <form id="checkInForm" action="<?= base_url('check-in/store') ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="client_checked_in_at" id="client_checked_in_at">
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">How are you feeling today?</label>
                        <div class="btn-group w-100" role="group">
                            <?php
                            $moods = [
                                'great' => ['icon' => 'emoji-smile-fill', 'color' => 'success', 'label' => 'Great'],
                                'good' => ['icon' => 'emoji-smile', 'color' => 'info', 'label' => 'Good'],
                                'okay' => ['icon' => 'emoji-neutral', 'color' => 'secondary', 'label' => 'Okay'],
                                'struggling' => ['icon' => 'emoji-frown', 'color' => 'warning', 'label' => 'Struggling'],
                                'blocked' => ['icon' => 'emoji-dizzy', 'color' => 'danger', 'label' => 'Blocked'],
                            ];
                            $currentMood = $today_check_in['mood'] ?? 'okay';
                            ?>
                            <?php foreach ($moods as $value => $mood): ?>
                            <input type="radio" class="btn-check" name="mood" id="mood_<?= $value ?>" value="<?= $value ?>" <?= $currentMood === $value ? 'checked' : '' ?> required>
                            <label class="btn btn-outline-<?= $mood['color'] ?>" for="mood_<?= $value ?>">
                                <i class="bi bi-<?= $mood['icon'] ?>"></i><br>
                                <small><?= $mood['label'] ?></small>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">What did you accomplish yesterday?</label>
                        <textarea name="yesterday_accomplishments" class="form-control" rows="3" placeholder="List your accomplishments..."><?= $today_check_in['yesterday_accomplishments'] ?? '' ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">What's your plan for today?</label>
                        <textarea name="today_plan" class="form-control" rows="3" placeholder="What will you work on today?" required><?= $today_check_in['today_plan'] ?? '' ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Any blockers or challenges?</label>
                        <textarea name="blockers" class="form-control" rows="2" placeholder="Describe any blockers (optional)"><?= $today_check_in['blockers'] ?? '' ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-check-circle"></i> <?= !empty($today_check_in) ? 'Update Check-In' : 'Submit Check-In' ?>
                    </button>
                </form>
                <?php else: ?>
                <p class="text-muted text-center mb-0">Check-in edits are locked after checkout. See you tomorrow!</p>
                <?php endif; ?>
            </div>
        </div>
        <?php if (!empty($today_check_in['checked_out_at'])): ?>
        <div class="card">
            <div class="card-header"><i class="bi bi-journal-check"></i> Today's Summary</div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small">Checked In</div>
                        <div class="fw-bold"><?= date('g:i A', strtotime($today_check_in['checked_in_at'])) ?></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small">Checked Out</div>
                        <div class="fw-bold"><?= date('g:i A', strtotime($today_check_in['checked_out_at'])) ?></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small">Mood</div>
                        <div class="fw-bold text-capitalize"><?= esc($today_check_in['mood'] ?? 'n/a') ?></div>
                    </div>
                </div>
                <?php if (!empty($today_check_in['today_plan'])): ?>
                <div class="mb-2">
                    <strong>Today's plan:</strong>
                    <div class="text-muted"><?= esc($today_check_in['today_plan']) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($today_check_in['blockers'])): ?>
                <div class="mb-0">
                    <strong>Blockers:</strong>
                    <div class="text-danger"><?= esc($today_check_in['blockers']) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history"></i> Recent Check-Ins
            </div>
            <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                <?php if (empty($recent_check_ins)): ?>
                <p class="text-muted text-center py-4">No recent check-ins</p>
                <?php else: ?>
                <?php foreach ($recent_check_ins as $checkIn): ?>
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong><?= date('M d, Y', strtotime($checkIn['check_in_date'])) ?></strong>
                        <?php
                        $moodColors = ['great' => 'success', 'good' => 'info', 'okay' => 'secondary', 'struggling' => 'warning', 'blocked' => 'danger'];
                        ?>
                        <span class="badge bg-<?= $moodColors[$checkIn['mood']] ?>">
                            <?= ucfirst($checkIn['mood']) ?>
                        </span>
                    </div>
                    <?php if ($checkIn['today_plan']): ?>
                    <small class="text-muted d-block mb-1"><strong>Plan:</strong> <?= esc(substr($checkIn['today_plan'], 0, 100)) ?><?= strlen($checkIn['today_plan']) > 100 ? '...' : '' ?></small>
                    <?php endif; ?>
                    <?php if ($checkIn['blockers']): ?>
                    <small class="text-danger d-block"><strong>Blockers:</strong> <?= esc(substr($checkIn['blockers'], 0, 100)) ?></small>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <?php if (auth()->user()->inGroup('admin')): ?>
        <div class="card mt-3">
            <div class="card-header">
                <i class="bi bi-people"></i> Team Check-Ins
            </div>
            <div class="card-body">
                <a href="<?= base_url('check-in/team') ?>" class="btn btn-outline-primary w-100">
                    View Team Check-Ins
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

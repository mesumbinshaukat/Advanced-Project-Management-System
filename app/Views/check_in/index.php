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
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-calendar-check"></i> Today's Check-In (<?= date('l, F j, Y') ?>)
            </div>
            <div class="card-body">
                <form action="<?= base_url('check-in/store') ?>" method="post">
                    <?= csrf_field() ?>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">How are you feeling today?</label>
                        <div class="btn-group w-100" role="group">
                            <?php
                            $moods = [
                                'great' => ['icon' => 'emoji-smile-fill', 'color' => 'success', 'label' => 'Great'],
                                'good' => ['icon' => 'emoji-smile', 'color' => 'info', 'label' => 'Good'],
                                'okay' => ['icon' => 'emoji-neutral', 'color' => 'secondary', 'label' => 'Okay'],
                                'bad' => ['icon' => 'emoji-frown', 'color' => 'warning', 'label' => 'Bad'],
                                'terrible' => ['icon' => 'emoji-dizzy', 'color' => 'danger', 'label' => 'Terrible'],
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
                        <label class="form-label fw-bold">Productivity Level</label>
                        <select name="productivity" class="form-select" required>
                            <option value="">Select productivity level...</option>
                            <option value="1" <?= ($today_check_in['productivity'] ?? '') === '1' ? 'selected' : '' ?>>Very Low</option>
                            <option value="2" <?= ($today_check_in['productivity'] ?? '') === '2' ? 'selected' : '' ?>>Low</option>
                            <option value="3" <?= ($today_check_in['productivity'] ?? '') === '3' ? 'selected' : '' ?>>Medium</option>
                            <option value="4" <?= ($today_check_in['productivity'] ?? '') === '4' ? 'selected' : '' ?>>High</option>
                            <option value="5" <?= ($today_check_in['productivity'] ?? '') === '5' ? 'selected' : '' ?>>Very High</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">What did you accomplish?</label>
                        <textarea name="achievements" class="form-control" rows="3" placeholder="List your accomplishments..."><?= $today_check_in['achievements'] ?? '' ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">What's your plan?</label>
                        <textarea name="plans" class="form-control" rows="3" placeholder="What will you work on?" required><?= $today_check_in['plans'] ?? '' ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Any blockers or challenges?</label>
                        <textarea name="blockers" class="form-control" rows="2" placeholder="Describe any blockers (optional)"><?= $today_check_in['blockers'] ?? '' ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-check-circle"></i> Submit Check-In
                    </button>
                </form>
            </div>
        </div>
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
                        <strong><?= date('M d, Y', strtotime($checkIn['date'])) ?></strong>
                        <?php
                        $moodColors = ['great' => 'success', 'good' => 'info', 'okay' => 'secondary', 'bad' => 'warning', 'terrible' => 'danger'];
                        ?>
                        <span class="badge bg-<?= $moodColors[$checkIn['mood']] ?>">
                            <?= ucfirst($checkIn['mood']) ?>
                        </span>
                    </div>
                    <?php if ($checkIn['plans']): ?>
                    <small class="text-muted d-block mb-1"><strong>Plan:</strong> <?= esc(substr($checkIn['plans'], 0, 100)) ?><?= strlen($checkIn['plans']) > 100 ? '...' : '' ?></small>
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

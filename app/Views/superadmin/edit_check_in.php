<?= $this->extend('layouts/superadmin') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4 px-3 px-md-4">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <h1 class="h3 mb-0">Edit Check-in</h1>
        <a href="<?= base_url('/x9k2m8p5q7/dashboard') ?>" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="post" action="<?= base_url('/x9k2m8p5q7/edit-check-in/' . $checkin['id']) ?>" novalidate>
                        <?= csrf_field() ?>
                        
                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <label for="check_in_date" class="form-label">Check-in Date</label>
                                <input type="date" class="form-control" id="check_in_date" 
                                       name="check_in_date" value="<?= $checkin['check_in_date'] ?>" required>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label for="mood" class="form-label">Mood</label>
                                <select class="form-select" id="mood" name="mood">
                                    <option value="">Select mood</option>
                                    <option value="happy" <?= $checkin['mood'] === 'happy' ? 'selected' : '' ?>>Happy</option>
                                    <option value="neutral" <?= $checkin['mood'] === 'neutral' ? 'selected' : '' ?>>Neutral</option>
                                    <option value="stressed" <?= $checkin['mood'] === 'stressed' ? 'selected' : '' ?>>Stressed</option>
                                    <option value="tired" <?= $checkin['mood'] === 'tired' ? 'selected' : '' ?>>Tired</option>
                                    <option value="excited" <?= $checkin['mood'] === 'excited' ? 'selected' : '' ?>>Excited</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="achievements" class="form-label">Achievements</label>
                            <textarea class="form-control" id="achievements" name="achievements" 
                                      rows="3"><?= esc($checkin['achievements']) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="plans" class="form-label">Plans</label>
                            <textarea class="form-control" id="plans" name="plans" 
                                      rows="3"><?= esc($checkin['plans']) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="blockers" class="form-label">Blockers</label>
                            <textarea class="form-control" id="blockers" name="blockers" 
                                      rows="3"><?= esc($checkin['blockers']) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" 
                                      rows="3"><?= esc($checkin['notes']) ?></textarea>
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <div class="mb-3">
                                <small class="text-muted d-block">
                                    User: <strong><?= esc($checkin['username']) ?></strong>
                                </small>
                                <small class="text-muted d-block">
                                    Check-in ID: <?= $checkin['id'] ?>
                                </small>
                            </div>
                            <div class="d-flex flex-column flex-sm-row gap-2">
                                <button type="submit" class="btn btn-primary">Update Check-in</button>
                                <a href="<?= base_url('/x9k2m8p5q7/dashboard') ?>" class="btn btn-outline-secondary">Cancel</a>
                                <form method="post" action="<?= base_url('/x9k2m8p5q7/delete-check-in/' . $checkin['id']) ?>" 
                                      style="display:inline;" onsubmit="return confirm('Delete this check-in?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-outline-danger">Delete</button>
                                </form>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Check-in Details</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted d-block">User</small>
                        <strong><?= esc($checkin['username']) ?></strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">Date</small>
                        <strong><?= $checkin['check_in_date'] ?></strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">Created</small>
                        <strong><?= $checkin['created_at'] ?></strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">Updated</small>
                        <strong><?= $checkin['updated_at'] ?></strong>
                    </div>
                    <div>
                        <small class="text-muted d-block">Check-in ID</small>
                        <strong><?= $checkin['id'] ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

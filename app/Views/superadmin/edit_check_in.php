<?= $this->extend('layouts/superadmin') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Edit Check-in</h1>
        <a href="<?= base_url('/x9k2m8p5q7/dashboard') ?>" class="btn btn-outline-secondary">Back to Dashboard</a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="post" action="<?= base_url('/x9k2m8p5q7/edit-check-in/' . $checkin['id']) ?>">
                        <?= csrf_field() ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="check_in_date" class="form-label">Check-in Date</label>
                                <input type="date" class="form-control" id="check_in_date" 
                                       name="check_in_date" value="<?= $checkin['check_in_date'] ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="mood" class="form-label">Mood</label>
                                <select class="form-control" id="mood" name="mood">
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

                        <div class="d-flex justify-content-between">
                            <div>
                                <small class="text-muted">
                                    User: <strong><?= esc($checkin['username']) ?></strong> | 
                                    Check-in ID: <?= $checkin['id'] ?>
                                </small>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary">Update Check-in</button>
                                <a href="<?= base_url('/x9k2m8p5q7/dashboard') ?>" class="btn btn-outline-secondary">Cancel</a>
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
                    <p><strong>User:</strong> <?= esc($checkin['username']) ?></p>
                    <p><strong>Date:</strong> <?= $checkin['check_in_date'] ?></p>
                    <p><strong>Created:</strong> <?= $checkin['created_at'] ?></p>
                    <p><strong>Updated:</strong> <?= $checkin['updated_at'] ?></p>
                    <p><strong>Check-in ID:</strong> <?= $checkin['id'] ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

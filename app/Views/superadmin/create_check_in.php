<?= $this->extend('layouts/superadmin') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4 px-3 px-md-4">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <h1 class="h3 mb-0">Create Check-in</h1>
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
                    <form method="post" action="<?= base_url('/x9k2m8p5q7/create-check-in') ?>" novalidate>
                        <?= csrf_field() ?>
                        
                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <label for="user_id" class="form-label">User</label>
                                <select class="form-select" id="user_id" name="user_id" required>
                                    <option value="">-- Select a user --</option>
                                    <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['id'] ?>"><?= esc($user['username']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label for="check_in_date" class="form-label">Check-in Date</label>
                                <input type="date" class="form-control" id="check_in_date" 
                                       name="check_in_date" value="<?= old('check_in_date') ?>" required>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <label for="mood" class="form-label">Mood</label>
                                <select class="form-select" id="mood" name="mood">
                                    <option value="">-- Select mood --</option>
                                    <option value="Great" <?= old('mood') === 'Great' ? 'selected' : '' ?>>Great</option>
                                    <option value="Good" <?= old('mood') === 'Good' ? 'selected' : '' ?>>Good</option>
                                    <option value="Neutral" <?= old('mood') === 'Neutral' ? 'selected' : '' ?>>Neutral</option>
                                    <option value="Bad" <?= old('mood') === 'Bad' ? 'selected' : '' ?>>Bad</option>
                                    <option value="Terrible" <?= old('mood') === 'Terrible' ? 'selected' : '' ?>>Terrible</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="achievements" class="form-label">Achievements</label>
                            <textarea class="form-control" id="achievements" name="achievements" 
                                      rows="2"><?= old('achievements') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="plans" class="form-label">Plans</label>
                            <textarea class="form-control" id="plans" name="plans" 
                                      rows="2"><?= old('plans') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="blockers" class="form-label">Blockers</label>
                            <textarea class="form-control" id="blockers" name="blockers" 
                                      rows="2"><?= old('blockers') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" 
                                      rows="2"><?= old('notes') ?></textarea>
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex flex-column flex-sm-row gap-2">
                                <button type="submit" class="btn btn-primary">Create Check-in</button>
                                <a href="<?= base_url('/x9k2m8p5q7/dashboard') ?>" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

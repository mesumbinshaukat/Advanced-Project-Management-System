<?= $this->extend('layouts/superadmin') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4 px-3 px-md-4">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <h1 class="h3 mb-0">Edit Time Entry</h1>
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
                    <form method="post" action="<?= base_url('/x9k2m8p5q7/edit-time-entry/' . $entry['id']) ?>" novalidate>
                        <?= csrf_field() ?>
                        
                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <label for="hours" class="form-label">Hours</label>
                                <input type="number" min="0.1" step="any" class="form-control" id="hours" 
                                       name="hours" value="<?= $entry['hours'] ?>" required>
                                <small class="text-muted d-block mt-1">Enter hours (e.g., 2.63, 2.5, 3)</small>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label for="date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="date" 
                                       name="date" value="<?= $entry['date'] ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="3" required><?= esc($entry['description']) ?></textarea>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <label for="task_id" class="form-label">Task ID (optional)</label>
                                <input type="number" class="form-control" id="task_id" 
                                       name="task_id" value="<?= $entry['task_id'] ?>">
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="is_billable" 
                                           name="is_billable" <?= $entry['is_billable'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_billable">
                                        Billable
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <div class="mb-3">
                                <small class="text-muted d-block">
                                    User: <strong><?= esc($entry['username']) ?></strong>
                                </small>
                                <small class="text-muted d-block">
                                    Entry ID: <?= $entry['id'] ?>
                                </small>
                            </div>
                            <div class="d-flex flex-column flex-sm-row gap-2">
                                <button type="submit" class="btn btn-primary">Update Entry</button>
                                <a href="<?= base_url('/x9k2m8p5q7/dashboard') ?>" class="btn btn-outline-secondary">Cancel</a>
                                <form method="post" action="<?= base_url('/x9k2m8p5q7/delete-time-entry/' . $entry['id']) ?>" 
                                      style="display:inline;" onsubmit="return confirm('Delete this time entry?');">
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
                    <h5 class="mb-0">Entry Details</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted d-block">User</small>
                        <strong><?= esc($entry['username']) ?></strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">Created</small>
                        <strong><?= $entry['created_at'] ?></strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">Updated</small>
                        <strong><?= $entry['updated_at'] ?></strong>
                    </div>
                    <div>
                        <small class="text-muted d-block">Entry ID</small>
                        <strong><?= $entry['id'] ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

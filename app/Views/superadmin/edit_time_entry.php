<?= $this->extend('layouts/superadmin') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Edit Time Entry</h1>
        <a href="<?= base_url('/x9k2m8p5q7/dashboard') ?>" class="btn btn-outline-secondary">Back to Dashboard</a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="post" action="<?= base_url('/x9k2m8p5q7/edit-time-entry/' . $entry['id']) ?>">
                        <?= csrf_field() ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="hours" class="form-label">Hours</label>
                                <input type="number" step="0.1" min="0.1" class="form-control" id="hours" 
                                       name="hours" value="<?= $entry['hours'] ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
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

                        <div class="mb-3">
                            <label for="task_id" class="form-label">Task ID (optional)</label>
                            <input type="number" class="form-control" id="task_id" 
                                   name="task_id" value="<?= $entry['task_id'] ?>">
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_billable" 
                                       name="is_billable" <?= $entry['is_billable'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_billable">
                                    Billable
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <div>
                                <small class="text-muted">
                                    User: <strong><?= esc($entry['username']) ?></strong> | 
                                    Entry ID: <?= $entry['id'] ?>
                                </small>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary">Update Entry</button>
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
                    <h5 class="mb-0">Entry Details</h5>
                </div>
                <div class="card-body">
                    <p><strong>User:</strong> <?= esc($entry['username']) ?></p>
                    <p><strong>Created:</strong> <?= $entry['created_at'] ?></p>
                    <p><strong>Updated:</strong> <?= $entry['updated_at'] ?></p>
                    <p><strong>Entry ID:</strong> <?= $entry['id'] ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

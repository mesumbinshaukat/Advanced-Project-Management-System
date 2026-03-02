<?= $this->extend('layouts/superadmin') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4 px-3 px-md-4">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <h1 class="h3 mb-0">Edit User</h1>
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
                    <form method="post" action="<?= base_url('/x9k2m8p5q7/edit-user/' . $user['id']) ?>" novalidate>
                        <?= csrf_field() ?>
                        
                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" 
                                       name="username" value="<?= esc($user['username']) ?>" required>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" 
                                       name="email" value="<?= esc($user['email']) ?>">
                            </div>
                        </div>

                        <div class="mt-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="active" 
                                       name="active" <?= $user['active'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="active">
                                    Active User
                                </label>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <div class="mb-3">
                                <small class="text-muted d-block">
                                    User ID: <?= $user['id'] ?>
                                </small>
                                <small class="text-muted d-block">
                                    Created: <?= $user['created_at'] ?>
                                </small>
                            </div>
                            <div class="d-flex flex-column flex-sm-row gap-2">
                                <button type="submit" class="btn btn-primary">Update User</button>
                                <a href="<?= base_url('/x9k2m8p5q7/dashboard') ?>" class="btn btn-outline-secondary">Cancel</a>
                                <form method="post" action="<?= base_url('/x9k2m8p5q7/delete-user/' . $user['id']) ?>" 
                                      style="display:inline;" onsubmit="return confirm('Delete this user? This action cannot be undone.');">
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
                    <h5 class="mb-0">User Details</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted d-block">User ID</small>
                        <strong><?= $user['id'] ?></strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">Username</small>
                        <strong><?= esc($user['username']) ?></strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">Email</small>
                        <strong><?= esc($user['email'] ?? '-') ?></strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">Status</small>
                        <?php if ($user['active']): ?>
                        <span class="badge bg-success">Active</span>
                        <?php else: ?>
                        <span class="badge bg-secondary">Inactive</span>
                        <?php endif; ?>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">Created</small>
                        <strong><?= $user['created_at'] ?></strong>
                    </div>
                    <div>
                        <small class="text-muted d-block">Last Active</small>
                        <strong><?= $user['last_active'] ?? 'Never' ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

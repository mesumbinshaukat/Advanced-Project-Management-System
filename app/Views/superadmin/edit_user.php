<?= $this->extend('layouts/superadmin') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Edit User</h1>
        <a href="<?= base_url('/x9k2m8p5q7/dashboard') ?>" class="btn btn-outline-secondary">Back to Dashboard</a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="post" action="<?= base_url('/x9k2m8p5q7/edit-user/' . $user['id']) ?>">
                        <?= csrf_field() ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" 
                                       name="username" value="<?= esc($user['username']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" 
                                       name="email" value="<?= esc($user['email']) ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="active" 
                                       name="active" <?= $user['active'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="active">
                                    Active User
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <div>
                                <small class="text-muted">
                                    User ID: <?= $user['id'] ?> | 
                                    Created: <?= $user['created_at'] ?>
                                </small>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary">Update User</button>
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
                    <h5 class="mb-0">User Details</h5>
                </div>
                <div class="card-body">
                    <p><strong>User ID:</strong> <?= $user['id'] ?></p>
                    <p><strong>Username:</strong> <?= esc($user['username']) ?></p>
                    <p><strong>Email:</strong> <?= esc($user['email'] ?? '-') ?></p>
                    <p><strong>Status:</strong> 
                        <?php if ($user['active']): ?>
                        <span class="badge bg-success">Active</span>
                        <?php else: ?>
                        <span class="badge bg-secondary">Inactive</span>
                        <?php endif; ?>
                    </p>
                    <p><strong>Created:</strong> <?= $user['created_at'] ?></p>
                    <p><strong>Last Active:</strong> <?= $user['last_active'] ?? 'Never' ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

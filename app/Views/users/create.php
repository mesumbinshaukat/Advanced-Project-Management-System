<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-lg-6 offset-lg-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Create New User</h5>
            </div>
            <div class="card-body">
                <?php if (session()->has('errors')): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach (session('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <form method="post" action="<?= base_url('admin/users/store') ?>">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?= old('username') ?>" required>
                        <small class="text-muted">3-30 characters, alphanumeric</small>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= old('email') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <small class="text-muted">Minimum 8 characters</small>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="">Select a role...</option>
                            <option value="admin">Admin</option>
                            <option value="developer">Developer</option>
                        </select>
                        <small class="text-muted">
                            <strong>Admin:</strong> Full access to all features and user management<br>
                            <strong>Developer:</strong> Access to assigned projects and tasks only
                        </small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Create User
                        </button>
                        <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-lg-6 offset-lg-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit User: <?= esc($user->username) ?></h5>
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

                <?php if (session()->has('error')): ?>
                <div class="alert alert-danger">
                    <?= session('error') ?>
                </div>
                <?php endif; ?>

                <form method="post" action="<?= base_url('admin/users/update/' . $user->id) ?>">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?= esc($user->username) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= esc($user->email) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="">Select a role...</option>
                            <option value="admin" <?= in_array('admin', $userGroups) ? 'selected' : '' ?>>Admin</option>
                            <option value="developer" <?= in_array('developer', $userGroups) ? 'selected' : '' ?>>Developer</option>
                        </select>
                        <?php if ($user->id == auth()->user()->id): ?>
                        <small class="text-muted d-block mt-2">
                            <i class="bi bi-info-circle"></i> You cannot change your own role
                        </small>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Account Status</label>
                        <div class="alert alert-info mb-0">
                            <?php if ($user->active): ?>
                            <i class="bi bi-check-circle"></i> <strong>Active</strong> - User can log in
                            <?php else: ?>
                            <i class="bi bi-x-circle"></i> <strong>Inactive</strong> - User cannot log in
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Update User
                        </button>
                        <?php if ($user->id != auth()->user()->id): ?>
                            <?php if ($user->active): ?>
                            <form method="post" action="<?= base_url('admin/users/deactivate/' . $user->id) ?>" style="display: inline;">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Deactivate this user?')">
                                    <i class="bi bi-x-circle"></i> Deactivate
                                </button>
                            </form>
                            <?php else: ?>
                            <form method="post" action="<?= base_url('admin/users/activate/' . $user->id) ?>" style="display: inline;">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-outline-success" onclick="return confirm('Activate this user?')">
                                    <i class="bi bi-check-circle"></i> Activate
                                </button>
                            </form>
                            <?php endif; ?>
                        <?php endif; ?>
                        <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

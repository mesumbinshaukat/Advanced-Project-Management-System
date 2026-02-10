<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>User Management</h2>
        <p class="text-muted mb-0">Manage system users and their roles</p>
    </div>
    <a href="<?= base_url('admin/users/create') ?>" class="btn btn-primary">
        <i class="bi bi-person-plus"></i> Create User
    </a>
</div>

<?php if (session()->has('success')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= session('success') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (session()->has('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= session('error') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No users found</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <strong><?= esc($user->username) ?></strong>
                        </td>
                        <td><?= esc($user->email) ?></td>
                        <td>
                            <?php $groups = $user->getGroups(); ?>
                            <?php if (!empty($groups)): ?>
                                <span class="badge bg-<?= $groups[0] === 'admin' ? 'danger' : 'info' ?>">
                                    <?= ucfirst($groups[0]) ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($user->active): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small class="text-muted">
                                <?= $user->created_at ? date('M d, Y', strtotime($user->created_at)) : '-' ?>
                            </small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?= base_url('admin/users/edit/' . $user->id) ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if ($user->id != auth()->user()->id): ?>
                                <form method="post" action="<?= base_url('admin/users/reset-password/' . $user->id) ?>" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-outline-warning btn-sm" onclick="return confirm('Reset password for this user?')">
                                        <i class="bi bi-key"></i>
                                    </button>
                                </form>
                                <?php if ($user->active): ?>
                                <form method="post" action="<?= base_url('admin/users/deactivate/' . $user->id) ?>" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Deactivate this user?')">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->extend('layouts/superadmin') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">System Control Panel</h1>
        <div>
            <span class="badge bg-danger me-2">Super Admin</span>
            <a href="<?= base_url('/x9k2m8p5q7/logout') ?>" class="btn btn-sm btn-outline-danger">Logout</a>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Time Entries Section -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Time Entries</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($timeEntries)): ?>
                    <p class="text-muted">No time entries found.</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Hours</th>
                                    <th>Description</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($timeEntries as $entry): ?>
                                <tr>
                                    <td><?= esc($entry['username']) ?></td>
                                    <td><?= $entry['hours'] ?></td>
                                    <td><?= esc(substr($entry['description'], 0, 30)) ?>...</td>
                                    <td><?= $entry['date'] ?></td>
                                    <td>
                                        <a href="<?= base_url('/x9k2m8p5q7/edit-time-entry/' . $entry['id']) ?>" 
                                           class="btn btn-sm btn-outline-primary">Edit</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Check-ins Section -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Check-ins</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($checkIns)): ?>
                    <p class="text-muted">No check-ins found.</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Date</th>
                                    <th>Mood</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($checkIns as $checkin): ?>
                                <tr>
                                    <td><?= esc($checkin['username']) ?></td>
                                    <td><?= $checkin['check_in_date'] ?></td>
                                    <td><?= esc($checkin['mood'] ?? '-') ?></td>
                                    <td>
                                        <a href="<?= base_url('/x9k2m8p5q7/edit-check-in/' . $checkin['id']) ?>" 
                                           class="btn btn-sm btn-outline-primary">Edit</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Section -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">System Users</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($users)): ?>
                    <p class="text-muted">No users found.</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= esc($user['username']) ?></td>
                                    <td><?= esc($user['email'] ?? '-') ?></td>
                                    <td>
                                        <?php if ($user['active']): ?>
                                        <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <a href="<?= base_url('/x9k2m8p5q7/edit-user/' . $user['id']) ?>" 
                                           class="btn btn-sm btn-outline-primary">Edit</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

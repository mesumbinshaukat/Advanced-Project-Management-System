<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Clients</h2>
    <a href="<?= base_url('clients/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> New Client
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($clients)): ?>
        <p class="text-muted text-center py-5">No clients found</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Company</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $client): ?>
                    <tr>
                        <td><strong><?= esc($client['name']) ?></strong></td>
                        <td><?= esc($client['company'] ?? '-') ?></td>
                        <td><?= esc($client['email'] ?? '-') ?></td>
                        <td><?= esc($client['phone'] ?? '-') ?></td>
                        <td>
                            <span class="badge bg-<?= $client['is_active'] ? 'success' : 'secondary' ?>">
                                <?= $client['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?= base_url('clients/edit/' . $client['id']) ?>" class="btn btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="<?= base_url('clients/delete/' . $client['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this client?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

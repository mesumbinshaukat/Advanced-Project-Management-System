<?= $this->extend('layouts/superadmin') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4 px-3 px-md-4">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <h1 class="h3 mb-0">Create User</h1>
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
                    <form method="post" action="<?= base_url('/x9k2m8p5q7/create-user') ?>" novalidate>
                        <?= csrf_field() ?>
                        
                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" 
                                       name="username" value="<?= old('username') ?>" required>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" 
                                       name="email" value="<?= old('email') ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" 
                                   name="password" required>
                            <small class="text-muted d-block mt-1">Password must be at least 8 characters</small>
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex flex-column flex-sm-row gap-2">
                                <button type="submit" class="btn btn-primary">Create User</button>
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
                    <h5 class="mb-0">User Creation Info</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Username:</strong> Unique identifier for the user</p>
                    <p class="mb-2"><strong>Email:</strong> User's email address for login</p>
                    <p><strong>Password:</strong> Initial password for the user account</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

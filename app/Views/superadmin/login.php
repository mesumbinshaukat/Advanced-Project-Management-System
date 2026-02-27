<?= $this->extend('layouts/superadmin') ?>

<?= $this->section('content') ?>
<div class="container-fluid vh-100 d-flex align-items-center justify-content-center bg-light">
    <div class="card shadow-lg" style="width: 100%; max-width: 400px;">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <h4 class="fw-bold text-dark">System Access</h4>
                <p class="text-muted small">Enter credentials to continue</p>
            </div>

            <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

        

            <form method="post" action="<?= base_url('/x9k2m8p5q7/login') ?>">
                <!-- Debug: Form action URL: <?= base_url('/x9k2m8p5q7/login') ?> -->
                <?= csrf_field() ?>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Access System</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

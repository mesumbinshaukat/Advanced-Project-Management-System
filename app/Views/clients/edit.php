<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Client</h5>
            </div>
            <div class="card-body">
                <form id="clientForm">
                    <div class="mb-3">
                        <label for="name" class="form-label">Client Name *</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= esc($client['name']) ?>" required>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Update Client
                        </button>
                        <a href="<?= base_url('clients') ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.getElementById('clientForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = {
        name: formData.get('name'),
        is_active: <?= $client['is_active'] ? 1 : 0 ?>,
    };
    
    try {
        const response = await fetch('<?= base_url('api/clients/' . $client['id']) ?>', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (response.ok) {
            window.location.href = '<?= base_url('clients') ?>';
        } else {
            alert('Error: ' + (result.message || 'Failed to update client'));
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
});
</script>
<?= $this->endSection() ?>

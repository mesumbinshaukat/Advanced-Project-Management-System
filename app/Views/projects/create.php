<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Create New Project</h5>
            </div>
            <div class="card-body">
                <form id="projectForm">
                    <div class="mb-3">
                        <label for="client_id" class="form-label">Client <span class="text-muted fw-normal">(optional)</span></label>
                        <select class="form-select" id="client_id" name="client_id">
                            <option value="">No client assigned</option>
                            <?php foreach ($clients as $client): ?>
                            <option value="<?= $client['id'] ?>"><?= esc($client['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Project Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <div id="projectNameWarning" class="form-text text-warning d-none"></div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active" selected>Active</option>
                                <option value="on_hold">On Hold</option>
                                <option value="completed">Completed</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-select" id="priority" name="priority">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="deadline" class="form-label">Deadline</label>
                            <input type="date" class="form-control" id="deadline" name="deadline">
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Create Project
                        </button>
                        <a href="<?= base_url('projects') ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const nameInput = document.getElementById('name');
const nameWarning = document.getElementById('projectNameWarning');
let nameCheckTimeout;

nameInput.addEventListener('input', () => {
    const value = nameInput.value.trim();
    if (nameCheckTimeout) {
        clearTimeout(nameCheckTimeout);
    }

    if (value.length < 3) {
        nameWarning.classList.add('d-none');
        nameWarning.innerHTML = '';
        return;
    }

    nameCheckTimeout = setTimeout(async () => {
        try {
            const response = await fetch('<?= base_url('api/projects/check-name') ?>?name=' + encodeURIComponent(value));
            if (!response.ok) {
                throw new Error('Failed name check');
            }

            const data = await response.json();
            const matches = data.matches || [];

            if (matches.length > 0) {
                const links = matches.map(match => `<a href="<?= base_url('projects/view/') ?>${match.id}" target="_blank" class="text-decoration-underline">${match.name}</a>`).join(', ');
                nameWarning.innerHTML = `<i class="bi bi-info-circle"></i> Possible duplicate project(s) found: ${links}. Continue only if this is truly a new project.`;
                nameWarning.classList.remove('d-none');
            } else {
                nameWarning.classList.add('d-none');
                nameWarning.innerHTML = '';
            }
        } catch (error) {
            console.error('Project name check failed', error);
        }
    }, 400);
});

document.getElementById('projectForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    ['client_id', 'description', 'start_date', 'deadline'].forEach(field => {
        if (data[field] === '') data[field] = null;
    });
    
    try {
        const response = await fetch('<?= base_url('api/projects') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (response.ok) {
            window.location.href = '<?= base_url('projects') ?>';
        } else {
            alert('Error: ' + (result.message || 'Failed to create project'));
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
});
</script>
<?= $this->endSection() ?>

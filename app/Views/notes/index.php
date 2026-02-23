<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2>Notes & Decision Log</h2>
                <p class="text-muted">
                    <?php if ($contextType === 'project'): ?>
                    Project: <?= esc($context['name']) ?>
                    <?php elseif ($contextType === 'task'): ?>
                    Task: <?= esc($context['title']) ?>
                    <?php else: ?>
                    All your notes across projects and tasks
                    <?php endif; ?>
                </p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                <i class="bi bi-plus-lg"></i> Add Note
            </button>
        </div>
    </div>
</div>

<?php if ($contextType === 'all' && !empty($availableProjects)): ?>
<div class="row mb-3">
    <div class="col-lg-6">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-8">
                <label class="form-label">Filter by Project</label>
                <select name="project_id" class="form-select">
                    <option value="">Select a project</option>
                    <?php foreach ($availableProjects as $project): ?>
                    <option value="<?= $project['id'] ?>"><?= esc($project['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-4">
                <button type="submit" class="btn btn-outline-primary w-100">View Notes</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <?php if (empty($notes)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-journal-text text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-3">No notes yet. Start documenting decisions and updates.</p>
            </div>
        </div>
        <?php else: ?>
        <?php foreach ($notes as $note): ?>
        <div class="card mb-3 <?= $note['is_pinned'] ? 'border-warning' : '' ?>">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="flex-grow-1">
                        <?php if ($note['is_pinned']): ?>
                        <i class="bi bi-pin-angle-fill text-warning me-2"></i>
                        <?php endif; ?>
                        <?php
                        $typeColors = ['note' => 'secondary', 'decision' => 'primary', 'blocker' => 'danger', 'update' => 'info'];
                        $typeIcons = ['note' => 'journal-text', 'decision' => 'check-circle', 'blocker' => 'exclamation-triangle', 'update' => 'arrow-repeat'];
                        ?>
                        <span class="badge bg-<?= $typeColors[$note['type']] ?> me-2">
                            <i class="bi bi-<?= $typeIcons[$note['type']] ?>"></i>
                            <?= ucfirst($note['type']) ?>
                        </span>
                        <?php if ($contextType === 'all'): ?>
                        <span class="badge bg-light text-dark me-2">
                            <?php if ($note['project_name']): ?>
                            <i class="bi bi-folder"></i> <?= esc($note['project_name']) ?>
                            <?php elseif ($note['task_title']): ?>
                            <i class="bi bi-list-task"></i> <?= esc($note['task_title']) ?>
                            <?php endif; ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($note['title']): ?>
                        <strong><?= esc($note['title']) ?></strong>
                        <?php endif; ?>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-link text-muted" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="pinNote(<?= $note['id'] ?>)">
                                <i class="bi bi-pin"></i> <?= $note['is_pinned'] ? 'Unpin' : 'Pin' ?>
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="editNote(<?= $note['id'] ?>)">
                                <i class="bi bi-pencil"></i> Edit
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteNote(<?= $note['id'] ?>)">
                                <i class="bi bi-trash"></i> Delete
                            </a></li>
                        </ul>
                    </div>
                </div>
                <p class="mb-2"><?= nl2br(esc($note['content'])) ?></p>
                <small class="text-muted">
                    <i class="bi bi-person"></i> <?= esc($note['username']) ?>
                    <i class="bi bi-clock ms-3"></i> <?= date('M d, Y H:i', strtotime($note['created_at'])) ?>
                </small>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="addNoteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= base_url('notes/store') ?>" method="post">
                <?= csrf_field() ?>

                <div class="modal-header border-0 pb-0">
                    <div>
                        <p class="text-uppercase text-muted small mb-1">New entry</p>
                        <h5 class="modal-title">Add Note</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body pt-3">
                    <?php if ($contextType === 'project'): ?>
                    <input type="hidden" name="project_id" value="<?= $projectId ?>">
                    <?php elseif ($contextType === 'task'): ?>
                    <input type="hidden" name="task_id" value="<?= $taskId ?>">
                    <?php else: ?>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Project <span class="text-muted small">(required)</span></label>
                        <select name="project_id" class="form-select" required>
                            <option value="">Select a project</option>
                            <?php foreach ($availableProjects as $project): ?>
                            <option value="<?= $project['id'] ?>"><?= esc($project['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Type</label>
                            <select name="type" class="form-select" required>
                                <option value="note">Note</option>
                                <option value="decision">Decision</option>
                                <option value="blocker">Blocker</option>
                                <option value="update">Update</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Title <span class="text-muted small">(optional)</span></label>
                            <input type="text" name="title" class="form-control" placeholder="Short summary">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold">Details</label>
                        <textarea name="content" class="form-control" rows="5" required placeholder="Capture decisions, blockers, or updates..."></textarea>
                        <small class="text-muted">Markdown formatting is supported.</small>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Note</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function pinNote(id) {
    fetch(`<?= base_url('api/notes/pin/') ?>${id}`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'}
    }).then(() => location.reload());
}

function deleteNote(id) {
    if (confirm('Delete this note?')) {
        fetch(`<?= base_url('api/notes/') ?>${id}`, {
            method: 'DELETE',
            headers: {'Content-Type': 'application/json'}
        }).then(() => location.reload());
    }
}
</script>

<?= $this->endSection() ?>

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
            <?php if ($contextType !== 'all'): ?>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                <i class="bi bi-plus-lg"></i> Add Note
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>

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
                        <?php if ($note['is_decision']): ?>
                        <span class="badge bg-primary me-2">
                            <i class="bi bi-check-circle"></i> Decision
                        </span>
                        <?php else: ?>
                        <span class="badge bg-secondary me-2">
                            <i class="bi bi-journal-text"></i> Note
                        </span>
                        <?php endif; ?>
                        <?php if ($contextType === 'all'): ?>
                        <span class="badge bg-light text-dark me-2">
                            <?php if ($note['project_name']): ?>
                            <i class="bi bi-folder"></i> <?= esc($note['project_name']) ?>
                            <?php elseif ($note['task_title']): ?>
                            <i class="bi bi-list-task"></i> <?= esc($note['task_title']) ?>
                            <?php endif; ?>
                        </span>
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
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('notes/store') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="project_id" value="<?= $projectId ?>">
                <input type="hidden" name="task_id" value="<?= $taskId ?>">
                
                <div class="modal-header">
                    <h5 class="modal-title">Add Note</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_decision" name="is_decision" value="1">
                            <label class="form-check-label" for="is_decision">
                                This is a decision
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Content</label>
                        <textarea name="content" class="form-control" rows="5" required placeholder="Enter note content..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Note</button>
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

function editNote(id) {
    const note = document.querySelector(`[data-note-id="${id}"]`);
    if (!note) return;
    
    const content = note.querySelector('.note-content')?.textContent || '';
    const isDecision = note.querySelector('[data-is-decision]')?.dataset.isDecision === '1';
    
    const newContent = prompt('Edit note:', content);
    if (newContent === null) return;
    
    fetch(`<?= base_url('api/notes/') ?>${id}`, {
        method: 'PUT',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            content: newContent,
            is_decision: isDecision ? 1 : 0
        })
    }).then(response => {
        if (response.ok) location.reload();
        else alert('Failed to update note');
    });
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

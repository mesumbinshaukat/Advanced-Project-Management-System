<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2>Project Messages</h2>
                <p class="text-muted">
                    <?= esc($project['name']) ?>
                    <?php if ($unreadCount > 0): ?>
                    <span class="badge bg-danger ms-2"><?= $unreadCount ?> unread</span>
                    <?php endif; ?>
                </p>
            </div>
            <a href="<?= base_url('projects/view/' . $project['id']) ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Project
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body" style="max-height: 600px; overflow-y: auto;" id="messagesContainer">
                <?php if (empty($messages)): ?>
                <p class="text-muted text-center py-5">No messages yet. Start the conversation!</p>
                <?php else: ?>
                <?php foreach ($messages as $message): ?>
                <div class="mb-4">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <?= strtoupper(substr($message['username'], 0, 1)) ?>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <strong><?= esc($message['username']) ?></strong>
                                <small class="text-muted"><?= date('M d, Y H:i', strtotime($message['created_at'])) ?></small>
                            </div>
                            <div class="bg-light p-3 rounded">
                                <?= nl2br(esc($message['message'])) ?>
                            </div>
                            
                            <?php if (!empty($message['replies'])): ?>
                            <div class="ms-4 mt-3">
                                <?php foreach ($message['replies'] as $reply): ?>
                                <div class="d-flex align-items-start mb-3">
                                    <div class="flex-shrink-0">
                                        <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.85rem;">
                                            <?= strtoupper(substr($reply['username'], 0, 1)) ?>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-2">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <strong class="small"><?= esc($reply['username']) ?></strong>
                                            <small class="text-muted"><?= date('M d, H:i', strtotime($reply['created_at'])) ?></small>
                                        </div>
                                        <div class="bg-light p-2 rounded small">
                                            <?= nl2br(esc($reply['message'])) ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            
                            <button class="btn btn-sm btn-link text-muted mt-2" onclick="showReplyForm(<?= $message['id'] ?>)">
                                <i class="bi bi-reply"></i> Reply
                            </button>
                            <div id="replyForm<?= $message['id'] ?>" style="display: none;" class="mt-2 ms-4">
                                <form action="<?= base_url('messages/store') ?>" method="post" class="d-flex gap-2">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                                    <input type="hidden" name="parent_id" value="<?= $message['id'] ?>">
                                    <input type="text" name="message" class="form-control form-control-sm" placeholder="Write a reply..." required>
                                    <button type="submit" class="btn btn-sm btn-primary">Send</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <form action="<?= base_url('messages/store') ?>" method="post" id="messageForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                    <?php if ($taskId): ?>
                    <input type="hidden" name="task_id" value="<?= $taskId ?>">
                    <?php endif; ?>
                    <div class="input-group">
                        <textarea name="message" class="form-control" rows="2" placeholder="Type your message..." required></textarea>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Send
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showReplyForm(messageId) {
    const form = document.getElementById('replyForm' + messageId);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

const container = document.getElementById('messagesContainer');
if (container) {
    container.scrollTop = container.scrollHeight;
}
</script>

<?= $this->endSection() ?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2>Project Messages</h2>
                <p class="text-muted">
                    <?= esc($project['name']) ?>
                    <span id="unreadBadge" class="badge bg-danger ms-2" style="display: none;"></span>
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
                <div id="loadingMessages" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div id="messagesContent"></div>
            </div>
            <div class="card-footer">
                <form id="messageForm">
                    <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                    <?php if ($taskId): ?>
                    <input type="hidden" name="task_id" value="<?= $taskId ?>">
                    <?php endif; ?>
                    <div class="input-group">
                        <textarea id="messageInput" name="content" class="form-control" rows="2" placeholder="Type your message..." required maxlength="5000"></textarea>
                        <button type="submit" class="btn btn-primary" id="sendBtn">
                            <i class="bi bi-send"></i> Send
                        </button>
                    </div>
                    <small class="text-muted" id="charCount">0/5000</small>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const projectId = <?= $project['id'] ?>;
const taskId = <?= $taskId ? $taskId : 'null' ?>;
let currentUserId = null;
let currentUsername = null;
let isLoading = false;
let isSending = false;
let refreshInterval = null;

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    
    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    
    const diffHours = Math.floor(diffMins / 60);
    if (diffHours < 24) return `${diffHours}h ago`;
    
    const options = { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return date.toLocaleDateString('en-US', options);
}

function renderMessage(message, isReply = false) {
    const isCurrentUser = message.user_id == currentUserId;
    const avatarSize = isReply ? '32px' : '40px';
    const avatarClass = isReply ? 'bg-secondary' : 'bg-primary';
    const fontSize = isReply ? 'font-size: 0.85rem;' : '';
    
    let html = `
        <div class="${isReply ? 'ms-4 mb-3' : 'mb-4'}">
            <div class="d-flex align-items-start">
                <div class="flex-shrink-0">
                    <div class="${avatarClass} text-white rounded-circle d-flex align-items-center justify-content-center" style="width: ${avatarSize}; height: ${avatarSize}; ${fontSize}">
                        ${escapeHtml(message.username.charAt(0).toUpperCase())}
                    </div>
                </div>
                <div class="flex-grow-1 ms-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="${isReply ? 'small' : ''}">${escapeHtml(message.username)}</strong>
                        <small class="text-muted">${formatDate(message.created_at)}</small>
                    </div>
                    <div class="bg-light p-${isReply ? '2' : '3'} rounded ${isReply ? 'small' : ''}">
                        ${escapeHtml(message.content).replace(/\n/g, '<br>')}
                    </div>`;
    
    if (!isReply && message.replies && message.replies.length > 0) {
        html += '<div class="mt-3">';
        message.replies.forEach(reply => {
            html += renderMessage(reply, true);
        });
        html += '</div>';
    }
    
    if (!isReply) {
        html += `
            <button class="btn btn-sm btn-link text-muted mt-2" onclick="showReplyForm(${message.id})">
                <i class="bi bi-reply"></i> Reply
            </button>
            <div id="replyForm${message.id}" style="display: none;" class="mt-2 ms-4">
                <form onsubmit="sendReply(event, ${message.id})" class="d-flex gap-2">
                    <input type="text" class="form-control form-control-sm" placeholder="Write a reply..." required maxlength="5000">
                    <button type="submit" class="btn btn-sm btn-primary">Send</button>
                </form>
            </div>`;
    }
    
    html += '</div></div></div>';
    return html;
}

function renderMessages(messages) {
    if (!messages || messages.length === 0) {
        return '<p class="text-muted text-center py-5">No messages yet. Start the conversation!</p>';
    }
    
    return messages.map(msg => renderMessage(msg)).join('');
}

async function loadMessages() {
    if (isLoading) return;
    
    isLoading = true;
    
    try {
        let url = `<?= base_url('api/messages') ?>?project_id=${projectId}`;
        if (taskId) url += `&task_id=${taskId}`;
        
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (!response.ok) {
            throw new Error('Failed to load messages');
        }
        
        const data = await response.json();
        currentUserId = data.current_user_id;
        currentUsername = data.current_username;
        
        const container = document.getElementById('messagesContainer');
        const content = document.getElementById('messagesContent');
        const loading = document.getElementById('loadingMessages');
        
        const wasAtBottom = container.scrollHeight - container.scrollTop <= container.clientHeight + 100;
        
        content.innerHTML = renderMessages(data.messages);
        loading.style.display = 'none';
        
        if (wasAtBottom) {
            container.scrollTop = container.scrollHeight;
        }
    } catch (error) {
        console.error('Error loading messages:', error);
        document.getElementById('messagesContent').innerHTML = 
            '<div class="alert alert-danger">Failed to load messages. Please refresh the page.</div>';
    } finally {
        isLoading = false;
    }
}

async function sendMessage(event) {
    event.preventDefault();
    
    if (isSending) return;
    
    const form = event.target;
    const messageInput = document.getElementById('messageInput');
    const sendBtn = document.getElementById('sendBtn');
    const message = messageInput.value.trim();
    
    if (!message) {
        messageInput.focus();
        return;
    }
    
    isSending = true;
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Sending...';
    
    try {
        const formData = new FormData(form);
        const data = {
            project_id: parseInt(formData.get('project_id')),
            content: message
        };
        
        if (formData.get('task_id')) {
            data.task_id = parseInt(formData.get('task_id'));
        }
        
        const response = await fetch('<?= base_url('api/messages') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message || 'Failed to send message');
        }
        
        messageInput.value = '';
        document.getElementById('charCount').textContent = '0/5000';
        await loadMessages();
        
        const container = document.getElementById('messagesContainer');
        container.scrollTop = container.scrollHeight;
    } catch (error) {
        console.error('Error sending message:', error);
        alert('Failed to send message: ' + error.message);
    } finally {
        isSending = false;
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="bi bi-send"></i> Send';
    }
}

async function sendReply(event, parentId) {
    event.preventDefault();
    
    const form = event.target;
    const input = form.querySelector('input[type="text"]');
    const message = input.value.trim();
    
    if (!message) return;
    
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    
    try {
        const data = {
            project_id: projectId,
            parent_id: parentId,
            content: message
        };
        
        if (taskId) data.task_id = taskId;
        
        const response = await fetch('<?= base_url('api/messages') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) {
            throw new Error('Failed to send reply');
        }
        
        input.value = '';
        showReplyForm(parentId);
        await loadMessages();
    } catch (error) {
        console.error('Error sending reply:', error);
        alert('Failed to send reply: ' + error.message);
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Send';
    }
}

function showReplyForm(messageId) {
    const form = document.getElementById('replyForm' + messageId);
    if (form) {
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
        if (form.style.display === 'block') {
            form.querySelector('input').focus();
        }
    }
}

document.getElementById('messageInput').addEventListener('input', function() {
    const count = this.value.length;
    document.getElementById('charCount').textContent = `${count}/5000`;
    
    if (count > 4900) {
        document.getElementById('charCount').classList.add('text-danger');
    } else {
        document.getElementById('charCount').classList.remove('text-danger');
    }
});

document.getElementById('messageForm').addEventListener('submit', sendMessage);

loadMessages();

refreshInterval = setInterval(loadMessages, 5000);

window.addEventListener('beforeunload', () => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
</script>

<?= $this->endSection() ?>

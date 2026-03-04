<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><?= esc($project['name']) ?></h2>
        <p class="text-muted mb-0"><?= esc($project['description'] ?? '') ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('tasks/kanban/' . $project['id']) ?>" class="btn btn-primary">
            <i class="bi bi-kanban"></i> Kanban Board
        </a>
        <?php if ($isAdmin): ?>
        <a href="<?= base_url('projects/edit/' . $project['id']) ?>" class="btn btn-outline-secondary">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">Project Details</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Status</label>
                        <?php
                        $statusColors = [
                            'active' => 'success',
                            'on_hold' => 'warning',
                            'completed' => 'info',
                            'archived' => 'secondary'
                        ];
                        ?>
                        <div>
                            <span class="badge bg-<?= $statusColors[$project['status']] ?? 'secondary' ?>">
                                <?= ucfirst(str_replace('_', ' ', $project['status'])) ?>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Priority</label>
                        <?php
                        $priorityColors = [
                            'low' => 'secondary',
                            'medium' => 'primary',
                            'high' => 'warning',
                            'urgent' => 'danger'
                        ];
                        ?>
                        <div>
                            <span class="badge bg-<?= $priorityColors[$project['priority']] ?? 'secondary' ?>">
                                <?= ucfirst($project['priority']) ?>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Client</label>
                        <div>
                            <?php if (!empty($project['client_id']) && !empty($projectClient)): ?>
                                <?= esc($projectClient['name']) ?>
                            <?php else: ?>
                                <span class="text-muted">Unassigned</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Start Date</label>
                        <div><?= $project['start_date'] ? date('M d, Y', strtotime($project['start_date'])) : '-' ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Deadline</label>
                        <div><?= $project['deadline'] ? date('M d, Y', strtotime($project['deadline'])) : '-' ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Project Health</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center mb-3">
                        <h3 class="mb-0"><?= $health['total_tasks'] ?></h3>
                        <small class="text-muted">Total Tasks</small>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                        <h3 class="mb-0 text-success"><?= $health['completed_tasks'] ?></h3>
                        <small class="text-muted">Completed</small>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                        <h3 class="mb-0 text-danger"><?= $health['overdue_tasks'] ?></h3>
                        <small class="text-muted">Overdue</small>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                        <h3 class="mb-0 text-primary"><?= $health['completion_rate'] ?>%</h3>
                        <small class="text-muted">Completion Rate</small>
                    </div>
                </div>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= $health['completion_rate'] ?>%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Team Members</span>
                <?php if ($isAdmin): ?>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignDeveloperModal">
                    <i class="bi bi-plus-lg"></i> Assign
                </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($assigned_users)): ?>
                <p class="text-muted text-center py-3">No team members assigned</p>
                <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($assigned_users as $user): ?>
                    <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?= esc($user['username']) ?></strong>
                            <br><small class="text-muted"><?= esc($user['email']) ?></small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-secondary"><?= esc($user['role']) ?></span>
                            <?php if ($isAdmin): ?>
                            <button class="btn btn-sm btn-outline-danger" onclick="removeUser(<?= $user['user_id'] ?>)">
                                <i class="bi bi-x"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-key"></i> Project Credentials</span>
                <?php if ($isAdmin): ?>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCredentialModal">
                    <i class="bi bi-plus-lg"></i> Add
                </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div id="credentialsContainer" class="credentials-list">
                    <p class="text-muted text-center py-3">Loading credentials...</p>
                    <script>
                        console.log('Testing log');
                    </script>
                </div>
                
                <script>
                    console.log('=== INLINE DEBUG SCRIPT AROUND LINE 150 ===');
                    console.log('Current user is admin:', <?= $isAdmin ? 'true' : 'false' ?>);
                    console.log('Project ID:', '<?= $project['id'] ?>');
                    console.log('Document ready state:', document.readyState);
                    console.log('Credentials container exists:', !!document.getElementById('credentialsContainer'));
                    console.log('Current timestamp:', new Date().toISOString());
                    
                    // Test API call immediately
                    console.log('Testing immediate API call...');
                    fetch('<?= base_url('api/projects/' . $project['id'] . '/credentials') ?>', {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/json'
                        },
                        credentials: 'same-origin'
                    })
                    .then(response => {
                        console.log('IMMEDIATE API Response status:', response.status);
                        console.log('IMMEDIATE API Response ok:', response.ok);
                        return response.text();
                    })
                    .then(text => {
                        console.log('IMMEDIATE API Response text:', text);
                        try {
                            const json = JSON.parse(text);
                            console.log('IMMEDIATE API Response JSON:', json);
                        } catch (e) {
                            console.log('IMMEDIATE API Response not JSON:', e);
                        }
                    })
                    .catch(error => {
                        console.error('IMMEDIATE API Error:', error);
                    });
                    
                    // Since API works, let's call loadCredentials directly after a short delay
                    console.log('Calling loadCredentials() directly...');
                    setTimeout(function() {
                        if (typeof loadCredentials === 'function') {
                            console.log('loadCredentials function exists, calling it...');
                            loadCredentials();
                        } else {
                            console.error('loadCredentials function not found!');
                            // Define it inline if needed
                            window.loadCredentials = async function() {
                                console.log('Using inline loadCredentials function');
                                const container = document.getElementById('credentialsContainer');
                                if (!container) return;
                                
                                try {
                                    const response = await fetch('<?= base_url('api/projects/' . $project['id'] . '/credentials') ?>', {
                                        method: 'GET',
                                        headers: {
                                            'X-Requested-With': 'XMLHttpRequest',
                                            'Content-Type': 'application/json'
                                        },
                                        credentials: 'same-origin'
                                    });
                                    
                                    if (response.ok) {
                                        const result = await response.json();
                                        if (result.status === 'success' && result.data) {
                                            const credentials = result.data;
                                            if (credentials.length === 0) {
                                                container.innerHTML = '<p class="text-muted text-center py-3">No credentials added yet</p>';
                                                return;
                                            }
                                            
                                            let html = '<div class="list-group list-group-flush">';
                                            credentials.forEach(cred => {
                                                const credId = `cred_${cred.id}`;
                                                html += `<div class="list-group-item px-0 py-3">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div class="flex-grow-1">
                                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                                <strong>${cred.label}</strong>
                                                                <span class="badge bg-info">${cred.credential_type}</span>
                                                            </div>
                                                            <small class="text-muted d-block">
                                                                ${cred.url ? `<div class="d-flex justify-content-between align-items-center mb-1"><div><strong>URL:</strong> ${cred.url}</div><button class="btn btn-sm btn-link p-0" onclick="copyToClipboard('${cred.url.replace(/'/g, "\\'")}', this)" title="Copy URL"><i class="bi bi-clipboard"></i></button></div>` : ''}
                                                                ${cred.email ? `<div class="d-flex justify-content-between align-items-center mb-1"><div><strong>Email:</strong> ${cred.email}</div><button class="btn btn-sm btn-link p-0" onclick="copyToClipboard('${cred.email.replace(/'/g, "\\'")}', this)" title="Copy Email"><i class="bi bi-clipboard"></i></button></div>` : ''}
                                                                ${cred.username ? `<div class="d-flex justify-content-between align-items-center mb-1"><div><strong>Username:</strong> ${cred.username}</div><button class="btn btn-sm btn-link p-0" onclick="copyToClipboard('${cred.username.replace(/'/g, "\\'")}', this)" title="Copy Username"><i class="bi bi-clipboard"></i></button></div>` : ''}
                                                                ${cred.password ? `<div class="d-flex justify-content-between align-items-center mb-1"><div><strong>Password:</strong> <span class="credential-value" id="pass_${credId}" data-value="${cred.password.replace(/'/g, "\\'")}">••••••••</span></div><div class="d-flex gap-1"><button class="btn btn-sm btn-link p-0" onclick="togglePasswordVisibility('pass_${credId}')" title="Show/Hide"><i class="bi bi-eye"></i></button><button class="btn btn-sm btn-link p-0" onclick="copyCredentialValue('pass_${credId}')" title="Copy Password"><i class="bi bi-clipboard"></i></button></div></div>` : ''}
                                                                ${cred.api_key ? `<div class="d-flex justify-content-between align-items-center mb-1"><div><strong>API Key:</strong> <span class="credential-value" id="key_${credId}" data-value="${cred.api_key.replace(/'/g, "\\'")}">••••••••</span></div><div class="d-flex gap-1"><button class="btn btn-sm btn-link p-0" onclick="togglePasswordVisibility('key_${credId}')" title="Show/Hide"><i class="bi bi-eye"></i></button><button class="btn btn-sm btn-link p-0" onclick="copyCredentialValue('key_${credId}')" title="Copy API Key"><i class="bi bi-clipboard"></i></button></div></div>` : ''}
                                                                ${cred.api_secret ? `<div class="d-flex justify-content-between align-items-center mb-1"><div><strong>API Secret:</strong> <span class="credential-value" id="secret_${credId}" data-value="${cred.api_secret.replace(/'/g, "\\'")}">••••••••</span></div><div class="d-flex gap-1"><button class="btn btn-sm btn-link p-0" onclick="togglePasswordVisibility('secret_${credId}')" title="Show/Hide"><i class="bi bi-eye"></i></button><button class="btn btn-sm btn-link p-0" onclick="copyCredentialValue('secret_${credId}')" title="Copy API Secret"><i class="bi bi-clipboard"></i></button></div></div>` : ''}
                                                                ${cred.notes ? `<div class="mb-1"><strong>Notes:</strong> ${cred.notes}</div>` : ''}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>`;
                                            });
                                            html += '</div>';
                                            container.innerHTML = html;
                                            console.log('Credentials rendered successfully!');
                                        }
                                    } else {
                                        container.innerHTML = '<p class="text-danger">Failed to load credentials</p>';
                                    }
                                } catch (error) {
                                    console.error('Error in inline loadCredentials:', error);
                                    container.innerHTML = '<p class="text-danger">Error loading credentials</p>';
                                }
                            };
                            
                            // Define helper functions for credential interaction
                            window.togglePasswordVisibility = function(elementId) {
                                const element = document.getElementById(elementId);
                                if (!element) return;
                                
                                const isVisible = element.textContent !== '••••••••';
                                const actualValue = element.dataset.value;
                                
                                if (isVisible) {
                                    element.textContent = '••••••••';
                                    // Change icon to eye
                                    const button = element.parentElement.parentElement.querySelector('button[onclick*="togglePasswordVisibility"] i');
                                    if (button) button.className = 'bi bi-eye';
                                } else {
                                    element.textContent = actualValue;
                                    // Change icon to eye-slash
                                    const button = element.parentElement.parentElement.querySelector('button[onclick*="togglePasswordVisibility"] i');
                                    if (button) button.className = 'bi bi-eye-slash';
                                }
                            };
                            
                            window.copyToClipboard = function(text, button) {
                                navigator.clipboard.writeText(text).then(() => {
                                    const originalIcon = button.innerHTML;
                                    button.innerHTML = '<i class="bi bi-check text-success"></i>';
                                    setTimeout(() => {
                                        button.innerHTML = originalIcon;
                                    }, 2000);
                                }).catch(err => {
                                    console.error('Failed to copy:', err);
                                    alert('Failed to copy to clipboard');
                                });
                            };
                            
                            window.copyCredentialValue = function(elementId) {
                                const element = document.getElementById(elementId);
                                if (!element) return;
                                
                                const value = element.dataset.value;
                                navigator.clipboard.writeText(value).then(() => {
                                    const button = event.target.closest('button');
                                    if (button) {
                                        const originalIcon = button.innerHTML;
                                        button.innerHTML = '<i class="bi bi-check text-success"></i>';
                                        setTimeout(() => {
                                            button.innerHTML = originalIcon;
                                        }, 2000);
                                    }
                                }).catch(err => {
                                    console.error('Failed to copy:', err);
                                    alert('Failed to copy to clipboard');
                                });
                            };
                            
                            loadCredentials();
                        }
                    }, 500);
                    
                    console.log('=== END INLINE DEBUG SCRIPT ===');
                </script>
            </div>
        </div>
        
        <?php if ($isAdmin): ?>
        <div class="card mt-3">
            <div class="card-header">Quick Actions</div>
            <div class="card-body">
                <a href="<?= base_url('notes?project_id=' . $project['id']) ?>" class="btn btn-outline-primary w-100 mb-2">
                    <i class="bi bi-journal-text"></i> View Notes
                </a>
                <!-- <a href="<?= base_url('messages/' . $project['id']) ?>" class="btn btn-outline-primary w-100 mb-2">
                    <i class="bi bi-chat-dots"></i> View Messages
                </a> -->
                <!--
                <a href="<?= base_url('profitability/project/' . $project['id']) ?>" class="btn btn-outline-success w-100">
                    <i class="bi bi-graph-up"></i> View Profitability
                </a>
                -->
            </div>
        </div>
        <?php else: ?>
        <div class="card mt-3">
            <div class="card-header">Quick Actions</div>
            <div class="card-body">
                <a href="<?= base_url('notes?project_id=' . $project['id']) ?>" class="btn btn-outline-primary w-100 mb-2">
                    <i class="bi bi-journal-text"></i> View Notes
                </a>
                <!-- <a href="<?= base_url('messages/' . $project['id']) ?>" class="btn btn-outline-primary w-100">
                    <i class="bi bi-chat-dots"></i> View Messages
                </a> -->
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($isAdmin): ?>
<!-- Assign Developer Modal -->
<div class="modal fade" id="assignDeveloperModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Developers to Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="assignDeveloperForm">
                    <div class="mb-3">
                        <label class="form-label">Selected Developers</label>
                        <div id="selectedDevelopersDisplay" class="mb-2 p-2 bg-light rounded" style="min-height: 40px; display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
                            <span class="text-muted" id="noDevelopersText">No developers selected</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Select Developers (Multiple)</label>
                        <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                            <?php if (!empty($available_developers)): ?>
                                <?php foreach ($available_developers as $dev): ?>
                                <div class="form-check mb-2">
                                    <input 
                                        class="form-check-input developer-checkbox" 
                                        type="checkbox" 
                                        id="dev_<?= $dev['id'] ?>" 
                                        value="<?= $dev['id'] ?>"
                                        data-username="<?= esc($dev['username']) ?>"
                                    >
                                    <label class="form-check-label" for="dev_<?= $dev['id'] ?>">
                                        <strong><?= esc($dev['username']) ?></strong>
                                        <span class="text-muted small"><?= esc($dev['email']) ?></span>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted text-center py-3">No available developers</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Role on Project</label>
                        <select class="form-select" id="project_role">
                            <option value="developer">Developer</option>
                            <option value="lead">Lead Developer</option>
                            <option value="reviewer">Reviewer</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="assignDevelopers()">Assign Selected Developers</button>
            </div>
        </div>
    </div>
</div>

<script>
console.log('=== PROJECT VIEW SCRIPT LOADED ===');
console.log('User is admin:', <?= $isAdmin ? 'true' : 'false' ?>);
console.log('Project ID:', '<?= $project['id'] ?>');
console.log('About to define functions...');

// Handle developer checkbox selection and display
function updateSelectedDevelopersDisplay() {
    const checkboxes = document.querySelectorAll('.developer-checkbox:checked');
    const displayDiv = document.getElementById('selectedDevelopersDisplay');
    
    if (checkboxes.length === 0) {
        displayDiv.innerHTML = '<span class="text-muted" id="noDevelopersText">No developers selected</span>';
    } else {
        let html = '';
        checkboxes.forEach(checkbox => {
            const username = checkbox.dataset.username;
            const devId = checkbox.value;
            html += `<span class="badge bg-primary d-flex align-items-center gap-2">
                ${username}
                <button type="button" class="btn-close btn-close-white" data-dev-id="${devId}" style="font-size: 0.7rem;"></button>
            </span>`;
        });
        displayDiv.innerHTML = html;
        
        // Add click handlers to remove badges
        displayDiv.querySelectorAll('.btn-close').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const devId = btn.dataset.devId;
                document.getElementById(`dev_${devId}`).checked = false;
                updateSelectedDevelopersDisplay();
            });
        });
    }
}

console.log('Setting up admin-only event listeners...');

// Add event listeners to all checkboxes (admin only)
const developerCheckboxes = document.querySelectorAll('.developer-checkbox');
if (developerCheckboxes.length > 0) {
    console.log('Found developer checkboxes, adding listeners');
    developerCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedDevelopersDisplay);
    });
} else {
    console.log('No developer checkboxes found (developer view)');
}

// Initialize display when modal is shown (admin only)
const modal = document.getElementById('assignDeveloperModal');
if (modal) {
    console.log('Found assign developer modal, adding listener');
    modal.addEventListener('show.bs.modal', function() {
        updateSelectedDevelopersDisplay();
    });
} else {
    console.log('No assign developer modal found (developer view)');
}

async function assignDevelopers() {
    const checkedBoxes = document.querySelectorAll('.developer-checkbox:checked');
    const projectRoleElement = document.getElementById('project_role');
    
    if (!projectRoleElement) {
        console.log('Project role element not found (developer view)');
        return;
    }
    
    const projectRole = projectRoleElement.value;
    
    if (checkedBoxes.length === 0) {
        alert('Please select at least one developer');
        return;
    }
    
    const developerIds = Array.from(checkedBoxes).map(checkbox => checkbox.value);
    
    try {
        // Assign each developer sequentially
        for (const developerId of developerIds) {
            const response = await fetch('<?= base_url('api/projects/' . $project['id'] . '/assign') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    user_id: developerId,
                    role: projectRole
                })
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                alert(data.message || 'Failed to assign developer ' + developerId);
                return;
            }
        }
        
        // All assignments successful
        location.reload();
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

async function removeUser(userId) {
    if (!confirm('Are you sure you want to remove this user from the project?')) {
        return;
    }
    
    try {
        const response = await fetch(`<?= base_url('api/projects/' . $project['id'] . '/users/') ?>${userId}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (response.ok) {
            location.reload();
        } else {
            alert(data.message || 'Failed to remove user');
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

// Load project credentials - make it globally available
window.loadCredentials = async function loadCredentials() {
    const container = document.getElementById('credentialsContainer');
    if (!container) {
        console.error('Credentials container not found');
        return;
    }
    
    const apiUrl = '<?= base_url('api/projects/' . $project['id'] . '/credentials') ?>';
    console.log('Loading credentials from:', apiUrl);
    
    try {
        // Create abort controller for timeout
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout
        
        const response = await fetch(apiUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            signal: controller.signal
        });
        
        clearTimeout(timeoutId);
        
        console.log('Response status:', response.status, response.statusText);
        console.log('Response headers:', response.headers);
        
        if (!response.ok) {
            console.error('Credentials API error:', response.status, response.statusText);
            let errorMessage = 'Failed to load credentials';
            
            try {
                const errorText = await response.text();
                console.error('Response body:', errorText);
                
                // Try to parse as JSON to get a better error message
                try {
                    const errorData = JSON.parse(errorText);
                    if (errorData.message) {
                        errorMessage = errorData.message;
                    }
                } catch (parseError) {
                    // If not JSON, use the raw text if it's meaningful
                    if (errorText && errorText.length < 200) {
                        errorMessage = errorText;
                    }
                }
            } catch (textError) {
                console.error('Error reading response text:', textError);
            }
            
            if (response.status === 403) {
                container.innerHTML = '<p class="text-warning"><i class="bi bi-shield-exclamation"></i> Access denied: You do not have permission to view credentials for this project</p>';
            } else if (response.status === 404) {
                container.innerHTML = '<p class="text-info"><i class="bi bi-info-circle"></i> No credentials found for this project</p>';
            } else {
                container.innerHTML = '<p class="text-danger"><i class="bi bi-exclamation-triangle"></i> ' + errorMessage + ' (HTTP ' + response.status + ')</p>';
            }
            return;
        }
        
        let result;
        try {
            result = await response.json();
            console.log('API Response:', result);
        } catch (parseError) {
            console.error('Failed to parse JSON response:', parseError);
            const responseText = await response.text();
            console.error('Response text:', responseText);
            container.innerHTML = '<p class="text-danger">Invalid API response format</p>';
            return;
        }
        
        if (!result.status || result.status !== 'success') {
            console.error('Credentials API returned error:', result);
            container.innerHTML = '<p class="text-danger">' + (result.message || 'Failed to load credentials') + '</p>';
            return;
        }
        
        const credentials = result.data || [];
        console.log('Credentials count:', credentials.length);
        
        if (credentials.length === 0) {
            container.innerHTML = '<p class="text-muted text-center py-3">No credentials added yet</p>';
            return;
        }
        
        let html = '<div class="list-group list-group-flush">';
        credentials.forEach(cred => {
            const isAdmin = <?= $isAdmin ? 'true' : 'false' ?>;
            const credId = `cred_${cred.id}`;
            html += `
                <div class="list-group-item px-0 py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <strong>${escapeHtml(cred.label)}</strong>
                                <span class="badge bg-info">${escapeHtml(cred.credential_type)}</span>
                            </div>
                            <small class="text-muted d-block">
                                ${cred.url ? `<div class="d-flex justify-content-between align-items-center"><div><strong>URL:</strong> ${escapeHtml(cred.url)}</div><button class="btn btn-sm btn-link p-0" onclick="copyToClipboard('${escapeHtml(cred.url)}', this)" title="Copy URL"><i class="bi bi-clipboard"></i></button></div>` : ''}
                                ${cred.email ? `<div class="d-flex justify-content-between align-items-center"><div><strong>Email:</strong> ${escapeHtml(cred.email)}</div><button class="btn btn-sm btn-link p-0" onclick="copyToClipboard('${escapeHtml(cred.email)}', this)" title="Copy Email"><i class="bi bi-clipboard"></i></button></div>` : ''}
                                ${cred.username ? `<div class="d-flex justify-content-between align-items-center"><div><strong>Username:</strong> ${escapeHtml(cred.username)}</div><button class="btn btn-sm btn-link p-0" onclick="copyToClipboard('${escapeHtml(cred.username)}', this)" title="Copy Username"><i class="bi bi-clipboard"></i></button></div>` : ''}
                                ${cred.password ? `<div class="d-flex justify-content-between align-items-center"><div><strong>Password:</strong> <span class="credential-value" id="pass_${credId}" data-value="${escapeHtml(cred.password)}">••••••••</span></div><div class="d-flex gap-1"><button class="btn btn-sm btn-link p-0" onclick="togglePasswordVisibility('pass_${credId}')" title="Show/Hide"><i class="bi bi-eye"></i></button><button class="btn btn-sm btn-link p-0" onclick="copyCredentialValue('pass_${credId}')" title="Copy Password"><i class="bi bi-clipboard"></i></button></div></div>` : ''}
                                ${cred.api_key ? `<div class="d-flex justify-content-between align-items-center"><div><strong>API Key:</strong> <span class="credential-value" id="key_${credId}" data-value="${escapeHtml(cred.api_key)}">••••••••</span></div><div class="d-flex gap-1"><button class="btn btn-sm btn-link p-0" onclick="togglePasswordVisibility('key_${credId}')" title="Show/Hide"><i class="bi bi-eye"></i></button><button class="btn btn-sm btn-link p-0" onclick="copyCredentialValue('key_${credId}')" title="Copy API Key"><i class="bi bi-clipboard"></i></button></div></div>` : ''}
                                ${cred.api_secret ? `<div class="d-flex justify-content-between align-items-center"><div><strong>API Secret:</strong> <span class="credential-value" id="secret_${credId}" data-value="${escapeHtml(cred.api_secret)}">••••••••</span></div><div class="d-flex gap-1"><button class="btn btn-sm btn-link p-0" onclick="togglePasswordVisibility('secret_${credId}')" title="Show/Hide"><i class="bi bi-eye"></i></button><button class="btn btn-sm btn-link p-0" onclick="copyCredentialValue('secret_${credId}')" title="Copy API Secret"><i class="bi bi-clipboard"></i></button></div></div>` : ''}
                                ${cred.notes ? `<div><strong>Notes:</strong> ${escapeHtml(cred.notes)}</div>` : ''}
                            </small>
                        </div>
                        ${isAdmin ? `
                            <div class="d-flex gap-2 ms-2">
                                <button class="btn btn-sm btn-outline-primary" onclick="editCredential(${cred.id})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteCredential(${cred.id})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    } catch (error) {
        console.error('Error loading credentials:', error);
        container.innerHTML = '<p class="text-danger">Error loading credentials: ' + error.message + '</p>';
    }
}

window.escapeHtml = function(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
};

window.togglePasswordVisibility = function(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const isVisible = element.textContent !== '••••••••';
    const actualValue = element.dataset.value;
    
    if (isVisible) {
        element.textContent = '••••••••';
    } else {
        element.textContent = actualValue;
    }
}

window.copyToClipboard = function(text, button) {
    navigator.clipboard.writeText(text).then(() => {
        const originalIcon = button.innerHTML;
        button.innerHTML = '<i class="bi bi-check"></i>';
        setTimeout(() => {
            button.innerHTML = originalIcon;
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy:', err);
        alert('Failed to copy to clipboard');
    });
};

window.copyCredentialValue = function(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const value = element.dataset.value;
    navigator.clipboard.writeText(value).then(() => {
        const button = event.target.closest('button');
        if (button) {
            const originalIcon = button.innerHTML;
            button.innerHTML = '<i class="bi bi-check"></i>';
            setTimeout(() => {
                button.innerHTML = originalIcon;
            }, 2000);
        }
    }).catch(err => {
        console.error('Failed to copy:', err);
        alert('Failed to copy to clipboard');
    });
}

async function addCredential() {
    const form = document.getElementById('credentialForm');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    if (!data.label || !data.credential_type) {
        alert('Please fill in all required fields');
        return;
    }
    
    try {
        const response = await fetch('<?= base_url('api/projects/' . $project['id'] . '/credentials') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (response.ok) {
            bootstrap.Modal.getInstance(document.getElementById('addCredentialModal')).hide();
            form.reset();
            loadCredentials();
        } else {
            alert(result.message || 'Failed to add credential');
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

async function editCredential(credentialId) {
    alert('Edit functionality coming soon');
}

async function deleteCredential(credentialId) {
    if (!confirm('Are you sure you want to delete this credential?')) {
        return;
    }
    
    try {
        const response = await fetch(`<?= base_url('api/projects/' . $project['id'] . '/credentials/') ?>${credentialId}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.json();
        
        if (response.ok) {
            loadCredentials();
        } else {
            alert(result.message || 'Failed to delete credential');
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

// Simple API test function
async function testCredentialsAPI() {
    const apiUrl = '<?= base_url('api/projects/' . $project['id'] . '/credentials') ?>';
    console.log('=== TESTING CREDENTIALS API ===');
    console.log('API URL:', apiUrl);
    console.log('User is admin:', <?= $isAdmin ? 'true' : 'false' ?>);
    
    try {
        const response = await fetch(apiUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });
        
        console.log('API Response Status:', response.status);
        console.log('API Response OK:', response.ok);
        
        if (response.ok) {
            const data = await response.json();
            console.log('API Response Data:', data);
            return data;
        } else {
            const errorText = await response.text();
            console.error('API Error Response:', errorText);
            return null;
        }
    } catch (error) {
        console.error('API Call Failed:', error);
        return null;
    }
}

// Debug function to test credentials loading
function testCredentialsSetup() {
    console.log('=== CREDENTIALS DEBUG TEST ===');
    const container = document.getElementById('credentialsContainer');
    console.log('Container found:', !!container);
    console.log('User is admin:', <?= $isAdmin ? 'true' : 'false' ?>);
    console.log('Project ID:', '<?= $project['id'] ?>');
    
    if (container) {
        container.innerHTML = '<p class="text-info">Testing credentials API...</p>';
        
        // Test API first
        testCredentialsAPI().then(result => {
            if (result) {
                console.log('API test successful, calling loadCredentials()');
                loadCredentials();
            } else {
                console.error('API test failed');
                container.innerHTML = '<p class="text-danger">API test failed - check console for details</p>';
            }
        });
    } else {
        console.error('Credentials container not found!');
    }
}

// Ensure all functions exist to prevent JavaScript errors
if (typeof editCredential === 'undefined') {
    window.editCredential = function(id) {
        console.log('Edit credential function not available for developers');
    };
}

if (typeof deleteCredential === 'undefined') {
    window.deleteCredential = function(id) {
        console.log('Delete credential function not available for developers');
    };
}

if (typeof addCredential === 'undefined') {
    window.addCredential = function() {
        console.log('Add credential function not available for developers');
    };
}

// Load credentials on page load
console.log('Setting up DOMContentLoaded listener for credentials');
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded fired, calling testCredentialsSetup()');
    try {
        testCredentialsSetup();
    } catch (error) {
        console.error('Error in testCredentialsSetup:', error);
    }
});

// Also try to load immediately in case DOM is already ready
if (document.readyState === 'loading') {
    console.log('Document still loading, waiting for DOMContentLoaded');
} else {
    console.log('Document already loaded, calling testCredentialsSetup() immediately');
    setTimeout(function() {
        try {
            testCredentialsSetup();
        } catch (error) {
            console.error('Error in delayed testCredentialsSetup:', error);
        }
    }, 100);
}
</script>

<?php if ($isAdmin): ?>
<!-- Add/Edit Credential Modal -->
<div class="modal fade" id="addCredentialModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Project Credential</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="credentialForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Label *</label>
                            <input type="text" class="form-control" name="label" placeholder="e.g., Production Database" required>
                            <small class="form-text text-muted">A descriptive name for this credential</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type *</label>
                            <select class="form-select" name="credential_type" required>
                                <option value="">Select Type</option>
                                <option value="database">Database</option>
                                <option value="smtp">SMTP</option>
                                <option value="payment_gateway">Payment Gateway</option>
                                <option value="api">API Keys</option>
                                <option value="hosting">Hosting/Server</option>
                                <option value="cdn">CDN</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">URL</label>
                            <input type="text" class="form-control" name="url" placeholder="https://example.com">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" placeholder="user@example.com">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" placeholder="Username">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" placeholder="Password">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">API Key</label>
                            <input type="password" class="form-control" name="api_key" placeholder="API Key">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">API Secret</label>
                            <input type="password" class="form-control" name="api_secret" placeholder="API Secret">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3" placeholder="Additional notes or instructions"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addCredential()">Add Credential</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php endif; ?>
</div>

<?= $this->endSection() ?>

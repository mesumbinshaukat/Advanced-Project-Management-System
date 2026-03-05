<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><?= esc($task['title']) ?></h5>
                <div>
                    <?php if ($isAdmin): ?>
                        <a href="<?= base_url('tasks/edit/' . $task['id']) ?>" class="btn btn-sm btn-primary">Edit</a>
                    <?php else: ?>
                        <?php if (in_array($task['status'], ['todo', 'in_progress'])): ?>
                            <button class="btn btn-sm btn-success" onclick="console.log('Button clicked for task <?= $task['id'] ?>'); console.log('submitTaskForReview function exists:', typeof submitTaskForReview); submitTaskForReview(<?= $task['id'] ?>)">
                                <i class="bi bi-check-circle"></i> Request Review
                            </button>
                        <?php elseif ($task['status'] === 'submitted_for_review'): ?>
                            <span class="badge bg-info">Review Requested</span>
                        <?php elseif ($task['status'] === 'needs_revision'): ?>
                            <span class="badge bg-warning">Needs Revision</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    <a href="<?= base_url('tasks') ?>" class="btn btn-sm btn-secondary">Back</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted">Project</h6>
                        <p><a href="<?= base_url('projects/view/' . $project['id']) ?>"><?= esc($project['name']) ?></a></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Status</h6>
                        <p>
                            <span class="badge bg-<?= $task['status'] === 'done' ? 'success' : ($task['status'] === 'in_progress' ? 'info' : 'warning') ?>">
                                <?= ucfirst(str_replace('_', ' ', $task['status'])) ?>
                            </span>
                        </p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted">Priority</h6>
                        <p>
                            <span class="badge bg-<?= $task['priority'] === 'high' ? 'danger' : ($task['priority'] === 'medium' ? 'warning' : 'info') ?>">
                                <?= ucfirst($task['priority']) ?>
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Assigned To</h6>
                        <p>
                            <?php if (!empty($assigned_developers)): ?>
                                <?php foreach ($assigned_developers as $developer): ?>
                                    <span class="badge bg-primary"><?= esc($developer['username']) ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-muted">Unassigned</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted">Deadline</h6>
                        <p><?= $task['deadline'] ? date('M d, Y', strtotime($task['deadline'])) : 'No deadline' ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Estimated Hours</h6>
                        <p><?= $task['estimated_hours'] ?? 'Not set' ?></p>
                    </div>
                </div>

                <?php if ($task['is_blocked']): ?>
                <div class="alert alert-danger" role="alert">
                    <strong>Blocked:</strong> <?= esc($task['blocker_reason'] ?? 'No reason provided') ?>
                </div>
                <?php endif; ?>

                <div class="mb-4">
                    <h6 class="text-muted">Description</h6>
                    <div class="card-text">
                        <?= $task['description'] ? nl2br(esc($task['description'])) : '<em>No description</em>' ?>
                    </div>
                </div>

                <?php if ($task['tags']): ?>
                <div class="mb-4">
                    <h6 class="text-muted">Tags</h6>
                    <p>
                        <?php foreach (explode(',', $task['tags']) as $tag): ?>
                        <span class="badge bg-secondary"><?= esc(trim($tag)) ?></span>
                        <?php endforeach; ?>
                    </p>
                </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">Created</h6>
                        <p><?= date('M d, Y H:i', strtotime($task['created_at'])) ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Updated</h6>
                        <p><?= date('M d, Y H:i', strtotime($task['updated_at'])) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Task Details</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">Task ID</small>
                    <p class="mb-0">#<?= $task['id'] ?></p>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Status</small>
                    <p class="mb-0"><?= ucfirst(str_replace('_', ' ', $task['status'])) ?></p>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Priority</small>
                    <p class="mb-0"><?= ucfirst($task['priority']) ?></p>
                </div>
                <?php if ($task['completed_at']): ?>
                <div class="mb-3">
                    <small class="text-muted">Completed</small>
                    <p class="mb-0"><?= date('M d, Y H:i', strtotime($task['completed_at'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Task Submission Quality Checklist Modal - Inline -->
<div class="modal fade" id="taskSubmissionChecklistModal" tabindex="-1" aria-labelledby="taskSubmissionChecklistModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="taskSubmissionChecklistModalLabel">
                    <i class="bi bi-clipboard-check"></i> Quality Assurance Checklist
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>Quality Check Required:</strong> Please confirm all items below before submitting your task for review. This ensures high-quality deliverables and reduces review cycles.
                </div>
                
                <form id="taskSubmissionChecklistForm">
                    <div class="checklist-items">
                        <div class="form-check mb-3 p-3 border rounded">
                            <input class="form-check-input" type="checkbox" id="is_responsive" name="is_responsive" value="1">
                            <label class="form-check-label fw-bold" for="is_responsive">
                                <i class="bi bi-phone text-primary me-2"></i>Website is responsive across all devices
                            </label>
                            <div class="text-muted small mt-1">
                                Tested on mobile, tablet, and desktop viewports (320px, 768px, 1200px+)
                            </div>
                        </div>

                        <div class="form-check mb-3 p-3 border rounded">
                            <input class="form-check-input" type="checkbox" id="no_ai_generated_text" name="no_ai_generated_text" value="1">
                            <label class="form-check-label fw-bold" for="no_ai_generated_text">
                                <i class="bi bi-robot text-warning me-2"></i>No AI-generated text without review
                            </label>
                            <div class="text-muted small mt-1">
                                All content has been manually reviewed, edited, and approved for accuracy
                            </div>
                        </div>

                        <div class="form-check mb-3 p-3 border rounded">
                            <input class="form-check-input" type="checkbox" id="all_links_working" name="all_links_working" value="1">
                            <label class="form-check-label fw-bold" for="all_links_working">
                                <i class="bi bi-link-45deg text-success me-2"></i>All links are working properly
                            </label>
                            <div class="text-muted small mt-1">
                                Internal navigation, external links, and anchor links have been tested
                            </div>
                        </div>

                        <div class="form-check mb-3 p-3 border rounded">
                            <input class="form-check-input" type="checkbox" id="code_reviewed" name="code_reviewed" value="1">
                            <label class="form-check-label fw-bold" for="code_reviewed">
                                <i class="bi bi-code-slash text-info me-2"></i>Code has been self-reviewed
                            </label>
                            <div class="text-muted small mt-1">
                                Code quality, comments, naming conventions, and best practices checked
                            </div>
                        </div>

                        <div class="form-check mb-3 p-3 border rounded">
                            <input class="form-check-input" type="checkbox" id="functionality_tested" name="functionality_tested" value="1">
                            <label class="form-check-label fw-bold" for="functionality_tested">
                                <i class="bi bi-gear text-secondary me-2"></i>All functionality has been tested
                            </label>
                            <div class="text-muted small mt-1">
                                Features work as expected in different scenarios and edge cases
                            </div>
                        </div>

                        <div class="form-check mb-3 p-3 border rounded">
                            <input class="form-check-input" type="checkbox" id="cross_browser_tested" name="cross_browser_tested" value="1">
                            <label class="form-check-label fw-bold" for="cross_browser_tested">
                                <i class="bi bi-browser-chrome text-danger me-2"></i>Cross-browser compatibility tested
                            </label>
                            <div class="text-muted small mt-1">
                                Tested in Chrome, Firefox, Safari, and Edge browsers
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="additional_notes" class="form-label fw-bold">
                            <i class="bi bi-chat-text text-muted me-2"></i>Additional Notes (Optional)
                        </label>
                        <textarea class="form-control" id="additional_notes" name="additional_notes" rows="3" 
                                  placeholder="Any additional information about the task completion, challenges faced, or notes for the reviewer..."></textarea>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Note:</strong> Please review the quality checklist items below. You can submit for review even if not all items are checked, but completing them helps ensure quality.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
                <button type="button" class="btn btn-success" id="submitTaskWithChecklist">
                    <i class="bi bi-check-circle"></i> Submit for Review
                </button>
            </div>
        </div>
    </div>
</div>

<script>
console.log('=== TASK VIEW SCRIPT START (USING SCRIPTS SECTION) ===');
console.log('Task view script loading...');
console.log('Available modal-related elements:', Array.from(document.querySelectorAll('[id*="modal"], [id*="Modal"], [class*="modal"]')).map(el => ({id: el.id, class: el.className})));

// Submit task for review function - defined in global scope
window.submitTaskForReview = function(taskId) {
    console.log('submitTaskForReview called with taskId:', taskId);
    
    // Show modal directly
    const checklistModal = document.getElementById('taskSubmissionChecklistModal');
    const checklistForm = document.getElementById('taskSubmissionChecklistForm');
    
    console.log('Modal elements check:', {
        modal: !!checklistModal,
        form: !!checklistForm,
        modalId: checklistModal?.id,
        formId: checklistForm?.id
    });
    
    if (!checklistModal || !checklistForm) {
        alert('Quality checklist modal not available. Please refresh the page and try again.');
        return;
    }
    
    console.log('Modal elements found, showing modal...');
    
    // Reset form
    checklistForm.reset();
    
    // Update submit button state (always enabled since items are optional)
    const submitButton = document.getElementById('submitTaskWithChecklist');
    submitButton.disabled = false;
    
    // Show modal
    const modal = new bootstrap.Modal(checklistModal);
    modal.show();
    console.log('Modal should now be visible');
    
    // Store task ID for submission
    window.currentTaskId = taskId;
};

console.log('submitTaskForReview function defined. Type:', typeof window.submitTaskForReview);

// Initialize modal functionality when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Task view DOMContentLoaded fired');
    const submitButton = document.getElementById('submitTaskWithChecklist');
    const checklistForm = document.getElementById('taskSubmissionChecklistForm');
    
    if (submitButton && checklistForm) {
        console.log('Modal form elements found, adding event listeners');
        
        // Add checkbox change listeners (optional items, no validation required)
        const checkboxes = checklistForm.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                // Submit button always enabled since items are optional
                submitButton.disabled = false;
                submitButton.classList.remove('btn-secondary');
                submitButton.classList.add('btn-success');
            });
        });
        
        // Handle checklist submission
        submitButton.addEventListener('click', async function() {
            console.log('Submit button clicked, currentTaskId:', window.currentTaskId);
            
            if (!window.currentTaskId) {
                alert('Error: No task selected');
                return;
            }
            
            // Collect checklist data
            const formData = new FormData(checklistForm);
            const checklistData = {};
            
            // Convert form data to object
            for (let [key, value] of formData.entries()) {
                if (key === 'additional_notes') {
                    checklistData[key] = value;
                } else {
                    checklistData[key] = value === '1' ? 1 : 0;
                }
            }
            
            // Ensure all required checkboxes are checked
            const requiredChecks = ['is_responsive', 'no_ai_generated_text', 'all_links_working', 'code_reviewed', 'functionality_tested', 'cross_browser_tested'];
            for (let check of requiredChecks) {
                if (!checklistData[check]) {
                    checklistData[check] = 0;
                }
            }
            
            console.log('Submitting checklist data:', checklistData);
            
            // Disable submit button during submission
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="bi bi-hourglass-split"></i> Submitting...';
            
            try {
                const response = await fetch(`<?= base_url('api/tasks/') ?>${window.currentTaskId}/submit-review`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(checklistData)
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    // Hide modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('taskSubmissionChecklistModal'));
                    if (modal) modal.hide();
                    
                    // Show success message
                    alert('Task submitted for review successfully with quality checklist!');
                    
                    // Reload page to reflect changes
                    location.reload();
                } else {
                    alert(data.message || 'Failed to submit task for review');
                }
            } catch (error) {
                console.error('Error submitting task for review:', error);
                alert('Error submitting task for review');
            } finally {
                // Re-enable submit button
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="bi bi-check-circle"></i> Submit for Review';
            }
        });
    } else {
        console.log('Modal form elements not found');
    }
});

console.log('=== TASK VIEW SCRIPT END ===');
</script>
<?= $this->endSection() ?>

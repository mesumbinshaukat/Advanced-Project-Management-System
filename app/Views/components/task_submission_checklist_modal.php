<!-- Task Submission Quality Checklist Modal -->
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
                            <input class="form-check-input" type="checkbox" id="is_responsive" name="is_responsive" value="1" required>
                            <label class="form-check-label fw-bold" for="is_responsive">
                                <i class="bi bi-phone text-primary me-2"></i>Website is responsive across all devices
                            </label>
                            <div class="text-muted small mt-1">
                                Tested on mobile, tablet, and desktop viewports (320px, 768px, 1200px+)
                            </div>
                        </div>

                        <div class="form-check mb-3 p-3 border rounded">
                            <input class="form-check-input" type="checkbox" id="no_ai_generated_text" name="no_ai_generated_text" value="1" required>
                            <label class="form-check-label fw-bold" for="no_ai_generated_text">
                                <i class="bi bi-robot text-warning me-2"></i>No AI-generated text without review
                            </label>
                            <div class="text-muted small mt-1">
                                All content has been manually reviewed, edited, and approved for accuracy
                            </div>
                        </div>

                        <div class="form-check mb-3 p-3 border rounded">
                            <input class="form-check-input" type="checkbox" id="all_links_working" name="all_links_working" value="1" required>
                            <label class="form-check-label fw-bold" for="all_links_working">
                                <i class="bi bi-link-45deg text-success me-2"></i>All links are working properly
                            </label>
                            <div class="text-muted small mt-1">
                                Internal navigation, external links, and anchor links have been tested
                            </div>
                        </div>

                        <div class="form-check mb-3 p-3 border rounded">
                            <input class="form-check-input" type="checkbox" id="code_reviewed" name="code_reviewed" value="1" required>
                            <label class="form-check-label fw-bold" for="code_reviewed">
                                <i class="bi bi-code-slash text-info me-2"></i>Code has been self-reviewed
                            </label>
                            <div class="text-muted small mt-1">
                                Code quality, comments, naming conventions, and best practices checked
                            </div>
                        </div>

                        <div class="form-check mb-3 p-3 border rounded">
                            <input class="form-check-input" type="checkbox" id="functionality_tested" name="functionality_tested" value="1" required>
                            <label class="form-check-label fw-bold" for="functionality_tested">
                                <i class="bi bi-gear text-secondary me-2"></i>All functionality has been tested
                            </label>
                            <div class="text-muted small mt-1">
                                Features work as expected in different scenarios and edge cases
                            </div>
                        </div>

                        <div class="form-check mb-3 p-3 border rounded">
                            <input class="form-check-input" type="checkbox" id="cross_browser_tested" name="cross_browser_tested" value="1" required>
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

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Important:</strong> All checklist items must be completed before you can submit the task for review.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
                <button type="button" class="btn btn-success" id="submitTaskWithChecklist" disabled>
                    <i class="bi bi-check-circle"></i> Submit for Review
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Task Submission Checklist Modal Logic
console.log('=== MODAL COMPONENT SCRIPT START ===');
console.log('Task submission checklist modal script loading...');
console.log('Current window object keys:', Object.keys(window).filter(k => k.includes('Task') || k.includes('show')));
console.log('Document readyState:', document.readyState);

// Define the function immediately in global scope
window.showTaskSubmissionChecklist = function(taskId) {
    console.log('showTaskSubmissionChecklist called with taskId:', taskId);
    
    // Wait for DOM elements to be available
    const checklistModal = document.getElementById('taskSubmissionChecklistModal');
    const checklistForm = document.getElementById('taskSubmissionChecklistForm');
    
    if (!checklistModal || !checklistForm) {
        console.log('Modal elements not ready, waiting...');
        setTimeout(function() {
            window.showTaskSubmissionChecklist(taskId);
        }, 100);
        return;
    }
    
    console.log('Modal elements found, proceeding...');
    currentTaskId = taskId;
    
    // Reset form and show modal
    checklistForm.reset();
    updateSubmitButton();
    
    const modal = new bootstrap.Modal(checklistModal);
    modal.show();
    console.log('Modal should now be visible');
};

console.log('showTaskSubmissionChecklist function defined immediately. Type:', typeof window.showTaskSubmissionChecklist);
console.log('=== MODAL COMPONENT SCRIPT END ===');

// Store task ID for submission
let currentTaskId = null;

// Initialize modal functionality when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Modal component DOMContentLoaded fired');
    const checklistModal = document.getElementById('taskSubmissionChecklistModal');
    const checklistForm = document.getElementById('taskSubmissionChecklistForm');
    const submitButton = document.getElementById('submitTaskWithChecklist');
    const checkboxes = checklistForm.querySelectorAll('input[type="checkbox"]');
    
    // Enable/disable submit button based on checklist completion
    window.updateSubmitButton = function() {
        const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
        submitButton.disabled = !allChecked;
        
        if (allChecked) {
            submitButton.classList.remove('btn-secondary');
            submitButton.classList.add('btn-success');
        } else {
            submitButton.classList.remove('btn-success');
            submitButton.classList.add('btn-secondary');
        }
    };
    
    // Add event listeners to all checkboxes
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', window.updateSubmitButton);
    });
    
    // Initialize button state
    window.updateSubmitButton();
    
    // Handle checklist submission
    submitButton.addEventListener('click', async function() {
        if (!currentTaskId) {
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
        
        // Disable submit button during submission
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="bi bi-hourglass-split"></i> Submitting...';
        
        try {
            const response = await fetch(`<?= base_url('api/tasks/') ?>${currentTaskId}/submit-review`, {
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
                const modal = bootstrap.Modal.getInstance(checklistModal);
                modal.hide();
                
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
            updateSubmitButton();
        }
    });
    
    // Reset form when modal is hidden
    checklistModal.addEventListener('hidden.bs.modal', function() {
        checklistForm.reset();
        currentTaskId = null;
        updateSubmitButton();
    });
});
</script>

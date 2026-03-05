<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskSubmissionChecklistModel extends Model
{
    protected $table = 'task_submission_checklists';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'task_id',
        'user_id',
        'is_responsive',
        'no_ai_generated_text',
        'all_links_working',
        'code_reviewed',
        'functionality_tested',
        'cross_browser_tested',
        'additional_notes',
        'submitted_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'task_id' => 'required|integer',
        'user_id' => 'required|integer',
        'is_responsive' => 'required|in_list[0,1]',
        'no_ai_generated_text' => 'required|in_list[0,1]',
        'all_links_working' => 'required|in_list[0,1]',
        'code_reviewed' => 'required|in_list[0,1]',
        'functionality_tested' => 'required|in_list[0,1]',
        'cross_browser_tested' => 'required|in_list[0,1]',
        'submitted_at' => 'required|valid_date'
    ];

    protected $validationMessages = [
        'task_id' => [
            'required' => 'Task ID is required',
            'integer' => 'Task ID must be a valid integer'
        ],
        'user_id' => [
            'required' => 'User ID is required',
            'integer' => 'User ID must be a valid integer'
        ],
        'is_responsive' => [
            'required' => 'Responsive check is required',
            'in_list' => 'Responsive check must be 0 or 1'
        ],
        'no_ai_generated_text' => [
            'required' => 'AI-generated text check is required',
            'in_list' => 'AI-generated text check must be 0 or 1'
        ],
        'all_links_working' => [
            'required' => 'Links working check is required',
            'in_list' => 'Links working check must be 0 or 1'
        ],
        'code_reviewed' => [
            'required' => 'Code review check is required',
            'in_list' => 'Code review check must be 0 or 1'
        ],
        'functionality_tested' => [
            'required' => 'Functionality test check is required',
            'in_list' => 'Functionality test check must be 0 or 1'
        ],
        'cross_browser_tested' => [
            'required' => 'Cross-browser test check is required',
            'in_list' => 'Cross-browser test check must be 0 or 1'
        ],
        'submitted_at' => [
            'required' => 'Submission time is required',
            'valid_date' => 'Submission time must be a valid date'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Get checklist for a specific task
     */
    public function getTaskChecklist($taskId)
    {
        return $this->where('task_id', $taskId)
                   ->orderBy('submitted_at', 'DESC')
                   ->first();
    }

    /**
     * Get checklist with user information
     */
    public function getTaskChecklistWithUser($taskId)
    {
        return $this->select('task_submission_checklists.*, users.username')
                   ->join('users', 'users.id = task_submission_checklists.user_id')
                   ->where('task_submission_checklists.task_id', $taskId)
                   ->orderBy('task_submission_checklists.submitted_at', 'DESC')
                   ->first();
    }

    /**
     * Create or update checklist for task submission
     */
    public function createTaskChecklist($data)
    {
        // Check if checklist already exists for this task
        $existing = $this->where('task_id', $data['task_id'])->first();
        
        if ($existing) {
            // Update existing checklist
            return $this->update($existing['id'], $data);
        } else {
            // Create new checklist
            return $this->insert($data);
        }
    }

    /**
     * Validate all required checks are completed
     */
    public function validateChecklist($data)
    {
        $requiredChecks = [
            'is_responsive',
            'no_ai_generated_text',
            'all_links_working',
            'code_reviewed',
            'functionality_tested',
            'cross_browser_tested'
        ];

        foreach ($requiredChecks as $check) {
            if (!isset($data[$check]) || $data[$check] != 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get checklist items configuration
     */
    public function getChecklistItems()
    {
        return [
            'is_responsive' => [
                'label' => 'Website is responsive across all devices',
                'description' => 'Tested on mobile, tablet, and desktop viewports'
            ],
            'no_ai_generated_text' => [
                'label' => 'No AI-generated text without review',
                'description' => 'All content has been manually reviewed and approved'
            ],
            'all_links_working' => [
                'label' => 'All links are working properly',
                'description' => 'Internal and external links have been tested'
            ],
            'code_reviewed' => [
                'label' => 'Code has been self-reviewed',
                'description' => 'Code quality, comments, and best practices checked'
            ],
            'functionality_tested' => [
                'label' => 'All functionality has been tested',
                'description' => 'Features work as expected in different scenarios'
            ],
            'cross_browser_tested' => [
                'label' => 'Cross-browser compatibility tested',
                'description' => 'Tested in Chrome, Firefox, Safari, and Edge'
            ]
        ];
    }
}

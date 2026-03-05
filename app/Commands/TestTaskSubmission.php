<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestTaskSubmission extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'test:submission';
    protected $description = 'Test task submission checklist functionality';

    public function run(array $params)
    {
        CLI::write('=== TASK SUBMISSION CHECKLIST TEST ===', 'green');
        CLI::newLine();

        // Get database connection
        $db = \Config\Database::connect();

        CLI::write('1. CHECKING DATABASE TABLES...', 'yellow');

        // Check if task_submission_checklists table exists
        $tables = $db->listTables();
        if (in_array('task_submission_checklists', $tables)) {
            CLI::write('✅ task_submission_checklists table exists', 'green');
            
            // Show table structure
            $fields = $db->getFieldData('task_submission_checklists');
            CLI::write('   Table structure:', 'light_gray');
            foreach ($fields as $field) {
                CLI::write("   - {$field->name} ({$field->type})", 'light_gray');
            }
        } else {
            CLI::write('❌ task_submission_checklists table missing', 'red');
            return;
        }

        CLI::newLine();
        CLI::write('2. SEEDING DEMO DATA...', 'yellow');

        // Create test user (developer)
        $userModel = new \CodeIgniter\Shield\Models\UserModel();
        $existingUser = $userModel->where('username', 'testdev')->first();

        if (!$existingUser) {
            CLI::write('Creating test developer user...', 'light_gray');
            
            $user = new \CodeIgniter\Shield\Entities\User([
                'username' => 'testdev',
                'email'    => 'testdev@example.com',
                'active'   => 1,
            ]);
            
            $userModel->save($user);
            $userId = $userModel->getInsertID();
            
            // Set password
            $user = $userModel->findById($userId);
            $user->password = 'password123';
            $userModel->save($user);
            
            // Add to developer group
            $user->addGroup('developer');
            
            CLI::write("✅ Test developer created (ID: $userId)", 'green');
        } else {
            $userId = $existingUser->id;
            CLI::write("✅ Test developer exists (ID: $userId)", 'green');
        }

        // Create test client
        $clientModel = new \App\Models\ClientModel();
        $existingClient = $clientModel->where('name', 'Test Client')->first();

        if (!$existingClient) {
            CLI::write('Creating test client...', 'light_gray');
            $clientData = [
                'name' => 'Test Client',
                'email' => 'client@example.com',
                'phone' => '123-456-7890',
                'address' => '123 Test St',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $clientModel->insert($clientData);
            $clientId = $clientModel->getInsertID();
            CLI::write("✅ Test client created (ID: $clientId)", 'green');
        } else {
            $clientId = $existingClient['id'];
            CLI::write("✅ Test client exists (ID: $clientId)", 'green');
        }

        // Create test project
        $projectModel = new \App\Models\ProjectModel();
        $existingProject = $projectModel->where('name', 'Test Project for Checklist')->first();

        if (!$existingProject) {
            CLI::write('Creating test project...', 'light_gray');
            $projectData = [
                'name' => 'Test Project for Checklist',
                'description' => 'A test project to verify task submission checklist functionality',
                'client_id' => $clientId,
                'status' => 'active',
                'priority' => 'medium',
                'start_date' => date('Y-m-d'),
                'deadline' => date('Y-m-d', strtotime('+30 days')),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $projectModel->insert($projectData);
            $projectId = $projectModel->getInsertID();
            CLI::write("✅ Test project created (ID: $projectId)", 'green');
        } else {
            $projectId = $existingProject['id'];
            CLI::write("✅ Test project exists (ID: $projectId)", 'green');
        }

        // Assign user to project
        $projectUserModel = new \App\Models\ProjectUserModel();
        $existingAssignment = $projectUserModel->where('project_id', $projectId)->where('user_id', $userId)->first();

        if (!$existingAssignment) {
            CLI::write('Assigning user to project...', 'light_gray');
            $assignmentData = [
                'project_id' => $projectId,
                'user_id' => $userId,
                'role' => 'developer',
                'assigned_at' => date('Y-m-d H:i:s')
            ];
            
            $projectUserModel->insert($assignmentData);
            CLI::write('✅ User assigned to project', 'green');
        } else {
            CLI::write('✅ User already assigned to project', 'green');
        }

        // Create test task
        $taskModel = new \App\Models\TaskModel();
        $existingTask = $taskModel->where('title', 'Test Task for Checklist Submission')->first();

        if (!$existingTask) {
            CLI::write('Creating test task...', 'light_gray');
            $taskData = [
                'title' => 'Test Task for Checklist Submission',
                'description' => 'A test task to verify the quality checklist submission workflow',
                'project_id' => $projectId,
                'status' => 'in_progress',
                'priority' => 'medium',
                'estimated_hours' => 8.0,
                'start_date' => date('Y-m-d'),
                'deadline' => date('Y-m-d', strtotime('+7 days')),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $taskModel->insert($taskData);
            $taskId = $taskModel->getInsertID();
            CLI::write("✅ Test task created (ID: $taskId)", 'green');
        } else {
            $taskId = $existingTask['id'];
            CLI::write("✅ Test task exists (ID: $taskId)", 'green');
        }

        // Assign task to user
        $taskAssignmentModel = new \App\Models\TaskAssignmentModel();
        $existingTaskAssignment = $taskAssignmentModel->where('task_id', $taskId)->where('user_id', $userId)->first();

        if (!$existingTaskAssignment) {
            CLI::write('Assigning task to user...', 'light_gray');
            $taskAssignmentData = [
                'task_id' => $taskId,
                'user_id' => $userId,
                'assigned_at' => date('Y-m-d H:i:s')
            ];
            
            $taskAssignmentModel->insert($taskAssignmentData);
            CLI::write('✅ Task assigned to user', 'green');
        } else {
            CLI::write('✅ Task already assigned to user', 'green');
        }

        CLI::newLine();
        CLI::write('3. TESTING CHECKLIST MODEL...', 'yellow');

        $checklistData = [
            'is_responsive' => 1,
            'no_ai_generated_text' => 1,
            'all_links_working' => 1,
            'code_reviewed' => 1,
            'functionality_tested' => 1,
            'cross_browser_tested' => 1,
            'additional_notes' => 'Test submission via CLI command'
        ];

        $checklistModel = new \App\Models\TaskSubmissionChecklistModel();
        
        // Prepare checklist data with required fields
        $testChecklistData = array_merge($checklistData, [
            'task_id' => $taskId,
            'user_id' => $userId,
            'submitted_at' => date('Y-m-d H:i:s')
        ]);
        
        // Validate checklist
        if ($checklistModel->validateChecklist($testChecklistData)) {
            CLI::write('✅ Checklist validation passed', 'green');
            
            // Test database insertion
            if ($checklistModel->createTaskChecklist($testChecklistData)) {
                CLI::write('✅ Checklist saved to database', 'green');
                
                // Update task status
                $taskUpdateData = [
                    'status' => 'submitted_for_review',
                    'submitted_for_review_at' => date('Y-m-d H:i:s')
                ];
                
                if ($taskModel->update($taskId, $taskUpdateData)) {
                    CLI::write('✅ Task status updated to submitted_for_review', 'green');
                } else {
                    CLI::write('❌ Failed to update task status', 'red');
                }
            } else {
                CLI::write('❌ Failed to save checklist to database', 'red');
                CLI::write('Errors: ' . json_encode($checklistModel->errors()), 'red');
            }
        } else {
            CLI::write('❌ Checklist validation failed', 'red');
            CLI::write('Errors: ' . json_encode($checklistModel->errors()), 'red');
        }

        CLI::newLine();
        CLI::write('4. VERIFYING DATABASE RECORDS...', 'yellow');

        // Check if checklist was saved
        $savedChecklist = $db->table('task_submission_checklists')
            ->where('task_id', $taskId)
            ->where('user_id', $userId)
            ->get()
            ->getRowArray();

        if ($savedChecklist) {
            CLI::write('✅ Checklist record found in database:', 'green');
            foreach ($savedChecklist as $key => $value) {
                CLI::write("   $key: $value", 'light_gray');
            }
        } else {
            CLI::write('❌ No checklist record found in database', 'red');
        }

        // Check task status
        $updatedTask = $taskModel->find($taskId);
        if ($updatedTask && $updatedTask['status'] === 'submitted_for_review') {
            CLI::write('✅ Task status correctly updated to submitted_for_review', 'green');
        } else {
            CLI::write('❌ Task status not updated correctly', 'red');
            CLI::write('   Current status: ' . ($updatedTask['status'] ?? 'unknown'), 'red');
        }

        CLI::newLine();
        CLI::write('5. CHECKING MODAL HTML IN VIEW...', 'yellow');

        // Test if modal HTML is being included properly
        $taskViewPath = APPPATH . 'Views/tasks/view.php';
        if (file_exists($taskViewPath)) {
            $viewContent = file_get_contents($taskViewPath);
            
            if (strpos($viewContent, 'taskSubmissionChecklistModal') !== false) {
                CLI::write('✅ Modal HTML found in task view file', 'green');
            } else {
                CLI::write('❌ Modal HTML not found in task view file', 'red');
            }
            
            if (strpos($viewContent, 'submitTaskForReview') !== false) {
                CLI::write('✅ submitTaskForReview function found in task view file', 'green');
            } else {
                CLI::write('❌ submitTaskForReview function not found in task view file', 'red');
            }
            
            if (strpos($viewContent, '<?= $this->section(\'scripts\') ?>') !== false) {
                CLI::write('✅ Scripts section properly defined', 'green');
            } else {
                CLI::write('❌ Scripts section not properly defined', 'red');
            }
        } else {
            CLI::write('❌ Task view file not found', 'red');
        }

        CLI::newLine();
        CLI::write('6. GENERATING TEST URLS...', 'yellow');

        CLI::write('Test URLs:', 'white');
        CLI::write('- Task View: ' . base_url("tasks/view/$taskId"), 'cyan');
        CLI::write('- Project Kanban: ' . base_url("tasks/kanban/$projectId"), 'cyan');
        CLI::write('- API Endpoint: ' . base_url("api/tasks/$taskId/submit-review"), 'cyan');

        CLI::newLine();
        CLI::write('=== TEST COMPLETE ===', 'green');
        CLI::write('Next steps:', 'white');
        CLI::write('1. Visit the task view URL above', 'light_gray');
        CLI::write('2. Check browser console for JavaScript errors', 'light_gray');
        CLI::write('3. Verify modal HTML is in DOM', 'light_gray');
        CLI::write('4. Test the Request Review button', 'light_gray');
    }
}

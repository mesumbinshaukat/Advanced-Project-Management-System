<?php

namespace App\Controllers;

use App\Models\ProjectTemplateModel;
use App\Models\TaskTemplateModel;
use App\Models\ProjectModel;
use App\Models\TaskModel;

class TemplatesController extends BaseController
{
    protected $projectTemplateModel;
    protected $taskTemplateModel;

    public function __construct()
    {
        $this->projectTemplateModel = new ProjectTemplateModel();
        $this->taskTemplateModel = new TaskTemplateModel();
    }

    public function index()
    {
        $projectTemplates = $this->projectTemplateModel->getActiveTemplates();
        $taskTemplates = $this->taskTemplateModel->getActiveTemplates();

        return view('templates/index', [
            'title' => 'Templates',
            'project_templates' => $projectTemplates,
            'task_templates' => $taskTemplates,
        ]);
    }

    public function createProject()
    {
        return view('templates/create_project', [
            'title' => 'Create Project Template',
        ]);
    }

    public function storeProject()
    {
        $data = $this->request->getPost();
        $data['created_by'] = auth()->id();
        
        if (isset($data['task_templates_json'])) {
            $data['task_templates'] = json_decode($data['task_templates_json'], true);
            unset($data['task_templates_json']);
        }

        if (!$this->projectTemplateModel->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $this->projectTemplateModel->errors());
        }

        return redirect()->to('/templates')->with('success', 'Project template created');
    }

    public function createTask()
    {
        return view('templates/create_task', [
            'title' => 'Create Task Template',
        ]);
    }

    public function storeTask()
    {
        $data = $this->request->getPost();
        $data['created_by'] = auth()->id();
        
        if (isset($data['checklist_json'])) {
            $data['checklist_items'] = json_decode($data['checklist_json'], true);
            unset($data['checklist_json']);
        }

        if (!$this->taskTemplateModel->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $this->taskTemplateModel->errors());
        }

        return redirect()->to('/templates')->with('success', 'Task template created');
    }

    public function useProjectTemplate($templateId)
    {
        $template = $this->projectTemplateModel->find($templateId);

        if (!$template) {
            return redirect()->back()->with('error', 'Template not found');
        }

        // Ensure task_templates is decoded
        if (isset($template['task_templates']) && is_string($template['task_templates'])) {
            $decoded = json_decode($template['task_templates'], true);
            $template['task_templates'] = is_array($decoded) ? $decoded : [];
        }

        return view('templates/use_project', [
            'title' => 'Use Project Template',
            'template' => $template,
        ]);
    }

    public function applyProjectTemplate()
    {
        try {
            $templateId = $this->request->getPost('template_id');
            
            if (empty($templateId)) {
                return redirect()->back()->with('error', 'Template ID is required');
            }
            
            $template = $this->projectTemplateModel->find($templateId);

            if (!$template) {
                return redirect()->back()->with('error', 'Template not found');
            }

            // Ensure task_templates is decoded
            if (isset($template['task_templates']) && is_string($template['task_templates'])) {
                $decoded = json_decode($template['task_templates'], true);
                $template['task_templates'] = is_array($decoded) ? $decoded : [];
            }

            // Validate required fields
            $name = trim($this->request->getPost('name'));
            $clientId = $this->request->getPost('client_id');
            
            if (empty($name)) {
                return redirect()->back()->withInput()->with('error', 'Project name is required');
            }
            
            if (empty($clientId)) {
                return redirect()->back()->withInput()->with('error', 'Client is required');
            }

            $projectModel = new ProjectModel();
            $projectData = [
                'name' => $name,
                'client_id' => (int)$clientId,
                'description' => $template['description'] ?? null,
                'priority' => $template['default_priority'] ?? 'medium',
                'budget' => $template['default_budget'] ?? null,
                'start_date' => $this->request->getPost('start_date') ?: null,
                'deadline' => $this->request->getPost('deadline') ?: null,
                'status' => 'active',
                'created_by' => auth()->id(),
            ];

            if (!$projectModel->insert($projectData)) {
                return redirect()->back()->withInput()->with('errors', $projectModel->errors());
            }

            $projectId = $projectModel->getInsertID();

            // Create tasks from template
            if (!empty($template['task_templates'])) {
                $taskTemplates = $template['task_templates'];
                
                // Decode JSON if it's a string
                if (is_string($taskTemplates)) {
                    $decoded = json_decode($taskTemplates, true);
                    $taskTemplates = is_array($decoded) ? $decoded : [];
                }
                
                if (is_array($taskTemplates) && count($taskTemplates) > 0) {
                    $taskModel = new TaskModel();
                    foreach ($taskTemplates as $taskTemplate) {
                        if (is_array($taskTemplate) && isset($taskTemplate['title'])) {
                            $taskModel->insert([
                                'project_id' => $projectId,
                                'title' => $taskTemplate['title'],
                                'description' => $taskTemplate['description'] ?? null,
                                'priority' => $taskTemplate['priority'] ?? 'medium',
                                'estimated_hours' => $taskTemplate['estimated_hours'] ?? null,
                                'status' => 'backlog',
                                'created_by' => auth()->id(),
                            ]);
                        }
                    }
                }
            }

            return redirect()->to("/projects/view/{$projectId}")->with('success', 'Project created from template');
        } catch (\Exception $e) {
            log_message('error', 'Apply template error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Error creating project: ' . $e->getMessage());
        }
    }
}

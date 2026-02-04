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
            'project_templates' => $projectTemplates,
            'task_templates' => $taskTemplates,
        ]);
    }

    public function createProject()
    {
        return view('templates/create_project');
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
        return view('templates/create_task');
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

        return view('templates/use_project', [
            'template' => $template,
        ]);
    }

    public function applyProjectTemplate()
    {
        $templateId = $this->request->getPost('template_id');
        $template = $this->projectTemplateModel->find($templateId);

        if (!$template) {
            return redirect()->back()->with('error', 'Template not found');
        }

        $projectModel = new ProjectModel();
        $projectData = [
            'name' => $this->request->getPost('name'),
            'client_id' => $this->request->getPost('client_id'),
            'description' => $template['description'],
            'priority' => $template['default_priority'],
            'budget' => $template['default_budget'],
            'start_date' => $this->request->getPost('start_date'),
            'deadline' => $this->request->getPost('deadline'),
            'status' => 'active',
            'created_by' => auth()->id(),
        ];

        if (!$projectModel->insert($projectData)) {
            return redirect()->back()->withInput()->with('errors', $projectModel->errors());
        }

        $projectId = $projectModel->getInsertID();

        if (!empty($template['task_templates'])) {
            $taskModel = new TaskModel();
            foreach ($template['task_templates'] as $taskTemplate) {
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

        return redirect()->to("/projects/view/{$projectId}")->with('success', 'Project created from template');
    }
}

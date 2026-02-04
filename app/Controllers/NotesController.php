<?php

namespace App\Controllers;

use App\Models\NoteModel;
use App\Models\ProjectModel;
use App\Models\TaskModel;

class NotesController extends BaseController
{
    protected $noteModel;
    protected $projectModel;
    protected $taskModel;

    public function __construct()
    {
        $this->noteModel = new NoteModel();
        $this->projectModel = new ProjectModel();
        $this->taskModel = new TaskModel();
    }

    public function index()
    {
        $projectId = $this->request->getGet('project_id');
        $taskId = $this->request->getGet('task_id');

        if ($projectId) {
            $notes = $this->noteModel->getProjectNotes($projectId);
            $context = $this->projectModel->find($projectId);
            $contextType = 'project';
        } elseif ($taskId) {
            $notes = $this->noteModel->getTaskNotes($taskId);
            $context = $this->taskModel->find($taskId);
            $contextType = 'task';
        } else {
            return redirect()->back()->with('error', 'Invalid context');
        }

        return view('notes/index', [
            'notes' => $notes,
            'context' => $context,
            'contextType' => $contextType,
            'projectId' => $projectId,
            'taskId' => $taskId,
        ]);
    }

    public function create()
    {
        $projectId = $this->request->getGet('project_id');
        $taskId = $this->request->getGet('task_id');

        return view('notes/create', [
            'projectId' => $projectId,
            'taskId' => $taskId,
        ]);
    }

    public function store()
    {
        $data = $this->request->getPost();
        $data['user_id'] = auth()->id();

        if (!$this->noteModel->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $this->noteModel->errors());
        }

        $redirectUrl = $data['project_id'] 
            ? "/notes?project_id={$data['project_id']}" 
            : "/notes?task_id={$data['task_id']}";

        return redirect()->to($redirectUrl)->with('success', 'Note created successfully');
    }

    public function edit($id)
    {
        $note = $this->noteModel->find($id);

        if (!$note) {
            return redirect()->back()->with('error', 'Note not found');
        }

        return view('notes/edit', ['note' => $note]);
    }

    public function update($id)
    {
        $data = $this->request->getPost();

        if (!$this->noteModel->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $this->noteModel->errors());
        }

        $note = $this->noteModel->find($id);
        $redirectUrl = $note['project_id'] 
            ? "/notes?project_id={$note['project_id']}" 
            : "/notes?task_id={$note['task_id']}";

        return redirect()->to($redirectUrl)->with('success', 'Note updated successfully');
    }

    public function delete($id)
    {
        $note = $this->noteModel->find($id);
        
        if (!$note) {
            return redirect()->back()->with('error', 'Note not found');
        }

        $this->noteModel->delete($id);

        $redirectUrl = $note['project_id'] 
            ? "/notes?project_id={$note['project_id']}" 
            : "/notes?task_id={$note['task_id']}";

        return redirect()->to($redirectUrl)->with('success', 'Note deleted successfully');
    }
}

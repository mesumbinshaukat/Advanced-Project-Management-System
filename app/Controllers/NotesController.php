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
        try {
            $user = auth()->user();
            $isAdmin = $user->inGroup('admin');
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
                // Show all notes for the user
                $notes = $this->noteModel
                    ->select('notes.*, projects.name as project_name, tasks.title as task_title, users.username')
                    ->join('users', 'users.id = notes.user_id')
                    ->join('projects', 'projects.id = notes.project_id', 'left')
                    ->join('tasks', 'tasks.id = notes.task_id', 'left')
                    ->where('notes.user_id', $user->id)
                    ->orderBy('notes.created_at', 'DESC')
                    ->findAll();
                $context = null;
                $contextType = 'all';
            }

            return view('notes/index', [
                'title' => 'Notes',
                'notes' => $notes,
                'context' => $context,
                'contextType' => $contextType,
                'projectId' => $projectId,
                'taskId' => $taskId,
            ]);
        } catch (\Throwable $e) {
            $errorFile = WRITEPATH . 'logs/error_debug.log';
            $errorMsg = date('Y-m-d H:i:s') . ' - ' . get_class($e) . ': ' . $e->getMessage() . "\n";
            $errorMsg .= "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
            $errorMsg .= "Trace:\n" . $e->getTraceAsString() . "\n\n";
            file_put_contents($errorFile, $errorMsg, FILE_APPEND);
            
            throw $e;
        }
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

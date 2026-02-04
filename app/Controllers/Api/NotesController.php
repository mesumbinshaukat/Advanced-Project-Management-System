<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\NoteModel;

class NotesController extends ResourceController
{
    protected $modelName = 'App\Models\NoteModel';
    protected $format = 'json';

    public function index()
    {
        $projectId = $this->request->getGet('project_id');
        $taskId = $this->request->getGet('task_id');

        $model = new NoteModel();

        if ($projectId) {
            $notes = $model->getProjectNotes($projectId);
        } elseif ($taskId) {
            $notes = $model->getTaskNotes($taskId);
        } else {
            return $this->failValidationErrors('project_id or task_id required');
        }

        return $this->respond($notes);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        $data['user_id'] = auth()->id();

        if (!$this->model->insert($data)) {
            return $this->failValidationErrors($this->model->errors());
        }

        return $this->respondCreated([
            'id' => $this->model->getInsertID(),
            'message' => 'Note created successfully'
        ]);
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);

        if (!$this->model->update($id, $data)) {
            return $this->failValidationErrors($this->model->errors());
        }

        return $this->respond(['message' => 'Note updated successfully']);
    }

    public function delete($id = null)
    {
        if (!$this->model->delete($id)) {
            return $this->failNotFound('Note not found');
        }

        return $this->respondDeleted(['message' => 'Note deleted successfully']);
    }

    public function pin($id = null)
    {
        $note = $this->model->find($id);
        if (!$note) {
            return $this->failNotFound('Note not found');
        }

        $this->model->update($id, ['is_pinned' => !$note['is_pinned']]);

        return $this->respond(['message' => 'Note pin status updated']);
    }
}

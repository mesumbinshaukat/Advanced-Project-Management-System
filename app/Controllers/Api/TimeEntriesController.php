<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\TimeEntryModel;

class TimeEntriesController extends ResourceController
{
    use ResponseTrait;

    protected $modelName = 'App\Models\TimeEntryModel';
    protected $format = 'json';

    public function index()
    {
        $user = auth()->user();
        $isAdmin = $user->inGroup('admin');
        
        $filters = [
            'start_date' => $this->request->getGet('start_date'),
            'end_date' => $this->request->getGet('end_date'),
            'project_id' => $this->request->getGet('project_id'),
        ];
        
        $model = new TimeEntryModel();
        $entries = $model->getTimeEntriesForUser($user->id, $isAdmin, $filters);
        
        return $this->respond([
            'status' => 'success',
            'data' => $entries
        ]);
    }

    public function show($id = null)
    {
        $user = auth()->user();
        $isAdmin = $user->inGroup('admin');
        
        $model = new TimeEntryModel();
        $entry = $model->find($id);
        
        if (!$entry) {
            return $this->failNotFound('Time entry not found');
        }

        if (!$isAdmin && $entry['user_id'] != $user->id) {
            return $this->failForbidden('You can only view your own time entries');
        }
        
        return $this->respond([
            'status' => 'success',
            'data' => $entry
        ]);
    }

    public function create()
    {
        $model = new TimeEntryModel();
        $data = $this->request->getJSON(true);
        
        $data['user_id'] = auth()->id();
        
        if (!$model->insert($data)) {
            return $this->fail($model->errors());
        }
        
        $entryId = $model->getInsertID();
        
        return $this->respondCreated([
            'status' => 'success',
            'message' => 'Time entry created successfully',
            'data' => ['id' => $entryId]
        ]);
    }

    public function update($id = null)
    {
        $user = auth()->user();
        $isAdmin = $user->inGroup('admin');
        
        $model = new TimeEntryModel();
        $entry = $model->find($id);
        
        if (!$entry) {
            return $this->failNotFound('Time entry not found');
        }

        if (!$isAdmin && $entry['user_id'] != $user->id) {
            return $this->failForbidden('You can only update your own time entries');
        }
        
        $data = $this->request->getJSON(true);
        
        if (!$model->update($id, $data)) {
            return $this->fail($model->errors());
        }
        
        return $this->respond([
            'status' => 'success',
            'message' => 'Time entry updated successfully'
        ]);
    }

    public function delete($id = null)
    {
        $user = auth()->user();
        $isAdmin = $user->inGroup('admin');
        
        $model = new TimeEntryModel();
        $entry = $model->find($id);
        
        if (!$entry) {
            return $this->failNotFound('Time entry not found');
        }

        if (!$isAdmin && $entry['user_id'] != $user->id) {
            return $this->failForbidden('You can only delete your own time entries');
        }
        
        if (!$model->delete($id)) {
            return $this->fail('Failed to delete time entry');
        }
        
        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'Time entry deleted successfully'
        ]);
    }
}

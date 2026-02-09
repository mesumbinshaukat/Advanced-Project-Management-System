<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\ClientModel;

class ClientsController extends ResourceController
{
    use ResponseTrait;

    protected $modelName = 'App\Models\ClientModel';
    protected $format = 'json';

    public function index()
    {
        $model = new ClientModel();
        $clients = $model->findAll();
        
        return $this->respond([
            'status' => 'success',
            'data' => $clients
        ]);
    }

    public function show($id = null)
    {
        $model = new ClientModel();
        $client = $model->find($id);
        
        if (!$client) {
            return $this->failNotFound('Client not found');
        }
        
        return $this->respond([
            'status' => 'success',
            'data' => $client
        ]);
    }

    public function create()
    {
        $model = new ClientModel();
        $data = $this->request->getJSON(true);
        
        if (!$model->insert($data)) {
            return $this->fail($model->errors());
        }
        
        $clientId = $model->getInsertID();
        
        return $this->respondCreated([
            'status' => 'success',
            'message' => 'Client created successfully',
            'data' => ['id' => $clientId]
        ]);
    }

    public function update($id = null)
    {
        $model = new ClientModel();
        $client = $model->find($id);
        
        if (!$client) {
            return $this->failNotFound('Client not found');
        }
        
        $data = $this->request->getJSON(true);
        
        if (!$model->update($id, $data)) {
            return $this->fail($model->errors());
        }
        
        return $this->respond([
            'status' => 'success',
            'message' => 'Client updated successfully'
        ]);
    }

    public function delete($id = null)
    {
        $model = new ClientModel();
        $client = $model->find($id);
        
        if (!$client) {
            return $this->failNotFound('Client not found');
        }
        
        if (!$model->delete($id)) {
            return $this->fail('Failed to delete client');
        }
        
        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'Client deleted successfully'
        ]);
    }
}

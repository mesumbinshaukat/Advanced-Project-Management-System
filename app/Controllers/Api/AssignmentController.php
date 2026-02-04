<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Services\AssignmentService;

class AssignmentController extends ResourceController
{
    protected $format = 'json';

    public function suggest()
    {
        $projectId = $this->request->getGet('project_id');
        $taskId = $this->request->getGet('task_id');

        if (!$projectId) {
            return $this->failValidationErrors('project_id required');
        }

        $service = new AssignmentService();
        $suggestion = $service->suggestAssignment($projectId, $taskId);

        if (!$suggestion) {
            return $this->respond(['message' => 'No developers available for this project']);
        }

        return $this->respond($suggestion);
    }

    public function workload($userId = null)
    {
        $service = new AssignmentService();

        if ($userId) {
            $workload = $service->getDeveloperWorkload($userId);
            return $this->respond($workload);
        }

        $projectId = $this->request->getGet('project_id');
        $workloads = $service->getAllDevelopersWorkload($projectId);

        return $this->respond($workloads);
    }
}

<?php

namespace App\Controllers;

use App\Services\AssignmentService;

class DevelopersController extends BaseController
{
    protected $assignmentService;

    public function __construct()
    {
        $this->assignmentService = new AssignmentService();
    }

    public function index()
    {
        $workloads = $this->assignmentService->getAllDevelopersWorkload();

        return view('developers/index', [
            'title' => 'Developers',
            'developers' => $workloads,
        ]);
    }

    public function workload($userId)
    {
        $workload = $this->assignmentService->getDeveloperWorkload($userId);

        $db = \Config\Database::connect();
        $user = $db->table('users')->where('id', $userId)->get()->getRow();

        if (!$user) {
            return redirect()->back()->with('error', 'Developer not found');
        }

        return view('developers/workload', [
            'title' => 'Developer Workload',
            'user' => $user,
            'workload' => $workload,
        ]);
    }
}

<?php

namespace App\Controllers;

use App\Services\ProfitabilityService;

class ProfitabilityController extends BaseController
{
    protected $profitabilityService;

    public function __construct()
    {
        $this->profitabilityService = new ProfitabilityService();
    }

    public function index()
    {
        if (!auth()->user()->inGroup('admin')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $overall = $this->profitabilityService->getOverallProfitability();
        $trend = $this->profitabilityService->getProfitabilityTrend(6);
        $topProjects = $this->profitabilityService->getTopProfitableProjects(10);

        return view('profitability/index', [
            'title' => 'Profitability Analysis',
            'overall' => $overall,
            'trend' => $trend,
            'top_projects' => $topProjects,
        ]);
    }

    public function project($projectId)
    {
        if (!auth()->user()->inGroup('admin')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $projectModel = new \App\Models\ProjectModel();
        $project = $projectModel->find($projectId);

        if (!$project) {
            return redirect()->to('/profitability')->with('error', 'Project not found');
        }

        $profitability = $this->profitabilityService->getProjectProfitability($projectId);

        return view('profitability/project', [
            'title' => 'Profitability - ' . $project['name'],
            'project' => $project,
            'profitability' => $profitability,
        ]);
    }
}

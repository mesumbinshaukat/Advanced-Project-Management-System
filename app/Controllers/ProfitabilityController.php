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

        $profitability = $this->profitabilityService->getProjectProfitability($projectId);

        return $this->response->setJSON($profitability);
    }
}

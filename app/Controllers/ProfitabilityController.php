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
        try {
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
        } catch (\Throwable $e) {
            $errorFile = WRITEPATH . 'logs/error_debug.log';
            $errorMsg = date('Y-m-d H:i:s') . ' - ProfitabilityController - ' . get_class($e) . ': ' . $e->getMessage() . "\n";
            $errorMsg .= "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
            $errorMsg .= "Trace:\n" . $e->getTraceAsString() . "\n\n";
            file_put_contents($errorFile, $errorMsg, FILE_APPEND);
            
            throw $e;
        }
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

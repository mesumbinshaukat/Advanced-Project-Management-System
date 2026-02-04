<?php

namespace App\Controllers;

use App\Services\PerformanceService;

class PerformanceController extends BaseController
{
    protected $performanceService;

    public function __construct()
    {
        $this->performanceService = new PerformanceService();
    }

    public function index()
    {
        if (!auth()->user()->inGroup('admin')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $developers = $this->performanceService->getAllDevelopersPerformance();

        return view('performance/index', [
            'title' => 'Performance Metrics',
            'developers' => $developers,
        ]);
    }

    public function developer($userId)
    {
        $scores = $this->performanceService->calculatePerformanceScore($userId);
        $trend = $this->performanceService->getPerformanceTrend($userId, 6);

        return view('performance/developer', [
            'title' => 'Developer Performance',
            'user_id' => $userId,
            'scores' => $scores,
            'trend' => $trend,
        ]);
    }

    public function updateAll()
    {
        if (!auth()->user()->inGroup('admin')) {
            return redirect()->back()->with('error', 'Access denied');
        }

        $count = $this->performanceService->updateAllPerformanceScores();

        return redirect()->to('/performance')->with('success', "Updated performance scores for {$count} developers");
    }
}

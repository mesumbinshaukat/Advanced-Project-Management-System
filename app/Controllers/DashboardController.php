<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\DashboardService;

class DashboardController extends BaseController
{
    protected $dashboardService;

    public function __construct()
    {
        $this->dashboardService = new DashboardService();
    }

    public function index()
    {
        $user = auth()->user();
        $isAdmin = $user->inGroup('admin');
        
        $data = [
            'title' => 'Dashboard',
            'user' => $user,
            'isAdmin' => $isAdmin,
        ];

        if ($isAdmin) {
            $executiveData = $this->dashboardService->getExecutiveDashboard();
            $data = array_merge($data, $executiveData);
        } else {
            $developerData = $this->dashboardService->getDeveloperDashboard($user->id);
            $data = array_merge($data, $developerData);
        }

        return view('dashboard/index', $data);
    }
}

<?php

namespace App\Controllers;

use App\Services\AlertService;
use App\Models\AlertModel;

class AlertsController extends BaseController
{
    protected $alertService;
    protected $alertModel;

    public function __construct()
    {
        $this->alertService = new AlertService();
        $this->alertModel = new AlertModel();
    }

    public function index()
    {
        $user = auth()->user();
        $isAdmin = $user->inGroup('admin');
        
        if ($isAdmin) {
            // Admins see all alerts
            $alerts = $this->alertService->getActiveAlerts();
        } else {
            // Developers only see their own alerts
            $alerts = $this->alertModel->getUserAlerts($user->id);
        }

        return view('alerts/index', [
            'title' => 'Alerts',
            'alerts' => $alerts,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function resolve($id)
    {
        $user = auth()->user();
        $isAdmin = $user->inGroup('admin');
        
        $alert = $this->alertModel->find($id);
        
        if (!$alert) {
            return redirect()->back()->with('error', 'Alert not found');
        }
        
        // Check permissions: admins can resolve any alert, developers only their own
        if (!$isAdmin && $alert['user_id'] != $user->id) {
            return redirect()->back()->with('error', 'You do not have permission to resolve this alert');
        }

        $this->alertService->resolveAlert($id);

        return redirect()->back()->with('success', 'Alert resolved');
    }

    public function generate()
    {
        if (!auth()->user()->inGroup('admin')) {
            return redirect()->back()->with('error', 'Access denied');
        }

        $this->alertService->generateAllAlerts();

        return redirect()->to('/alerts')->with('success', 'Alerts generated successfully');
    }
}

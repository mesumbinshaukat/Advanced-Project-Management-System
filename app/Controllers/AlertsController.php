<?php

namespace App\Controllers;

use App\Services\AlertService;

class AlertsController extends BaseController
{
    protected $alertService;

    public function __construct()
    {
        $this->alertService = new AlertService();
    }

    public function index()
    {
        $alerts = $this->alertService->getActiveAlerts();

        return view('alerts/index', [
            'alerts' => $alerts,
        ]);
    }

    public function resolve($id)
    {
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

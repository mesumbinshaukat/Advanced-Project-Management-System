<?php

namespace App\Controllers;

use App\Services\CapacityService;

class CapacityController extends BaseController
{
    protected $capacityService;

    public function __construct()
    {
        $this->capacityService = new CapacityService();
    }

    public function index()
    {
        if (!auth()->user()->inGroup('admin')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $forecast = $this->capacityService->getCapacityForecast();
        $allocations = $this->capacityService->getProjectCapacityAllocation();

        return view('capacity/index', [
            'title' => 'Capacity Planning',
            'forecast' => $forecast,
            'allocations' => $allocations,
        ]);
    }
}

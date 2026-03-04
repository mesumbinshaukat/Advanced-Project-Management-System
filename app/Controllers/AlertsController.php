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
            // Developers see alerts assigned to them OR alerts for projects/tasks they're involved in
            $alerts = $this->getDeveloperRelevantAlerts($user->id);
        }

        return view('alerts/index', [
            'title' => 'Alerts',
            'alerts' => $alerts,
            'isAdmin' => $isAdmin,
        ]);
    }
    
    private function getDeveloperRelevantAlerts($userId)
    {
        $db = \Config\Database::connect();
        
        // Get alerts directly assigned to the user
        $userAlerts = $this->alertModel->getUserAlerts($userId);
        
        // Get project IDs where user is assigned
        $projectIds = $db->table('project_users')
            ->select('project_id')
            ->where('user_id', $userId)
            ->get()
            ->getResultArray();
        
        $projectIdList = array_column($projectIds, 'project_id');
        
        // Get task IDs where user is assigned (both legacy and new assignment methods)
        $taskIds = [];
        
        // Legacy task assignments
        $legacyTasks = $db->table('tasks')
            ->select('id')
            ->where('assigned_to', $userId)
            ->where('deleted_at', null)
            ->get()
            ->getResultArray();
        
        $taskIds = array_merge($taskIds, array_column($legacyTasks, 'id'));
        
        // New task assignments
        $newTasks = $db->table('task_assignments')
            ->select('task_id')
            ->where('user_id', $userId)
            ->get()
            ->getResultArray();
        
        $taskIds = array_merge($taskIds, array_column($newTasks, 'task_id'));
        $taskIds = array_unique($taskIds);
        
        // Get alerts for projects and tasks the user is involved in
        $relevantAlerts = [];
        
        if (!empty($projectIdList)) {
            $projectAlerts = $this->alertModel
                ->where('entity_type', 'project')
                ->whereIn('entity_id', $projectIdList)
                ->where('is_resolved', 0)
                ->orderBy('severity', 'DESC')
                ->orderBy('created_at', 'DESC')
                ->findAll();
            
            $relevantAlerts = array_merge($relevantAlerts, $projectAlerts);
        }
        
        if (!empty($taskIds)) {
            $taskAlerts = $this->alertModel
                ->where('entity_type', 'task')
                ->whereIn('entity_id', $taskIds)
                ->where('is_resolved', 0)
                ->orderBy('severity', 'DESC')
                ->orderBy('created_at', 'DESC')
                ->findAll();
            
            $relevantAlerts = array_merge($relevantAlerts, $taskAlerts);
        }
        
        // Combine user alerts and relevant alerts, remove duplicates
        $allAlerts = array_merge($userAlerts, $relevantAlerts);
        
        // Remove duplicates based on alert ID
        $uniqueAlerts = [];
        $seenIds = [];
        
        foreach ($allAlerts as $alert) {
            if (!in_array($alert['id'], $seenIds)) {
                $uniqueAlerts[] = $alert;
                $seenIds[] = $alert['id'];
            }
        }
        
        // Sort by severity and creation date
        usort($uniqueAlerts, function($a, $b) {
            $severityOrder = ['critical' => 4, 'high' => 3, 'medium' => 2, 'low' => 1];
            $aSeverity = $severityOrder[$a['severity']] ?? 0;
            $bSeverity = $severityOrder[$b['severity']] ?? 0;
            
            if ($aSeverity === $bSeverity) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            }
            
            return $bSeverity - $aSeverity;
        });
        
        return $uniqueAlerts;
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

        try {
            $this->alertService->generateAllAlerts();
            return redirect()->to('/alerts')->with('success', 'Alerts generated successfully');
        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();
            
            // Check if it's a database schema error
            if (strpos($errorMsg, 'Unknown column') !== false) {
                return redirect()->to('/alerts')->with('warning', 'Database schema needs updating. Please run the migration script at /add_missing_columns.php');
            }
            
            return redirect()->to('/alerts')->with('error', 'Error generating alerts: ' . $errorMsg);
        }
    }
}

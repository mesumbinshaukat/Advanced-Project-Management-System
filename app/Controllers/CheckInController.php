<?php

namespace App\Controllers;

use App\Models\DailyCheckInModel;

class CheckInController extends BaseController
{
    protected $checkInModel;

    public function __construct()
    {
        $this->checkInModel = new DailyCheckInModel();
    }

    public function index()
    {
        $userId = auth()->id();
        $todayCheckIn = $this->checkInModel->getTodayCheckIn($userId);
        $recentCheckIns = $this->checkInModel->getRecentCheckIns($userId, 7);
        $streak = $this->checkInModel->getCheckInStreak($userId);

        return view('check_in/index', [
            'title' => 'Daily Check-In',
            'today_check_in' => $todayCheckIn,
            'recent_check_ins' => $recentCheckIns,
            'streak' => $streak,
        ]);
    }

    public function store()
    {
        $userId = auth()->id();
        $data = $this->request->getPost();
        $data['user_id'] = $userId;
        $data['date'] = date('Y-m-d');

        $existing = $this->checkInModel->getTodayCheckIn($userId);

        if ($existing) {
            if (!$this->checkInModel->update($existing['id'], $data)) {
                return redirect()->back()->withInput()->with('errors', $this->checkInModel->errors());
            }
        } else {
            if (!$this->checkInModel->insert($data)) {
                return redirect()->back()->withInput()->with('errors', $this->checkInModel->errors());
            }
        }

        $db = \Config\Database::connect();
        $db->table('users')->where('id', $userId)->update([
            'last_check_in' => date('Y-m-d'),
            'last_activity' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/check-in')->with('success', 'Check-in saved successfully');
    }

    public function team()
    {
        if (!auth()->user()->inGroup('admin')) {
            return redirect()->to('/check-in')->with('error', 'Access denied');
        }

        $date = $this->request->getGet('date') ?? date('Y-m-d');
        $teamCheckIns = $this->checkInModel->getTeamCheckIns($date);

        return view('check_in/team', [
            'title' => 'Team Check-Ins',
            'check_ins' => $teamCheckIns,
            'date' => $date,
        ]);
    }
}

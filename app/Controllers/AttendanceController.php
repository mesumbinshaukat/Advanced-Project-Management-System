<?php

namespace App\Controllers;

use App\Models\DailyCheckInModel;
use CodeIgniter\Shield\Models\UserModel;

class AttendanceController extends BaseController
{
    protected DailyCheckInModel $checkInModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->checkInModel = new DailyCheckInModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        if (!auth()->user()->inGroup('admin')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        $selectedMonth = $this->request->getGet('month') ?? date('Y-m');
        if (!preg_match('/^\d{4}-\d{2}$/', $selectedMonth)) {
            $selectedMonth = date('Y-m');
        }

        $startDate = $selectedMonth . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        $allUsers = $this->userModel
            ->select("users.id, users.username, users.last_check_in, users.active, auth_identities.secret as email")
            ->join(
                'auth_identities',
                "auth_identities.user_id = users.id AND auth_identities.type = 'email_password'",
                'left'
            )
            ->where('users.active', 1)
            ->orderBy('users.username', 'ASC')
            ->asArray()
            ->findAll();

        $selectedUserId = $this->request->getGet('user');
        $users = $allUsers;
        if (!empty($selectedUserId) && ctype_digit($selectedUserId)) {
            $users = array_values(array_filter($allUsers, fn ($user) => (string)$user['id'] === $selectedUserId));
        }

        if (empty($users)) {
            $users = $allUsers;
        }

        $userIds = array_column($users, 'id');
        $dateRange = $this->generateDateRange($startDate, $endDate);
        $totalWorkingDays = count($dateRange);

        $checkIns = empty($userIds)
            ? []
            : $this->checkInModel->getCheckInsBetween($startDate, $endDate, $userIds);

        $userCheckins = [];
        $dailyTrend = array_fill_keys($dateRange, 0);
        $moodSummary = [];

        foreach ($checkIns as $checkIn) {
            $userId = $checkIn['user_id'];
            $date = $checkIn['check_in_date'];
            $userCheckins[$userId][$date] = $checkIn;

            if (isset($dailyTrend[$date])) {
                $dailyTrend[$date]++;
            }

            $mood = $checkIn['mood'];
            $moodSummary[$mood] = ($moodSummary[$mood] ?? 0) + 1;
        }

        $attendanceRows = [];
        $totalPresent = 0;
        foreach ($users as $user) {
            $userId = $user['id'];
            $userDates = isset($userCheckins[$userId]) ? array_keys($userCheckins[$userId]) : [];
            $presentDays = count($userDates);
            $absentDays = max($totalWorkingDays - $presentDays, 0);
            $attendanceRate = $totalWorkingDays > 0 ? round(($presentDays / $totalWorkingDays) * 100, 1) : 0;
            $totalPresent += $presentDays;

            $attendanceRows[] = [
                'user' => $user,
                'present_days' => $presentDays,
                'absent_days' => $absentDays,
                'attendance_rate' => $attendanceRate,
                'streak' => $this->checkInModel->getCheckInStreak($userId),
                'last_check_in' => $user['last_check_in'],
            ];
        }

        $totalSlots = $totalWorkingDays * max(count($users), 1);
        $stats = [
            'total_users' => count($users),
            'total_present' => $totalPresent,
            'total_absent' => max($totalSlots - $totalPresent, 0),
            'average_rate' => $totalSlots > 0 ? round(($totalPresent / $totalSlots) * 100, 1) : 0,
            'best_streak' => !empty($attendanceRows) ? max(array_column($attendanceRows, 'streak')) : 0,
        ];

        $topAttendance = $attendanceRows;
        usort($topAttendance, fn ($a, $b) => $b['attendance_rate'] <=> $a['attendance_rate']);
        $topAttendance = array_slice($topAttendance, 0, 5);
        $topAttendanceChart = [
            'labels' => array_map(fn ($row) => $row['user']['username'], $topAttendance),
            'values' => array_map(fn ($row) => $row['attendance_rate'], $topAttendance),
        ];

        $monthOptions = [];
        $base = strtotime(date('Y-m-01'));
        for ($i = 0; $i < 12; $i++) {
            $ts = strtotime("-{$i} months", $base);
            $value = date('Y-m', $ts);
            $monthOptions[] = [
                'value' => $value,
                'label' => date('F Y', $ts),
            ];
        }

        return view('attendance/index', [
            'title' => 'Attendance & Productivity',
            'selectedMonth' => $selectedMonth,
            'selectedUserId' => $selectedUserId && ctype_digit($selectedUserId) ? $selectedUserId : null,
            'monthOptions' => $monthOptions,
            'userFilterOptions' => $allUsers,
            'attendanceRows' => $attendanceRows,
            'stats' => $stats,
            'chartTrend' => [
                'labels' => array_map(fn ($date) => date('M j', strtotime($date)), array_keys($dailyTrend)),
                'values' => array_values($dailyTrend),
            ],
            'moodSummary' => $this->normalizeMoodSummary($moodSummary),
            'topAttendanceChart' => $topAttendanceChart,
            'totalWorkingDays' => $totalWorkingDays,
            'userDailyMap' => $userCheckins,
            'dateRange' => $dateRange,
        ]);
    }

    private function generateDateRange(string $startDate, string $endDate): array
    {
        $range = [];
        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);

        while ($start <= $end) {
            $range[] = $start->format('Y-m-d');
            $start->modify('+1 day');
        }

        return $range;
    }

    private function normalizeMoodSummary(array $summary): array
    {
        $moods = ['great', 'good', 'okay', 'struggling', 'blocked'];
        $normalized = [];

        foreach ($moods as $mood) {
            $normalized[$mood] = $summary[$mood] ?? 0;
        }

        return $normalized;
    }
}

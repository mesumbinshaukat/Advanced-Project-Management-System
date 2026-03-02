<?php

namespace App\Controllers;

use App\Models\SystemConfigModel;
use CodeIgniter\Shield\Entities\User;

class SuperAdminController extends BaseController
{
    protected $systemConfigModel;

    public function __construct()
    {
        $this->systemConfigModel = new SystemConfigModel();
    }

    private function calculateAttendanceStats($checkIns, $users, $startDate, $endDate)
    {
        $dateRange = $this->generateDateRange($startDate, $endDate);
        $totalWorkingDays = count($dateRange);

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

            $mood = strtolower($checkIn['mood'] ?? '');
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
            ];
        }

        $totalSlots = $totalWorkingDays * max(count($users), 1);
        $stats = [
            'total_users' => count($users),
            'total_present' => $totalPresent,
            'total_absent' => max($totalSlots - $totalPresent, 0),
            'average_rate' => $totalSlots > 0 ? round(($totalPresent / $totalSlots) * 100, 1) : 0,
            'total_working_days' => $totalWorkingDays,
        ];

        $topAttendance = $attendanceRows;
        usort($topAttendance, fn ($a, $b) => $b['attendance_rate'] <=> $a['attendance_rate']);
        $topAttendance = array_slice($topAttendance, 0, 5);

        return [
            'stats' => $stats,
            'attendance_rows' => $attendanceRows,
            'top_attendance' => $topAttendance,
            'daily_trend' => $dailyTrend,
            'mood_summary' => $this->normalizeMoodSummary($moodSummary),
            'date_range' => $dateRange,
            'user_checkins' => $userCheckins,
        ];
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

    private function verifySuperAdmin()
    {
        log_message('debug', 'SuperAdminController::verifySuperAdmin() - Checking superadmin authentication');

        $session = session();
        if (!$session->has('superadmin_logged_in')) {
            log_message('debug', 'SuperAdminController::verifySuperAdmin() - No superadmin_logged_in session key found');
            return redirect()->to('/x9k2m8p5q7/login')->with('error', 'Access denied');
        }

        $isLoggedIn = $session->get('superadmin_logged_in');
        log_message('debug', 'SuperAdminController::verifySuperAdmin() - superadmin_logged_in value: ' . ($isLoggedIn ? 'true' : 'false'));

        if ($isLoggedIn !== true) {
            log_message('debug', 'SuperAdminController::verifySuperAdmin() - Authentication failed, redirecting to login');
            return redirect()->to('/x9k2m8p5q7/login')->with('error', 'Access denied');
        }

        log_message('debug', 'SuperAdminController::verifySuperAdmin() - Authentication successful');
        return true;
    }

    public function login()
    {
        log_message('debug', '=== SuperAdminController::login() - Method started ===');
        log_message('debug', 'Request method: ' . strtoupper($this->request->getMethod()));
        log_message('debug', 'Request URI: ' . $this->request->getUri());

        // Check if this is a POST request
        $isPost = strtoupper($this->request->getMethod()) === 'POST';
        log_message('debug', 'Is POST: ' . ($isPost ? 'YES' : 'NO'));

        if ($isPost) {
            log_message('debug', '=== SuperAdminController::login() - Processing POST request ===');

            $email = trim($this->request->getPost('email') ?? '');
            $password = $this->request->getPost('password') ?? '';

            log_message('debug', 'Received email: [' . $email . ']');
            log_message('debug', 'Received password length: ' . strlen($password));

            // Fetch stored credentials
            log_message('debug', 'Fetching stored credentials from database');
            $storedEmail = $this->systemConfigModel->getConfig('superadmin_email');
            $storedPassword = $this->systemConfigModel->getConfig('superadmin_password');
            $storedUsername = $this->systemConfigModel->getConfig('superadmin_username');

            log_message('debug', 'Stored email: [' . ($storedEmail ?? 'NULL') . ']');
            log_message('debug', 'Stored password length: ' . strlen($storedPassword ?? ''));
            log_message('debug', 'Stored username: [' . ($storedUsername ?? 'NULL') . ']');

            // Validate credentials exist
            if (!$storedEmail || !$storedPassword) {
                log_message('error', 'Missing stored credentials in database');
                return redirect()->back()->with('error', 'System configuration error');
            }

            // Compare credentials
            $emailMatch = ($email === $storedEmail);
            $passwordMatch = ($password === $storedPassword);

            log_message('debug', 'Email match: ' . ($emailMatch ? 'YES' : 'NO'));
            log_message('debug', 'Password match: ' . ($passwordMatch ? 'YES' : 'NO'));

            if ($emailMatch && $passwordMatch) {
                log_message('debug', 'Authentication SUCCESSFUL');

                session()->set([
                    'superadmin_logged_in' => true,
                    'superadmin_username' => $storedUsername,
                    'superadmin_email' => $storedEmail
                ]);

                log_message('debug', 'Session set, redirecting to dashboard');
                return redirect()->to('/x9k2m8p5q7/dashboard');
            } else {
                log_message('debug', 'Authentication FAILED - credentials do not match');
                return redirect()->back()->with('error', 'Invalid credentials');
            }
        }

        log_message('debug', 'GET request - showing login form');
        return view('superadmin/login', [
            'title' => 'System Access'
        ]);
    }

    public function dashboard()
    {
        log_message('debug', 'SuperAdminController::dashboard() - Method started');

        $verificationResult = $this->verifySuperAdmin();
        if ($verificationResult !== true) {
            log_message('debug', 'SuperAdminController::dashboard() - Authentication verification failed');
            return $verificationResult;
        }

        log_message('debug', 'SuperAdminController::dashboard() - Authentication verified, loading dashboard data');

        $db = \Config\Database::connect();

        // Get recent time entries
        log_message('debug', 'SuperAdminController::dashboard() - Fetching time entries');
        $timeEntries = $db->table('time_entries')
            ->select('time_entries.*, users.username')
            ->join('users', 'users.id = time_entries.user_id')
            ->orderBy('time_entries.created_at', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        log_message('debug', 'SuperAdminController::dashboard() - Time entries fetched: ' . count($timeEntries));

        // Get recent check-ins
        log_message('debug', 'SuperAdminController::dashboard() - Fetching check-ins');
        $checkIns = $db->table('daily_check_ins')
            ->select('daily_check_ins.*, users.username')
            ->join('users', 'users.id = daily_check_ins.user_id')
            ->orderBy('daily_check_ins.check_in_date', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        log_message('debug', 'SuperAdminController::dashboard() - Check-ins fetched: ' . count($checkIns));

        // Get users
        log_message('debug', 'SuperAdminController::dashboard() - Fetching users');
        $users = $db->table('users')
            ->select('users.*, auth_identities.secret as email')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = \'email_password\'', 'left')
            ->orderBy('users.username', 'ASC')
            ->get()
            ->getResultArray();

        log_message('debug', 'SuperAdminController::dashboard() - Users fetched: ' . count($users));

        // Get attendance data with month filtering
        log_message('debug', 'SuperAdminController::dashboard() - Fetching attendance data');
        $selectedMonth = $this->request->getGet('month') ?? date('Y-m');
        
        // Validate month format (YYYY-MM)
        if (!preg_match('/^\d{4}-\d{2}$/', $selectedMonth)) {
            $selectedMonth = date('Y-m');
        }
        
        log_message('debug', 'SuperAdminController::dashboard() - Selected month: ' . $selectedMonth);
        
        $startDate = $selectedMonth . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        $allCheckIns = $db->table('daily_check_ins')
            ->select('daily_check_ins.*, users.username')
            ->join('users', 'users.id = daily_check_ins.user_id')
            ->where('daily_check_ins.check_in_date >=', $startDate)
            ->where('daily_check_ins.check_in_date <=', $endDate)
            ->where('users.active', 1)
            ->orderBy('daily_check_ins.check_in_date', 'DESC')
            ->get()
            ->getResultArray();

        // Calculate attendance stats
        $attendanceStats = $this->calculateAttendanceStats($allCheckIns, $users, $startDate, $endDate);

        log_message('debug', 'SuperAdminController::dashboard() - Attendance data fetched');
        
        // Generate month options for filter (last 12 months)
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
        
        log_message('debug', 'SuperAdminController::dashboard() - Rendering dashboard view');

        return view('superadmin/dashboard', [
            'title' => 'System Control Panel',
            'timeEntries' => $timeEntries,
            'checkIns' => $checkIns,
            'users' => $users,
            'attendanceStats' => $attendanceStats,
            'allCheckIns' => $allCheckIns,
            'selectedMonth' => $selectedMonth,
            'monthOptions' => $monthOptions
        ]);
    }

    public function editTimeEntry($id)
    {
        log_message('debug', '=== SuperAdminController::editTimeEntry() - Started ===');
        log_message('debug', 'Entry ID: ' . $id);
        log_message('debug', 'Request method: ' . strtoupper($this->request->getMethod()));

        if ($this->verifySuperAdmin() !== true) {
            log_message('error', 'SuperAdminController::editTimeEntry() - Admin verification failed');
            return $this->verifySuperAdmin();
        }

        $db = \Config\Database::connect();
        $timeEntry = $db->table('time_entries')
            ->select('time_entries.*, users.username')
            ->join('users', 'users.id = time_entries.user_id')
            ->where('time_entries.id', $id)
            ->get()
            ->getRowArray();

        if (!$timeEntry) {
            log_message('error', 'SuperAdminController::editTimeEntry() - Time entry not found: ' . $id);
            return redirect()->back()->with('error', 'Time entry not found');
        }

        log_message('debug', 'SuperAdminController::editTimeEntry() - Time entry found');

        if (strtoupper($this->request->getMethod()) === 'POST') {
            log_message('debug', '=== SuperAdminController::editTimeEntry() - Processing POST request ===');

            $hours = $this->request->getPost('hours');
            $description = $this->request->getPost('description');
            $date = $this->request->getPost('date');
            $task_id = $this->request->getPost('task_id');
            $is_billable = $this->request->getPost('is_billable');

            log_message('debug', 'Received hours: [' . $hours . ']');
            log_message('debug', 'Received description: [' . substr($description, 0, 50) . ']');
            log_message('debug', 'Received date: [' . $date . ']');
            log_message('debug', 'Received task_id: [' . ($task_id ?? 'NULL') . ']');
            log_message('debug', 'Received is_billable: [' . ($is_billable ? 'YES' : 'NO') . ']');

            // Validate required fields
            if (empty($hours) || empty($description) || empty($date)) {
                log_message('error', 'SuperAdminController::editTimeEntry() - Missing required fields');
                return redirect()->back()->withInput()->with('error', 'Please fill in all required fields');
            }

            $data = [
                'hours' => floatval($hours),
                'description' => $description,
                'date' => $date,
                'task_id' => !empty($task_id) ? intval($task_id) : null,
                'is_billable' => $is_billable ? 1 : 0,
            ];

            log_message('debug', 'Data to update: ' . json_encode($data));

            try {
                $db->table('time_entries')->where('id', $id)->update($data);
                $affectedRows = $db->affectedRows();
                
                log_message('debug', 'SuperAdminController::editTimeEntry() - Affected rows: ' . $affectedRows);
                log_message('debug', 'SuperAdminController::editTimeEntry() - Update executed successfully');
                
                return redirect()->to('/x9k2m8p5q7/dashboard')->with('success', 'Time entry updated successfully');
            } catch (\Exception $e) {
                log_message('error', 'SuperAdminController::editTimeEntry() - Update exception: ' . $e->getMessage());
                return redirect()->back()->withInput()->with('error', 'Failed to update time entry: ' . $e->getMessage());
            }
        }

        log_message('debug', 'SuperAdminController::editTimeEntry() - Showing edit form (GET request)');
        return view('superadmin/edit_time_entry', [
            'title' => 'Edit Time Entry',
            'entry' => $timeEntry
        ]);
    }

    public function editCheckIn($id)
    {
        log_message('debug', '=== SuperAdminController::editCheckIn() - Started ===');
        log_message('debug', 'Check-in ID: ' . $id);
        log_message('debug', 'Request method: ' . strtoupper($this->request->getMethod()));

        if ($this->verifySuperAdmin() !== true) {
            log_message('error', 'SuperAdminController::editCheckIn() - Admin verification failed');
            return $this->verifySuperAdmin();
        }

        $db = \Config\Database::connect();
        $checkIn = $db->table('daily_check_ins')
            ->select('daily_check_ins.*, users.username')
            ->join('users', 'users.id = daily_check_ins.user_id')
            ->where('daily_check_ins.id', $id)
            ->get()
            ->getRowArray();

        if (!$checkIn) {
            log_message('error', 'SuperAdminController::editCheckIn() - Check-in not found: ' . $id);
            return redirect()->back()->with('error', 'Check-in not found');
        }

        log_message('debug', 'SuperAdminController::editCheckIn() - Check-in found');

        if (strtoupper($this->request->getMethod()) === 'POST') {
            log_message('debug', '=== SuperAdminController::editCheckIn() - Processing POST request ===');

            $check_in_date = $this->request->getPost('check_in_date');
            $mood = $this->request->getPost('mood');
            $achievements = $this->request->getPost('achievements');
            $plans = $this->request->getPost('plans');
            $blockers = $this->request->getPost('blockers');
            $notes = $this->request->getPost('notes');

            log_message('debug', 'Received check_in_date: [' . $check_in_date . ']');
            log_message('debug', 'Received mood: [' . $mood . ']');
            log_message('debug', 'Received achievements: [' . substr($achievements, 0, 50) . ']');
            log_message('debug', 'Received plans: [' . substr($plans, 0, 50) . ']');
            log_message('debug', 'Received blockers: [' . substr($blockers, 0, 50) . ']');
            log_message('debug', 'Received notes: [' . substr($notes, 0, 50) . ']');

            // Validate required fields
            if (empty($check_in_date)) {
                log_message('error', 'SuperAdminController::editCheckIn() - Missing required field: check_in_date');
                return redirect()->back()->withInput()->with('error', 'Check-in date is required');
            }

            $data = [
                'check_in_date' => $check_in_date,
                'mood' => !empty($mood) ? $mood : null,
                'achievements' => $achievements,
                'plans' => $plans,
                'blockers' => $blockers,
                'notes' => $notes,
            ];

            log_message('debug', 'Data to update: ' . json_encode($data));

            try {
                $db->table('daily_check_ins')->where('id', $id)->update($data);
                $affectedRows = $db->affectedRows();
                
                log_message('debug', 'SuperAdminController::editCheckIn() - Affected rows: ' . $affectedRows);
                log_message('debug', 'SuperAdminController::editCheckIn() - Update executed successfully');
                
                return redirect()->to('/x9k2m8p5q7/dashboard')->with('success', 'Check-in updated successfully');
            } catch (\Exception $e) {
                log_message('error', 'SuperAdminController::editCheckIn() - Update exception: ' . $e->getMessage());
                return redirect()->back()->withInput()->with('error', 'Failed to update check-in: ' . $e->getMessage());
            }
        }

        log_message('debug', 'SuperAdminController::editCheckIn() - Showing edit form (GET request)');
        return view('superadmin/edit_check_in', [
            'title' => 'Edit Check-in',
            'checkin' => $checkIn
        ]);
    }

    public function editUser($id)
    {
        log_message('debug', '=== SuperAdminController::editUser() - Started ===');
        log_message('debug', 'User ID: ' . $id);
        log_message('debug', 'Request method: ' . strtoupper($this->request->getMethod()));

        if ($this->verifySuperAdmin() !== true) {
            log_message('error', 'SuperAdminController::editUser() - Admin verification failed');
            return $this->verifySuperAdmin();
        }

        $db = \Config\Database::connect();
        $user = $db->table('users')
            ->select('users.*, auth_identities.secret as email')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = \'email_password\'', 'left')
            ->where('users.id', $id)
            ->get()
            ->getRowArray();

        if (!$user) {
            log_message('error', 'SuperAdminController::editUser() - User not found: ' . $id);
            return redirect()->back()->with('error', 'User not found');
        }

        log_message('debug', 'SuperAdminController::editUser() - User found');

        if (strtoupper($this->request->getMethod()) === 'POST') {
            log_message('debug', '=== SuperAdminController::editUser() - Processing POST request ===');

            $username = $this->request->getPost('username');
            $email = $this->request->getPost('email');
            $active = $this->request->getPost('active');

            log_message('debug', 'Received username: [' . $username . ']');
            log_message('debug', 'Received email: [' . $email . ']');
            log_message('debug', 'Received active: [' . ($active ? 'YES' : 'NO') . ']');

            // Validate required fields
            if (empty($username)) {
                log_message('error', 'SuperAdminController::editUser() - Missing required field: username');
                return redirect()->back()->withInput()->with('error', 'Username is required');
            }

            $data = [
                'username' => $username,
                'active' => $active ? 1 : 0,
            ];

            log_message('debug', 'Data to update in users table: ' . json_encode($data));

            try {
                $db->table('users')->where('id', $id)->update($data);
                $affectedRows = $db->affectedRows();
                log_message('debug', 'SuperAdminController::editUser() - Users table affected rows: ' . $affectedRows);

                // Update email if provided and different
                $newEmail = $email;
                if ($newEmail && $newEmail !== $user['email']) {
                    log_message('debug', 'SuperAdminController::editUser() - Updating email from [' . ($user['email'] ?? 'NULL') . '] to [' . $newEmail . ']');
                    
                    $db->table('auth_identities')
                        ->where('user_id', $id)
                        ->where('type', 'email_password')
                        ->update(['secret' => $newEmail]);
                    
                    $emailAffectedRows = $db->affectedRows();
                    log_message('debug', 'SuperAdminController::editUser() - Email table affected rows: ' . $emailAffectedRows);
                } else {
                    log_message('debug', 'SuperAdminController::editUser() - Email unchanged or not provided');
                }

                log_message('debug', 'SuperAdminController::editUser() - Update completed successfully');
                return redirect()->to('/x9k2m8p5q7/dashboard')->with('success', 'User updated successfully');
            } catch (\Exception $e) {
                log_message('error', 'SuperAdminController::editUser() - Update exception: ' . $e->getMessage());
                return redirect()->back()->withInput()->with('error', 'Failed to update user: ' . $e->getMessage());
            }
        }

        log_message('debug', 'SuperAdminController::editUser() - Showing edit form (GET request)');
        return view('superadmin/edit_user', [
            'title' => 'Edit User',
            'user' => $user
        ]);
    }

    public function deleteTimeEntry($id)
    {
        log_message('debug', '=== SuperAdminController::deleteTimeEntry() - Started ===');
        log_message('debug', 'Entry ID: ' . $id);

        if ($this->verifySuperAdmin() !== true) {
            log_message('error', 'SuperAdminController::deleteTimeEntry() - Admin verification failed');
            return $this->verifySuperAdmin();
        }

        $db = \Config\Database::connect();
        
        try {
            // Verify entry exists
            $entry = $db->table('time_entries')->where('id', $id)->get()->getRowArray();
            if (!$entry) {
                log_message('error', 'SuperAdminController::deleteTimeEntry() - Entry not found: ' . $id);
                return redirect()->back()->with('error', 'Time entry not found');
            }

            log_message('debug', 'SuperAdminController::deleteTimeEntry() - Entry found, deleting');

            // Delete the entry
            $db->table('time_entries')->where('id', $id)->delete();
            $affectedRows = $db->affectedRows();

            log_message('debug', 'SuperAdminController::deleteTimeEntry() - Deleted, affected rows: ' . $affectedRows);
            return redirect()->to('/x9k2m8p5q7/dashboard')->with('success', 'Time entry deleted successfully');
        } catch (\Exception $e) {
            log_message('error', 'SuperAdminController::deleteTimeEntry() - Exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete time entry: ' . $e->getMessage());
        }
    }

    public function deleteCheckIn($id)
    {
        log_message('debug', '=== SuperAdminController::deleteCheckIn() - Started ===');
        log_message('debug', 'Check-in ID: ' . $id);

        if ($this->verifySuperAdmin() !== true) {
            log_message('error', 'SuperAdminController::deleteCheckIn() - Admin verification failed');
            return $this->verifySuperAdmin();
        }

        $db = \Config\Database::connect();
        
        try {
            // Verify check-in exists
            $checkin = $db->table('daily_check_ins')->where('id', $id)->get()->getRowArray();
            if (!$checkin) {
                log_message('error', 'SuperAdminController::deleteCheckIn() - Check-in not found: ' . $id);
                return redirect()->back()->with('error', 'Check-in not found');
            }

            log_message('debug', 'SuperAdminController::deleteCheckIn() - Check-in found, deleting');

            // Delete the check-in
            $db->table('daily_check_ins')->where('id', $id)->delete();
            $affectedRows = $db->affectedRows();

            log_message('debug', 'SuperAdminController::deleteCheckIn() - Deleted, affected rows: ' . $affectedRows);
            return redirect()->to('/x9k2m8p5q7/dashboard')->with('success', 'Check-in deleted successfully');
        } catch (\Exception $e) {
            log_message('error', 'SuperAdminController::deleteCheckIn() - Exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete check-in: ' . $e->getMessage());
        }
    }

    public function deleteUser($id)
    {
        log_message('debug', '=== SuperAdminController::deleteUser() - Started ===');
        log_message('debug', 'User ID: ' . $id);

        if ($this->verifySuperAdmin() !== true) {
            log_message('error', 'SuperAdminController::deleteUser() - Admin verification failed');
            return $this->verifySuperAdmin();
        }

        $db = \Config\Database::connect();
        
        try {
            // Verify user exists
            $user = $db->table('users')->where('id', $id)->get()->getRowArray();
            if (!$user) {
                log_message('error', 'SuperAdminController::deleteUser() - User not found: ' . $id);
                return redirect()->back()->with('error', 'User not found');
            }

            log_message('debug', 'SuperAdminController::deleteUser() - User found, deleting');

            // Delete auth identities first
            $db->table('auth_identities')->where('user_id', $id)->delete();
            log_message('debug', 'SuperAdminController::deleteUser() - Auth identities deleted');

            // Delete the user
            $db->table('users')->where('id', $id)->delete();
            $affectedRows = $db->affectedRows();

            log_message('debug', 'SuperAdminController::deleteUser() - User deleted, affected rows: ' . $affectedRows);
            return redirect()->to('/x9k2m8p5q7/dashboard')->with('success', 'User deleted successfully');
        } catch (\Exception $e) {
            log_message('error', 'SuperAdminController::deleteUser() - Exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }

    public function createTimeEntry()
    {
        log_message('debug', '=== SuperAdminController::createTimeEntry() - Started ===');
        log_message('debug', 'Request method: ' . strtoupper($this->request->getMethod()));

        if ($this->verifySuperAdmin() !== true) {
            log_message('error', 'SuperAdminController::createTimeEntry() - Admin verification failed');
            return $this->verifySuperAdmin();
        }

        $db = \Config\Database::connect();

        if (strtoupper($this->request->getMethod()) === 'POST') {
            log_message('debug', '=== SuperAdminController::createTimeEntry() - Processing POST request ===');

            $user_id = $this->request->getPost('user_id');
            $hours = $this->request->getPost('hours');
            $description = $this->request->getPost('description');
            $date = $this->request->getPost('date');
            $task_id = $this->request->getPost('task_id');
            $is_billable = $this->request->getPost('is_billable');

            log_message('debug', 'Received user_id: [' . $user_id . ']');
            log_message('debug', 'Received hours: [' . $hours . ']');
            log_message('debug', 'Received description: [' . substr($description, 0, 50) . ']');
            log_message('debug', 'Received date: [' . $date . ']');

            // Validate required fields
            if (empty($user_id) || empty($hours) || empty($description) || empty($date)) {
                log_message('error', 'SuperAdminController::createTimeEntry() - Missing required fields');
                return redirect()->back()->withInput()->with('error', 'Please fill in all required fields');
            }

            // Verify user exists
            $user = $db->table('users')->where('id', $user_id)->get()->getRowArray();
            if (!$user) {
                log_message('error', 'SuperAdminController::createTimeEntry() - User not found: ' . $user_id);
                return redirect()->back()->withInput()->with('error', 'User not found');
            }

            $data = [
                'user_id' => intval($user_id),
                'hours' => floatval($hours),
                'description' => $description,
                'date' => $date,
                'task_id' => !empty($task_id) ? intval($task_id) : null,
                'is_billable' => $is_billable ? 1 : 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            log_message('debug', 'Data to insert: ' . json_encode($data));

            try {
                $db->table('time_entries')->insert($data);
                log_message('debug', 'SuperAdminController::createTimeEntry() - Insert successful');
                return redirect()->to('/x9k2m8p5q7/dashboard')->with('success', 'Time entry created successfully');
            } catch (\Exception $e) {
                log_message('error', 'SuperAdminController::createTimeEntry() - Insert exception: ' . $e->getMessage());
                return redirect()->back()->withInput()->with('error', 'Failed to create time entry: ' . $e->getMessage());
            }
        }

        // Get list of users for dropdown
        $users = $db->table('users')->where('active', 1)->orderBy('username', 'ASC')->get()->getResultArray();

        log_message('debug', 'SuperAdminController::createTimeEntry() - Showing create form (GET request)');
        return view('superadmin/create_time_entry', [
            'title' => 'Create Time Entry',
            'users' => $users
        ]);
    }

    public function createCheckIn()
    {
        log_message('debug', '=== SuperAdminController::createCheckIn() - Started ===');
        log_message('debug', 'Request method: ' . strtoupper($this->request->getMethod()));

        if ($this->verifySuperAdmin() !== true) {
            log_message('error', 'SuperAdminController::createCheckIn() - Admin verification failed');
            return $this->verifySuperAdmin();
        }

        $db = \Config\Database::connect();

        if (strtoupper($this->request->getMethod()) === 'POST') {
            log_message('debug', '=== SuperAdminController::createCheckIn() - Processing POST request ===');

            $user_id = $this->request->getPost('user_id');
            $check_in_date = $this->request->getPost('check_in_date');
            $mood = $this->request->getPost('mood');
            $achievements = $this->request->getPost('achievements');
            $plans = $this->request->getPost('plans');
            $blockers = $this->request->getPost('blockers');
            $notes = $this->request->getPost('notes');

            log_message('debug', 'Received user_id: [' . $user_id . ']');
            log_message('debug', 'Received check_in_date: [' . $check_in_date . ']');

            // Validate required fields
            if (empty($user_id) || empty($check_in_date)) {
                log_message('error', 'SuperAdminController::createCheckIn() - Missing required fields');
                return redirect()->back()->withInput()->with('error', 'User and check-in date are required');
            }

            // Verify user exists
            $user = $db->table('users')->where('id', $user_id)->get()->getRowArray();
            if (!$user) {
                log_message('error', 'SuperAdminController::createCheckIn() - User not found: ' . $user_id);
                return redirect()->back()->withInput()->with('error', 'User not found');
            }

            $data = [
                'user_id' => intval($user_id),
                'check_in_date' => $check_in_date,
                'mood' => !empty($mood) ? $mood : null,
                'achievements' => $achievements,
                'plans' => $plans,
                'blockers' => $blockers,
                'notes' => $notes,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            log_message('debug', 'Data to insert: ' . json_encode($data));

            try {
                $db->table('daily_check_ins')->insert($data);
                log_message('debug', 'SuperAdminController::createCheckIn() - Insert successful');
                return redirect()->to('/x9k2m8p5q7/dashboard')->with('success', 'Check-in created successfully');
            } catch (\Exception $e) {
                log_message('error', 'SuperAdminController::createCheckIn() - Insert exception: ' . $e->getMessage());
                return redirect()->back()->withInput()->with('error', 'Failed to create check-in: ' . $e->getMessage());
            }
        }

        // Get list of users for dropdown
        $users = $db->table('users')->where('active', 1)->orderBy('username', 'ASC')->get()->getResultArray();

        log_message('debug', 'SuperAdminController::createCheckIn() - Showing create form (GET request)');
        return view('superadmin/create_check_in', [
            'title' => 'Create Check-in',
            'users' => $users
        ]);
    }

    public function createUser()
    {
        log_message('debug', '=== SuperAdminController::createUser() - Started ===');
        log_message('debug', 'Request method: ' . strtoupper($this->request->getMethod()));

        if ($this->verifySuperAdmin() !== true) {
            log_message('error', 'SuperAdminController::createUser() - Admin verification failed');
            return $this->verifySuperAdmin();
        }

        $db = \Config\Database::connect();

        if (strtoupper($this->request->getMethod()) === 'POST') {
            log_message('debug', '=== SuperAdminController::createUser() - Processing POST request ===');

            $username = $this->request->getPost('username');
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');

            log_message('debug', 'Received username: [' . $username . ']');
            log_message('debug', 'Received email: [' . $email . ']');

            // Validate required fields
            if (empty($username) || empty($email) || empty($password)) {
                log_message('error', 'SuperAdminController::createUser() - Missing required fields');
                return redirect()->back()->withInput()->with('error', 'Username, email, and password are required');
            }

            // Check if username already exists
            $existingUser = $db->table('users')->where('username', $username)->get()->getRowArray();
            if ($existingUser) {
                log_message('error', 'SuperAdminController::createUser() - Username already exists: ' . $username);
                return redirect()->back()->withInput()->with('error', 'Username already exists');
            }

            // Check if email already exists
            $existingEmail = $db->table('auth_identities')
                ->where('secret', $email)
                ->where('type', 'email_password')
                ->get()->getRowArray();
            if ($existingEmail) {
                log_message('error', 'SuperAdminController::createUser() - Email already exists: ' . $email);
                return redirect()->back()->withInput()->with('error', 'Email already exists');
            }

            try {
                // Create user
                $userData = [
                    'username' => $username,
                    'active' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];

                $db->table('users')->insert($userData);
                $userId = $db->insertID();
                log_message('debug', 'SuperAdminController::createUser() - User created with ID: ' . $userId);

                // Create auth identity
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $identityData = [
                    'user_id' => $userId,
                    'type' => 'email_password',
                    'secret' => $email,
                    'secret2' => $hashedPassword,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];

                $db->table('auth_identities')->insert($identityData);
                log_message('debug', 'SuperAdminController::createUser() - Auth identity created');

                return redirect()->to('/x9k2m8p5q7/dashboard')->with('success', 'User created successfully');
            } catch (\Exception $e) {
                log_message('error', 'SuperAdminController::createUser() - Exception: ' . $e->getMessage());
                return redirect()->back()->withInput()->with('error', 'Failed to create user: ' . $e->getMessage());
            }
        }

        log_message('debug', 'SuperAdminController::createUser() - Showing create form (GET request)');
        return view('superadmin/create_user', [
            'title' => 'Create User'
        ]);
    }

    public function testPost()
    {
        log_message('debug', '=== SuperAdminController::testPost() - Started ===');
        log_message('debug', 'Request method: ' . strtoupper($this->request->getMethod()));
        
        if (strtoupper($this->request->getMethod()) === 'POST') {
            log_message('debug', '=== POST REQUEST RECEIVED ===');
            log_message('debug', 'POST data: ' . json_encode($this->request->getPost()));
            return json_encode(['status' => 'success', 'message' => 'POST received', 'data' => $this->request->getPost()]);
        }
        
        return 'GET request received';
    }

    public function logout()
    {
        session()->remove(['superadmin_logged_in', 'superadmin_username', 'superadmin_email']);
        return redirect()->to('/x9k2m8p5q7/login');
    }
}

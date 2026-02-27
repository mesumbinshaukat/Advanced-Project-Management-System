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
        log_message('debug', 'SuperAdminController::login() - Method started');

        if ($this->request->getMethod() === 'post') {
            log_message('debug', 'SuperAdminController::login() - POST request detected');

            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');

            log_message('debug', 'SuperAdminController::login() - Received email: ' . $email);
            log_message('debug', 'SuperAdminController::login() - Received password length: ' . strlen($password));

            // Check if SystemConfigModel is available
            if (!$this->systemConfigModel) {
                log_message('error', 'SuperAdminController::login() - SystemConfigModel not initialized');
                return redirect()->back()->with('error', 'System configuration error');
            }

            log_message('debug', 'SuperAdminController::login() - Fetching stored credentials from database');

            $storedEmail = $this->systemConfigModel->getConfig('superadmin_email');
            $storedPassword = $this->systemConfigModel->getConfig('superadmin_password');
            $storedUsername = $this->systemConfigModel->getConfig('superadmin_username');

            log_message('debug', 'SuperAdminController::login() - Stored email retrieved: ' . ($storedEmail ? 'YES' : 'NO'));
            log_message('debug', 'SuperAdminController::login() - Stored password retrieved: ' . ($storedPassword ? 'YES' : 'NO'));
            log_message('debug', 'SuperAdminController::login() - Stored username retrieved: ' . ($storedUsername ? 'YES' : 'NO'));

            if (!$storedEmail || !$storedPassword) {
                log_message('error', 'SuperAdminController::login() - Missing stored credentials in database');
                return redirect()->back()->with('error', 'System configuration error');
            }

            log_message('debug', 'SuperAdminController::login() - Comparing credentials');
            log_message('debug', 'SuperAdminController::login() - Email comparison: ' . ($email === $storedEmail ? 'MATCH' : 'NO MATCH'));
            log_message('debug', 'SuperAdminController::login() - Password comparison: ' . ($password === $storedPassword ? 'MATCH' : 'NO MATCH'));

            if ($email === $storedEmail && $password === $storedPassword) {
                log_message('debug', 'SuperAdminController::login() - Authentication successful, setting session');

                session()->set([
                    'superadmin_logged_in' => true,
                    'superadmin_username' => $storedUsername,
                    'superadmin_email' => $storedEmail
                ]);

                log_message('debug', 'SuperAdminController::login() - Session set, redirecting to dashboard');
                return redirect()->to('/x9k2m8p5q7/dashboard');
            } else {
                log_message('debug', 'SuperAdminController::login() - Authentication failed, redirecting back with error');
                return redirect()->back()->with('error', 'Invalid credentials');
            }
        }

        log_message('debug', 'SuperAdminController::login() - GET request, showing login form');
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
        log_message('debug', 'SuperAdminController::dashboard() - Rendering dashboard view');

        return view('superadmin/dashboard', [
            'title' => 'System Control Panel',
            'timeEntries' => $timeEntries,
            'checkIns' => $checkIns,
            'users' => $users
        ]);
    }

    public function editTimeEntry($id)
    {
        if ($this->verifySuperAdmin() !== true) {
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
            return redirect()->back()->with('error', 'Time entry not found');
        }

        if ($this->request->getMethod() === 'post') {
            $data = [
                'hours' => $this->request->getPost('hours'),
                'description' => $this->request->getPost('description'),
                'date' => $this->request->getPost('date'),
                'task_id' => $this->request->getPost('task_id') ?: null,
                'is_billable' => $this->request->getPost('is_billable') ? 1 : 0,
            ];

            $db->table('time_entries')->where('id', $id)->update($data);
            return redirect()->to('/x9k2m8p5q7/dashboard')->with('success', 'Time entry updated');
        }

        return view('superadmin/edit_time_entry', [
            'title' => 'Edit Time Entry',
            'entry' => $timeEntry
        ]);
    }

    public function editCheckIn($id)
    {
        if ($this->verifySuperAdmin() !== true) {
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
            return redirect()->back()->with('error', 'Check-in not found');
        }

        if ($this->request->getMethod() === 'post') {
            $data = [
                'check_in_date' => $this->request->getPost('check_in_date'),
                'mood' => $this->request->getPost('mood'),
                'achievements' => $this->request->getPost('achievements'),
                'plans' => $this->request->getPost('plans'),
                'blockers' => $this->request->getPost('blockers'),
                'notes' => $this->request->getPost('notes'),
            ];

            $db->table('daily_check_ins')->where('id', $id)->update($data);
            return redirect()->to('/x9k2m8p5q7/dashboard')->with('success', 'Check-in updated');
        }

        return view('superadmin/edit_check_in', [
            'title' => 'Edit Check-in',
            'checkin' => $checkIn
        ]);
    }

    public function editUser($id)
    {
        if ($this->verifySuperAdmin() !== true) {
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
            return redirect()->back()->with('error', 'User not found');
        }

        if ($this->request->getMethod() === 'post') {
            $data = [
                'username' => $this->request->getPost('username'),
                'active' => $this->request->getPost('active') ? 1 : 0,
            ];

            $db->table('users')->where('id', $id)->update($data);

            // Update email if provided
            $newEmail = $this->request->getPost('email');
            if ($newEmail && $newEmail !== $user['email']) {
                $db->table('auth_identities')
                    ->where('user_id', $id)
                    ->where('type', 'email_password')
                    ->update(['secret' => $newEmail]);
            }

            return redirect()->to('/x9k2m8p5q7/dashboard')->with('success', 'User updated');
        }

        return view('superadmin/edit_user', [
            'title' => 'Edit User',
            'user' => $user
        ]);
    }

    public function logout()
    {
        session()->remove(['superadmin_logged_in', 'superadmin_username', 'superadmin_email']);
        return redirect()->to('/x9k2m8p5q7/login');
    }
}

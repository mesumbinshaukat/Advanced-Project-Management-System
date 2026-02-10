<?php

namespace App\Controllers;

use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Entities\User;

class UsersController extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        // Admin only
        if (!auth()->user()->inGroup('admin')) {
            return redirect()->to('dashboard')->with('error', 'Unauthorized access');
        }

        $users = $this->userModel->findAll();
        
        return view('users/index', [
            'title' => 'User Management',
            'users' => $users,
        ]);
    }

    public function create()
    {
        // Admin only
        if (!auth()->user()->inGroup('admin')) {
            return redirect()->to('dashboard')->with('error', 'Unauthorized access');
        }

        return view('users/create', [
            'title' => 'Create New User',
        ]);
    }

    public function store()
    {
        // Admin only
        if (!auth()->user()->inGroup('admin')) {
            return redirect()->to('dashboard')->with('error', 'Unauthorized access');
        }

        $rules = [
            'email' => 'required|valid_email|is_unique[auth_identities.secret]',
            'username' => 'required|min_length[3]|max_length[30]|is_unique[users.username]',
            'password' => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
            'role' => 'required|in_list[admin,developer]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            // Create user with Shield
            $user = new User([
                'username' => $this->request->getPost('username'),
                'email' => $this->request->getPost('email'),
                'password' => $this->request->getPost('password'),
            ]);

            $this->userModel->save($user);
            $userId = $this->userModel->getInsertID();

            // Assign role - fetch the newly created user and add group
            $newUser = $this->userModel->find($userId);
            if ($newUser) {
                $role = $this->request->getPost('role');
                $newUser->addGroup($role);
            }

            // Log activity
            $activityModel = new \App\Models\ActivityLogModel();
            $activityModel->logActivity('user', $userId, 'create');

            return redirect()->to('admin/users')->with('success', 'User created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    public function edit($userId)
    {
        // Admin only
        if (!auth()->user()->inGroup('admin')) {
            return redirect()->to('dashboard')->with('error', 'Unauthorized access');
        }

        $user = $this->userModel->find($userId);
        if (!$user) {
            return redirect()->to('admin/users')->with('error', 'User not found');
        }

        // Get user's groups
        $userGroups = $user->getGroups();

        return view('users/edit', [
            'title' => 'Edit User',
            'user' => $user,
            'userGroups' => $userGroups,
        ]);
    }

    public function update($userId)
    {
        // Admin only
        if (!auth()->user()->inGroup('admin')) {
            return redirect()->to('dashboard')->with('error', 'Unauthorized access');
        }

        $user = $this->userModel->find($userId);
        if (!$user) {
            return redirect()->to('admin/users')->with('error', 'User not found');
        }

        // Prevent admin from changing own role
        if ($userId == auth()->user()->id && $this->request->getPost('role') != $user->getGroups()[0]) {
            return redirect()->back()->with('error', 'You cannot change your own role');
        }

        $rules = [
            'email' => 'required|valid_email',
            'username' => 'required|min_length[3]|max_length[30]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $user->email = $this->request->getPost('email');
            $user->username = $this->request->getPost('username');

            $this->userModel->save($user);

            // Update role if changed
            $newRole = $this->request->getPost('role');
            $currentRole = $user->getGroups()[0] ?? null;

            if ($newRole != $currentRole) {
                // Remove old role
                if ($currentRole) {
                    $user->removeGroup($currentRole);
                }
                // Add new role
                $user->addGroup($newRole);
            }

            // Log activity
            $activityModel = new \App\Models\ActivityLogModel();
            $activityModel->logActivity('user', $userId, 'update');

            return redirect()->to('admin/users')->with('success', 'User updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    public function deactivate($userId)
    {
        // Admin only
        if (!auth()->user()->inGroup('admin')) {
            return redirect()->to('admin/users')->with('error', 'Unauthorized access');
        }

        $user = $this->userModel->find($userId);
        if (!$user) {
            return redirect()->to('admin/users')->with('error', 'User not found');
        }

        // Prevent deactivating last admin
        if ($user->inGroup('admin')) {
            $adminCount = $this->userModel->where('active', 1)->findAll();
            $adminCount = array_filter($adminCount, function($u) {
                return $u->inGroup('admin');
            });
            
            if (count($adminCount) <= 1) {
                return redirect()->back()->with('error', 'Cannot deactivate the last admin user');
            }
        }

        // Prevent deactivating self
        if ($userId == auth()->user()->id) {
            return redirect()->back()->with('error', 'You cannot deactivate your own account');
        }

        try {
            $user->active = 0;
            $this->userModel->save($user);

            // Log activity
            $activityModel = new \App\Models\ActivityLogModel();
            $activityModel->logActivity('user', $userId, 'deactivate');

            return redirect()->to('admin/users')->with('success', 'User deactivated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to deactivate user: ' . $e->getMessage());
        }
    }

    public function resetPassword($userId)
    {
        // Admin only
        if (!auth()->user()->inGroup('admin')) {
            return redirect()->to('admin/users')->with('error', 'Unauthorized access');
        }

        $user = $this->userModel->find($userId);
        if (!$user) {
            return redirect()->to('admin/users')->with('error', 'User not found');
        }

        try {
            // Generate temporary password
            $tempPassword = bin2hex(random_bytes(8));
            
            $user->password = $tempPassword;
            $this->userModel->save($user);

            // Log activity
            $activityModel = new \App\Models\ActivityLogModel();
            $activityModel->logActivity('user', $userId, 'password_reset');

            // TODO: Send email with temporary password and reset link
            // For now, just show the temp password to admin

            return redirect()->back()->with('success', 'Password reset. Temporary password: ' . $tempPassword . ' (User should change on next login)');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to reset password: ' . $e->getMessage());
        }
    }
}

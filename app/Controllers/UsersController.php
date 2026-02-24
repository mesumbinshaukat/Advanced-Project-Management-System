<?php

namespace App\Controllers;

use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Entities\User;
use App\Models\UserSkillModel;

class UsersController extends BaseController
{
    protected $userModel;
    protected $userSkillModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->userSkillModel = new UserSkillModel();
    }

    public function index()
    {
        $users = $this->userModel
            ->select('users.*, auth_identities.secret as email')
            ->asArray()
            ->join('auth_identities', 'auth_identities.user_id = users.id', 'left')
            ->where('auth_identities.type', 'email_password')
            ->findAll();

        $userIds = array_column($users, 'id');
        $skillsMap = $this->userSkillModel->getSkillsForUsers($userIds);

        return view('users/index', [
            'title' => 'User Management',
            'users' => $users,
            'userSkills' => $skillsMap,
        ]);
    }

    public function create()
    {
        return view('users/create', [
            'title' => 'Create User',
        ]);
    }

    public function store()
    {
        $validation = \Config\Services::validation();
        
        $rules = [
            'username' => 'required|min_length[3]|max_length[30]|is_unique[users.username]',
            'email' => 'required|valid_email|is_unique[auth_identities.secret]',
            'password' => 'required|min_length[8]|strong_password',
            'password_confirm' => 'required|matches[password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $user = new User([
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
        ]);

        if (!$this->userModel->save($user)) {
            return redirect()->back()->withInput()->with('errors', $this->userModel->errors());
        }

        $userId = $this->userModel->getInsertID();
        $skillsInput = $this->request->getPost('skills') ?? '';
        $skills = array_filter(array_map('trim', explode(',', $skillsInput)));
        $this->userSkillModel->setUserSkills($userId, $skills);
        
        // Assign default 'developer' role to new users
        $user = $this->userModel->find($userId);
        $user->activate();
        $user->addGroup('developer');

        return redirect()->to('/users')->with('success', 'User created successfully');
    }

    public function edit($id)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->back()->with('error', 'User not found');
        }

        $skills = $this->userSkillModel->getSkillsForUser($id);

        return view('users/edit', [
            'title' => 'Edit User',
            'user' => $user,
            'skills' => $skills,
            'skills_display' => implode(', ', $skills),
        ]);
    }

    public function update($id)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->back()->with('error', 'User not found');
        }

        $validation = \Config\Services::validation();
        
        $rules = [
            'username' => "required|min_length[3]|max_length[30]|is_unique[users.username,id,{$id}]",
            'email' => "required|valid_email|is_unique[auth_identities.secret,user_id,{$id}]",
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $user->username = $this->request->getPost('username');
        
        if (!$this->userModel->save($user)) {
            return redirect()->back()->withInput()->with('errors', $this->userModel->errors());
        }

        $skillsInput = $this->request->getPost('skills') ?? '';
        $skills = array_filter(array_map('trim', explode(',', $skillsInput)));
        $this->userSkillModel->setUserSkills($id, $skills);

        return redirect()->to('/users')->with('success', 'User updated successfully');
    }

    public function delete($id)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->back()->with('error', 'User not found');
        }

        // Prevent deleting the current user
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot delete your own account');
        }

        $this->userModel->delete($id);

        return redirect()->to('/users')->with('success', 'User deleted successfully');
    }
}

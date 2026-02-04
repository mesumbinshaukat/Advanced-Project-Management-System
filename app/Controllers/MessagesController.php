<?php

namespace App\Controllers;

use App\Models\MessageModel;
use App\Models\ProjectModel;

class MessagesController extends BaseController
{
    protected $messageModel;
    protected $projectModel;

    public function __construct()
    {
        $this->messageModel = new MessageModel();
        $this->projectModel = new ProjectModel();
    }

    public function index($projectId)
    {
        $user = auth()->user();
        $isAdmin = $user->inGroup('admin');
        
        $project = $this->projectModel->find($projectId);

        if (!$project) {
            return redirect()->back()->with('error', 'Project not found');
        }

        if (!$isAdmin) {
            $projectUserModel = new \App\Models\ProjectUserModel();
            if (!$projectUserModel->isUserAssignedToProject($projectId, $user->id)) {
                return redirect()->to('/projects')->with('error', 'You do not have access to this project');
            }
        }

        $taskId = $this->request->getGet('task_id');
        $messages = $this->messageModel->getThreadedMessages($projectId, $taskId);
        $unreadCount = $this->messageModel->getUnreadCount($projectId, auth()->id());

        return view('messages/index', [
            'title' => 'Messages - ' . $project['name'],
            'project' => $project,
            'messages' => $messages,
            'taskId' => $taskId,
            'unreadCount' => $unreadCount,
        ]);
    }

    public function store()
    {
        $data = $this->request->getPost();
        $data['user_id'] = auth()->id();
        $data['message'] = trim($data['message'] ?? '');

        if (empty($data['message'])) {
            return redirect()->back()->withInput()->with('error', 'Message cannot be empty');
        }

        $user = auth()->user();
        $isAdmin = $user->inGroup('admin');
        
        if (!$isAdmin) {
            $projectUserModel = new \App\Models\ProjectUserModel();
            if (!$projectUserModel->isUserAssignedToProject($data['project_id'], $user->id)) {
                return redirect()->to('/projects')->with('error', 'You do not have access to this project');
            }
        }

        if (!$this->messageModel->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $this->messageModel->errors());
        }

        $redirectUrl = "/messages/{$data['project_id']}";
        if (isset($data['task_id']) && $data['task_id']) {
            $redirectUrl .= "?task_id={$data['task_id']}";
        }

        return redirect()->to($redirectUrl)->with('success', 'Message posted successfully');
    }
}

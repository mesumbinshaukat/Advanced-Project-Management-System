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
        $project = $this->projectModel->find($projectId);

        if (!$project) {
            return redirect()->back()->with('error', 'Project not found');
        }

        $taskId = $this->request->getGet('task_id');
        $messages = $this->messageModel->getThreadedMessages($projectId, $taskId);
        $unreadCount = $this->messageModel->getUnreadCount($projectId, auth()->id());

        return view('messages/index', [
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

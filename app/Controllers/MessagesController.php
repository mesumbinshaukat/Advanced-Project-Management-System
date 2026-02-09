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
        try {
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
        } catch (\Throwable $e) {
            $errorFile = WRITEPATH . 'logs/error_debug.log';
            $errorMsg = date('Y-m-d H:i:s') . ' - MessagesController - ' . get_class($e) . ': ' . $e->getMessage() . "\n";
            $errorMsg .= "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
            $errorMsg .= "Trace:\n" . $e->getTraceAsString() . "\n\n";
            file_put_contents($errorFile, $errorMsg, FILE_APPEND);
            
            throw $e;
        }
    }

    public function store()
    {
        $data = $this->request->getPost();
        $data['user_id'] = auth()->id();
        $data['content'] = trim($data['content'] ?? $data['message'] ?? '');

        if (empty($data['content'])) {
            return redirect()->back()->withInput()->with('error', 'Message cannot be empty');
        }
        
        unset($data['message']);

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

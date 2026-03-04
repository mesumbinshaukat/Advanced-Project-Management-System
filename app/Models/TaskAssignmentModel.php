<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskAssignmentModel extends Model
{
    protected $table = 'task_assignments';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $allowedFields = [
        'task_id',
        'user_id',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getAssignedUsers($taskId)
    {
        // First check if there are any assignments in task_assignments table
        $assignments = $this->select('task_assignments.*, users.username, users.email')
            ->join('users', 'users.id = task_assignments.user_id')
            ->where('task_assignments.task_id', $taskId)
            ->findAll();
        
        // If no assignments found, check legacy assigned_to field in tasks table
        if (empty($assignments)) {
            $db = \Config\Database::connect();
            $task = $db->table('tasks')
                ->select('tasks.assigned_to, users.id, users.username, users.email')
                ->join('users', 'users.id = tasks.assigned_to', 'left')
                ->where('tasks.id', $taskId)
                ->where('tasks.assigned_to IS NOT NULL')
                ->get()
                ->getRowArray();
            
            if ($task && $task['assigned_to']) {
                return [[
                    'id' => null,
                    'task_id' => $taskId,
                    'user_id' => $task['assigned_to'],
                    'username' => $task['username'],
                    'email' => $task['email'],
                ]];
            }
        }
        
        return $assignments;
    }

    public function assignUser($taskId, $userId)
    {
        return $this->insert([
            'task_id' => $taskId,
            'user_id' => $userId,
        ]);
    }

    public function removeAssignment($taskId, $userId)
    {
        return $this->where('task_id', $taskId)
            ->where('user_id', $userId)
            ->delete();
    }

    public function removeAllAssignments($taskId)
    {
        return $this->where('task_id', $taskId)->delete();
    }

    public function getAssignedUserIds($taskId)
    {
        $assignments = $this->where('task_id', $taskId)->findAll();
        return array_column($assignments, 'user_id');
    }

    public function syncAssignments($taskId, $userIds = [])
    {
        // Remove all existing assignments
        $this->removeAllAssignments($taskId);

        // Add new assignments
        if (!empty($userIds)) {
            foreach ($userIds as $userId) {
                $this->assignUser($taskId, $userId);
            }
        }

        return true;
    }
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class DailyCheckInModel extends Model
{
    protected $table = 'check_ins';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'user_id',
        'date',
        'mood',
        'productivity',
        'blockers',
        'achievements',
        'plans',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = '';
    protected $validationRules = [
        'user_id' => 'required|integer',
        'date' => 'required|valid_date',
        'mood' => 'required|in_list[great,good,okay,bad,terrible]',
    ];
    protected $validationMessages = [];
    protected $skipValidation = false;

    public function getTodayCheckIn($userId)
    {
        return $this->where('user_id', $userId)
            ->where('date', date('Y-m-d'))
            ->first();
    }

    public function getRecentCheckIns($userId, $days = 7)
    {
        return $this->where('user_id', $userId)
            ->where('date >=', date('Y-m-d', strtotime("-{$days} days")))
            ->orderBy('date', 'DESC')
            ->findAll();
    }

    public function getTeamCheckIns($date = null)
    {
        $date = $date ?? date('Y-m-d');
        
        return $this->select('check_ins.*, users.username')
            ->join('users', 'users.id = check_ins.user_id')
            ->where('check_ins.date', $date)
            ->orderBy('check_ins.mood', 'DESC')
            ->findAll();
    }

    public function getCheckInStreak($userId)
    {
        $checkIns = $this->select('date')
            ->where('user_id', $userId)
            ->orderBy('date', 'DESC')
            ->findAll(30);

        $streak = 0;
        $expectedDate = date('Y-m-d');

        foreach ($checkIns as $checkIn) {
            if ($checkIn['date'] === $expectedDate) {
                $streak++;
                $expectedDate = date('Y-m-d', strtotime($expectedDate . ' -1 day'));
            } else {
                break;
            }
        }

        return $streak;
    }
}

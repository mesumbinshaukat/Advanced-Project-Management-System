<?php

namespace App\Models;

use CodeIgniter\Model;

class DailyCheckInModel extends Model
{
    protected $table = 'daily_check_ins';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'user_id',
        'check_in_date',
        'mood',
        'yesterday_accomplishments',
        'today_plan',
        'blockers',
        'needs_help',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $validationRules = [
        'user_id' => 'required|integer',
        'check_in_date' => 'required|valid_date',
        'mood' => 'required|in_list[great,good,okay,struggling,blocked]',
    ];
    protected $validationMessages = [];
    protected $skipValidation = false;

    public function getTodayCheckIn($userId)
    {
        return $this->where('user_id', $userId)
            ->where('check_in_date', date('Y-m-d'))
            ->first();
    }

    public function getRecentCheckIns($userId, $days = 7)
    {
        return $this->where('user_id', $userId)
            ->where('check_in_date >=', date('Y-m-d', strtotime("-{$days} days")))
            ->orderBy('check_in_date', 'DESC')
            ->findAll();
    }

    public function getTeamCheckIns($date = null)
    {
        $date = $date ?? date('Y-m-d');
        
        return $this->select('daily_check_ins.*, users.username')
            ->join('users', 'users.id = daily_check_ins.user_id')
            ->where('daily_check_ins.check_in_date', $date)
            ->orderBy('daily_check_ins.mood', 'DESC')
            ->findAll();
    }

    public function getCheckInStreak($userId)
    {
        $checkIns = $this->select('check_in_date')
            ->where('user_id', $userId)
            ->orderBy('check_in_date', 'DESC')
            ->findAll(30);

        $streak = 0;
        $expectedDate = date('Y-m-d');

        foreach ($checkIns as $checkIn) {
            if ($checkIn['check_in_date'] === $expectedDate) {
                $streak++;
                $expectedDate = date('Y-m-d', strtotime($expectedDate . ' -1 day'));
            } else {
                break;
            }
        }

        return $streak;
    }
}

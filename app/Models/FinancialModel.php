<?php

namespace App\Models;

use CodeIgniter\Model;

class FinancialModel extends Model
{
    protected $table = 'financials';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'project_id',
        'hourly_rate',
        'fixed_price',
        'total_cost',
        'total_revenue',
        'profit_margin',
        'billing_type',
        'currency'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = '';

    protected $validationRules = [
        'project_id' => 'required|is_natural_no_zero',
        'billing_type' => 'required|in_list[hourly,fixed,retainer]',
    ];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = ['calculateProfitMargin'];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    public function getProjectFinancials($projectId)
    {
        return $this->where('project_id', $projectId)->first();
    }

    public function calculateProjectRevenue($projectId)
    {
        $timeModel = new TimeEntryModel();
        $financial = $this->getProjectFinancials($projectId);
        
        if (!$financial) {
            return 0;
        }

        if ($financial['billing_type'] === 'fixed') {
            return $financial['fixed_price'];
        }

        $totalHours = $timeModel->select('SUM(hours) as total')
            ->join('tasks', 'tasks.id = time_entries.task_id')
            ->where('tasks.project_id', $projectId)
            ->where('time_entries.is_billable', 1)
            ->first();

        return ($totalHours['total'] ?? 0) * ($financial['hourly_rate'] ?? 0);
    }

    protected function calculateProfitMargin(array $data)
    {
        if (isset($data['data']['total_revenue']) && isset($data['data']['total_cost'])) {
            $revenue = $data['data']['total_revenue'];
            $cost = $data['data']['total_cost'];
            
            if ($revenue > 0) {
                $data['data']['profit_margin'] = (($revenue - $cost) / $revenue) * 100;
            }
        }
        
        return $data;
    }
}

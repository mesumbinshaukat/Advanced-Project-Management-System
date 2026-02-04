<?php

namespace App\Models;

use CodeIgniter\Model;

class PerformanceMetricModel extends Model
{
    protected $table = 'performance_metrics';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'user_id',
        'project_id',
        'metric_date',
        'tasks_completed',
        'hours_logged',
        'efficiency_score',
        'quality_score',
        'on_time_delivery'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'on_time_delivery' => 'boolean',
    ];
    protected array $castHandlers = [];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = '';

    protected $validationRules = [
        'user_id' => 'required|is_natural_no_zero',
        'metric_date' => 'required|valid_date',
    ];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    public function getUserMetrics($userId, $startDate = null, $endDate = null)
    {
        $builder = $this->where('user_id', $userId);
        
        if ($startDate) {
            $builder->where('metric_date >=', $startDate);
        }
        
        if ($endDate) {
            $builder->where('metric_date <=', $endDate);
        }
        
        return $builder->orderBy('metric_date', 'DESC')->findAll();
    }
}

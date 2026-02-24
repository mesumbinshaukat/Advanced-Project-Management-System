<?php

namespace App\Models;

use CodeIgniter\Model;

class UserSkillModel extends Model
{
    protected $table = 'user_skills';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['user_id', 'skill'];

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
        'user_id' => 'required|is_natural_no_zero',
        'skill' => 'required|min_length[2]|max_length[100]'
    ];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    public function getSkillsForUser(int $userId): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('skill', 'ASC')
            ->findColumn('skill') ?? [];
    }

    public function getSkillsForUsers(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        $rows = $this->whereIn('user_id', $userIds)
            ->orderBy('skill', 'ASC')
            ->findAll();

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['user_id']][] = $row['skill'];
        }

        return $grouped;
    }

    public function getAllSkills(): array
    {
        return $this->select('skill')
            ->distinct()
            ->orderBy('skill', 'ASC')
            ->findColumn('skill') ?? [];
    }

    public function setUserSkills(int $userId, array $skills): void
    {
        $skills = array_values(array_unique(array_filter(array_map(static function ($skill) {
            return trim($skill);
        }, $skills))));

        $this->where('user_id', $userId)->delete();

        if (empty($skills)) {
            return;
        }

        $batch = array_map(static function ($skill) use ($userId) {
            return [
                'user_id' => $userId,
                'skill' => $skill,
            ];
        }, $skills);

        $this->insertBatch($batch);
    }
}

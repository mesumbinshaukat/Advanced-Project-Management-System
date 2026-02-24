<?php

namespace App\Controllers;

use App\Models\DailyCheckInModel;
use CodeIgniter\Shield\Models\UserModel;

class CheckInController extends BaseController
{
    protected DailyCheckInModel $checkInModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->checkInModel = new DailyCheckInModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $userId = auth()->id();
        $todayCheckIn = $this->checkInModel->getTodayCheckIn($userId);
        $recentCheckIns = $this->checkInModel->getRecentCheckIns($userId, 7);
        $streak = $this->checkInModel->getCheckInStreak($userId);

        return view('check_in/index', [
            'title' => 'Daily Check-In',
            'today_check_in' => $todayCheckIn,
            'recent_check_ins' => $recentCheckIns,
            'streak' => $streak,
            'can_checkout' => $this->canCheckout($todayCheckIn),
            'has_checked_out' => !empty($todayCheckIn['checked_out_at']),
        ]);
    }

    public function store()
    {
        $userId = auth()->id();
        $payload = $this->request->getPost();
        $payload['user_id'] = $userId;
        $payload['check_in_date'] = date('Y-m-d');

        $existing = $this->checkInModel->getTodayCheckIn($userId);

        if ($existing && !empty($existing['checked_out_at'])) {
            return redirect()->back()->with('error', 'You have already checked out for today. Come back tomorrow.');
        }

        $clientCheckedIn = $this->sanitizeClientTimestamp($this->request->getPost('client_checked_in_at'));
        $now = $clientCheckedIn ?? date('Y-m-d H:i:s');

        $payload['checkout_ready'] = 1;

        if ($existing) {
            if (empty($existing['checked_in_at'])) {
                $payload['checked_in_at'] = $now;
            }

            if (!$this->checkInModel->update($existing['id'], $payload)) {
                return redirect()->back()->withInput()->with('errors', $this->checkInModel->errors());
            }
        } else {
            $payload['checked_in_at'] = $now;

            if (!$this->checkInModel->insert($payload)) {
                return redirect()->back()->withInput()->with('errors', $this->checkInModel->errors());
            }
        }

        $this->touchUserActivity($userId, $now);

        return redirect()->to('/check-in')->with('success', 'Check-in saved successfully');
    }

    public function checkout()
    {
        $userId = auth()->id();
        $today = $this->checkInModel->getTodayCheckIn($userId);

        if (!$this->canCheckout($today)) {
            return redirect()->back()->with('error', 'Checkout not available. Make sure you have checked in and not already checked out.');
        }

        $clientCheckout = $this->sanitizeClientTimestamp($this->request->getPost('client_checked_out_at'));

        $update = [
            'checked_out_at' => $clientCheckout ?? date('Y-m-d H:i:s'),
            'checkout_ready' => 0,
        ];

        if (!$this->checkInModel->update($today['id'], $update)) {
            return redirect()->back()->with('error', 'Unable to record checkout. Please try again.');
        }

        $this->touchUserActivity($userId);

        return redirect()->to('/check-in')->with('success', 'Checked out successfully. Enjoy the rest of your day!');
    }

    public function team()
    {
        if (!auth()->user()->inGroup('admin')) {
            return redirect()->to('/check-in')->with('error', 'Access denied');
        }

        $date = $this->request->getGet('date') ?? date('Y-m-d');
        $teamCheckIns = $this->checkInModel->getTeamCheckIns($date);

        $developers = $this->userModel
            ->asArray()
            ->select('users.id, users.username')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
            ->where('auth_groups_users.group', 'developer')
            ->where('users.active', 1)
            ->groupBy('users.id')
            ->orderBy('users.username', 'ASC')
            ->findAll();

        $checkInMap = [];
        foreach ($teamCheckIns as $entry) {
            $checkInMap[$entry['user_id']] = $entry;
        }

        $stats = [
            'checked_in' => 0,
            'checked_out' => 0,
            'missing' => 0,
            'total' => count($developers),
        ];

        foreach ($developers as $developer) {
            $entry = $checkInMap[$developer['id']] ?? null;

            if (!empty($entry['checked_out_at'])) {
                $stats['checked_out']++;
            } elseif (!empty($entry['checked_in_at'])) {
                $stats['checked_in']++;
            } else {
                $stats['missing']++;
            }
        }

        return view('check_in/team', [
            'title' => 'Team Check-Ins',
            'date' => $date,
            'developers' => $developers,
            'check_ins' => $checkInMap,
            'stats' => $stats,
        ]);
    }

    public function updateTimes()
    {
        if (!auth()->user()->inGroup('admin')) {
            return redirect()->to('/check-in')->with('error', 'Access denied');
        }

        $checkInId = $this->request->getPost('check_in_id');
        $userId = $this->request->getPost('user_id');
        $checkInDate = $this->request->getPost('check_in_date') ?? date('Y-m-d');

        if (empty($checkInId) && empty($userId)) {
            return redirect()->back()->with('error', 'Missing check-in reference.');
        }

        $checkIn = null;

        if (!empty($checkInId)) {
            $checkIn = $this->checkInModel->find($checkInId);
        }

        if (!$checkIn && !empty($userId)) {
            $checkIn = $this->checkInModel
                ->where('user_id', $userId)
                ->where('check_in_date', $checkInDate)
                ->first();

            if (!$checkIn) {
                $newId = $this->checkInModel->insert([
                    'user_id' => $userId,
                    'check_in_date' => $checkInDate,
                    'mood' => 'okay',
                    'checkout_ready' => 0,
                ], true);

                $checkIn = $this->checkInModel->find($newId);
            }
        }

        if (!$checkIn) {
            return redirect()->back()->with('error', 'Check-in record not found.');
        }

        $id = $checkIn['id'];

        $checkedInAt = $this->request->getPost('checked_in_at');
        $checkedOutAt = $this->request->getPost('checked_out_at');
        $checkoutReady = $this->request->getPost('checkout_ready');

        $payload = [];

        if ($checkedInAt !== null) {
            $payload['checked_in_at'] = $checkedInAt === '' ? null : date('Y-m-d H:i:s', strtotime($checkedInAt));
        }

        if ($checkedOutAt !== null) {
            $payload['checked_out_at'] = $checkedOutAt === '' ? null : date('Y-m-d H:i:s', strtotime($checkedOutAt));
        }

        if (!empty($payload['checked_in_at']) && !empty($payload['checked_out_at']) && $payload['checked_out_at'] < $payload['checked_in_at']) {
            return redirect()->back()->withInput()->with('error', 'Check-out time cannot be earlier than check-in time.');
        }

        if ($checkoutReady !== null) {
            $payload['checkout_ready'] = (int) (bool) $checkoutReady;
        } elseif (!empty($payload['checked_out_at'])) {
            $payload['checkout_ready'] = 0;
        } elseif (!empty($payload['checked_in_at'])) {
            $payload['checkout_ready'] = 1;
        }

        if (empty($payload)) {
            return redirect()->back()->with('info', 'No changes to update.');
        }

        if (!$this->checkInModel->update($id, $payload)) {
            return redirect()->back()->with('error', 'Failed to update times.');
        }

        return redirect()->back()->with('success', 'Check-in times updated successfully.');
    }

    private function canCheckout(?array $entry): bool
    {
        return !empty($entry)
            && !empty($entry['checked_in_at'])
            && empty($entry['checked_out_at'])
            && !empty($entry['checkout_ready'])
            && $entry['check_in_date'] === date('Y-m-d');
    }

    private function touchUserActivity(int $userId, ?string $referenceTime = null): void
    {
        $referenceTime = $referenceTime ?? date('Y-m-d H:i:s');
        $db = \Config\Database::connect();
        $updateFields = [
            'last_check_in' => date('Y-m-d', strtotime($referenceTime)),
        ];

        if ($this->columnExists($db, 'users', 'last_activity')) {
            $updateFields['last_activity'] = $referenceTime;
        }

        $db->table('users')->where('id', $userId)->update($updateFields);
    }

    private function columnExists($db, string $table, string $column): bool
    {
        try {
            $fields = $db->getFieldNames($table);
            return in_array($column, $fields, true);
        } catch (\Throwable $th) {
            return false;
        }
    }

    private function sanitizeClientTimestamp(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $value = trim($value);

        $formats = [
            'Y-m-d H:i:s',
            \DateTime::ATOM,
            \DateTime::RFC3339,
        ];

        foreach ($formats as $format) {
            $dt = \DateTime::createFromFormat($format, $value);
            if ($dt instanceof \DateTime) {
                return $dt->format('Y-m-d H:i:s');
            }
        }

        $timestamp = strtotime($value);

        if ($timestamp !== false) {
            return date('Y-m-d H:i:s', $timestamp);
        }

        return null;
    }
}

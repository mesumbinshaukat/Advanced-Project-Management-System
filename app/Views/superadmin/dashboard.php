<?= $this->extend('layouts/superadmin') ?>

<?= $this->section('styles') ?>
<style>
    /* Attendance Stat Cards */
    .attendance-stat-card {
        border-radius: 0.75rem !important;
        padding: 1.5rem !important;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        color: white !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15) !important;
        transition: all 0.3s ease !important;
        border: none !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: space-between !important;
        min-height: 140px !important;
    }

    .attendance-stat-card:hover {
        transform: translateY(-8px) !important;
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.2) !important;
    }

    .attendance-stat-card.success {
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%) !important;
    }

    .attendance-stat-card.warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
    }

    .attendance-stat-card.info {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
    }

    .attendance-stat-value {
        font-size: 2.5rem !important;
        font-weight: 700 !important;
        margin: 0.5rem 0 !important;
        line-height: 1 !important;
    }

    .attendance-stat-label {
        font-size: 0.875rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        opacity: 0.95 !important;
        font-weight: 600 !important;
    }

    .attendance-stat-subtext {
        font-size: 0.75rem !important;
        opacity: 0.85 !important;
        margin-top: 0.5rem !important;
    }

    /* Attendance Dots */
    .attendance-dot {
        width: 20px !important;
        height: 20px !important;
        border-radius: 4px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        color: #fff !important;
        font-weight: 600 !important;
        font-size: 0.65rem !important;
        margin: 2px !important;
        cursor: pointer !important;
        transition: transform 0.2s ease !important;
        border: 1px solid rgba(255,255,255,0.2) !important;
    }

    .attendance-dot:hover {
        transform: scale(1.25) !important;
    }

    .attendance-dot.present {
        background: #22c55e !important;
    }

    .attendance-dot.absent {
        background: #e2e8f0 !important;
        color: #475569 !important;
    }

    .attendance-dot.weekend {
        background: #c084fc !important;
    }

    .attendance-map {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 4px !important;
        max-width: 100% !important;
    }

    /* Progress Bar */
    .attendance-progress {
        height: 8px !important;
        border-radius: 999px !important;
        background: #e5e7eb !important;
        overflow: hidden !important;
        margin-bottom: 0.5rem !important;
    }

    .attendance-progress-bar {
        height: 100% !important;
        background: linear-gradient(90deg, #22c55e, #16a34a) !important;
        transition: width 0.3s ease !important;
    }

    /* Top Attendance Items */
    .top-attendance-item {
        display: flex !important;
        align-items: center !important;
        gap: 1rem !important;
        padding: 0.75rem 0 !important;
        border-bottom: 1px solid #e5e7eb !important;
    }

    .top-attendance-item:last-child {
        border-bottom: none !important;
    }

    .top-attendance-rank {
        font-weight: 700 !important;
        font-size: 1.25rem !important;
        color: #667eea !important;
        min-width: 2rem !important;
    }

    .top-attendance-name {
        flex: 1 !important;
        font-weight: 600 !important;
        font-size: 0.95rem !important;
    }

    .top-attendance-rate {
        font-weight: 700 !important;
        color: #22c55e !important;
        font-size: 0.95rem !important;
    }

    /* Chart Wrapper */
    .chart-wrapper {
        position: relative !important;
        height: 260px !important;
        width: 100% !important;
    }

    /* Responsive Design */
    @media (max-width: 992px) {
        .attendance-stat-value {
            font-size: 2rem !important;
        }
    }

    @media (max-width: 768px) {
        .attendance-stat-value {
            font-size: 1.75rem !important;
        }

        .attendance-dot {
            width: 16px !important;
            height: 16px !important;
            font-size: 0.6rem !important;
        }

        .attendance-stat-card {
            min-height: 120px !important;
            padding: 1.25rem !important;
        }
    }

    @media (max-width: 576px) {
        .attendance-stat-value {
            font-size: 1.5rem !important;
        }

        .attendance-stat-label {
            font-size: 0.75rem !important;
        }

        .attendance-stat-subtext {
            font-size: 0.7rem !important;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4 px-3 px-md-4">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <h1 class="h3 mb-0">System Control Panel</h1>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="badge bg-danger">Super Admin</span>
            <a href="<?= base_url('/x9k2m8p5q7/logout') ?>" class="btn btn-sm btn-outline-danger">Logout</a>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-2 mb-4">
        <div class="col-auto">
            <a href="<?= base_url('/x9k2m8p5q7/create-user') ?>" class="btn btn-sm btn-success">+ Create User</a>
        </div>
        <div class="col-auto">
            <a href="<?= base_url('/x9k2m8p5q7/create-time-entry') ?>" class="btn btn-sm btn-info">+ Create Time Entry</a>
        </div>
        <div class="col-auto">
            <a href="<?= base_url('/x9k2m8p5q7/create-check-in') ?>" class="btn btn-sm btn-warning">+ Create Check-in</a>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Advanced Attendance Analytics Section -->
    <?php if (!empty($attendanceStats)): ?>
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-3">
                <h5 class="mb-0">
                    <i class="bi bi-calendar-check"></i> Attendance Analytics - <?= date('F Y', strtotime($selectedMonth . '-01')) ?>
                </h5>
                
                <!-- Month Filter -->
                <form method="GET" action="<?= base_url('/x9k2m8p5q7/dashboard') ?>" class="d-flex gap-2 align-items-center">
                    <label for="attendanceMonth" class="form-label mb-0 text-nowrap">Select Month:</label>
                    <select class="form-select form-select-sm" id="attendanceMonth" name="month" style="max-width: 200px;" onchange="this.form.submit();">
                        <?php foreach ($monthOptions as $option): ?>
                        <option value="<?= $option['value'] ?>" <?= $option['value'] === $selectedMonth ? 'selected' : '' ?>>
                            <?= esc($option['label']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <a href="<?= base_url('/x9k2m8p5q7/dashboard') ?>" class="btn btn-sm btn-outline-secondary" title="Reset to current month">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </form>
            </div>
        </div>

        <!-- Attendance Stats Cards -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="attendance-stat-card">
                <div class="attendance-stat-label">Active Users</div>
                <div class="attendance-stat-value"><?= $attendanceStats['stats']['total_users'] ?></div>
                <div class="attendance-stat-subtext">Team members tracked</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="attendance-stat-card success">
                <div class="attendance-stat-label">Attendance Rate</div>
                <div class="attendance-stat-value"><?= $attendanceStats['stats']['average_rate'] ?>%</div>
                <div class="attendance-stat-subtext">Average presence this month</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="attendance-stat-card warning">
                <div class="attendance-stat-label">Total Check-ins</div>
                <div class="attendance-stat-value"><?= $attendanceStats['stats']['total_present'] ?></div>
                <div class="attendance-stat-subtext">Out of <?= $attendanceStats['stats']['total_working_days'] ?> days</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="attendance-stat-card info">
                <div class="attendance-stat-label">Absences</div>
                <div class="attendance-stat-value"><?= $attendanceStats['stats']['total_absent'] ?></div>
                <div class="attendance-stat-subtext">Across all users</div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-graph-up"></i> Daily Check-in Trend</h6>
                </div>
                <div class="card-body">
                    <div class="chart-wrapper">
                        <canvas id="attendanceTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-3">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-emoji-smile"></i> Mood Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-wrapper">
                        <canvas id="attendanceMoodChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-3">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-trophy"></i> Top Attendance</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($attendanceStats['top_attendance'])): ?>
                        <?php foreach ($attendanceStats['top_attendance'] as $index => $row): ?>
                        <div class="top-attendance-item">
                            <div class="top-attendance-rank">#<?= $index + 1 ?></div>
                            <div class="top-attendance-name"><?= esc($row['user']['username']) ?></div>
                            <div class="top-attendance-rate"><?= $row['attendance_rate'] ?>%</div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center mb-0">No attendance data yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Monthly Attendance Grid -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-calendar-range"></i> Monthly Attendance Grid</h6>
                    <small class="text-muted d-block mt-2">
                        <span class="attendance-dot present" style="display: inline-block;"></span> Present
                        <span class="attendance-dot absent" style="display: inline-block; margin-left: 1rem;"></span> Absent
                        <span class="attendance-dot weekend" style="display: inline-block; margin-left: 1rem;"></span> Weekend
                    </small>
                </div>
                <div class="card-body">
                    <?php if (!empty($attendanceStats['attendance_rows'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-nowrap">User</th>
                                    <th class="text-center text-nowrap">Present</th>
                                    <th class="text-center text-nowrap">Absent</th>
                                    <th class="text-center text-nowrap">Rate</th>
                                    <th style="min-width: 300px;">Activity Map</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendanceStats['attendance_rows'] as $row): ?>
                                <tr>
                                    <td class="text-nowrap">
                                        <strong><?= esc($row['user']['username']) ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success"><?= $row['present_days'] ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary"><?= $row['absent_days'] ?></span>
                                    </td>
                                    <td class="text-center">
                                        <div>
                                            <div class="attendance-progress">
                                                <div class="attendance-progress-bar" style="width: <?= $row['attendance_rate'] ?>%"></div>
                                            </div>
                                            <small class="fw-bold"><?= $row['attendance_rate'] ?>%</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="attendance-map">
                                            <?php foreach ($attendanceStats['date_range'] as $date): ?>
                                                <?php
                                                    $isWeekend = in_array(date('N', strtotime($date)), ['6','7'], true);
                                                    $present = isset($attendanceStats['user_checkins'][$row['user']['id']][$date]);
                                                    $classes = $present ? 'present' : ($isWeekend ? 'weekend' : 'absent');
                                                ?>
                                                <span class="attendance-dot <?= $classes ?>" title="<?= date('M d', strtotime($date)) ?>">
                                                    <?= date('j', strtotime($date)) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p class="text-muted text-center py-4">No attendance data for this month</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row g-3">
        <!-- Time Entries Section -->
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Recent Time Entries</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($timeEntries)): ?>
                    <p class="text-muted">No time entries found.</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-nowrap">User</th>
                                    <th class="text-nowrap">Hours</th>
                                    <th class="d-none d-md-table-cell">Description</th>
                                    <th class="text-nowrap">Date</th>
                                    <th class="text-nowrap">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($timeEntries as $entry): ?>
                                <tr>
                                    <td class="text-nowrap"><?= esc($entry['username']) ?></td>
                                    <td class="text-nowrap"><?= $entry['hours'] ?></td>
                                    <td class="d-none d-md-table-cell text-truncate" title="<?= esc($entry['description']) ?>">
                                        <?= esc(substr($entry['description'], 0, 30)) ?>...
                                    </td>
                                    <td class="text-nowrap"><?= $entry['date'] ?></td>
                                    <td class="text-nowrap">
                                        <a href="<?= base_url('/x9k2m8p5q7/edit-time-entry/' . $entry['id']) ?>" 
                                           class="btn btn-sm btn-outline-primary">Edit</a>
                                        <form method="post" action="<?= base_url('/x9k2m8p5q7/delete-time-entry/' . $entry['id']) ?>" 
                                              style="display:inline;" onsubmit="return confirm('Delete this time entry?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Check-ins Section -->
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Recent Check-ins</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($checkIns)): ?>
                    <p class="text-muted">No check-ins found.</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-nowrap">User</th>
                                    <th class="text-nowrap">Date</th>
                                    <th class="text-nowrap">Mood</th>
                                    <th class="text-nowrap">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($checkIns as $checkin): ?>
                                <tr>
                                    <td class="text-nowrap"><?= esc($checkin['username']) ?></td>
                                    <td class="text-nowrap"><?= $checkin['check_in_date'] ?></td>
                                    <td class="text-nowrap">
                                        <?php if ($checkin['mood']): ?>
                                            <span class="badge bg-info"><?= esc($checkin['mood']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-nowrap">
                                        <a href="<?= base_url('/x9k2m8p5q7/edit-check-in/' . $checkin['id']) ?>" 
                                           class="btn btn-sm btn-outline-primary">Edit</a>
                                        <form method="post" action="<?= base_url('/x9k2m8p5q7/delete-check-in/' . $checkin['id']) ?>" 
                                              style="display:inline;" onsubmit="return confirm('Delete this check-in?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Section -->
    <div class="row g-3 mt-2">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">System Users</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($users)): ?>
                    <p class="text-muted">No users found.</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-nowrap">Username</th>
                                    <th class="d-none d-sm-table-cell">Email</th>
                                    <th class="text-nowrap">Status</th>
                                    <th class="d-none d-md-table-cell text-nowrap">Created</th>
                                    <th class="text-nowrap">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td class="text-nowrap"><strong><?= esc($user['username']) ?></strong></td>
                                    <td class="d-none d-sm-table-cell text-truncate" title="<?= esc($user['email'] ?? '-') ?>">
                                        <?= esc($user['email'] ?? '-') ?>
                                    </td>
                                    <td class="text-nowrap">
                                        <?php if ($user['active']): ?>
                                        <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="d-none d-md-table-cell text-nowrap"><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                    <td class="text-nowrap">
                                        <a href="<?= base_url('/x9k2m8p5q7/edit-user/' . $user['id']) ?>" 
                                           class="btn btn-sm btn-outline-primary">Edit</a>
                                        <form method="post" action="<?= base_url('/x9k2m8p5q7/delete-user/' . $user['id']) ?>" 
                                              style="display:inline;" onsubmit="return confirm('Delete this user? This action cannot be undone.');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    window.attendanceCharts = window.attendanceCharts || {};

    const mountChart = (id, config) => {
        const ctx = document.getElementById(id);
        if (!ctx) {
            return;
        }

        if (window.attendanceCharts[id]) {
            window.attendanceCharts[id].destroy();
        }

        window.attendanceCharts[id] = new Chart(ctx, config);
    };

    // Daily Trend Chart
    const trendLabels = <?= json_encode(array_map(fn($date) => date('M j', strtotime($date)), array_keys($attendanceStats['daily_trend'] ?? [])), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const trendValues = <?= json_encode(array_values($attendanceStats['daily_trend'] ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

    if (trendLabels.length > 0) {
        mountChart('attendanceTrendChart', {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Check-Ins',
                    data: trendValues,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    }

    // Mood Distribution Chart
    const moodLabels = <?= json_encode(array_keys($attendanceStats['mood_summary'] ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const moodValues = <?= json_encode(array_values($attendanceStats['mood_summary'] ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const moodColors = ['#22c55e', '#0ea5e9', '#94a3b8', '#f97316', '#ef4444'];

    if (moodLabels.length > 0) {
        mountChart('attendanceMoodChart', {
            type: 'doughnut',
            data: {
                labels: moodLabels.map(m => m.charAt(0).toUpperCase() + m.slice(1)),
                datasets: [{
                    data: moodValues,
                    backgroundColor: moodColors,
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: { size: 12 }
                        }
                    }
                }
            }
        });
    }
</script>
<?= $this->endSection() ?>

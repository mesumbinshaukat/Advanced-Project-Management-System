<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
    .filter-card .form-select,
    .filter-card .form-control {
        border-radius: 0.65rem;
    }

    .attendance-map {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(18px, 1fr));
        gap: 4px;
        max-width: 420px;
        overflow-x: auto;
    }

    .attendance-dot {
        width: 18px;
        height: 18px;
        border-radius: 4px;
        font-size: 0.65rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 600;
    }

    .attendance-dot.present {
        background: #22c55e;
    }

    .attendance-dot.absent {
        background: #e2e8f0;
        color: #475569;
    }

    .attendance-dot.weekend {
        background: #c084fc;
    }

    .stat-chip {
        border-radius: 999px;
        padding: 0.35rem 0.9rem;
        font-size: 0.85rem;
        background: #e2e8f0;
        color: #334155;
        font-weight: 600;
    }

    @media (max-width: 992px) {
        .attendance-map-wrapper {
            overflow-x: auto;
        }
    }

    .chart-wrapper {
        position: relative;
        height: 260px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row g-4">
    <div class="col-12">
        <div class="card filter-card">
            <div class="card-body">
                <form class="row g-3 align-items-end" method="GET" action="<?= base_url('attendance') ?>">
                    <div class="col-md-4">
                        <label for="month" class="form-label">Month</label>
                        <select class="form-select" id="month" name="month">
                            <?php foreach ($monthOptions as $option): ?>
                                <option value="<?= $option['value'] ?>" <?= $option['value'] === $selectedMonth ? 'selected' : '' ?>>
                                    <?= esc($option['label']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="user" class="form-label">Specific User</label>
                        <select class="form-select" id="user" name="user">
                            <option value="">All Team Members</option>
                            <?php foreach ($userFilterOptions as $user): ?>
                                <option value="<?= $user['id'] ?>" <?= (string)$user['id'] === ($selectedUserId ?? '') ? 'selected' : '' ?>>
                                    <?= esc($user['username']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button class="btn btn-primary flex-grow-1" type="submit">
                            <i class="bi bi-funnel"></i> Apply Filters
                        </button>
                        <a href="<?= base_url('attendance') ?>" class="btn btn-outline-secondary" title="Reset filters">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="stat-label text-uppercase">Active Team Members</div>
            <div class="stat-value"><?= $stats['total_users'] ?></div>
            <small>Total tracked in this view</small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card success">
            <div class="stat-label text-uppercase">Attendance Rate</div>
            <div class="stat-value"><?= $stats['average_rate'] ?>%</div>
            <small>Average presence this month</small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card warning">
            <div class="stat-label text-uppercase">Total Absences</div>
            <div class="stat-value"><?= $stats['total_absent'] ?></div>
            <small>Across tracked working days</small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card danger">
            <div class="stat-label text-uppercase">Best Streak</div>
            <div class="stat-value"><?= $stats['best_streak'] ?> days</div>
            <small>Longest current check-in streak</small>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-graph-up"></i> Daily Check-In Trend</span>
                <span class="stat-chip">Total Days: <?= $totalWorkingDays ?></span>
            </div>
            <div class="card-body">
                <div class="chart-wrapper">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-emoji-smile"></i> Mood Distribution</div>
            <div class="card-body">
                <div class="chart-wrapper">
                    <canvas id="moodChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-trophy"></i> Top Attendance</div>
            <div class="card-body">
                <?php if (!empty($topAttendanceChart['labels'])): ?>
                    <div class="chart-wrapper">
                        <canvas id="topChart"></canvas>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center mb-0">Not enough attendance data for ranking yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-people"></i> Monthly Attendance Grid</span>
        <span class="text-muted small">Green = Present · Gray = Absent · Purple = Weekend</span>
    </div>
    <div class="card-body table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Present</th>
                    <th>Absent</th>
                    <th>Attendance</th>
                    <th>Streak</th>
                    <th>Last Check-In</th>
                    <th style="min-width: 300px;">Activity Map</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($attendanceRows)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No attendance data for the selected filters</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($attendanceRows as $row): ?>
                        <?php $user = $row['user']; ?>
                        <tr>
                            <td>
                                <strong><?= esc($user['username']) ?></strong><br>
                                <small class="text-muted"><?= esc($user['email'] ?? 'no-email') ?></small>
                            </td>
                            <td><span class="badge bg-success"><?= $row['present_days'] ?></span></td>
                            <td><span class="badge bg-secondary"><?= $row['absent_days'] ?></span></td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold"><?= $row['attendance_rate'] ?>%</span>
                                    <small class="text-muted">of <?= $totalWorkingDays ?> days</small>
                                </div>
                            </td>
                            <td><span class="badge bg-primary"><?= $row['streak'] ?> days</span></td>
                            <td><?= $row['last_check_in'] ? date('M d, Y', strtotime($row['last_check_in'])) : '<span class="text-muted">N/A</span>' ?></td>
                            <td>
                                <div class="attendance-map-wrapper">
                                    <div class="attendance-map">
                                        <?php foreach ($dateRange as $date): ?>
                                            <?php
                                                $isWeekend = in_array(date('N', strtotime($date)), ['6','7'], true);
                                                $present = isset($userDailyMap[$user['id']][$date]);
                                                $classes = $present ? 'present' : ($isWeekend ? 'weekend' : 'absent');
                                            ?>
                                            <span class="attendance-dot <?= $classes ?>" title="<?= date('M d', strtotime($date)) ?>">
                                                <?= date('j', strtotime($date)) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
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

    const trendLabels = <?= json_encode($chartTrend['labels'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const trendValues = <?= json_encode($chartTrend['values'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

    mountChart('trendChart', {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [{
                label: 'Check-Ins',
                data: trendValues,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, .15)',
                fill: true,
                tension: 0.35,
                pointRadius: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            }
        }
    });

    const moodLabels = <?= json_encode(array_keys($moodSummary), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const moodValues = <?= json_encode(array_values($moodSummary), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const moodColors = ['#22c55e', '#0ea5e9', '#94a3b8', '#f97316', '#ef4444'];

    mountChart('moodChart', {
        type: 'doughnut',
        data: {
            labels: moodLabels,
            datasets: [{
                data: moodValues,
                backgroundColor: moodColors,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    const topLabels = <?= json_encode($topAttendanceChart['labels'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const topValues = <?= json_encode($topAttendanceChart['values'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

    if (topLabels.length > 0) {
        mountChart('topChart', {
            type: 'bar',
            data: {
                labels: topLabels,
                datasets: [{
                    label: 'Attendance %',
                    data: topValues,
                    borderRadius: 8,
                    backgroundColor: '#10b981'
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true,
                        max: 100,
                        ticks: { callback: (value) => value + '%' }
                    }
                }
            }
        });
    }
</script>
<?= $this->endSection() ?>

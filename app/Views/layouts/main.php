<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Project Management System') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --sidebar-width: 260px;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f8fafc;
            min-height: 100vh;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: #1e293b;
            color: #fff;
            padding: 1.5rem 0;
            overflow-y: auto;
            z-index: 1000;
            transition: transform 0.3s ease;
        }
        
        .sidebar-brand {
            padding: 0 1.5rem 1.5rem;
            font-size: 1.25rem;
            font-weight: 700;
            color: #fff;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 1rem;
        }
        
        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-nav-item {
            margin: 0.25rem 0.75rem;
        }
        
        .sidebar-nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: #cbd5e1;
            text-decoration: none;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }
        
        .sidebar-nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        
        .sidebar-nav-link.active {
            background: var(--primary-color);
            color: #fff;
        }
        
        .sidebar-nav-link i {
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
        }
        
        .topbar {
            background: #fff;
            padding: 1rem 2rem;
            margin: -2rem -2rem 2rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card {
            border: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 1.25rem 1.5rem;
            font-weight: 600;
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%);
            color: #fff;
            padding: 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
        }
        
        .stat-card.success {
            background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
        }
        
        .stat-card.warning {
            background: linear-gradient(135deg, var(--warning-color) 0%, #d97706 100%);
        }
        
        .stat-card.danger {
            background: linear-gradient(135deg, var(--danger-color) 0%, #dc2626 100%);
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .stat-label {
            opacity: 0.9;
            font-size: 0.875rem;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background: #1d4ed8;
            border-color: #1d4ed8;
        }
        
        .badge {
            padding: 0.35rem 0.75rem;
            border-radius: 0.5rem;
            font-weight: 500;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            border-bottom: 2px solid #e2e8f0;
            font-weight: 600;
            color: #475569;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
    <?= $this->renderSection('styles') ?>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-kanban"></i> PM System
        </div>
        <ul class="sidebar-nav">
            <li class="sidebar-nav-item">
                <a href="<?= base_url('dashboard') ?>" class="sidebar-nav-link <?= url_is('dashboard') ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="<?= base_url('projects') ?>" class="sidebar-nav-link <?= url_is('projects*') ? 'active' : '' ?>">
                    <i class="bi bi-folder"></i> Projects
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="<?= base_url('tasks') ?>" class="sidebar-nav-link <?= url_is('tasks*') ? 'active' : '' ?>">
                    <i class="bi bi-check2-square"></i> Tasks
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="<?= base_url('time/tracker') ?>" class="sidebar-nav-link <?= url_is('time*') ? 'active' : '' ?>">
                    <i class="bi bi-stopwatch"></i> Time Tracker
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="<?= base_url('check-in') ?>" class="sidebar-nav-link <?= url_is('check-in*') ? 'active' : '' ?>">
                    <i class="bi bi-calendar-check"></i> Daily Check-In
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="<?= base_url('notes') ?>" class="sidebar-nav-link <?= url_is('notes*') ? 'active' : '' ?>">
                    <i class="bi bi-journal-text"></i> Notes
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="<?= base_url('alerts') ?>" class="sidebar-nav-link <?= url_is('alerts*') ? 'active' : '' ?>">
                    <i class="bi bi-bell"></i> Alerts
                </a>
            </li>
            
            <?php if (auth()->user()->inGroup('admin')): ?>
            <!-- Admin Only Features -->
            <li class="sidebar-nav-item mt-3">
                <div class="px-3 py-2 text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                    Admin Tools
                </div>
            </li>
            <li class="sidebar-nav-item">
                <a href="<?= base_url('clients') ?>" class="sidebar-nav-link <?= url_is('clients*') ? 'active' : '' ?>">
                    <i class="bi bi-people"></i> Clients
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="<?= base_url('attendance') ?>" class="sidebar-nav-link <?= url_is('attendance*') ? 'active' : '' ?>">
                    <i class="bi bi-calendar4-week"></i> Attendance
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="<?= base_url('developers') ?>" class="sidebar-nav-link <?= url_is('developers*') ? 'active' : '' ?>">
                    <i class="bi bi-person-badge"></i> Developers
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="<?= base_url('performance') ?>" class="sidebar-nav-link <?= url_is('performance*') ? 'active' : '' ?>">
                    <i class="bi bi-graph-up"></i> Performance
                </a>
            </li>
            <!--
            <li class="sidebar-nav-item">
                <a href="<?= base_url('profitability') ?>" class="sidebar-nav-link <?= url_is('profitability*') ? 'active' : '' ?>">
                    <i class="bi bi-currency-dollar"></i> Profitability
                </a>
            </li>
            -->
            <li class="sidebar-nav-item">
                <a href="<?= base_url('capacity') ?>" class="sidebar-nav-link <?= url_is('capacity*') ? 'active' : '' ?>">
                    <i class="bi bi-bar-chart"></i> Capacity
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="<?= base_url('templates') ?>" class="sidebar-nav-link <?= url_is('templates*') ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-text"></i> Templates
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="<?= base_url('users') ?>" class="sidebar-nav-link <?= url_is('users*') ? 'active' : '' ?>">
                    <i class="bi bi-people"></i> Users
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="main-content">
        <div class="topbar">
            <div>
                <h4 class="mb-0"><?= esc($title ?? 'Dashboard') ?></h4>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted"><?= esc(auth()->user()->username) ?></span>
                <span class="badge bg-primary"><?= auth()->user()->inGroup('admin') ? 'Admin' : 'Developer' ?></span>
                <a href="<?= base_url('logout') ?>" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>

        <?php if (session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if (session()->has('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?= $this->renderSection('content') ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>

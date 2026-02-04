# Quick Start Guide

Complete step-by-step instructions for local development setup.

## Prerequisites

- **PHP 8.1+** with extensions: intl, mbstring, json, mysqlnd
- **MySQL 5.7+** or MariaDB 10.3+
- **Composer** (latest version)
- **XAMPP/WAMP** (recommended for Windows) or **MAMP** (for macOS)
- **Git** (optional, for version control)

## Setup Steps

### 1. Install CodeIgniter 4.7.0

Navigate to your project directory and run:
```bash
composer create-project codeigniter4/appstarter:4.7.0 .
```

Wait for all dependencies to install.

### 2. Install CodeIgniter Shield

Install the authentication library:
```bash
composer require codeigniter4/shield:^1.0
```

Run Shield setup:
```bash
php spark shield:setup
```

This publishes Shield configuration files and migrations.

### 3. Configure Environment

Copy the example environment file:
```bash
cp env.example .env
```

**Windows users:**
```bash
copy env.example .env
```

Edit `.env` and configure the following:

```ini
# Environment
CI_ENVIRONMENT = development

# Base URL
app.baseURL = 'http://localhost:8080'

# Database
database.default.hostname = localhost
database.default.database = project_management_db
database.default.username = root
database.default.password = 
database.default.DBDriver = MySQLi
database.default.DBPrefix = 
database.default.port = 3306

# Encryption (generate a key)
encryption.key = 
```

**Generate encryption key:**
```bash
php spark key:generate
```

### 4. Create Database

**Option A: Using MySQL Command Line**
```bash
mysql -u root -e "CREATE DATABASE project_management_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

**Option B: Using phpMyAdmin**
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Click "New" in the left sidebar
3. Database name: `project_management_db`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"

**Option C: Using MySQL Workbench**
1. Open MySQL Workbench
2. Connect to your local server
3. Execute: `CREATE DATABASE project_management_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`

### 5. Run All Migrations

Run migrations to create all database tables:
```bash
php spark migrate --all
```

This creates **22 tables** including:
- **Shield tables**: users, auth_identities, auth_groups_users, etc.
- **Milestone 1**: clients, projects, tasks, time_entries, activity_logs, financials, performance_metrics, project_users
- **Milestone 2**: notes, messages, enhanced projects/tasks
- **Milestone 3**: daily_check_ins, alerts, project_templates, task_templates, enhanced users

### 6. Seed Roles and Permissions

Create admin and developer roles with permissions:
```bash
php spark db:seed RolesPermissionsSeeder
```

This configures:
- **Admin role**: Full system access
- **Developer role**: Restricted access (assigned projects/tasks only, no financials)

### 7. Create Admin User

**Option A: Interactive Creation**
```bash
php spark shield:user create
```

Follow the prompts:
- Username: `admin`
- Email: `admin@example.com`
- Password: `admin123` (or your choice)

Add user to admin group:
```bash
php spark shield:group add admin admin
```

**Option B: Using Seeder (if available)**
```bash
php spark db:seed AdminUserSeeder
```

Default credentials:
- Email: `admin@example.com`
- Password: `admin123`

### 8. Start Development Server

Start the built-in PHP development server:
```bash
php spark serve
```

The application will be available at: **http://localhost:8080**

**Alternative port:**
```bash
php spark serve --port=8081
```

### 9. Access Application

1. Open browser: http://localhost:8080
2. Click **"Login"** in the navigation
3. Enter credentials:
   - Email: `admin@example.com`
   - Password: `admin123` (or your password)
4. You'll be redirected to the **Executive Dashboard**

## Post-Setup: Initial Data

### Create Your First Client
1. Navigate to **Clients** → **Create New Client**
2. Fill in client details
3. Click **Save**

### Create Your First Project
1. Navigate to **Projects** → **Create New Project**
2. Select the client
3. Set budget, deadline, priority
4. Click **Save**

### Add Tasks
1. Click on the project
2. Click **Add Task**
3. Fill in task details
4. Assign to yourself or another developer
5. Click **Save**

### View Kanban Board
1. Navigate to **Tasks** → **Kanban Board**
2. Select a project
3. Drag and drop tasks between columns
4. Status updates automatically via AJAX

### Log Time
1. Navigate to **Time Tracking** → **Tracker**
2. Select a task
3. Start the live timer OR enter manual time
4. Click **Stop & Save** or **Save Entry**

### Daily Check-In
1. Navigate to **Check-In**
2. Select your mood
3. Enter yesterday's accomplishments
4. Enter today's plan
5. Note any blockers
6. Click **Submit Check-In**

## Admin-Only Features

### Generate Alerts
1. Navigate to **Alerts**
2. Click **Generate Alerts**
3. System scans for deadline risks, inactivity, overload, budget risks, blockers

### Update Performance Scores
1. Navigate to **Performance**
2. Click **Update All Scores**
3. System calculates scores for all developers

### View Profitability
1. Navigate to **Profitability**
2. View overall metrics, trends, and top projects

### Check Capacity
1. Navigate to **Capacity**
2. View utilization, capacity gap, and hiring recommendations

### Create Templates
1. Navigate to **Templates**
2. Click **New Project Template** or **New Task Template**
3. Configure template settings
4. Save for reuse

## Troubleshooting

### Database Connection Issues
**Error:** "Unable to connect to database"

**Solutions:**
- Verify MySQL/MariaDB is running (check XAMPP/WAMP control panel)
- Check database credentials in `.env`
- Ensure database `project_management_db` exists
- Test connection: `mysql -u root -p` then `SHOW DATABASES;`

### Migration Errors
**Error:** "Migration failed"

**Solutions:**
- Check database user has CREATE/ALTER/DROP permissions
- Verify all migration files exist in `app/Database/Migrations`
- Try rollback: `php spark migrate:rollback` then `php spark migrate --all`
- Check for syntax errors in migration files

### Permission Errors
**Error:** "Unable to write to writable directory"

**Solutions:**
- Ensure `writable` directory exists and is writable
- **Windows:** Right-click → Properties → Security → Edit permissions
- **Linux/Mac:** `chmod -R 755 writable/`
- **Linux/Mac:** `chown -R www-data:www-data writable/` (if using Apache)
- Explore the Kanban board for task management
- Set up time tracking for your tasks
- Configure project financials (admin only)
- Review activity logs for accountability
- Customize the system to your workflow

## Default Routes

- Dashboard: `/dashboard`
- Projects: `/projects`
- Tasks: `/tasks`
- Clients: `/clients` (admin only)
- Time Tracking: `/time`
- API Base: `/api/`

## API Testing

You can test API endpoints using tools like Postman or curl:

```bash
# Get all projects (requires authentication)
curl -X GET http://localhost:8080/api/projects

# Create a new task
curl -X POST http://localhost:8080/api/tasks \
  -H "Content-Type: application/json" \
  -d '{"project_id":1,"title":"New Task","status":"todo"}'
```

## Support

For issues or questions, refer to the main README.md or contact the development team.

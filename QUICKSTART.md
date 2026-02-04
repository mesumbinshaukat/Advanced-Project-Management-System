# Quick Start Guide

Follow these steps to get the Advanced Project Management System running on your local machine.

## Prerequisites

Ensure you have the following installed:
- PHP 8.1+ with required extensions (intl, mbstring, json, mysqlnd)
- MySQL 5.7+
- Composer
- Git (optional)

## Installation Steps

### 1. Create CodeIgniter 4.7.0 Project

```bash
composer create-project codeigniter4/appstarter:4.7.0 .
```

### 2. Install CodeIgniter Shield

```bash
composer require codeigniter4/shield:^1.0
```

### 3. Setup Shield

```bash
php spark shield:setup
```

This will publish Shield's configuration files and migrations.

### 4. Configure Database

Create a MySQL database:

```sql
CREATE DATABASE project_management_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Copy the environment file and configure database settings:

```bash
cp env.example .env
```

Edit `.env` and update the database settings:

```ini
database.default.hostname = localhost
database.default.database = project_management_db
database.default.username = root
database.default.password = your_password
database.default.DBDriver = MySQLi
database.default.port = 3306
```

### 5. Run Migrations

Run all migrations to create the database tables:

```bash
php spark migrate --all
```

### 6. Seed Roles and Permissions

```bash
php spark db:seed RolesPermissionsSeeder
```

### 7. Create Admin User

Create your first admin user:

```bash
php spark shield:user create
```

Follow the prompts to enter:
- Email address
- Username
- Password

Then assign the admin group:

```bash
php spark shield:group add [username] admin
```

Replace `[username]` with the username you just created.

### 8. Start Development Server

```bash
php spark serve
```

The application will be available at: **http://localhost:8080**

## First Login

1. Navigate to http://localhost:8080
2. Click on "Login" or go to http://localhost:8080/login
3. Enter your admin credentials
4. You'll be redirected to the dashboard

## Creating Your First Project

1. From the dashboard, click "Projects" in the sidebar
2. Click "New Project" button
3. First, create a client by going to "Clients" → "New Client"
4. Return to Projects and create a new project
5. Assign team members (if you have developer users)
6. Create tasks from the project view or Kanban board

## Creating Developer Users

To create a developer user:

```bash
php spark shield:user create
```

Then assign the developer group:

```bash
php spark shield:group add [username] developer
```

## Troubleshooting

### Database Connection Issues
- Verify MySQL is running
- Check database credentials in `.env`
- Ensure the database exists

### Permission Errors
- Check file permissions: `writable/` folder should be writable
- On Linux/Mac: `chmod -R 777 writable/`

### Migration Errors
- Ensure all migrations ran successfully
- Check `php spark migrate:status`
- If needed, rollback and re-run: `php spark migrate:rollback` then `php spark migrate --all`

### Shield Not Working
- Verify Shield is installed: `composer show codeigniter4/shield`
- Check that `php spark shield:setup` was run
- Verify Shield migrations ran: `php spark migrate:status`

## Next Steps

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

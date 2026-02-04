# Advanced Project Management System

A comprehensive, API-first project management system built with CodeIgniter 4.7.0 featuring advanced intelligence, real-time collaboration, and data-driven insights. Designed for immediate visibility and accountability by design.

## Overview

This system provides complete project lifecycle management with intelligent automation, performance analytics, and capacity forecasting. Built for teams that need fast, distraction-free workflows with powerful insights.

## Key Features

### Core Project Management
- **Project Workspace**: Complete project management with client details, budget, timeline, documentation, and repository links
- **Interactive Kanban Board**: Drag-and-drop task management with SortableJS (Backlog → Todo → In Progress → Review → Done)
- **Task Management**: Full lifecycle tracking with priorities, deadlines, assignments, blockers, and tags
- **Client Management**: Comprehensive client information and project associations
- **Time Tracking**: Live JavaScript timers plus manual entries with billable/non-billable tracking
- **Notes & Decision Log**: Per-project and per-task notes with types (note/decision/blocker/update) and pin functionality
- **Integrated Messaging**: Threaded project messaging with replies and unread tracking

### Advanced Intelligence Features (Milestone 3)

#### Developer Performance Scoring
- **Automated Calculation**: Multi-factor performance scores updated automatically
- **Deadline Score (40%)**: On-time completion rate over 30 days
- **Speed Score (30%)**: Efficiency based on estimated vs actual hours
- **Engagement Score (30%)**: Daily check-ins, activity logs, and time entries
- **Performance Trends**: 6-month historical tracking per developer
- **Visual Dashboard**: Color-coded scores with detailed breakdowns

#### Daily Check-In Workflow
- **Simple Interface**: Mood selection, accomplishments, today's plan, blockers
- **Streak Tracking**: Gamified daily engagement with streak counter
- **Team Visibility**: Admin view of all team check-ins by date
- **Help Flagging**: Developers can flag when they need assistance
- **Activity Integration**: Check-ins update last_activity timestamps

#### Automated Alerts System
- **Deadline Risk Alerts**: Tasks due within 3 days (critical/high severity)
- **Inactivity Detection**: Developers inactive 3+ days or missing check-ins
- **Overload Warnings**: Developers with 10+ active tasks
- **Budget Risk Alerts**: Projects using 80%+ of budget
- **Blocker Notifications**: Immediate alerts for blocked tasks
- **Smart Grouping**: Alerts grouped by severity (critical/high/medium/low)
- **One-Click Resolution**: Resolve alerts directly from dashboard

#### Reusable Templates
- **Project Templates**: Pre-configured projects with task lists, budgets, timelines
- **Task Templates**: Reusable task definitions with checklists and estimates
- **Quick Application**: One-click project creation from templates
- **JSON Storage**: Flexible template structure for complex workflows

#### Profitability Dashboard
- **Real-Time Metrics**: Total revenue, cost, profit, and margin calculations
- **Project-Level Analysis**: Individual project profitability with billing types
- **6-Month Trends**: Historical profitability tracking
- **Hourly vs Fixed**: Support for hourly, fixed-price, and retainer billing
- **Top Performers**: Ranked list of most profitable projects
- **Cost Calculation**: Automatic cost estimation based on logged hours

#### Capacity Forecasting
- **Team Size Analysis**: Current developer count and available hours/week
- **Utilization Tracking**: Real-time capacity utilization percentage
- **Demand Calculation**: Total estimated hours from active tasks
- **Capacity Gap**: Visual comparison of supply vs demand
- **Hiring Recommendations**: Data-driven suggestions for team expansion
- **Urgency Levels**: High/medium/low urgency based on utilization and gap
- **Project Allocation**: Breakdown of capacity needs per project

### Activity Monitoring (Optional Note)
The system includes comprehensive activity tracking (last_activity timestamps, daily check-ins, time entries, task updates). This provides transparency and accountability while respecting company culture. All monitoring is designed for workflow improvement, not surveillance.

### Role-Based Access Control

#### Admin Role
- Full system access with all capabilities
- Create, edit, archive, and delete projects
- Assign developers and manage workloads
- Access financial metrics and profitability dashboards
- View all time tracking data
- Modify priorities, deadlines, and statuses
- Access system-wide analytics
- Configure templates and automations

#### Developer Role
- View only assigned projects and tasks
- Update task statuses
- Log time entries
- Submit daily check-ins
- Upload files and comment
- Report blockers
- **Restrictions**: No access to financial data, no visibility into unrelated projects, no permission changes

## Technology Stack

- **Framework**: CodeIgniter 4.7.0
- **Authentication**: CodeIgniter Shield
- **Database**: MySQL
- **Frontend**: Bootstrap 5, Vanilla JavaScript, SortableJS
- **Architecture**: API-first with RESTful endpoints

## System Requirements

- PHP 8.1 or higher
- MySQL 5.7 or higher
- Composer
- Apache/Nginx web server (for production)

## Database Schema

### Core Tables (Milestone 1)
- `clients` - Client information and contact details
- `projects` - Project details linked to clients with health status
- `tasks` - Tasks with status, priority, deadlines, ownership, and blockers
- `users` - User accounts with performance scores (managed by Shield)
- `time_entries` - Time logs linked to tasks and users
- `activity_logs` - Comprehensive audit trail for all major actions
- `financials` - Project pricing and profitability data
- `performance_metrics` - User and project performance tracking
- `project_users` - Project team assignments (junction table)

### Milestone 2 Tables
- `notes` - Notes and decision log per project/task
- `messages` - Threaded project messaging system
- Enhanced `projects` - Added documentation, repository_url, staging_url, production_url, health_status
- Enhanced `tasks` - Added is_blocked, blocker_reason, tags

### Milestone 3 Tables
- `daily_check_ins` - Developer daily check-ins with mood and blockers
- `alerts` - Automated system alerts for risks and inactivity
- `project_templates` - Reusable project templates with task lists
- `task_templates` - Reusable task templates with checklists
- Enhanced `users` - Added performance_score, deadline_score, speed_score, engagement_score, last_check_in, last_activity

## Features Highlights

### Interactive Kanban Board
- Drag-and-drop task management
- Real-time status updates
- Visual priority indicators
- Deadline tracking with overdue alerts

### Dashboard
- **Admin Dashboard**: Project health overview, system-wide metrics, recent activity
- **Developer Dashboard**: Personal task list, hours logged, completion statistics

### API-First Architecture
- RESTful API endpoints for all entities
- JSON responses with proper HTTP status codes
- Permission-based access control on all endpoints

### Responsive Design
- Clean, fast, distraction-free interface
- 100% mobile responsive
- Modern UI inspired by Linear/ClickUp/Monday

## Security Features

- Role-based access control (RBAC)
- Permission-based filters on all routes
- Activity logging for accountability
- Secure password hashing via Shield
- CSRF protection (configurable)
- Input validation and sanitization

## Setup Instructions

### Prerequisites
- PHP 8.1+
- MySQL 5.7+
- Composer
- XAMPP/WAMP (for local development)

### Installation Steps

1. **Create CodeIgniter Project**
   ```bash
   composer create-project codeigniter4/appstarter:4.7.0 .
   ```

2. **Install CodeIgniter Shield**
   ```bash
   composer require codeigniter4/shield:^1.0
   php spark shield:setup
   ```

3. **Configure Environment**
   ```bash
   cp env.example .env
   ```
   Edit `.env` and set:
   - Database credentials (MySQL)
   - Base URL
   - Environment to development

4. **Create Database**
   ```bash
   mysql -u root -e "CREATE DATABASE project_management_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```

5. **Run Migrations**
   ```bash
   php spark migrate --all
   ```

6. **Seed Roles and Permissions**
   ```bash
   php spark db:seed RolesPermissionsSeeder
   ```

7. **Create Admin User**
   ```bash
   php spark shield:user create
   php spark shield:group add [username] admin
   ```

8. **Start Development Server**
   ```bash
   php spark serve
   ```

9. **Access Application**
   - URL: http://localhost:8080
   - Login with admin credentials

For detailed step-by-step instructions, see `QUICKSTART.md`.

## Architecture

### API-First Design
- RESTful endpoints for all entities
- ResourceController pattern
- JSON responses with proper HTTP status codes
- Permission-based access control on all routes

### Service Layer
- **DashboardService**: Executive dashboard data aggregation
- **PerformanceService**: Developer performance score calculations
- **AlertService**: Automated alert generation and management
- **AssignmentService**: Smart task assignment recommendations
- **ProfitabilityService**: Financial metrics and profitability analysis
- **CapacityService**: Team capacity forecasting and hiring recommendations

### Modular Structure
```
app/
├── Controllers/        # Web and API controllers
├── Models/            # Database models with validation
├── Services/          # Business logic layer
├── Views/             # Server-rendered views (Bootstrap 5)
├── Filters/           # RBAC and permission filters
├── Database/
│   ├── Migrations/    # Database schema migrations
│   └── Seeds/         # Data seeders
└── Config/            # Configuration files
```

### Frontend Stack
- **Bootstrap 5**: Responsive UI framework
- **Vanilla JavaScript**: Minimal, fast interactions
- **SortableJS**: Kanban drag-and-drop (CDN)
- **AJAX**: Real-time updates without page reloads

## Success Metrics

### Immediate Visibility
- **Dashboard Load Time**: < 5 seconds
- **Project Health**: Auto-calculated and color-coded
- **Critical Alerts**: Displayed prominently on dashboard
- **Real-Time Updates**: AJAX for status changes, timers, messaging

### Accountability by Design
- **Activity Logging**: All major actions logged automatically
- **Performance Scores**: Updated automatically based on behavior
- **Daily Check-Ins**: Streak tracking and team visibility
- **Audit Trail**: Complete history with user, IP, and timestamp

### Leadership Rarely Asks for Updates
- **Executive Dashboard**: All key metrics at a glance
- **Automated Alerts**: Proactive risk detection
- **Profitability Dashboard**: Real-time financial insights
- **Capacity Forecasting**: Data-driven hiring recommendations
- **Performance Tracking**: Objective developer metrics

## Development Notes

This is a **local development setup**. For production deployment:
- Enable HTTPS
- Configure proper database credentials
- Set up automated backups
- Enable CSRF protection
- Configure email notifications
- Set up queue workers for background jobs
- Implement rate limiting
- Configure proper logging
- Set up monitoring and alerting

## API Documentation

For complete API endpoint documentation, see `API.md`.

## License

Proprietary - All rights reserved

## Support

For issues or questions, please contact the development team.

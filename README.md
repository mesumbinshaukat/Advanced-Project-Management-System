# Advanced Project Management System

A modern, API-first project management system built with CodeIgniter 4.7.0 and CodeIgniter Shield for authentication and role-based access control.

## Features

### Core Functionality
- **Project Management**: Create, edit, archive, and delete projects with client associations
- **Task Management**: Kanban board with drag-and-drop functionality (Backlog → Todo → In Progress → Review → Done)
- **Time Tracking**: Log time entries against tasks with billable/non-billable tracking
- **Client Management**: Manage client information and associations
- **Activity Logging**: Comprehensive audit trail for all major actions
- **Financial Tracking**: Project budgets, hourly rates, and profitability metrics
- **Performance Metrics**: Track user and project performance over time

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

The system includes the following tables:
- `clients` - Client information
- `projects` - Project details linked to clients
- `tasks` - Tasks with status, priority, deadlines, and ownership
- `users` - User accounts (managed by Shield)
- `time_entries` - Time logs linked to tasks and users
- `activity_logs` - Audit trail for all major actions
- `financials` - Project pricing and profitability data
- `performance_metrics` - User and project performance tracking
- `project_users` - Project team assignments

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

## Development Notes

This is a local development setup. For production deployment:
- Enable HTTPS
- Configure proper database credentials
- Set up automated backups
- Enable CSRF protection
- Configure email notifications
- Set up queue workers for background jobs
- Implement rate limiting
- Configure proper logging

## License

Proprietary - All rights reserved

## Support

For issues or questions, please contact the development team.

# API Documentation

Complete RESTful API reference for the Advanced Project Management System.

## Base URL

```
http://localhost:8080/api
```

## Authentication

All API endpoints require authentication via CodeIgniter Shield. Use session-based authentication (cookies) or token-based authentication (Bearer tokens).

**Session-based**: Login via `/login` endpoint, session cookie is automatically set.

**Token-based** (future): Include `Authorization: Bearer {token}` header.

## Response Format

All responses are in JSON format:

**Success Response:**
```json
{
  "status": "success",
  "data": {},
  "message": "Optional message"
}
```

**Error Response:**
```json
{
  "status": "error",
  "message": "Error description",
  "errors": {}
}
```

## HTTP Status Codes

- `200 OK` - Request succeeded
- `201 Created` - Resource created successfully
- `400 Bad Request` - Invalid request data
- `401 Unauthorized` - Authentication required
- `403 Forbidden` - Insufficient permissions
- `404 Not Found` - Resource not found
- `422 Unprocessable Entity` - Validation errors
- `500 Internal Server Error` - Server error

## Rate Limiting

Currently no rate limiting in local development. Production deployment should implement rate limiting.

---

## Projects API

### List Projects
```
GET /api/projects
```

**Permissions**: `projects.view.assigned` or `projects.view.all`

**Response**:
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "client_id": 1,
      "name": "Project Name",
      "description": "Project description",
      "status": "active",
      "priority": "high",
      "start_date": "2024-01-01",
      "deadline": "2024-12-31",
      "budget": 50000.00,
      "client_name": "Client Name"
    }
  ]
}
```

### Get Project
```
GET /api/projects/{id}
```

**Permissions**: `projects.view.assigned` or `projects.view.all`

### Create Project
```
POST /api/projects
```

**Permissions**: `projects.create`

**Request Body**:
```json
{
  "client_id": 1,
  "name": "New Project",
  "description": "Project description",
  "status": "active",
  "priority": "medium",
  "start_date": "2024-01-01",
  "deadline": "2024-12-31",
  "budget": 50000.00
}
```

### Update Project
```
PUT /api/projects/{id}
```

**Permissions**: `projects.edit`

**Request Body**: Same as Create Project

### Delete Project
```
DELETE /api/projects/{id}
```

**Permissions**: `projects.delete`

### Assign User to Project
```
POST /api/projects/{id}/assign
```

**Permissions**: `projects.assign`

**Request Body**:
```json
{
  "user_id": 5
}
```

### Remove User from Project
```
DELETE /api/projects/{id}/users/{user_id}
```

**Permissions**: `projects.assign`

---

## Tasks API

### List Tasks
```
GET /api/tasks
```

**Permissions**: `tasks.view.assigned` or `tasks.view.all`

**Query Parameters**:
- `project_id` - Filter by project
- `status` - Filter by status (backlog, todo, in_progress, review, done)
- `assigned_to` - Filter by assigned user

### Get Task
```
GET /api/tasks/{id}
```

**Permissions**: `tasks.view.assigned` or `tasks.view.all`

### Create Task
```
POST /api/tasks
```

**Permissions**: `tasks.create`

**Request Body**:
```json
{
  "project_id": 1,
  "title": "Task Title",
  "description": "Task description",
  "status": "backlog",
  "priority": "high",
  "assigned_to": 5,
  "estimated_hours": 8.5,
  "start_date": "2024-01-01",
  "deadline": "2024-01-15",
  "is_blocked": false,
  "blocker_reason": null,
  "tags": "frontend,urgent"
}
```

### Update Task
```
PUT /api/tasks/{id}
```

**Permissions**: `tasks.update.status` (for status changes) or task owner

**Request Body**: Same as Create Task

### Update Task Status
```
POST /api/tasks/{id}/status
```

**Permissions**: `tasks.update.status`

**Request Body**:
```json
{
  "status": "in_progress",
  "order_position": 2
}
```

**Note**: Used by Kanban board drag-and-drop

### Delete Task
```
DELETE /api/tasks/{id}
```

**Permissions**: `tasks.delete`

---

## Clients API

### List Clients
```
GET /api/clients
```

**Permissions**: Admin only

### Get Client
```
GET /api/clients/{id}
```

**Permissions**: Admin only

### Create Client
```
POST /api/clients
```

**Permissions**: Admin only

**Request Body**:
```json
{
  "name": "Client Name",
  "email": "client@example.com",
  "phone": "+1234567890",
  "company": "Company Name",
  "address": "123 Main St"
}
```

### Update Client
```
PUT /api/clients/{id}
```

**Permissions**: Admin only

### Delete Client
```
DELETE /api/clients/{id}
```

**Permissions**: Admin only

---

## Time Entries API

### List Time Entries
```
GET /api/time-entries
```

**Permissions**: `time.log` or `time.view.all`

**Query Parameters**:
- `user_id` - Filter by user
- `task_id` - Filter by task
- `entry_date` - Filter by date

### Get Time Entry
```
GET /api/time-entries/{id}
```

**Permissions**: `time.log` or `time.view.all`

### Create Time Entry
```
POST /api/time-entries
```

**Permissions**: `time.log`

**Request Body**:
```json
{
  "task_id": 10,
  "entry_date": "2024-01-15",
  "hours": 4.5,
  "description": "Worked on feature implementation",
  "is_billable": true
}
```

**Note**: `user_id` is automatically set to authenticated user

### Update Time Entry
```
PUT /api/time-entries/{id}
```

**Permissions**: `time.log` (own entries) or `time.view.all` (all entries)

### Delete Time Entry
```
DELETE /api/time-entries/{id}
```

**Permissions**: `time.log` (own entries) or `time.view.all` (all entries)

---

## Notes API (Milestone 2)

### List Notes
```
GET /api/notes
```

**Permissions**: `tasks.view.assigned` or `tasks.view.all`

**Query Parameters**:
- `project_id` - Get project notes
- `task_id` - Get task notes

### Create Note
```
POST /api/notes
```

**Permissions**: `tasks.view.assigned` or `tasks.view.all`

**Request Body**:
```json
{
  "project_id": 1,
  "task_id": null,
  "title": "Note Title",
  "content": "Note content",
  "type": "decision",
  "is_pinned": false
}
```

**Note Types**: `note`, `decision`, `blocker`, `update`

### Update Note
```
PUT /api/notes/{id}
```

**Permissions**: Note owner or admin

### Delete Note
```
DELETE /api/notes/{id}
```

**Permissions**: Note owner or admin

### Pin/Unpin Note
```
POST /api/notes/pin/{id}
```

**Permissions**: Note owner or admin

---

## Messages API (Milestone 2)

### List Messages
```
GET /api/messages
```

**Permissions**: `projects.view.assigned` or `projects.view.all`

**Query Parameters**:
- `project_id` - Required, filter by project
- `task_id` - Optional, filter by task

**Response**: Returns threaded messages with replies

### Create Message
```
POST /api/messages
```

**Permissions**: `projects.view.assigned` or `projects.view.all`

**Request Body**:
```json
{
  "project_id": 1,
  "task_id": null,
  "parent_id": null,
  "message": "Message content"
}
```

**Note**: Set `parent_id` for threaded replies

### Mark Message as Read
```
POST /api/messages/{id}/read
```

**Permissions**: Authenticated user

### Get Unread Count
```
GET /api/messages/unread
```

**Permissions**: Authenticated user

**Query Parameters**:
- `project_id` - Required

**Response**:
```json
{
  "count": 5
}
```

---

## Assignment API (Milestone 2)

### Get Assignment Suggestion
```
GET /api/assignment/suggest
```

**Permissions**: `projects.view.all` (Admin only)

**Query Parameters**:
- `project_id` - Required
- `task_id` - Optional

**Response**:
```json
{
  "user_id": 5,
  "username": "developer1",
  "workload": {
    "active_tasks": 3,
    "hours_this_week": 28.5,
    "score": 44.25
  },
  "all_workloads": {}
}
```

### Get Developer Workload
```
GET /api/assignment/workload
```

**Permissions**: `projects.view.all` (Admin only)

**Query Parameters**:
- `project_id` - Optional, filter by project

**Response**: Array of all developers with workload data

### Get Specific Developer Workload
```
GET /api/assignment/workload/{user_id}
```

**Permissions**: `projects.view.all` (Admin only)

**Response**:
```json
{
  "active_tasks": 5,
  "hours_this_week": 32.0,
  "tasks_by_status": {
    "backlog": 1,
    "todo": 2,
    "in_progress": 2,
    "review": 0,
    "done": 10
  }
}
```

---

## Milestone 3 Features

All Milestone 3 features (Performance, Alerts, Templates, Profitability, Capacity, Check-Ins) are primarily accessed via web interface. API endpoints can be added as needed for mobile/external integrations.

### Future API Endpoints (Not Yet Implemented)

The following endpoints could be added for external integrations:

- `GET /api/performance/developers` - Get all developer performance scores
- `GET /api/performance/developer/{id}` - Get specific developer performance
- `GET /api/alerts` - Get active alerts
- `POST /api/alerts/{id}/resolve` - Resolve an alert
- `GET /api/check-ins/today` - Get today's check-in
- `POST /api/check-ins` - Submit daily check-in
- `GET /api/profitability/overview` - Get profitability metrics
- `GET /api/capacity/forecast` - Get capacity forecast
- `GET /api/templates/projects` - List project templates
- `GET /api/templates/tasks` - List task templates

---

## Error Handling

### Validation Errors (422)

```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "title": "The title field is required",
    "deadline": "The deadline must be a valid date"
  }
}
```

### Permission Errors (403)

```json
{
  "status": "error",
  "message": "Permission denied: You cannot update this task"
}
```

### Not Found Errors (404)

```json
{
  "status": "error",
  "message": "Task not found"
}
```

---

## Usage Examples

### Create Task with cURL

```bash
curl -X POST http://localhost:8080/api/tasks \
  -H "Content-Type: application/json" \
  -H "Cookie: ci_session=..." \
  -d '{
    "project_id": 1,
    "title": "Implement login feature",
    "description": "Add user authentication",
    "status": "todo",
    "priority": "high",
    "estimated_hours": 8
  }'
```

### Update Task Status (Kanban)

```bash
curl -X PUT http://localhost:8080/api/tasks/5 \
  -H "Content-Type: application/json" \
  -H "Cookie: ci_session=..." \
  -d '{
    "status": "in_progress",
    "order_position": 1
  }'
```

### Create Time Entry

```bash
curl -X POST http://localhost:8080/api/time-entries \
  -H "Content-Type: application/json" \
  -H "Cookie: ci_session=..." \
  -d '{
    "task_id": 5,
    "entry_date": "2024-01-15",
    "hours": 4.5,
    "description": "Implemented authentication logic",
    "is_billable": true
  }'
```

### Get Assignment Suggestion

```bash
curl -X GET "http://localhost:8080/api/assignment/suggest?project_id=1" \
  -H "Cookie: ci_session=..."
```

---

## Best Practices

1. **Always check permissions**: Ensure user has required permissions before making API calls
2. **Handle errors gracefully**: Check HTTP status codes and parse error messages
3. **Use appropriate HTTP methods**: GET for reading, POST for creating, PUT for updating, DELETE for deleting
4. **Include CSRF tokens**: For POST/PUT/DELETE requests (if CSRF protection is enabled)
5. **Validate input**: Client-side validation before API calls reduces server load
6. **Cache responses**: Cache GET responses where appropriate to reduce API calls
7. **Use query parameters**: Filter and paginate large datasets using query parameters
8. **Monitor rate limits**: Respect rate limits in production environments

---

## Webhook Support (Future)

Future versions may include webhook support for:
- Task status changes
- Project updates
- Alert notifications
- Performance score updates
- Check-in submissions

---

## GraphQL Support (Future)

Future versions may include GraphQL endpoint for more flexible queries:
```
POST /api/graphql
```

---

## Support

For API issues or questions:
- Check `writable/logs` for detailed error logs
- Review CodeIgniter 4 REST documentation
- Contact development team

**Permissions**: `projects.edit`

### Delete Project
```
DELETE /api/projects/{id}
```

**Permissions**: `projects.delete`

### Assign User to Project
```
POST /api/projects/{id}/assign
```

**Permissions**: `projects.assign`

**Request Body**:
```json
{
  "user_id": 2,
  "role": "member"
}
```

---

## Tasks API

### List Tasks
```
GET /api/tasks?project_id={id}&status={status}
```

**Permissions**: `tasks.view.assigned` or `tasks.view.all`

**Query Parameters**:
- `project_id` (optional) - Filter by project
- `status` (optional) - Filter by status

### Get Task
```
GET /api/tasks/{id}
```

**Permissions**: `tasks.view.assigned` or `tasks.view.all`

### Create Task
```
POST /api/tasks
```

**Permissions**: `tasks.create`

**Request Body**:
```json
{
  "project_id": 1,
  "title": "Task Title",
  "description": "Task description",
  "status": "todo",
  "priority": "high",
  "assigned_to": 2,
  "estimated_hours": 8.0,
  "start_date": "2024-01-01",
  "deadline": "2024-01-15"
}
```

### Update Task
```
PUT /api/tasks/{id}
```

**Permissions**: `tasks.update.status` (developers can only update status)

### Delete Task
```
DELETE /api/tasks/{id}
```

**Permissions**: `tasks.delete`

### Update Task Status
```
POST /api/tasks/{id}/status
```

**Permissions**: `tasks.update.status`

**Request Body**:
```json
{
  "status": "in_progress",
  "order_position": 0
}
```

---

## Clients API

### List Clients
```
GET /api/clients
```

**Permissions**: Admin only

### Get Client
```
GET /api/clients/{id}
```

**Permissions**: Admin only

### Create Client
```
POST /api/clients
```

**Permissions**: Admin only

**Request Body**:
```json
{
  "name": "Client Name",
  "email": "client@example.com",
  "phone": "123-456-7890",
  "company": "Company Name",
  "address": "123 Main St",
  "notes": "Additional notes",
  "is_active": 1
}
```

### Update Client
```
PUT /api/clients/{id}
```

**Permissions**: Admin only

### Delete Client
```
DELETE /api/clients/{id}
```

**Permissions**: Admin only

---

## Time Entries API

### List Time Entries
```
GET /api/time?start_date={date}&end_date={date}&project_id={id}
```

**Permissions**: `time.log` or `time.view.all`

**Query Parameters**:
- `start_date` (optional) - Filter by start date
- `end_date` (optional) - Filter by end date
- `project_id` (optional) - Filter by project

### Get Time Entry
```
GET /api/time/{id}
```

**Permissions**: `time.view.own` or `time.view.all`

### Create Time Entry
```
POST /api/time
```

**Permissions**: `time.log`

**Request Body**:
```json
{
  "task_id": 1,
  "hours": 4.5,
  "description": "Work description",
  "date": "2024-01-15",
  "is_billable": 1
}
```

### Update Time Entry
```
PUT /api/time/{id}
```

**Permissions**: Own entries only (or admin)

### Delete Time Entry
```
DELETE /api/time/{id}
```

**Permissions**: Own entries only (or admin)

---

## Permissions Reference

### Admin Permissions
- `projects.create`, `projects.edit`, `projects.delete`, `projects.archive`
- `projects.view.all`, `projects.assign`
- `clients.create`, `clients.edit`, `clients.delete`, `clients.view.all`
- `tasks.create`, `tasks.edit`, `tasks.delete`, `tasks.assign`, `tasks.view.all`
- `time.view.all`
- `financials.view`, `financials.edit`
- `analytics.view`
- `users.manage`, `settings.manage`

### Developer Permissions
- `projects.view.assigned`
- `tasks.view.assigned`, `tasks.update.status`
- `time.log`, `time.view.own`
- `comments.create`
- `files.upload`
- `checkins.submit`

---

## Error Handling

API errors return appropriate HTTP status codes with error messages:

```json
{
  "status": "error",
  "message": "Error description",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

## Rate Limiting

Currently not implemented for local development. Should be configured for production use.

## Examples

### cURL Examples

**Create a Project**:
```bash
curl -X POST http://localhost:8080/api/projects \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": 1,
    "name": "New Project",
    "status": "active",
    "priority": "high"
  }'
```

**Update Task Status**:
```bash
curl -X POST http://localhost:8080/api/tasks/1/status \
  -H "Content-Type: application/json" \
  -d '{"status": "in_progress"}'
```

**Log Time**:
```bash
curl -X POST http://localhost:8080/api/time \
  -H "Content-Type: application/json" \
  -d '{
    "task_id": 1,
    "hours": 3.5,
    "date": "2024-01-15",
    "is_billable": 1
  }'
```

## Notes

- All dates should be in `YYYY-MM-DD` format
- All datetime fields are in `YYYY-MM-DD HH:MM:SS` format
- Decimal values (hours, budget) support up to 2 decimal places
- Boolean values can be `0`, `1`, `true`, or `false`

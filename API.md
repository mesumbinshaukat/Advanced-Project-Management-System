# API Documentation

The Advanced Project Management System provides a RESTful API for all core entities. All API endpoints require authentication via CodeIgniter Shield.

## Base URL

```
http://localhost:8080/api
```

## Authentication

All API requests require authentication. Use Shield's session-based authentication or token-based authentication.

## Response Format

All responses are in JSON format:

```json
{
  "status": "success|error",
  "data": {},
  "message": "Optional message"
}
```

## HTTP Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `500` - Internal Server Error

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

# Login Credentials

## Admin Account

**Email:** admin@example.com  
**Password:** admin123  
**Role:** Administrator (full access)

## Access the Application

1. Navigate to: http://localhost:8080
2. You'll be redirected to the login page
3. Enter the credentials above
4. You'll be redirected to the admin dashboard

## User Status

- ✅ User is active
- ✅ Email identity configured
- ✅ Password hash updated
- ✅ Admin group assigned
- ✅ All permissions granted

## What You Can Do

As an admin, you have full access to:
- Create, edit, delete projects
- Manage clients
- Assign developers to projects
- View all tasks and time entries
- Access financial data and analytics
- Manage users and settings
- View system-wide activity logs

## Creating Additional Users

To create a developer user, you can use:
```bash
php spark shield:user create
```

Then assign them to the developer group:
```bash
php spark shield:group add [username] developer
```

Or create users directly through the admin dashboard once logged in.

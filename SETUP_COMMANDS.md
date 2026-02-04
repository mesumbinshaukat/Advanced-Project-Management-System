# Setup Commands for Advanced Project Management System

## Step 1: Create CodeIgniter 4.7.0 Project
```bash
composer create-project codeigniter4/appstarter:4.7.0 .
```

## Step 2: Install CodeIgniter Shield
```bash
composer require codeigniter4/shield:^1.0
```

## Step 3: Publish Shield Configuration
```bash
php spark shield:setup
```

## Step 4: Run Database Migrations
```bash
php spark migrate --all
```

## Step 5: Create Admin User (Run after migrations)
```bash
php spark shield:user create
# Follow prompts to create admin user
# Then assign admin group:
php spark shield:group add [username] admin
```

## Step 6: Start Development Server
```bash
php spark serve
```

Access at: http://localhost:8080

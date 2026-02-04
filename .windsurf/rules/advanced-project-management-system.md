---
trigger: always_on
---

Project: Advanced Project Management System (strictly per provided PDF spec document)
Framework: CodeIgniter 4.7+ (latest stable 4.x series)
Database: MySQL (local dev only; use migrations; follow exact DB guidelines: projects linked to clients, tasks with status/priority/deadlines/ownership, users with roles, time entries linked to tasks/users, activity logs for all major actions, financial/pricing table, performance metrics table). Use CodeIgniter migrations and Model.
Architecture: API-first (use ResourceController where possible, RESTful routes, ResponseTrait). Modular services. For local dev: no queues/heavy jobs initially (simple sync or database driver if needed). Server-rendered views + AJAX/JS for interactivity.
Authentication & RBAC: Install & use official CodeIgniter Shield (composer require codeigniter/shield). Roles/groups: 'admin' (full capabilities per spec) and 'developer' (restricted per spec: assigned projects/tasks only, no financials, no unrelated projects, no permission changes). Use Shield filters, groups, permissions.
UI/UX: Clean, fast, distraction-free, high performance, 100% responsive. Use Bootstrap 5 (CDN or assets) + minimal vanilla JS/AJAX + SortableJS (CDN) for Kanban drag-drop. Inspiration: Linear/ClickUp/Monday but simpler/faster. Interactive but simple (timers, status updates, drag-drop, forms, real-time-ish via AJAX/polling). No heavy visuals, no extra frameworks/libraries unless minimal & required (e.g., no Vue/React/SPA unless unavoidable).
Features: Implement ALL Mandatory System Modules + ALL Strongly Recommended Advanced Intelligence Features. NO extras, no complexity, no production setup (no Docker, no advanced scaling, no HTTPS/2FA enforcement beyond readiness, simple local backups note only).
Local Dev Only: composer create-project, php spark serve, .env for local DB (MySQL root/local creds), migrations run via spark.
Documentation: Generate ONLY: README.md (overview, setup), QUICKSTART.md (local run steps), API.md (endpoints summary). No other .md files.
Strict Rules: Adhere 100% to spec (roles/capabilities/restrictions, Kanban columns: Backlog→Todo→In Progress→Review→Done, immediate visibility, accountability logging, low-friction UX). Use validations, filters for permissions/logging. Output complete, runnable code per milestone (controllers, models, migrations, views/layouts, routes, services). Test locally. Clean code, namespaces, best practices per official CI4/Shield docs. No deprecations (use current APIs).
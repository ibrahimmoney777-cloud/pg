# PG Dissertation Management System (PHP + MySQL)

## Quick Start

1. Create database and tables:
   - Open MySQL and run `schema.sql`.
   - Or run `database.sql` for the newer unified schema (`users` + `projects`).
2. Put project in web server folder (XAMPP `htdocs` for example).
3. Update DB settings in `config.php` if needed.
4. Open browser:
   - `http://localhost/7q%20elnas2/index.php`

## New Database Script

- File: `database.sql`
- Database name: `university_project_management`
- Tables:
  - `users` (`student` / `supervisor` roles)
  - `projects` (title, description, department, file path, status, feedback)
- Status options:
  - `Pending`
  - `Approved`
  - `Rejected`

## Demo Accounts

- Student:
  - `ali / 1234`
  - `sara / 1234`
- Supervisor:
  - `dr_ahmed / admin`

## Main Features

- Role-based login (Student / Supervisor)
- Student uploads dissertation files (PDF/DOC/DOCX)
- Automatic versioning per student
- Supervisor review with status + comments
- Status flow:
  - `Pending`
  - `Under Review`
  - `Needs Revision`
  - `Approved`
- Filter submissions by status
- Dashboard-ready dark UI

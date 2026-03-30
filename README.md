# PG Dissertation Management System (PHP + MySQL)

## Quick Start

1. Create database and tables:
   - Open MySQL and run `schema.sql`.
2. Put project in web server folder (XAMPP `htdocs` for example).
3. Update DB settings in `config.php` if needed.
4. Open browser:
   - `http://localhost/7q%20elnas2/index.php`

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

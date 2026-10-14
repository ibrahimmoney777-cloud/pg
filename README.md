# PG Dissertation Management System

A server-rendered PHP/MySQL application for student dissertation uploads and supervisor review. Students can upload successive versions of a dissertation; supervisors can review submissions, change status, and leave feedback.

## Features

- Role-based Student / Supervisor sign-in
- PDF and DOCX upload (maximum 25 MiB, subject to PHP server limits)
- Automatic per-student version numbering
- Supervisor status updates and feedback
- Status filtering on the supervisor dashboard
- Controlled downloads: students can download their own files; supervisors can download submissions
- Best-effort email notification after a new submission

## Requirements

- PHP 8.0+ recommended (the code uses strict types and PDO)
- MySQL or MariaDB
- Apache/XAMPP or another PHP-capable web server
- PHP PDO MySQL extension enabled

## Local setup (XAMPP example)

1. Extract the project folder into `htdocs`.
2. Start Apache and MySQL.
3. Open phpMyAdmin or MySQL CLI and run **`schema.sql`**. This creates the `dissertation_system` database and the tables used by the current PHP code.
4. Review database host/name/user/password in `config.php`.
5. Ensure the `uploads/` directory is writable by the web-server process. Avoid making it world-writable.
6. Open `http://localhost/7q%20elnas2/index.php` (adjust the folder name/URL if you renamed the project).

### Demo credentials

| Role | Username | Password |
|---|---|---|
| Student | `ali` | `1234` |
| Student | `sara` | `1234` |
| Supervisor | `dr_ahmed` | `admin` |

These are local demonstration credentials, not production credentials.

## Database schema choice

Use `schema.sql` with the current application. `database.sql` describes a different `users`/`projects` schema and is not compatible with the current PHP queries without a migration. Details are in [`docs/DATABASE.md`](docs/DATABASE.md).

## Routes and request contracts

The project does not currently expose a REST/JSON API. It uses PHP pages and form handlers. See [`docs/API.md`](docs/API.md) for route access, methods, inputs, outputs, validation, and error boundaries.

## Testing

Run the lightweight automated helper tests with:

```bash
php tests/run.php
```

For upload, role access, supervisor review, download authorization, and failure-path tests, follow [`docs/TESTING.md`](docs/TESTING.md). The automated tests are not a substitute for integration tests against a configured MySQL instance.

## Configuration and security notes

- Database credentials, supervisor notification addresses, and SMTP settings live in `config.php`. Configure these for your own environment; do not commit real SMTP secrets.
- Database connection errors are logged server-side and shown as a generic browser message.
- SQL values use PDO prepared statements, and HTML output uses the shared `h()` escaping helper.
- Passwords in the supplied demo schema use SHA-256 for demonstration simplicity. Before production use, migrate to PHP `password_hash()` / `password_verify()`.
- Before public deployment, add CSRF protection, stronger upload content/MIME validation, secure session cookie settings, and storage outside the public web root.

## Project structure

```text
.
├── index.php          # Login page and login handler
├── student.php        # Student dashboard and submission history
├── supervisor.php    # Supervisor dashboard and status filtering
├── upload.php         # Student upload handler
├── review.php         # Supervisor review handler
├── download.php       # Authorized file download handler
├── logout.php         # Session logout
├── auth.php           # Shared session, role, and HTML-escaping helpers
├── config.php         # Database and mail configuration
├── schema.sql         # Active MySQL schema
├── database.sql       # Alternate schema; not used by current PHP pages
├── docs/              # API/routes, database, and testing documentation
└── tests/run.php      # Dependency-free helper unit tests
```

# Application Routes and Form Contracts

This project is a server-rendered PHP application. **It does not currently expose a JSON/REST API.** The routes below are browser pages and form handlers.

| Route | Method | Access | Inputs | Result |
|---|---|---|---|---|
| `index.php` | GET | Guest | None | Displays sign-in form; authenticated users are redirected to their dashboard. |
| `index.php` | POST | Guest | `role` (`student` or `supervisor`), `name`, `password` | Validates credentials and redirects to the role dashboard; invalid input shows an error. |
| `student.php` | GET | Student | Optional `msg` query parameter | Lists the signed-in student's submissions, newest version first. |
| `upload.php` | POST | Student | Multipart `dissertation_file` | Accepts PDF/DOCX up to 25 MiB, saves the file and metadata, then redirects to `student.php`. |
| `supervisor.php` | GET | Supervisor | Optional `status` filter and `msg` | Lists submissions for review, optionally filtered by status. |
| `review.php` | POST | Supervisor | `id`, `status`, `comment` | Updates review status and comment, then redirects to the supervisor dashboard. |
| `download.php` | GET | Logged-in user | `id` (submission ID) | Streams a file only to its owning student or a supervisor. Returns 400/403/404 for invalid, unauthorized, or missing submissions. |
| `logout.php` | GET | Logged-in session | None | Clears the session and redirects to sign-in. |

## Validation and error boundaries

- Authentication checks are centralized in `auth.php` (`isLoggedIn()`, `requireRole()`).
- SQL values use PDO prepared statements. The role-to-table mapping in `index.php` is a fixed allow-list.
- `upload.php` checks request method, PHP upload error, 25 MiB size limit, and `.pdf`/`.docx` extension. Server-level MIME/content validation is not currently implemented; extension checks alone do not prove file contents are safe.
- `review.php` only accepts `Under Review`, `Needs Revision`, or `Approved`; comments are stripped of HTML and limited to 2,000 characters.
- `download.php` checks ownership before sending file contents.
- `config.php` logs database connection diagnostics server-side and displays a generic error to the browser.
- Most form handlers use redirect + `msg` query parameters rather than structured HTTP/JSON errors.

## Important hardening work still recommended

Before public deployment, add CSRF tokens to state-changing forms, use `password_hash()`/`password_verify()` instead of SHA-256 password storage, validate uploaded file MIME/content, store uploads outside the public web root, and review session cookie settings. These are documented limitations, not features claimed to be implemented.

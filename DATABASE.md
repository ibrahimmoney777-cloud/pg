# Database Schema Notes

## Active schema

The PHP application currently connects to the database named `dissertation_system` (see `config.php`) and its queries use the tables defined in **`schema.sql`**. Run `schema.sql` for this version of the application.

### `students`

| Column | Type | Rules / purpose |
|---|---|---|
| `id` | `INT` | Primary key, auto-increment |
| `name` | `VARCHAR(100)` | Required, unique username |
| `password` | `VARCHAR(255)` | Stored SHA-256 digest in the current demo schema |

### `supervisors`

| Column | Type | Rules / purpose |
|---|---|---|
| `id` | `INT` | Primary key, auto-increment |
| `name` | `VARCHAR(100)` | Required, unique username |
| `password` | `VARCHAR(255)` | Stored SHA-256 digest in the current demo schema |

### `dissertations`

| Column | Type | Rules / purpose |
|---|---|---|
| `id` | `INT` | Primary key, auto-increment |
| `student_id` | `INT` | Required foreign key to `students.id`; cascades on student deletion |
| `file_name` | `VARCHAR(255)` | Original display name |
| `file_path` | `VARCHAR(255)` | Relative path used by the download handler |
| `version` | `INT` | Version number for this student's submission |
| `status` | `ENUM` | `Pending`, `Under Review`, `Needs Revision`, `Approved`; defaults to `Pending` |
| `comment` | `TEXT` | Supervisor feedback; nullable in schema, usually initialized as empty text |
| `upload_date` | `DATETIME` | Defaults to current timestamp |

Relationship: one student can have many dissertation versions; each dissertation row belongs to one student. The current schema does not enforce uniqueness of `(student_id, version)` at database level.

## Schema mismatch warning

`database.sql` defines a different, newer-looking `users`/`projects` model. The current PHP files query `students`, `supervisors`, and `dissertations`, so `database.sql` is **not compatible with the current PHP implementation without code migration**. Do not run both schema scripts and assume they represent the same schema. For the code in this archive, use `schema.sql`.

## Demo credentials

`schema.sql` inserts `ali / 1234`, `sara / 1234`, and `dr_ahmed / admin` using SHA-256 digests. These are demonstration credentials only and must not be used for a production deployment.

# Testing Plan

## 1. Run the automated helper tests

Requires PHP CLI; no third-party test framework is needed:

```bash
php tests/run.php
```

The test runner covers HTML escaping and basic session-state helper behavior. It does **not** exercise MySQL, browser rendering, email delivery, or file upload integration.

## 2. Manual integration test cases

| ID | Area | Setup / action | Expected result |
|---|---|---|---|
| AUTH-01 | Login | Sign in as `ali` with role Student and password `1234` | Redirect to `student.php`. |
| AUTH-02 | Login failure | Use an invalid password | Generic invalid-credentials message; no dashboard access. |
| AUTH-03 | Role guard | While signed in as Student, request `supervisor.php` directly | Redirect to sign-in. |
| AUTH-04 | Logout | Select logout, then revisit a protected page | Session is cleared; protected access is denied. |
| UP-01 | Upload success | Student uploads a valid PDF smaller than 25 MiB | File and row are saved; version increments; status starts as Pending. |
| UP-02 | Extension rejection | Upload a `.txt` file | Upload is rejected and user is redirected with an error message. |
| UP-03 | Size boundary | Upload a file larger than 25 MiB | Upload is rejected. Also test exactly 25 MiB as an allowed size boundary, subject to PHP server upload limits. |
| UP-04 | Upload transport error | Submit without a valid uploaded file / simulate a PHP upload error | No dissertation row should be created. |
| UP-05 | Versioning | Upload two files for the same student | Versions should be 1 then 2 (repeat to assess concurrent-upload behavior). |
| REV-01 | Review success | Supervisor sets status to Approved and adds feedback | Row updates and success message appears. |
| REV-02 | Invalid status | POST an unsupported status value | Update is rejected. |
| REV-03 | Comment boundary | Submit a comment longer than 2,000 characters | Stored comment is truncated to at most 2,000 characters. |
| DL-01 | Owner access | Student downloads their own submission | File is streamed. |
| DL-02 | Cross-user access | Student attempts to download another student's submission ID | HTTP 403; no file bytes are returned. |
| DL-03 | Missing record | Request a nonexistent submission ID | HTTP 404. |
| DB-01 | Database unavailable | Stop MySQL or configure an invalid DB name | Generic HTTP 500 message; detailed exception is written to server error log. |
| MAIL-01 | Email failure | Configure invalid SMTP credentials and upload a file | Submission still succeeds; email notification is best-effort. |

## 3. Error-boundary review checklist

- Confirm every protected page calls `requireRole()` or an equivalent authorization check.
- Confirm a failed upload cannot create a database row with a nonexistent file.
- Confirm unauthorized downloads return 403 before file contents are sent.
- Confirm missing database rows are handled without PHP notices or leaking SQL details.
- Test `uploads/` write permissions and PHP limits (`upload_max_filesize`, `post_max_size`, `max_file_uploads`).
- Review server logs after intentionally causing a DB/email failure.

## 4. Current known limitations

- Automated tests currently cover shared helper functions only; most cases above are manual integration tests.
- Version number allocation can race if two uploads happen at the same time; a database uniqueness constraint and transaction/locking strategy are recommended.
- CSRF protection and robust file-content validation are not yet implemented.

# TransactionSim Pro - Local Integration Guide

This workspace contains a PHP backend and a static frontend. The frontend (in `frontend/`) is updated to call backend endpoints under `backend/` (for example `backend/login.php`).

Quick steps to connect frontend -> backend locally:

1. Create the database and tables

   - Open a MySQL client and run `backend/schema.sql` (adjust permissions if needed):

     mysql -u root -p < backend/schema.sql

2. Update database credentials

   - Edit `backend/config.php` and set `$host`, `$dbname`, `$username`, and `$password` to match your environment.

3. Start a local PHP server from the project root (Windows PowerShell):

   php -S localhost:8000 -t .

   This will serve files; then open `http://localhost:8000/frontend/deepseek_html_20251015_e01843.html` in your browser.

4. Test endpoints

   - Login: POST JSON to `http://localhost:8000/backend/login.php` with { email, password }
   - Register: POST JSON to `http://localhost:8000/backend/register.php` with { name, email, password }
   - Transactions: POST JSON to `http://localhost:8000/backend/transactions.php` with { action: 'create'|'get_user'|'get_all', ... }

Notes and caveats

- Passwords are hashed using `password_hash()` on registration and verified with `password_verify()` on login.
- CORS headers are already set in `backend/config.php` for local testing.
- This is a minimal integration. For production, add CSRF protection, input validation, HTTPS, and stronger auth/session handling.

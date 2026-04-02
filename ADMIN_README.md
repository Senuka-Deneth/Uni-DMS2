# Admin Panel Overview

## Folder structure
- `admin/` — all admin-facing pages (`dashboard.php`, CRUD pages, and auth entry points).
  - `admin/includes/` — shared header/footer, flash helper, session bootstrapping, and authentication guard.
  - `admin/js/admin.js` — lightweight confirmation and upload helpers.
- `uploads/` — destination for all uploaded logos and images referenced by the admin panel.
- `css/admin.css` — admin-specific layout and table styles that layer on top of the existing `css/style.css` theme.
- `admin_schema.sql` — schema/migration script that defines the richer university data model, admin user, and seed data.

## Setup
1. Run `admin_schema.sql` against the `uni_dms` database to build the extended schema and sample rows.
2. Update `.env` (used by `includes/config.php` + `includes/db.php`) with the proper database credentials.
3. Point your web server at the project root so that the admin section is reachable at `/admin/`.
4. Ensure the `uploads/` directory is writable by PHP so logo uploads succeed.

## Admin access
- Username: `admin`
- Password: `Admin#1234`
- Visit `/admin/login.php` to authenticate. Unauthenticated users are redirected back to this login page.
- Once logged in, the sidebar links provide entry points to the dashboard, universities, degrees, extracurricular activities, and logout.

## Notes
- All admin pages reuse the shared `includes/db.php` (with SSL) and the glassmorphic look from `css/style.css`, so the admin panel feels cohesive with the public UI.
- Forms validate client-side through HTML5 attributes plus a small `admin/js/admin.js`, and they also set flash messages for server feedback.

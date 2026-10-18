# Diary_System_WUT (Target for UOPZ Function Hooking Experiment)

WUT (Web Under Test) Diary System is a test web application built for PHP UOPZ library function-hooking instrumentation experiments.
It is developed with PHP and MySQL, packaged and deployed in a Docker container.
The main purpose of this application is to generate various SQL statements during runtime, which can be captured and collected by our instrumentation tool based on UOPZ function hooks.

## 1. Original WUT (No Instrumentation)

### Features

This system implements role-based access control with two user roles: **ordinary user** and **administrator**.
They have separated login portals and isolated permission boundaries.

#### Authentication

PHP sessions maintain login status. Unauthenticated visitors accessing the homepage will be redirected to the login page.

- Ordinary users log in via the normal-user login page. Admin accounts are blocked here.
- Administrators log in through the dedicated admin login page. Normal user accounts cannot log in via this portal.
- The system updates each user's last login timestamp after every successful login.

#### Normal User Capabilities

- Register new accounts and log in.
- Create diary entries and mark diaries as **public** or **private**.
- Permission rule: users can view their own diaries and other users' public diaries, but cannot view others' private diaries.
- Post comments under public diaries.
- Access cookie & session management panel to inspect HTTP cookies and session state.

#### Administrator Capabilities (Admin Panel)

The admin panel is only accessible to admin accounts.

- List all registered users, showing username, role and last login time.
- User operations:
  - Edit username and user role.
  - One-click password reset for target users.
  - Delete user accounts. Deletion cascades to remove all related diaries and comments.
  - The system prevents deletion of the currently logged-in admin to avoid backend lockout.
- View all diaries in the system, including all private and public diaries of every user.

#### Homepage

After login, the homepage shows current server time, system introduction and all public diaries.
Only administrators can view the full user list on the homepage; ordinary users cannot see this list.

---

## 2. UOPZ Instrumented Version (Function Hook)

The instrumented version adds runtime SQL capture **without modifying the original business source code**.
It uses the UOPZ extension to hook PDO database methods and dynamically capture executed SQL statements.

### Principle

The hook script is automatically loaded via PHP `auto_prepend_file`.
`uopz_set_hook()` hooks `PDO::prepare` and `PDO::exec`.
When the web app runs database operations, the hook captures raw SQL queries and writes them to a log file.
The original business logic remains completely unchanged.

### How to enable instrumentation

1. Enable the UOPZ extension in the PHP environment.
2. Configure `auto_prepend_file` in `php.ini` to load the instrumentation script automatically.
3. All SQL queries from WUT will be intercepted and saved to `/tmp/sql_hook.log`.

## Deployment

1. Build and launch the Docker container with PHP + MySQL.
2. Compile and enable the UOPZ extension inside the container.
3. Initialize MySQL database tables.
4. Configure `php.ini` to load the UOPZ hook script for instrumentation.
5. Visit the web service and trigger database operations for SQL collection experiments.

## Notes

- This application is built purely for academic research and security instrumentation experiments.
- Do NOT deploy this application on public production networks.

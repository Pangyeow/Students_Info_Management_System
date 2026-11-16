# SIMS CICI: Student Information Management System

A web-based CRUD application for managing students, courses, and grades, built with **PHP**, **MySQL**, **HTML**, **CSS**, and **Bootstrap 5**.

**Live demo:** maniellalocsin.infinityfreeapp.com

---

## Features

### Core CRUD
| Operation | Students | Courses | Grades |
|---|---|---|---|
| **Create** | Add Student page and modal (`add.php`) | Add Course modal | Set on create |
| **Read** | Paginated, searchable, filterable list + details modal | Course directory with enrollment counts | Grades table |
| **Update** | Edit Student page (`edit.php`) | Edit Course modal | Edit Grade modal |
| **Delete** | Delete with confirmation (`delete.php`) | Delete with confirmation (blocked while students are assigned) | — |

### Extras
- **Dashboard:** total students, enrolled count, number of courses, new students this month, recent students, and enrollment distribution by course.
- **Reports:** enrollment rate and year-level breakdown, with a printable layout.
- **Search and filters:** search by ID, name, email, course, or major; filter by course and year level.
- **Automatic academic standing:** typing a GPA (1.00–5.00 or 0–100 scale) suggests the matching standing (Dean's Lister, Good Standing, Passed, Failed).
- **Light and dark theme toggle:** your choice is remembered in the browser.
- **Responsive layout:** sidebar on desktop, offcanvas hamburger menu on tablet and mobile, scrollable tables.
- **Safe deletes:** delete actions are POST-only and need confirmation in a modal.
- **Self-setting-up database:** `db_connect.php` creates any missing tables and adds sample data on first run.

---

## Tech Stack
- **Front end:** HTML5, CSS3, Bootstrap 5.3, Bootstrap Icons
- **Back end:** PHP 8 (mysqli with prepared statements)
- **Database:** MySQL / MariaDB (InnoDB)
- **Hosting:** InfinityFree
- **Local development:** XAMPP

---

## Database Design

Database: `sims_db` (see [`database.sql`](database.sql))

### `courses`
| Field | Type | Notes |
|---|---|---|
| `id` | INT, AUTO_INCREMENT | **Primary key** |
| `course_code` | VARCHAR(20) | UNIQUE, e.g. `BSIT` |
| `course_name` | VARCHAR(100) | Full program name |

### `students`
| Field | Type | Notes |
|---|---|---|
| `id` | INT, AUTO_INCREMENT | **Primary key** |
| `student_id` | VARCHAR(20) | UNIQUE school ID, e.g. `2026-0101` |
| `first_name` | VARCHAR(50) | Required |
| `last_name` | VARCHAR(50) | Required |
| `email` | VARCHAR(100) | Required |
| `course_id` | INT | **Foreign key** → `courses.id` |
| `major` | VARCHAR(100) | Optional |
| `year_level` | TINYINT | 1–4 |
| `status` | VARCHAR(20) | Enrolled / Inactive / Graduated |
| `gpa` | DECIMAL(5,2) | Grade or GPA |
| `remarks` | VARCHAR(100) | Academic standing |
| `created_at` | TIMESTAMP | Set automatically |

**Relationship:** one course has many students (`students.course_id` → `courses.id`, `ON UPDATE CASCADE`, `ON DELETE RESTRICT`). A course can't be deleted while students are still assigned to it.

---

## Project Structure
```
sims/
├── index.php           # Main page: Dashboard, Students, Courses, Grades, Reports tabs + course/grade actions
├── add.php             # Add student (INSERT)
├── edit.php            # Edit student (UPDATE)
├── delete.php          # Delete student (DELETE, POST only)
├── dashboard.php       # Redirects to the Dashboard tab
├── db_connect.php      # Database connection, schema setup, helper functions
├── database.sql        # Database export (tables + sample data)
├── includes/
│   ├── header.php      # <head>, sidebar navigation, top bar
│   └── footer.php      # Modals, Bootstrap JS, page scripts
└── assets/css/
    └── style.css       # Custom theme and responsive styles
```

---

## Running Locally (XAMPP)
1. Install [XAMPP](https://www.apachefriends.org/) and start **Apache** and **MySQL**.
2. Copy this folder to `C:\xampp\htdocs\sims`.
3. Open `http://localhost/phpmyadmin`, create a database named **`sims_db`**, and import `database.sql`.
4. Check the credentials in `db_connect.php` (XAMPP default: `localhost`, `root`, no password).
5. Visit `http://localhost/sims/`.

## Deploying to InfinityFree
1. Create a hosting account at [infinityfree.net](https://www.infinityfree.net).
2. In **Control Panel → MySQL Databases**, create a database and note its hostname, username, password, and database name.
3. Update `db_connect.php` with those values:
   ```php
   $host = "sqlXXX.infinityfree.com";
   $user = "if0_XXXXXXXX";
   $pass = "your_password";
   $db   = "if0_XXXXXXXX_sims";
   ```
4. Upload all project files to `htdocs/` using the **File Manager** (or FTP).
5. Open **phpMyAdmin** from the control panel, select your database, and import `database.sql`.
6. Visit your site URL and test every CRUD operation.

> **Note:** don't commit your real hosting password to a public repository.

---

## Author
Ma.Maniella Locsin BSIT 3A-SD: Final Project, Web Systems Technologies (PHP and MySQL Development)

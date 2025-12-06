# WMSU Grading System - PHP Backend API

## Quick Setup Guide

### Prerequisites
- **XAMPP** or **WAMP** installed (with PHP 7.4+ and MySQL)
- Or PHP CLI with MySQL

### Setup Steps

#### 1. Start Your Server

**For XAMPP:**
1. Open XAMPP Control Panel
2. Start **Apache** and **MySQL**

**For WAMP:**
1. Open WAMP
2. Ensure both Apache and MySQL are running (green icon)

#### 2. Copy Backend Files

Copy the entire `backend` folder to your server's web directory:

- **XAMPP:** `C:\xampp\htdocs\backend`
- **WAMP:** `C:\wamp64\www\backend`

Or create a symbolic link:
```powershell
# For XAMPP
New-Item -ItemType Junction -Path "C:\xampp\htdocs\backend" -Target "C:\Users\mahad\OneDrive\Documents\grading-system-app\backend"
```

#### 3. Create Database

1. Open **phpMyAdmin**: http://localhost/phpmyadmin
2. Click **Import** tab
3. Choose file: `backend/database/schema.sql`
4. Click **Go** to execute

Or via command line:
```bash
mysql -u root -p < backend/database/schema.sql
```

#### 4. Configure Database Connection

Edit `backend/config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'wmsu_grading');
define('DB_USER', 'root');
define('DB_PASS', '');  // Add your MySQL password if set
```

#### 5. Test the API

Open in browser: http://localhost/backend

You should see:
```json
{
  "success": true,
  "message": "API is running",
  "data": {
    "name": "WMSU Grading System API",
    "version": "1.0.0",
    "status": "running"
  }
}
```

#### 6. Update React Native App

Edit `src/constants/Config.ts`:
```typescript
BASE_URL: 'http://192.168.254.108/backend',
```
(Use your computer's IP address)

---

## Test Credentials

| Field | Value |
|-------|-------|
| Student ID | `2024-00001` |
| Password | `password123` |

---

## API Endpoints

### Authentication
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/auth/login` | Login with student_id and password |
| POST | `/auth/logout` | Logout (requires token) |
| POST | `/auth/refresh` | Refresh access token |
| GET | `/auth/profile` | Get current user profile |
| POST | `/auth/change-password` | Change password |

### Grades
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/grades` | List all grades |
| GET | `/grades/subject/{id}` | Get grade for subject |
| GET | `/grades/gwa` | Get GWA summary |
| GET | `/grades/summary` | Get grade distribution |

### Subjects
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/subjects` | List enrolled subjects |
| GET | `/subjects/{id}` | Get subject details |
| GET | `/subjects/schedule` | Get weekly schedule |

### Attendance
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/attendance` | List attendance records |
| GET | `/attendance/subject/{id}` | Get attendance by subject |
| GET | `/attendance/summary` | Get attendance summary |

### Notifications
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/notifications` | List notifications |
| PUT | `/notifications/{id}/read` | Mark as read |
| PUT | `/notifications/read-all` | Mark all as read |

### Semesters
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/semesters` | List all semesters |
| GET | `/semesters/current` | Get current semester |

---

## Folder Structure

```
backend/
├── index.php              # Main router
├── config/
│   ├── config.php         # App configuration
│   └── database.php       # Database connection
├── classes/
│   └── BaseController.php # Base controller class
├── controllers/
│   ├── AuthController.php
│   ├── StudentController.php
│   ├── GradeController.php
│   ├── SubjectController.php
│   ├── AttendanceController.php
│   ├── NotificationController.php
│   └── SemesterController.php
├── helpers/
│   ├── Auth.php           # JWT authentication
│   └── Response.php       # JSON response helper
└── database/
    └── schema.sql         # Database schema + sample data
```

---

## Testing with cURL

**Login:**
```bash
curl -X POST http://localhost/backend/auth/login \
  -H "Content-Type: application/json" \
  -d '{"student_id": "2024-00001", "password": "password123"}'
```

**Get Grades (with token):**
```bash
curl http://localhost/backend/grades \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## Troubleshooting

### CORS Issues
The `index.php` already includes CORS headers. If you still have issues, check your Apache/PHP configuration.

### 404 Errors
Make sure Apache's `mod_rewrite` is enabled and `.htaccess` is allowed.

### Database Connection Failed
- Check MySQL is running
- Verify credentials in `database.php`
- Make sure database `wmsu_grading` exists

### "Class not found" Errors
- Check file paths are correct
- File names must match class names exactly (case-sensitive on Linux)

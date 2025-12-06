# WMSU Grading System App

A modern React Native Expo application for Western Mindanao State University's student grading and monitoring system. Built with TypeScript, featuring an Apple-inspired UI design with full dark/light mode support.

![Expo SDK](https://img.shields.io/badge/Expo-SDK%2054-000020?style=flat-square&logo=expo)
![React Native](https://img.shields.io/badge/React%20Native-0.81-61DAFB?style=flat-square&logo=react)
![TypeScript](https://img.shields.io/badge/TypeScript-5.9-3178C6?style=flat-square&logo=typescript)

## 📱 Features

### Core Functionality
- **Dashboard** - Overview with GWA stats, semester summary, and recent grades
- **Grades Management** - View grades by semester with detailed breakdowns
- **Subjects** - Current enrolled subjects with schedules
- **Attendance Monitoring** - Track class attendance with calendar view
- **Profile & Settings** - User profile management and app preferences

### UI/UX Features
-  **WMSU Crimson Theme** - Primary color palette based on university identity
-  **Dark/Light Mode** - Full theming system with smooth transitions
-  **Apple-Inspired Design** - Modern, clean interface following iOS design patterns
-  **Accessible Components** - Built with accessibility in mind

### Technical Features
-  **Secure Authentication** - Token-based auth with secure storage
-  **REST API Integration** - Ready for vanilla PHP backend connection
-  **Persistent State** - Zustand with AsyncStorage persistence
-  **File-based Routing** - Expo Router for intuitive navigation

---

##  Folder Structure

```
grading-system-app/
├── COPY TO XAMPP/               # Backend PHP API (copy to XAMPP htdocs)
│   └── backend/                 # PHP REST API files
│       ├── .htaccess            # Apache rewrite rules
│       ├── index.php            # API entry point
│       ├── README.md            # Backend documentation
│       ├── config/              # Configuration files
│       │   ├── config.php       # App configuration (JWT secret, etc.)
│       │   └── database.php     # Database connection settings
│       ├── database/            # Database files
│       │   └── schema.sql       # Database schema (import this)
│       ├── classes/             # Base classes
│       │   └── BaseController.php
│       ├── helpers/             # Helper utilities
│       │   ├── Auth.php         # JWT authentication helper
│       │   └── Response.php     # JSON response formatter
│       ├── controllers/         # API controllers
│       │   ├── AuthController.php
│       │   ├── GradeController.php
│       │   ├── SubjectController.php
│       │   ├── AttendanceController.php
│       │   ├── NotificationController.php
│       │   ├── StudentController.php
│       │   ├── InstructorController.php
│       │   ├── CourseController.php
│       │   ├── EnrollmentController.php
│       │   ├── ScheduleController.php
│       │   └── SemesterController.php
│       └── admin/               # Admin panel (optional)
│
├── app/                          # Expo Router screens (file-based routing)
│   ├── _layout.tsx              # Root layout with providers
│   ├── index.tsx                # Entry redirect logic
│   ├── sign-in.tsx              # Login screen
│   └── (app)/                   # Authenticated routes (protected)
│       ├── _layout.tsx          # Auth check wrapper
│       ├── settings.tsx         # Settings screen
│       ├── (tabs)/              # Bottom tab navigation
│       │   ├── _layout.tsx      # Tab bar configuration
│       │   ├── dashboard.tsx    # Home dashboard
│       │   ├── grades.tsx       # Grades list
│       │   ├── subjects.tsx     # Enrolled subjects
│       │   ├── monitoring.tsx   # Attendance tracking
│       │   └── profile.tsx      # User profile
│       ├── grades/
│       │   └── [subjectId].tsx  # Grade detail (dynamic route)
│       └── subjects/
│           └── [id].tsx         # Subject detail (dynamic route)
│
├── src/                         # Source code
│   ├── components/              # Reusable components
│   │   └── ui/                  # UI component library
│   │       ├── index.ts         # Barrel exports
│   │       ├── ThemedView.tsx   # Theme-aware View wrapper
│   │       ├── ThemedText.tsx   # Typography with variants
│   │       ├── Button.tsx       # Button (5 variants)
│   │       ├── Input.tsx        # Form input with validation
│   │       ├── Card.tsx         # Card container
│   │       ├── Avatar.tsx       # User avatar
│   │       ├── Badge.tsx        # Status badges
│   │       ├── Header.tsx       # Screen headers
│   │       ├── LoadingSpinner.tsx
│   │       ├── EmptyState.tsx   # Empty list placeholder
│   │       └── Divider.tsx      # Separator line
│   │
│   ├── constants/               # App configuration
│   │   ├── index.ts             # Barrel exports
│   │   ├── Colors.ts            # WMSU color palette (light/dark)
│   │   ├── Layout.ts            # Spacing, sizing, breakpoints
│   │   └── Config.ts            # API endpoints, timeouts
│   │
│   ├── context/                 # React Context providers
│   │   ├── index.ts
│   │   └── ThemeContext.tsx     # Theme provider with toggle
│   │
│   ├── services/                # External integrations
│   │   └── api/                 # REST API layer
│   │       ├── index.ts
│   │       ├── client.ts        # Axios instance with interceptors
│   │       ├── auth.ts          # Login, logout, refresh token
│   │       ├── grades.ts        # Grades API calls
│   │       ├── subjects.ts      # Subjects API calls
│   │       ├── attendance.ts    # Attendance API calls
│   │       ├── notifications.ts # Notifications API
│   │       └── students.ts      # Student profile API
│   │
│   ├── store/                   # Zustand state management
│   │   ├── index.ts
│   │   ├── useAuthStore.ts      # Auth state (user, token)
│   │   ├── useSettingsStore.ts  # App settings (theme, notifications)
│   │   ├── useGradesStore.ts    # Grades data cache
│   │   ├── useSubjectsStore.ts  # Subjects data cache
│   │   └── useAttendanceStore.ts # Attendance records
│   │
│   └── types/                   # TypeScript definitions
│       ├── index.ts
│       ├── auth.ts              # User, credentials, tokens
│       ├── grades.ts            # Grade, GWASummary
│       ├── subjects.ts          # Subject, Schedule, Instructor
│       ├── attendance.ts        # AttendanceRecord, status
│       ├── notifications.ts     # Notification types
│       └── common.ts            # ApiResponse, ThemeMode
│
├── assets/                      # Static assets
│   ├── images/
│       ├── icon.png
│       ├── splash-icon.png
│       └── adaptive-icon.png
│
├── app.json                     # Expo configuration
├── tsconfig.json               # TypeScript config
├── package.json                # Dependencies
└── README.md                   # This file
```

---

## 🚀 Setup & Installation

### Prerequisites
- **Node.js** >= 18.x (LTS recommended)
- **npm** or **yarn**
- **Expo Go** app on your mobile device (for testing)
- **VS Code** with Expo Tools extension (recommended)
- **XAMPP** (for backend PHP API)
- **MySQL/MariaDB** (included with XAMPP)

### Installation Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd grading-system-app
   ```

2. **Setup Backend API**
   - Copy the entire `COPY TO XAMPP/backend` folder to your XAMPP `htdocs` directory
   - The backend should be at: `C:\xampp\htdocs\backend\`
   - Import `backend/database/schema.sql` into MySQL via phpMyAdmin
   - Update database credentials in `backend/config/database.php`
   - Configure JWT secret in `backend/config/config.php`
   - Start Apache and MySQL in XAMPP Control Panel
   - Backend will be accessible at `http://localhost/backend`

3. **Install dependencies**
   ```bash
   npm install --legacy-peer-deps
   ```
   > Note: `--legacy-peer-deps` is required due to React 19 peer dependency conflicts

4. **Start the development server**
   ```bash
   npx expo start
   ```

5. **Run on your device**
   - Scan the QR code with Expo Go (Android) or Camera app (iOS)
   - Or press `a` for Android emulator / `i` for iOS simulator

### Environment Configuration

Update the API base URL in `src/constants/Config.ts` to point to your backend:

```typescript
export const API_CONFIG = {
  BASE_URL: 'http://192.168.x.x/backend', // Replace with your local IP for testing
  TIMEOUT: 30000,
};
```

> **Note**: For testing on a physical device, use your computer's local IP address (e.g., `http://192.168.1.100/backend`) instead of `localhost`.

---

## Backend Usage Guide

### Database Setup

1. **Open phpMyAdmin**
   - Go to `http://localhost/phpmyadmin`
   - Create a new database named `wmsu_grading`

2. **Import Schema**
   - Select the `wmsu_grading` database
   - Click "Import" tab
   - Choose file: `backend/database/schema.sql`
   - Click "Go" to import

3. **Configure Database Connection**
   
   Edit `backend/config/database.php`:
   ```php
   <?php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'wmsu_grading');
   define('DB_USER', 'root');          // Your MySQL username
   define('DB_PASS', '');              // Your MySQL password
   define('DB_CHARSET', 'utf8mb4');
   ```

4. **Configure JWT Secret**
   
   Edit `backend/config/config.php`:
   ```php
   <?php
   define('JWT_SECRET', 'your-secret-key-here');  // Change this!
   define('ACCESS_TOKEN_EXPIRY', 3600);           // 1 hour
   define('REFRESH_TOKEN_EXPIRY', 2592000);       // 30 days
   ```

### Testing the Backend

1. **Test API Endpoint**
   ```bash
   # Open browser or use curl
   curl http://localhost/backend
   ```
   
   Expected response:
   ```json
   {
     "success": true,
     "message": "WMSU Grading System API",
     "version": "1.0.0"
   }
   ```

2. **Test Login Endpoint**
   ```bash
   curl -X POST http://localhost/backend/auth/login \
     -H "Content-Type: application/json" \
     -d '{"student_id":"2024-00001","password":"password123"}'
   ```

### Default Test Accounts

The schema includes sample student accounts:

| Student ID | Password | Name | Course |
|------------|----------|------|--------|
| `2024-00001` | `password123` | Juan Dela Cruz | BSIT |
| `2021-05289` | `password123` | Anas Mohammad Demonteverde | BSIT |
| `2021-00700` | `password123` | Sophia Tolosa | BSIT |


### API Request Examples

**Login:**
```bash
POST http://localhost/backend/auth/login
Content-Type: application/json

{
  "student_id": "2024-00001",
  "password": "password123"
}
```

**Get Grades (Authenticated):**
```bash
GET http://localhost/backend/grades
Authorization: Bearer <your_token_here>
```

**Get Subject Details:**
```bash
GET http://localhost/backend/subjects/3
Authorization: Bearer <your_token_here>
```

### Common Issues & Solutions

**Issue: 404 Not Found**
- Check if `.htaccess` is in the backend folder
- Ensure Apache `mod_rewrite` is enabled
- Verify backend is in correct path: `C:\xampp\htdocs\backend\`

**Issue: CORS Error**
- The backend includes CORS headers by default
- Check `index.php` for CORS configuration

**Issue: Database Connection Failed**
- Verify MySQL is running in XAMPP
- Check credentials in `config/database.php`
- Ensure database `wmsu_grading` exists

**Issue: Token Invalid**
- Check JWT_SECRET in `config/config.php`
- Token may have expired (default: 1 hour)
- Use refresh token endpoint to get new access token

### Finding Your Local IP

**Windows:**
```bash
ipconfig
# Look for "IPv4 Address" under your network adapter
```

**Mac/Linux:**
```bash
ifconfig
# Look for "inet" under your network adapter
```

Example: `192.168.1.100` → Use `http://192.168.1.100/backend` in app config

---

##  Theming System

### WMSU Color Palette

| Color | Hex | Usage |
|-------|-----|-------|
| Primary (Crimson) | `#8B0000` | Main brand color, buttons, accents |
| Primary Dark | `#6B0000` | Pressed states |
| Primary Muted | `#8B000015` | Backgrounds, badges |
| Secondary (Gold) | `#DAA520` | Secondary accents |
| Success | `#34C759` | Positive states |
| Warning | `#FF9500` | Alerts, cautions |
| Error | `#FF3B30` | Errors, destructive |
| Info | `#007AFF` | Informational |

### Theme Toggle

The app supports system theme detection and manual override:

```typescript
import { useTheme } from '../context/ThemeContext';

function MyComponent() {
  const { mode, toggleTheme, colors } = useTheme();
  // mode: 'light' | 'dark'
  // colors: themed color palette
}
```

---

## API Integration

### Backend Requirements

The app expects a vanilla PHP REST API with the following characteristics:
- **Authentication**: Token-based (Bearer tokens)
- **Response Format**: JSON
- **Base URL**: Configurable in `Config.ts`

### API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/auth/login` | POST | User authentication |
| `/auth/logout` | POST | Invalidate token |
| `/auth/refresh` | POST | Refresh access token |
| `/students/profile` | GET | Get student profile |
| `/students/profile` | PUT | Update profile |
| `/grades` | GET | List all grades |
| `/grades/semester/{id}` | GET | Grades by semester |
| `/grades/subject/{id}` | GET | Grade detail |
| `/grades/gwa` | GET | GWA summary |
| `/subjects` | GET | Enrolled subjects |
| `/subjects/{id}` | GET | Subject detail |
| `/subjects/current-semester` | GET | Current semester subjects |
| `/attendance` | GET | Attendance records |
| `/attendance/subject/{id}` | GET | Subject attendance |
| `/notifications` | GET | Notifications list |
| `/notifications/{id}/read` | PUT | Mark as read |



### Authentication Flow

1. User submits credentials on login screen
2. API returns `accessToken` and `refreshToken`
3. Tokens stored in `expo-secure-store`
4. Axios interceptor adds `Authorization: Bearer <token>` to requests
5. On 401 response, token is cleared and user redirected to login

---

## Component Library

### Available Components

| Component | Props | Description |
|-----------|-------|-------------|
| `ThemedView` | `style` | Theme-aware View wrapper |
| `ThemedText` | `variant`, `color`, `weight`, `align` | Typography with 10 variants |
| `Button` | `variant`, `size`, `loading`, `disabled` | 5 button styles |
| `Input` | `label`, `error`, `secureTextEntry`, `leftIcon` | Form input |
| `Card` | `variant`, `padding`, `onPress` | Container card |
| `Avatar` | `size`, `name`, `imageUrl` | User avatar |
| `Badge` | `label`, `variant`, `size` | Status badge |
| `Header` | `title`, `showBack`, `rightAction` | Screen header |
| `LoadingSpinner` | `size`, `message` | Loading indicator |
| `EmptyState` | `icon`, `title`, `message`, `action` | Empty placeholder |
| `Divider` | `style` | Separator line |

### Typography Variants

- `largeTitle`, `title1`, `title2`, `title3`
- `headline`, `body`, `callout`, `subheadline`
- `footnote`, `caption1`, `caption2`

---

## Features Completed

- [x] Project initialization with Expo SDK 54
- [x] TypeScript configuration
- [x] File-based routing with Expo Router
- [x] Complete folder structure
- [x] WMSU color palette (light/dark modes)
- [x] Layout constants and spacing system
- [x] API configuration and endpoints
- [x] TypeScript type definitions
- [x] Axios API client with interceptors
- [x] API service layer (auth, grades, subjects, attendance, notifications)
- [x] Zustand stores with persistence
- [x] Theme context provider
- [x] 11 reusable UI components
- [x] Login/Sign-in screen
- [x] Dashboard screen with stats
- [x] Grades list with semester picker
- [x] Grade detail screen
- [x] Subjects list with schedule
- [x] Subject detail screen
- [x] Attendance monitoring with calendar
- [x] Profile screen
- [x] Settings screen with theme toggle


---



## Key Dependencies

| Package | Version | Purpose |
|---------|---------|---------|
| expo | 54.0.0 | Core Expo SDK |
| expo-router | 6.0.15 | File-based navigation |
| react | 19.1.0 | React framework |
| react-native | 0.81.5 | Native rendering |
| zustand | 5.0.8 | State management |
| axios | 1.13.2 | HTTP client |
| expo-secure-store | 15.0.7 | Secure token storage |
| react-hook-form | 7.66.1 | Form handling |
| zod | 4.1.13 | Schema validation |
| @expo/vector-icons | 14.1.0 | Icon library |
| react-native-reanimated | 3.17.5 | Animations |
| expo-linear-gradient | 14.1.4 | Gradient backgrounds |

---

## License

This project is proprietary software developed for partial fulfillment of requirements in the App. Dev. and Emerging Tech. subject taught by Sir Saavedra, College of Computing Studies, Western Mindanao State University.

---

## Contributors

- Demonteverde, Anas Mohammad E. (BSIT 3C)
- Tolosa, Sophia E. (BSIT 3C)

---

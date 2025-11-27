# WMSU Grading System App - Presentation Script

## Video Recording Script for Professor

---

### INTRODUCTION (30 seconds)

**[Show splash screen]**

"Good day! This is a demonstration of the WMSU Grading System mobile application. This app was built using React Native with Expo, featuring a complete full-stack solution with a PHP REST API backend. Students can view their grades, subjects, attendance, and academic information on their mobile devices, while administrators can manage all data through a web-based admin panel."

---

### ADMIN DASHBOARD (1 minute 30 seconds)

**[Open browser: http://localhost/backend/admin/]**

"Before showing the mobile app, let me demonstrate the Admin Dashboard - a web-based management panel for administrators.

**[Enter password: admin123]**

This dashboard allows complete backend management:

1. **Dashboard Overview** - Shows statistics: total students, subjects, enrollments, and grades posted.

2. **Students Management** - Add, edit, or delete student accounts. Passwords are securely hashed using PHP's password_hash function.

**[Navigate to Students page]**

3. **Subjects Management** - Create subjects with codes, units, lecture hours, and lab hours.

4. **Enrollments** - Enroll students in subjects for specific semesters. This automatically creates grade entries.

**[Navigate to Grades page]**

5. **Grades Management** - Enter prelim, midterm, prefinal, and final grades. The system auto-calculates remarks (Passed/Failed) based on the final grade.

6. **Semesters** - Manage academic periods and set the current semester.

Any changes made here will immediately reflect in the mobile application. This is powered by:
- PHP with PDO for secure database operations
- MySQL database
- Bootstrap 5 for responsive UI
- Session-based authentication"

---

### LOGIN SCREEN (45 seconds)

**[Show mobile app login screen]**

"Now let's look at the mobile application. Starting with the login screen, we have a clean, Apple-inspired design following the WMSU crimson color scheme. The app uses:

- **React Native's KeyboardAvoidingView** - This automatically adjusts the layout when the keyboard appears, preventing it from covering the input fields.
- **Secure authentication** using JWT tokens stored with Expo SecureStore
- **Native Fetch API** for HTTP requests - optimized for Expo Go compatibility

Let me login with a student account..."

**[Enter credentials: 2024-00001 / password123]**

"The authentication connects to our PHP REST API backend. The server returns a JWT access token and refresh token, which are securely stored on the device for subsequent API calls."

---

### DASHBOARD SCREEN (1 minute)

**[Show dashboard after login]**

"This is the Dashboard - the home screen that gives students an overview of their academic status.

**React Native Features Used:**

1. **ScrollView with RefreshControl** - Students can pull down to refresh their data from the API.

2. **SafeAreaInsets** from react-native-safe-area-context - Ensures content doesn't overlap with the phone's notch or status bar.

3. **Dynamic Greeting** - Shows 'Good Morning', 'Good Afternoon', or 'Good Evening' based on the current time.

4. **Real-time Data** - The dashboard fetches actual data from the database:
   - Current GWA calculated from grades
   - Total units enrolled
   - Number of subjects
   - Recent grades

**[Pull down to refresh]**

The useCallback hook optimizes the refresh function, preventing unnecessary re-renders."

---

### GRADES SCREEN (1 minute)

**[Navigate to Grades tab]**

"The Grades screen displays academic grades organized by semester, fetched directly from the MySQL database.

**Key React Native Features:**

1. **Semester Filter** - Dropdown to filter grades by semester using useState for local component state.

2. **Zustand State Management** - We use Zustand, a lightweight state management library, to cache grades data globally across the app.

3. **Grade Color Coding** - Different badge colors based on grade values:
   - Green (1.00-1.75): Excellent
   - Blue (2.00-2.50): Good
   - Yellow (2.75-3.00): Passing
   - Red (5.00): Failed

4. **TouchableOpacity** - Each grade card is pressable and navigates to a detailed view.

**[Tap on a grade]**

5. **Dynamic Routing with Expo Router** - Uses file-based routing at `/grades/[id].tsx`. The ID parameter is extracted using useLocalSearchParams hook."

---

### SUBJECTS SCREEN (1 minute)

**[Navigate to Subjects tab]**

"The Subjects screen shows all enrolled subjects with their schedules, fetched from the API.

**Features Demonstrated:**

1. **API Integration** - Subjects are fetched using the native Fetch API with JWT authentication headers.

2. **Loading States** - Shows ActivityIndicator while data is being fetched, with proper error handling using try-catch.

3. **EmptyState Component** - A reusable component that displays when there's no data or an error occurs.

**[Show subject list]**

4. **Subject Cards** - Display subject code, name, units, schedule, and room information.

5. **Themed Components** - All components automatically adapt to light or dark mode using React Context API.

**[Tap on a subject]**

6. **Detail Navigation** - Uses Expo Router's `router.push()` with dynamic route parameters."

---

### SUBJECT DETAIL SCREEN (45 seconds)

**[Show subject detail]**

"The Subject Detail screen provides comprehensive information fetched from the backend:

- Subject code, name, and description
- Credit units (lecture and lab hours)
- Instructor details
- Weekly schedule with room assignments

**React Native Features:**

1. **useLocalSearchParams** - Retrieves the subject ID from the URL to fetch the correct data from the API.

2. **Nested ScrollView** - Allows smooth scrolling through long content with multiple sections.

3. **Ionicons** - We use @expo/vector-icons for consistent iconography throughout the app.

4. **Conditional Rendering** - Shows loading spinner while fetching, error state if failed, or content when successful."

---

### MONITORING/ATTENDANCE SCREEN (45 seconds)

**[Navigate to Monitoring tab]**

"The Monitoring screen tracks student attendance with visual indicators.

**Key Features:**

1. **Attendance Summary** - Shows attendance rate calculated from database records.

2. **Color-coded Status** - 
   - Green: Present
   - Red: Absent  
   - Yellow: Late
   - Blue: Excused

3. **Subject-wise Breakdown** - Attendance grouped by subject for easy tracking.

**Data Flow:**
Attendance data flows from MySQL → PHP API → Fetch request → Zustand store → React component."

---

### PROFILE SCREEN (30 seconds)

**[Navigate to Profile tab]**

"The Profile screen displays student information stored in the database:
- Student ID and email
- Course and year level
- Full name with proper formatting

**React Native Features:**

1. **Avatar Component** - Shows initials if no profile image is set, calculated from first and last name.

2. **useAuthStore** - Zustand store that persists user data across app sessions using AsyncStorage."

---

### SETTINGS SCREEN (45 seconds)

**[Navigate to Settings]**

"The Settings screen demonstrates several important features:

1. **Dark Mode Toggle** - Uses React Context (ThemeContext) to propagate theme changes throughout the entire app instantly.

**[Toggle dark mode]**

Notice how every screen updates simultaneously - this is the power of React's Context API combined with the useTheme custom hook.

2. **Switch Component** - Native React Native Switch component styled with the WMSU crimson color scheme.

3. **Notification Settings** - Toggle switches for push notifications and email notifications.

4. **Logout Functionality** - Clears the JWT tokens from SecureStore using Expo's SecureStore.deleteItemAsync and navigates back to login using router.replace()."

---

### TECHNICAL ARCHITECTURE (1 minute 30 seconds)

**[Can show code or diagrams]**

"Let me explain the complete technical architecture:

**Frontend Stack (React Native):**
- React Native 0.81.5 with Expo SDK 54
- TypeScript 5.9 for type safety
- Expo Router 6.0 for file-based navigation
- Zustand 5.0 for state management
- Native Fetch API for HTTP requests
- Expo SecureStore for JWT token storage

**Backend Stack (PHP):**
- Vanilla PHP 7.4+ REST API
- MySQL database (wmsu_grading)
- Custom JWT implementation (HS256)
- PDO for database operations
- Apache with mod_rewrite

**Admin Dashboard:**
- PHP with Bootstrap 5
- Session-based authentication
- Full CRUD for all entities

**Project Structure:**
```
/app                    - Expo Router screens
  /(app)/(tabs)        - Tab navigation screens
  /grades/[id].tsx     - Dynamic grade detail
  /subjects/[id].tsx   - Dynamic subject detail
/src
  /components/ui       - Reusable UI components
  /services/api        - API service layer
  /store              - Zustand state stores
  /context            - React Context providers
  /constants          - Colors, Layout, Config
```

**API Endpoints:**
- POST /auth/login - Authentication
- GET /subjects - Enrolled subjects
- GET /grades - Student grades
- GET /attendance - Attendance records

**Key React Native Concepts Demonstrated:**
1. Functional Components with Hooks (useState, useEffect, useCallback)
2. Custom Hooks (useTheme, useLocalSearchParams)
3. Context API for global state (theming)
4. Zustand for persistent state management
5. File-based routing with dynamic segments
6. Secure storage for sensitive data
7. Platform-agnostic UI components"

---

### FULL-STACK DATA FLOW DEMO (30 seconds)

**[Show both admin panel and mobile app side by side if possible]**

"Let me demonstrate the full-stack integration:

1. In the Admin Dashboard, I'll update a student's grade...

**[Update a grade in admin panel]**

2. Now in the mobile app, I'll pull to refresh...

**[Pull to refresh on mobile]**

3. The updated grade appears instantly!

This demonstrates the complete data flow:
Admin Panel → PHP API → MySQL → PHP API → Mobile App"

---

### CONCLUSION (30 seconds)

"In conclusion, this WMSU Grading System demonstrates a complete full-stack mobile application:

**Mobile App Features:**
- Clean, Apple-inspired UI with WMSU branding
- Secure JWT authentication
- Real-time data from MySQL database
- Dark mode support
- Cross-platform (iOS and Android)

**Admin Dashboard Features:**
- Complete CRUD operations for all entities
- Student, subject, enrollment, and grade management
- Semester management
- Responsive Bootstrap UI

**Technologies Mastered:**
- React Native with Expo
- TypeScript
- PHP REST API
- MySQL Database
- JWT Authentication

Thank you for watching!"

---

## Quick Reference: React Native Features Used

| Feature | Where Used |
|---------|------------|
| `useState` | Local component state (loading, error, data) |
| `useEffect` | Data fetching on mount |
| `useCallback` | Optimized refresh functions |
| `useContext` | Theme management via ThemeContext |
| `ScrollView` | All screens for scrollable content |
| `RefreshControl` | Pull-to-refresh functionality |
| `TouchableOpacity` | Pressable cards and buttons |
| `KeyboardAvoidingView` | Login form keyboard handling |
| `SafeAreaView` | Notch/status bar handling |
| `ActivityIndicator` | Loading states |
| `StyleSheet` | Performant styling |
| `Expo Router` | File-based navigation with dynamic routes |
| `useLocalSearchParams` | Extract URL parameters |
| `Expo SecureStore` | JWT token storage |
| `Zustand` | Global state management with persistence |
| `Native Fetch` | HTTP API calls with async/await |
| `React Context` | Theme propagation across app |

---

## Backend API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/auth/login` | Student authentication |
| POST | `/auth/logout` | Logout (clears session) |
| GET | `/subjects` | List enrolled subjects |
| GET | `/subjects/{id}` | Subject details |
| GET | `/grades` | List all grades |
| GET | `/grades/gwa` | GWA summary |
| GET | `/attendance` | Attendance records |
| GET | `/attendance/summary` | Attendance statistics |
| GET | `/semesters` | List semesters |
| GET | `/semesters/current` | Current semester |

---

## Admin Dashboard Pages

| Page | URL | Features |
|------|-----|----------|
| Dashboard | `/admin/` | Statistics, quick actions |
| Students | `/admin/?page=students` | CRUD for students |
| Subjects | `/admin/?page=subjects` | CRUD for subjects |
| Enrollments | `/admin/?page=enrollments` | Enroll students |
| Grades | `/admin/?page=grades` | Enter/update grades |
| Semesters | `/admin/?page=semesters` | Manage semesters |
| Courses | `/admin/?page=courses` | Manage degree programs |

---

## Suggested Video Structure

1. **0:00-0:30** - Introduction
2. **0:30-2:00** - Admin Dashboard Demo
3. **2:00-2:45** - Login Screen
4. **2:45-3:45** - Dashboard
5. **3:45-4:45** - Grades Screen
6. **4:45-5:45** - Subjects Screen
7. **5:45-6:30** - Subject Detail
8. **6:30-7:15** - Monitoring/Attendance
9. **7:15-7:45** - Profile
10. **7:45-8:30** - Settings & Dark Mode
11. **8:30-10:00** - Technical Architecture
12. **10:00-10:30** - Full-Stack Demo
13. **10:30-11:00** - Conclusion

**Total: ~10-11 minutes**

---

## Test Credentials

**Mobile App:**
- Student ID: `2024-00001`
- Password: `password123`

**Admin Dashboard:**
- URL: `http://localhost/backend/admin/`
- Password: `admin123`

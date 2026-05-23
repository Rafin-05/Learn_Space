# LearnSpace - Complete Setup Guide
## University Project | PHP + MySQL + XAMPP

---

## 📁 PROJECT FILE STRUCTURE

```
learnspace/
├── index.php                    ← Homepage (landing page)
├── database.sql                 ← Database setup file
├── assets/
│   └── css/
│       └── style.css            ← Global styles
├── includes/
│   └── db.php                   ← Database connection
├── admin/
│   ├── login.php                ← Admin login
│   ├── dashboard.php            ← Admin dashboard
│   ├── manage-users.php         ← Manage learners
│   ├── manage-instructors.php   ← Manage instructors
│   ├── courses.php              ← All courses list
│   ├── pending-courses.php      ← Approve/reject courses
│   ├── course-detail.php        ← Course detail + students
│   ├── course-action.php        ← Approve/reject handler
│   └── logout.php
├── instructor/
│   ├── register.php             ← Instructor signup
│   ├── login.php                ← Instructor login
│   ├── dashboard.php            ← Instructor dashboard
│   ├── add-course.php           ← Add new course with units/lessons
│   ├── my-courses.php           ← View all my courses
│   ├── course-view.php          ← View course detail + students
│   ├── profile.php              ← Update profile
│   └── logout.php
└── learner/
    ├── register.php             ← Learner signup
    ├── login.php                ← Learner login
    ├── dashboard.php            ← Learner dashboard
    ├── courses.php              ← Browse + search courses
    ├── course-detail.php        ← Course info + enroll
    ├── course-learn.php         ← Watch lessons + mark done
    ├── certificate.php          ← View/print certificate
    ├── my-learning.php          ← All enrolled courses
    ├── certificates.php         ← All certificates
    ├── profile.php              ← Update profile
    └── logout.php
```

---

## ✅ STEP 1 — INSTALL XAMPP

1. Download XAMPP from: https://www.apachefriends.org/download.html
2. Install with default options (Apache + MySQL + PHP)
3. Open **XAMPP Control Panel**
4. Click **Start** next to **Apache**
5. Click **Start** next to **MySQL**
6. Both should show green "Running" status

---

## ✅ STEP 2 — COPY PROJECT FILES

1. Open File Explorer
2. Navigate to: `C:\xampp\htdocs\`
3. Create a new folder named: **`learnspace`**
4. Copy ALL project files into `C:\xampp\htdocs\learnspace\`

Your folder structure should look like:
```
C:\xampp\htdocs\learnspace\
    ├── index.php
    ├── database.sql
    ├── assets\
    ├── includes\
    ├── admin\
    ├── instructor\
    └── learner\
```

---

## ✅ STEP 3 — CREATE THE DATABASE

### Method A: phpMyAdmin (Recommended)

1. Open your browser
2. Go to: **http://localhost/phpmyadmin**
3. Click **"New"** in the left sidebar
4. Type database name: `learnspace` → Click **Create**
5. Click on the `learnspace` database in the left panel
6. Click the **"Import"** tab at the top
7. Click **"Choose File"** → select `database.sql` from your project folder
8. Scroll down → Click **"Go"** / **"Import"**
9. You should see: *"Import has been successfully finished"*

### Method B: MySQL Command Line

```bash
mysql -u root -p
CREATE DATABASE learnspace;
USE learnspace;
SOURCE C:/xampp/htdocs/learnspace/database.sql;
```

---

## ✅ STEP 4 — CONFIGURE DATABASE CONNECTION

Open: `includes/db.php`

```php
define('DB_HOST', 'localhost');   // Keep as localhost
define('DB_USER', 'root');        // Default XAMPP username
define('DB_PASS', '');            // Default XAMPP password (empty)
define('DB_NAME', 'learnspace');  // Your database name
```

> ⚠️ If you set a MySQL password in XAMPP, enter it in DB_PASS

---

## ✅ STEP 5 — OPEN IN BROWSER

Open your browser and go to:
```
http://localhost/learnspace/
```

You should see the LearnSpace homepage! 🎉

---

## 🔑 STEP 6 — LOGIN CREDENTIALS

### Admin Panel
- **URL:** http://localhost/learnspace/admin/login.php
- **Username:** `admin`
- **Password:** `12345678`

### Instructor Account
- **URL:** http://localhost/learnspace/instructor/register.php
- Register a new instructor account
- Then login at: http://localhost/learnspace/instructor/login.php

### Learner Account
- **URL:** http://localhost/learnspace/learner/register.php
- Register a new learner account
- Then login at: http://localhost/learnspace/learner/login.php

---

## 🔄 STEP 7 — TEST THE FULL WORKFLOW

Follow this order to test everything:

### A) Test Admin
1. Go to http://localhost/learnspace/admin/login.php
2. Login with admin/12345678
3. Explore Dashboard, Manage Users, Manage Instructors, Courses

### B) Register an Instructor
1. Go to http://localhost/learnspace/instructor/register.php
2. Fill out the form → Submit
3. Login at http://localhost/learnspace/instructor/login.php

### C) Add a Course (Instructor)
1. Click "Add Course" in sidebar
2. Fill: Title, Description
3. Click "Add Unit" → enter unit name
4. Click "Add Lesson" → enter lesson name + a YouTube URL
   - Example URL: https://www.youtube.com/watch?v=dQw4w9WgXcQ
5. Submit the course

### D) Approve the Course (Admin)
1. Go to Admin Panel → Pending Courses
2. Click "Approve" on the course

### E) Register a Learner
1. Go to http://localhost/learnspace/learner/register.php
2. Create an account → Login

### F) Enroll in a Course (Learner)
1. Click "Browse Courses"
2. Find the approved course
3. Click "Enroll for Free"

### G) Learn & Complete (Learner)
1. Go to My Learning → Click "Continue"
2. Watch a lesson (YouTube videos embed directly!)
3. Click "Mark Unit as Done" when finished
4. Complete all units

### H) Get Certificate (Learner)
1. After all units are done → "Get Your Certificate!" button appears
2. Click to view the certificate
3. Use browser Print (Ctrl+P) to save as PDF

---

## 📋 FEATURE CHECKLIST

### ✅ Admin Panel
- [x] Login with username/password (admin/12345678)
- [x] Dashboard with statistics
- [x] View all learners → Block/Unblock users
- [x] View all instructors → Block/Unblock instructors
- [x] View all courses with student count
- [x] Approve or Reject pending courses
- [x] View course detail with enrolled students list
- [x] Logout

### ✅ Instructor Panel
- [x] Register new account
- [x] Login/Logout
- [x] Dashboard with stats (courses, students, ratings)
- [x] Add Course (Title + Description)
- [x] Add Units inside the course
- [x] Add Lessons inside units (Lesson Name + Lesson Link)
- [x] View course info (student count, average rating)
- [x] View enrolled students per course
- [x] View student reviews/ratings
- [x] Update Profile (Name, Bio, Expertise, Password)

### ✅ Learner Panel
- [x] Register new account
- [x] Login/Logout
- [x] Browse all approved courses
- [x] Search courses by title, description, or instructor name
- [x] View course detail (curriculum, instructor info, reviews)
- [x] Enroll in courses (FREE)
- [x] Watch lessons (YouTube videos embed, other links open in new tab)
- [x] View course progress (units completed / total units)
- [x] Mark each unit as "Done"
- [x] Automatic certificate generation when all units are completed
- [x] View and Print/Save certificate as PDF
- [x] View all certificates earned
- [x] Update Profile (Name, Bio, Password)

---

## 🗄️ DATABASE TABLES EXPLAINED

| Table | Purpose |
|-------|---------|
| `admins` | Admin login credentials |
| `users` | Learner accounts |
| `instructors` | Instructor accounts |
| `courses` | Course info + approval status |
| `units` | Course units (chapters) |
| `lessons` | Individual lessons with links |
| `enrollments` | Which learner enrolled in which course |
| `unit_completions` | Which units a learner has completed |
| `lesson_completions` | Individual lesson tracking |
| `ratings` | Course ratings & reviews |
| `certificates` | Issued certificates |

---

## 🛠️ TROUBLESHOOTING

### Problem: Blank white page
**Solution:** Enable PHP error display
1. Open `C:\xampp\php\php.ini`
2. Find: `display_errors = Off`
3. Change to: `display_errors = On`
4. Restart Apache in XAMPP

### Problem: "Connection failed" error
**Solutions:**
- Make sure MySQL is running in XAMPP Control Panel
- Check `includes/db.php` credentials
- Make sure the database `learnspace` was created
- Default XAMPP: user=`root`, password=`(empty)`

### Problem: Page not found (404)
**Solution:** 
- Check that files are in `C:\xampp\htdocs\learnspace\`
- URL should be `http://localhost/learnspace/`

### Problem: Login not working
**Solution:**
- Admin: Use exactly `admin` and `12345678`
- Learner/Instructor: Use password_hash — make sure you registered first
- Check that the database tables were imported correctly

### Problem: Course not showing after adding
**Solution:**
- Admin must approve the course first
- Go to Admin Panel → Pending Courses → Approve

### Problem: YouTube not embedding
**Solution:**
- Use full YouTube URL: `https://www.youtube.com/watch?v=VIDEO_ID`
- Short URLs like `youtu.be/...` also work
- Other links will show as clickable links instead

---

## 🎓 HOW CERTIFICATE WORKS

1. Learner enrolls in a course
2. Course has multiple units, each unit has lessons
3. Learner watches lessons (clicks lesson links)
4. Learner clicks **"Mark Unit as Done"** for each unit
5. When ALL units are marked done → Certificate is automatically issued
6. Certificate shows:
   - Learner's full name
   - Course title
   - Instructor name
   - Date of completion
   - Certificate ID
   - Platform signature
7. Learner can **Print** or **Save as PDF** using browser (Ctrl+P)

---

## 🔒 SECURITY NOTES (FOR PRODUCTION)

> This project is for educational/university purposes. For production:
- Use `password_hash()` for all passwords (already done for learners/instructors)
- Add CSRF protection tokens
- Sanitize all inputs with prepared statements (already done)
- Use HTTPS
- Move database credentials to environment variables

---

## 📞 QUICK ACCESS URLs

```
Homepage:           http://localhost/learnspace/
Admin Login:        http://localhost/learnspace/admin/login.php
Instructor Signup:  http://localhost/learnspace/instructor/register.php
Instructor Login:   http://localhost/learnspace/instructor/login.php
Learner Signup:     http://localhost/learnspace/learner/register.php
Learner Login:      http://localhost/learnspace/learner/login.php
```

---

*LearnSpace — University Project | Built with PHP, MySQL, HTML, CSS, JavaScript*

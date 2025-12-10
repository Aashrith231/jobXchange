# JobXchange - Internship & Job Portal

A complete PHP + MySQL web application for managing internships and job postings with three user roles: Admin, Recruiter, and Candidate.

## 🔧 Tech Stack

- **Frontend**: HTML, CSS, Vanilla JavaScript
- **Backend**: PHP (no frameworks)
- **Database**: MySQL
- **Server**: XAMPP

## 📋 Features

### User Roles
- **Admin**: Manage all jobs and users, approve/reject job postings
- **Recruiter**: Post jobs, view applicants
- **Candidate**: View and apply for jobs

### Core Functionality
- User authentication (signup/login) with role-based access
- Job posting system with admin approval workflow
- Application management system
- User and job management by admin
- Responsive modern UI design

## 📁 Project Structure

```
JobXchange/
├── public/
│   ├── login.php                    # Login page
│   ├── signup.php                   # Registration page
│   ├── dashboard_admin.php          # Admin dashboard
│   ├── dashboard_recruiter.php      # Recruiter dashboard
│   ├── dashboard_candidate.php      # Candidate dashboard
│   ├── post_job.php                 # Job posting form
│   ├── apply_job.php                # Job application form
│   ├── view_applicants.php          # View job applicants
│   └── styles.css                   # CSS styling
├── includes/
│   ├── db.php                       # Database connection
│   ├── header.php                   # Common header
│   └── footer.php                   # Common footer
├── actions/
│   ├── handle_signup.php            # Signup processing
│   ├── handle_login.php             # Login processing
│   ├── handle_logout.php            # Logout processing
│   ├── handle_post_job.php          # Job posting processing
│   ├── handle_apply.php             # Application processing
│   ├── handle_approve.php           # Job approval/rejection
│   ├── handle_delete_user.php       # User deletion
│   └── handle_delete_job.php        # Job deletion
├── database_schema.sql              # Database schema
└── README.md                        # This file
```

## 🚀 Installation & Setup

### Prerequisites
- XAMPP installed on your system
- Web browser (Chrome, Firefox, Edge, etc.)

### Step 1: Install XAMPP
1. Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Install XAMPP (default location: `C:\xampp`)
3. Open XAMPP Control Panel

### Step 2: Start Services
1. Open **XAMPP Control Panel**
2. Start **Apache** (for PHP)
3. Start **MySQL** (for Database)

### Step 3: Setup Project
The project is already in the correct location: `C:\xampp\htdocs\JobXchange`

### Step 4: Create Database
1. Open your web browser
2. Go to: `http://localhost/phpmyadmin`
3. Click on **"Import"** tab
4. Click **"Choose File"** and select: `C:\xampp\htdocs\JobXchange\database_schema.sql`
5. Click **"Go"** to import the database

**OR** you can create the database manually:
1. Go to `http://localhost/phpmyadmin`
2. Click **"SQL"** tab
3. Copy and paste the contents of `database_schema.sql`
4. Click **"Go"**

### Step 5: Access the Application
Open your browser and go to:
```
http://localhost/JobXchange/public/login.php
```

## 👤 Default Login Credentials

### Admin Account
- **Email**: admin@jobxchange.com
- **Password**: admin123

Use this account to approve jobs and manage users.

## 📖 How to Use

### For Candidates:
1. **Sign Up**: Go to signup page, select "Candidate" role
2. **Login**: Use your credentials
3. **Browse Jobs**: View all approved job listings
4. **Apply**: Click "Apply Now" and submit your application
5. **Track Applications**: View status of your applications

### For Recruiters:
1. **Sign Up**: Go to signup page, select "Recruiter" role
2. **Login**: Use your credentials
3. **Post Job**: Click "Post New Job", fill in details
4. **Wait for Approval**: Admin will review your job posting
5. **View Applicants**: Once approved, view candidates who applied

### For Admin:
1. **Login**: Use default admin credentials
2. **Approve Jobs**: Review pending jobs and approve/reject
3. **Manage Users**: View all users and delete if needed
4. **Manage Jobs**: View all jobs and delete if needed
5. **View Statistics**: Dashboard shows overall system statistics

## 🗄️ Database Schema

### Users Table
- `user_id` (Primary Key)
- `name`
- `email` (Unique)
- `password` (Hashed)
- `role` (admin, recruiter, candidate)
- `created_at`

### Jobs Table
- `job_id` (Primary Key)
- `recruiter_id` (Foreign Key → users)
- `title`
- `description`
- `skills`
- `salary`
- `type` (internship, full-time)
- `status` (pending, approved, rejected)
- `created_at`

### Applications Table
- `application_id` (Primary Key)
- `job_id` (Foreign Key → jobs)
- `user_id` (Foreign Key → users)
- `message`
- `status` (pending, reviewed, accepted, rejected)
- `applied_at`

## 🔐 Security Features

- Password hashing using PHP's `password_hash()`
- SQL injection prevention using `mysqli_real_escape_string()`
- Session-based authentication
- Role-based access control
- Input validation on all forms

## 🎨 UI Features

- Modern gradient design
- Responsive layout (mobile-friendly)
- Card-based job listings
- Color-coded status badges
- Smooth animations and transitions
- Clean and intuitive navigation

## 🐛 Troubleshooting

### Apache won't start
- **Issue**: Port 80 is already in use
- **Solution**: Stop IIS or change Apache port in XAMPP config

### MySQL won't start
- **Issue**: Port 3306 is already in use
- **Solution**: Stop other MySQL services or change port

### Database connection error
- **Issue**: Cannot connect to database
- **Solution**: 
  - Check if MySQL is running in XAMPP
  - Verify database name is "jobxchange"
  - Check db.php configuration

### Page not found
- **Issue**: 404 error
- **Solution**: 
  - Ensure URL is `http://localhost/JobXchange/public/login.php`
  - Check that files are in `C:\xampp\htdocs\JobXchange`

## 📝 Development Notes

- All passwords are hashed before storing
- Sessions are used for user authentication
- Foreign key constraints ensure data integrity
- Cascading delete removes related records
- Input validation on both client and server side

## 🔄 Future Enhancements

- Email notifications
- Resume upload functionality
- Advanced search and filtering
- Interview scheduling
- Company profiles
- Job categories/tags
- Profile management
- Password reset functionality

## 👨‍💻 Support

For issues or questions:
1. Check the troubleshooting section
2. Verify XAMPP services are running
3. Check browser console for errors
4. Verify database connection in phpMyAdmin

## 📄 License

This project is created for educational purposes.

---

**Made with ❤️ using PHP & MySQL**


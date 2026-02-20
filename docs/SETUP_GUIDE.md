# PARAGON COMMUNICATIONS - Complete Setup Guide

## 📋 Quick Summary of What's Been Built

✅ **Database Schema** - 10 tables with proper relationships and indexes  
✅ **PDO Database Connection** - Secure prepared statements, no SQL injection  
✅ **Google OAuth 2.0** - Full authentication flow with email verification  
✅ **Role-Based Access Control** - Head Admin, Admin, Manager, User roles  
✅ **Email Verification** - New user verification workflow  
✅ **Helper Functions** - 30+ utility functions for common operations  
✅ **Audit Logging** - Track all user actions and changes  
✅ **Dashboard** - Role-specific views with statistics  
✅ **Security** - Password hashing, prepared statements, session management  

---

## 🚀 Step-by-Step Setup Instructions

### Step 1: Database Setup (10 minutes)

#### Option A: Using phpMyAdmin (Recommended for Beginners)

1. **Open phpMyAdmin**
   ```
   http://localhost/phpmyadmin
   ```

2. **Create Database**
   - Right-click "New" in left panel
   - Database name: `paragon_db`
   - Charset: `utf8mb4`
   - Click "Create"

3. **Import Schema**
   - Select the `paragon_db` database
   - Go to "SQL" tab
   - Copy contents of `config/database_schema.sql`
   - Paste into SQL editor
   - Click "Go"

#### Option B: Using MySQL Command Line

```bash
# Open MySQL terminal
mysql -u root -p

# Create database
CREATE DATABASE paragon_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Use database
USE paragon_db;

# Import schema (from command line, not inside MySQL)
mysql -u root -p paragon_db < config/database_schema.sql
```

**Verify Installation:**
```bash
mysql -u root -p paragon_db -e "SHOW TABLES;"
```

You should see 10 tables:
- users
- admin_accounts
- head_admin_confirmations
- client_accounts
- call_out_history
- file_uploads
- reports
- audit_logs
- oauth_sessions

---

### Step 2: Environment Configuration (5 minutes)

1. **Create .env File**
   ```bash
   # Navigate to config/ directory
   cd config/
   
   # Copy template
   cp .env.example .env
   
   # Edit with your values
   # Windows: Open .env with Notepad
   # Linux/Mac: nano .env or vi .env
   ```

2. **Database Configuration** (Required)
   ```ini
   DB_HOST=localhost
   DB_NAME=paragon_db
   DB_USER=root
   DB_PASS=
   APP_ENV=development
   ```

3. **Save the file**

---

### Step 3: Google OAuth Setup (15 minutes)

#### 3A: Create Google Cloud Project

1. **Go to Google Cloud Console**
   ```
   https://console.cloud.google.com/
   ```

2. **Create New Project**
   - Click "Select a project" at top
   - Click "New Project"
   - Name: `PARAGON Communications`
   - Click "Create"
   - Wait 30-60 seconds

3. **Enable Google+ API**
   - Search bar: type "Google+ API"
   - Click "Google+ API"
   - Click "Enable"

4. **Create OAuth 2.0 Credentials**
   - Left menu: "Credentials"
   - "Create Credentials" → "OAuth client ID"
   - If prompted: "Configure OAuth consent screen"
   - External → Create
   - Fill form:
     - App name: PARAGON Communications
     - Support email: your-email@gmail.com
     - Click "Save and Continue" (skip optional sections)
   - Back to Credentials → "Create Credentials" → "OAuth client ID"
   - Application type: **Web application**
   - Name: `PARAGON Web Client`
   - Authorized redirect URIs: Add
     ```
     http://localhost/paragon/config/google-callback.php
     ```
   - Click "Create"

5. **Copy Credentials**
   - You'll see Client ID and Client Secret
   - Copy both values

#### 3B: Update .env File

```ini
GOOGLE_CLIENT_ID=your_client_id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your_client_secret_here
GOOGLE_REDIRECT_URI=http://localhost/paragon/config/google-callback.php
```

---

### Step 4: Web Server Configuration (5 minutes)

#### For XAMPP:

1. **Verify htdocs location**
   ```
   c:\xampp\htdocs\paragon\PARAGON-COMMUNICATIONS-CORP\
   ```

2. **Start XAMPP**
   - Open XAMPP Control Panel
   - Start "Apache"
   - Start "MySQL"

3. **Test Application**
   ```
   http://localhost/paragon/login.php
   ```

You should see the PARAGON login page with Google button.

---

### Step 5: Create Default Head Admin (5 minutes)

**Option A: Using phpMyAdmin**

1. Open phpMyAdmin → Select `paragon_db` database
2. Click `users` table
3. Click "Insert"
4. Fill in:
   - email: `admin@paragon.com`
   - first_name: `Head`
   - last_name: `Admin`
   - password: Generate hash using tool below
   - role: `head_admin`
   - status: `active`
   - email_verified: `1`
   - google_id: (leave empty)
5. Click "Go"

**Option B: Using PHP Script**

Create `setup-admin.php` in root:

```php
<?php
require_once 'config/database.php';
require_once 'config/helpers.php';

$email = 'admin@paragon.com';
$password = 'YourSecurePassword123'; // Change this!
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

$sql = "INSERT INTO users (email, first_name, last_name, password, role, status, email_verified) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $pdo->prepare($sql);
$stmt->execute([$email, 'Head', 'Admin', $hashedPassword, 'head_admin', 'active', 1]);

echo "✅ Head Admin created successfully!<br>";
echo "Email: " . $email . "<br>";
echo "Password: " . $password . "<br>";
echo "⚠️ Change password after first login!";
?>
```

Then visit: `http://localhost/paragon/setup-admin.php`

**Delete the setup file after:**
```bash
del setup-admin.php
```

---

### Step 6: Test the System (10 minutes)

#### Test 1: Google Login

1. Go to `http://localhost/paragon/login.php`
2. Click "Login with Google"
3. You should be redirected to Google login
4. After successful Google login, you'll be asked to verify email and select role
5. Complete registration

#### Test 2: Email/Password Login

1. Go to `http://localhost/paragon/login.php`
2. Use credentials:
   - Email: `admin@paragon.com`
   - Password: (the one you set above)
3. You should be logged in as Head Admin

#### Test 3: Role-Based Dashboard

1. After login, you should see Head Admin dashboard
2. Check sidebar shows: Dashboard, Administration, Reports sections
3. Click through different pages to verify navigation

---

## 📁 File Structure Overview

```
PARAGON-COMMUNICATIONS-CORP/
│
├── 📄 login.php                    # Login page with Google OAuth
├── 📄 register.php                 # User registration & email verification
├── 📄 dashboard.php                # Main dashboard (role-based)
├── 📄 logout.php                   # Logout handler
│
├── 📁 config/
│   ├── config.php                  # Configuration loader
│   ├── database.php                # PDO database connection
│   ├── database_schema.sql         # Database creation script
│   ├── authenticate.php            # Email/password authentication
│   ├── google-callback.php         # Google OAuth callback
│   ├── helpers.php                 # Helper functions (30+)
│   ├── .env                        # Your sensitive credentials (KEEP PRIVATE!)
│   └── .env.example                # Template for .env
│
├── 📁 import/
│   ├── upload.php                  # Excel file upload handler
│   └── process_excel.php           # Excel processing logic
│
├── 📁 assets/
│   └── style.css                   # Main stylesheet
│
├── 📁 admin/                       # (To be created)
│   ├── users.php                   # User management
│   ├── approvals.php               # Account approval workflow
│   └── system-settings.php         # System configuration
│
├── 📁 clients/                     # (To be created)
│   ├── list.php                    # Client account listing
│   ├── detail.php                  # Single client detail
│   ├── edit.php                    # Edit client account
│   ├── assigned.php                # Manager's assigned clients
│   └── callout.php                 # Call out status tracking
│
├── 📁 reports/                     # (To be created)
│   └── index.php                   # Reporting interface
│
├── README.md                       # Full documentation
├── SETUP_GUIDE.md                  # This file
└── .gitignore                      # (Should include .env!)
```

---

## 🔐 Security Best Practices

### 1. Keep `.env` Secure

```bash
# Add to .gitignore to prevent commits
echo "config/.env" >> .gitignore
```

### 2. Production Checklist

```ini
# In production .env:
APP_ENV=production
SESSION_SECURE=true
REQUIRE_HEAD_ADMIN_APPROVAL=true
```

### 3. Database Credentials

```bash
# Change default MySQL password
mysql -u root
ALTER USER 'root'@'localhost' IDENTIFIED BY 'YourSecurePassword';
FLUSH PRIVILEGES;
```

### 4. SSL Certificate (Production)

```bash
# Use HTTPS in production
GOOGLE_REDIRECT_URI=https://yourdomain.com/paragon/config/google-callback.php
SESSION_SECURE=true
```

---

## 🐛 Troubleshooting

### Issue: "Database Connection Failed"

**Solution:**
- Verify MySQL is running
- Check database name in .env matches created database
- Verify user credentials (root, password)

```bash
# Test connection:
mysql -u root -p paragon_db -e "SELECT 1;"
```

### Issue: "Google login redirects to login page"

**Solution:**
- Verify GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET in .env
- Check Google OAuth consent screen is configured
- Verify redirect URI matches exactly in Google Cloud

### Issue: "Table doesn't exist"

**Solution:**
- Run `database_schema.sql` again
- Check that all 10 tables were created:

```bash
mysql -u root -p paragon_db -e "SHOW TABLES;"
```

### Issue: "Session lost after refresh"

**Solution:**
- Check browser cookies are enabled
- Verify PHP session directory is writable:

```bash
# Linux/Mac
chmod 777 /tmp

# Windows (should be automatic)
```

### Issue: "Can't read .env file"

**Solution:**
```bash
# Verify file exists
ls config/.env

# Check file permissions (readable)
chmod 644 config/.env
```

---

## 📊 Database Tables Overview

### 1. `users`
User accounts with Google OAuth integration
- Stores all user information
- Tracks verification status
- Records last login time

### 2. `admin_accounts`
Admin/Manager approval workflow
- Linked to users table
- Tracks approval status
- Records approval timestamp

### 3. `client_accounts`
Master list of client/customer accounts
- Contains address, fees, call status
- Tracks creation and management
- Full-text searchable

### 4. `call_out_history`
Historical tracking of call out status changes
- Logs every status change
- Records who made the change
- Includes remarks/notes

### 5. `file_uploads`
Excel import tracking
- Records upload details
- Tracks import success/failure
- Stores processing logs

### 6. `audit_logs`
Complete action logging for compliance
- Logs every database change
- Tracks user actions
- Records IP address and user agent

### 7. `oauth_sessions`
Google OAuth token management
- Stores access tokens
- Tracks token expiration
- Manages token refresh

---

## 🎯 Common Tasks

### Register New Admin User

1. New user visits login.php
2. Clicks "Login with Google"
3. Completes Google authentication
4. Selects "Administrator" role
5. Account created with "pending" approval status
6. Head Admin sees pending approval in dashboard
7. Head Admin approves → Status changes to "active"

### Import Client Masterlist

1. Admin goes to "Import Masterlist"
2. Uploads Excel file with required columns
3. System validates data
4. Inserts into `client_accounts` table
5. Logs import details in `file_uploads` table

### Track Call Out Status

1. Admin/Manager views client accounts
2. Updates "call_out_status" (active/dormant/inactive)
3. Change logged to `call_out_history`
4. Audit log records the change

---

## 📞 Support

For issues or questions:
1. Check Troubleshooting section above
2. Review comments in individual PHP files
3. Check audit logs for system actions
4. Review error logs in MySQL

---

## ✅ Verification Checklist

- [ ] Database created with 10 tables
- [ ] .env file configured with credentials
- [ ] Google OAuth credentials obtained
- [ ] Head Admin account created
- [ ] Login page displays without errors
- [ ] Google login button works
- [ ] Email/password login works (development mode)
- [ ] Dashboard shows correct role-based menus
- [ ] Audit log shows activity
- [ ] Database helper functions work

---

## 🎓 Next Steps

After setup is complete:

1. **Customize Styling**
   - Edit `assets/style.css`
   - Update colors and branding

2. **Implement Missing Pages**
   - Create `admin/users.php`
   - Create `clients/list.php`
   - Create `reports/index.php`

3. **Email Configuration**
   - Set up SMTP for verification emails
   - Create email templates

4. **Excel Import**
   - Update `import/process_excel.php`
   - Add data validation

5. **Reporting**
   - Implement report generation
   - Add PDF export functionality

---

**Last Updated:** February 4, 2026  
**Version:** 1.0.0

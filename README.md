# PARAGON COMMUNICATIONS BACKEND MANAGEMENT SYSTEM

## Overview
PARAGON is a comprehensive backend management system for PARAGON Communications with role-based access control (Head Admin, Admin, Manager, User).

**Key Features:**
- ✅ Google OAuth 2.0 Authentication
- ✅ Email Verification for new accounts
- ✅ Role-Based Access Control (RBAC)
- ✅ Head Admin Account Confirmation
- ✅ Client Account Management (with Address, Fees, Call Status, etc.)
- ✅ Masterlist Monitoring (Excel import)
- ✅ Call Out History Tracking
- ✅ Reporting System
- ✅ Audit Logging
- ✅ PDO Database with prepared statements

---

## Technology Stack

| Component | Technology |
|-----------|------------|
| **Backend** | PHP 7.4+ |
| **Database** | MySQL 5.7+ / MariaDB |
| **Database Access** | PDO (with prepared statements) |
| **Authentication** | Google OAuth 2.0 |
| **Frontend** | HTML5, CSS3, JavaScript |
| **Session Management** | PHP Sessions with secure cookies |

---

## Installation & Setup

### 1. **Database Setup**

```bash
# Open MySQL/MariaDB
mysql -u root -p

# Create database
CREATE DATABASE paragon_db;
USE paragon_db;

# Import schema
SOURCE config/database_schema.sql;
```

Or use phpMyAdmin:
1. Go to http://localhost/phpmyadmin
2. Create database: `paragon_db`
3. Go to SQL tab and paste contents of `config/database_schema.sql`
4. Execute

### 2. **Environment Configuration**

```bash
# In config/ directory, copy .env.example to .env
cp config/.env.example config/.env

# Edit config/.env with your actual values:
```

**Required Configuration:**

#### A. Database (config/.env)
```ini
DB_HOST=localhost
DB_NAME=paragon_db
DB_USER=root
DB_PASS=your_password
```

#### B. Google OAuth Setup
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project
3. Enable **Google+ API**
4. Create **OAuth 2.0 Credentials** (Web Application)
5. Add Authorized redirect URI:
   ```
   http://localhost/paragon/config/google-callback.php
   ```
6. Copy Client ID and Client Secret to .env:
   ```ini
   GOOGLE_CLIENT_ID=your_client_id.apps.googleusercontent.com
   GOOGLE_CLIENT_SECRET=your_client_secret
   GOOGLE_REDIRECT_URI=http://localhost/paragon/config/google-callback.php
   ```

#### C. Email Configuration (Optional - for verification emails)
```ini
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
```

### 3. **Directory Permissions**

```bash
# Make sure PHP can write to logs and uploads directories
chmod 755 config/
chmod 644 config/.env
```

### 4. **Test Installation**

```bash
# Start your web server (XAMPP, WAMP, etc.)
# Navigate to: http://localhost/paragon/login.php

# Test Google Login or use demo credentials
# Email: test@example.com (will be created automatically in development)
# Password: any password
```

---

## User Roles & Permissions

### 1. **Head Admin**
- ✅ Approve/Reject Admin & Manager registrations
- ✅ Full system access
- ✅ User management
- ✅ All reporting features
- ✅ System settings

### 2. **Admin**
- ✅ Manage client accounts
- ✅ Import masterlist files (Excel)
- ✅ View/manage call outs
- ✅ Generate reports
- ✅ ❌ Approve other admins (requires Head Admin)

### 3. **Manager**
- ✅ View assigned client accounts
- ✅ Update client status
- ✅ Generate reports
- ✅ ❌ Import files
- ✅ ❌ User management

### 4. **User**
- ✅ View assigned accounts
- ✅ Basic reporting
- ✅ ❌ Account management
- ✅ ❌ File imports

---

## Database Schema

### Users Table
```sql
- id (PK)
- google_id (Google OAuth ID)
- email (UNIQUE)
- first_name, last_name
- password (hashed with bcrypt)
- role (head_admin, admin, manager, user)
- status (active, inactive, suspended)
- email_verified (boolean)
- verification_token
- created_at, updated_at, last_login
```

### Admin Accounts Table
```sql
- id (PK)
- user_id (FK)
- department
- phone, address, city, province, postal_code
- approval_status (pending, approved, rejected)
- approved_by (FK to users)
- approval_date
```

### Client Accounts Table
```sql
- id (PK)
- client_name
- email, phone
- address (LONGTEXT)
- amount_paid (DECIMAL)
- installation_fee (DECIMAL)
- call_out_status (active, dormant, inactive, pending)
- pull_out_remarks
- status_input_channel
- sales_category
- main_remarks
- created_by, managed_by (FK)
```

### Call Out History Table
```sql
- id (PK)
- client_account_id (FK)
- call_out_date
- status_before, status_after
- remarks
- updated_by (FK)
```

### File Uploads Table
```sql
- id (PK)
- filename, file_path, file_size
- total_records, successful_imports, failed_imports
- upload_status (pending, processing, completed, failed)
- uploaded_by (FK)
```

---

## API Endpoints

### Authentication
| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/login.php` | Display login page |
| POST | `/config/authenticate.php` | Email/Password login |
| GET | `/config/google-callback.php` | Google OAuth callback |
| POST | `/register.php` | Complete new user registration |
| GET | `/logout.php` | Logout user |

### Dashboard
| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/dashboard.php` | Main dashboard |

---

## File Structure

```
PARAGON-COMMUNICATIONS-CORP/
├── login.php                          # Login page with Google OAuth
├── register.php                       # Registration/Email verification
├── dashboard.php                      # Main dashboard (role-based)
├── logout.php                         # Logout handler
│
├── config/
│   ├── config.php                    # Configuration loader
│   ├── database.php                  # PDO database connection
│   ├── database_schema.sql           # Database schema creation
│   ├── authenticate.php              # Email/password authentication
│   ├── google-callback.php           # Google OAuth callback handler
│   ├── .env                          # Environment variables (KEEP SECURE!)
│   └── .env.example                  # Example environment file
│
├── import/
│   ├── upload.php                    # File upload handler
│   └── process_excel.php             # Excel file processor
│
├── assets/
│   └── style.css                     # Main stylesheet
│
└── README.md                          # This file
```

---

## Security Best Practices

### 1. **Password Security**
- ✅ Passwords hashed with bcrypt (PASSWORD_BCRYPT)
- ✅ Minimum 8 characters
- ✅ Server-side validation

### 2. **Database Security**
- ✅ PDO with prepared statements (prevents SQL injection)
- ✅ Parameterized queries for all operations
- ✅ Error logging without exposing details to users

### 3. **Session Security**
- ✅ HttpOnly cookies (prevents XSS attacks)
- ✅ Secure flag for HTTPS (set in production)
- ✅ Session timeout after 24 hours
- ✅ CSRF token support

### 4. **Google OAuth Security**
- ✅ State parameter to prevent CSRF
- ✅ Secure token exchange
- ✅ Token expiration handling

### 5. **Data Protection**
- ✅ Audit logging for all changes
- ✅ User IP logging
- ✅ Activity timestamps
- ✅ LONGTEXT fields for sensitive remarks

---

## Development Features

### Development Mode (APP_ENV=development)
In development mode:
- ✅ Detailed error messages displayed
- ✅ Auto-create users with any password
- ✅ Disabled HTTPS requirement for sessions

### Production Mode (APP_ENV=production)
In production:
- ❌ User-friendly error messages only
- ❌ Auto-user creation disabled
- ❌ HTTPS required
- ❌ Full error logging

---

## Common Tasks

### Create Default Head Admin Account

```php
// In PHP or database:
$hashedPassword = password_hash('YourSecurePassword', PASSWORD_BCRYPT);

INSERT INTO users (email, first_name, last_name, password, role, status, email_verified) 
VALUES ('admin@paragon.com', 'Head', 'Admin', '$hashedPassword', 'head_admin', 'active', 1);
```

### Approve Admin/Manager Account (as Head Admin)

```sql
UPDATE admin_accounts 
SET approval_status = 'approved', approved_date = NOW(), approved_by = 1
WHERE id = ?;

UPDATE users 
SET status = 'active' 
WHERE id = ?;
```

### Import Client Masterlist

1. Login as Admin
2. Navigate to "Masterlist Monitoring"
3. Upload Excel file with columns: client_name, email, phone, address, amount_paid, installation_fee, call_out_status, sales_category
4. System processes and imports to `client_accounts` table

---

## Troubleshooting

### Google OAuth Issues

**Problem:** "Invalid Client ID"
- Solution: Verify GOOGLE_CLIENT_ID in .env matches console.cloud.google.com
- Check redirect URI matches exactly in Google Console

**Problem:** "Redirect URI mismatch"
- Solution: In Google Console, exact match required:
  ```
  http://localhost/paragon/config/google-callback.php
  ```

### Database Issues

**Problem:** "Database Connection Failed"
- Solution: Check credentials in .env file
- Verify MySQL server is running
- Check database `paragon_db` exists

**Problem:** "Table doesn't exist"
- Solution: Run `database_schema.sql` in MySQL
- Or import via phpMyAdmin

### Session/Login Issues

**Problem:** "Session lost after page refresh"
- Solution: Enable cookies in browser
- Check `SESSION_HTTPONLY` setting
- Verify PHP session.save_path is writable

---

## Support & Contact

For issues, feature requests, or support:
- Email: support@paragon.com
- Documentation: See individual file comments

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0 | Feb 2026 | Initial release with Google OAuth & PDO |

---

## License

PARAGON Communications - All Rights Reserved

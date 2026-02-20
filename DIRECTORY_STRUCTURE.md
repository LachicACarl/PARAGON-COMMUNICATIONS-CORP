# Directory Structure

This document describes the organized directory structure of the Paragon Communications Corp system.

## Root Directory

```
PARAGON-COMMUNICATIONS-CORP/
├── admin/              # Admin panel pages and features
├── api/                # API endpoints for CRUD operations
├── assets/             # Static assets (CSS, images, etc.)
├── auth/               # Authentication and user management
├── config/             # Configuration files and database setup
├── docs/               # Project documentation
├── import/             # Data import scripts and files
├── includes/           # Reusable PHP includes (sidebars, headers, etc.)
├── pages/              # Main application pages
├── utils/              # Utility scripts and tools
└── README.md           # Main project documentation
```

## Directory Details

### `/admin`
Contains administrative pages and features:
- `backend-productivity.php` - Backend productivity tracking
- `call_out_status_readonly.php` - Read-only call-out status viewer
- `daily-count.php` - Daily count reports
- `dormants.php` - Dormant accounts management
- `monitoring.php` - System monitoring
- `pull-out.php` - Pull-out management
- `recallouts.php` - Recall management
- `s25-report.php` - S25 reporting
- `setup-address-data.php` - Address data setup
- `visit-remarks.php` - Visit remarks management
- `head-admin/` - Head admin exclusive features

### `/api`
RESTful API endpoints organized by operation type:
- **ADD operations**: `add-*.php` files for creating records
- **DELETE operations**: `delete-*.php` files for removing records
- **FETCH operations**: `fetch-*.php` files for retrieving data
- **UPDATE operations**: `update-*.php` files for modifying records
- **CONFIG handlers**: `config-handler-*.php` for system configuration

Managed entities:
- Amount Paid
- Call Out Status
- Installation Fees
- Main Remarks
- Pull Out Remarks
- Sales Category
- Status Input Channel

### `/assets`
Static files and stylesheets:
- `style.css` - Main stylesheet
- `tailwind-compat.css` - Tailwind CSS compatibility layer

### `/auth`
Authentication and user management:
- `login.php` - User login page
- `logout.php` - User logout handler
- `register.php` - New user registration
- `admin-login.php` - Admin login page
- `pending-approval.php` - Pending user approval page
- `approve-users.php` - User approval handler

### `/config`
Configuration and database management:
- `.env` - Environment variables (not in version control)
- `.env.example` - Environment variables template
- `config.php` - Main configuration file
- `database.php` - Database connection handler
- `database_schema.sql` - Complete database schema
- `authenticate.php` - Authentication logic
- `helpers.php` - Helper functions
- `google-*.php` - Google OAuth integration
- `create-*.php` - Database table creation scripts
- `insert-*.sql` - Initial data insertion scripts

### `/docs`
Project documentation:
- `ADMIN_CONVERSION_TEMPLATE.md` - Admin page conversion template
- `ADMIN_SEPARATION_GUIDE.md` - Guide for separating admin features
- `BUILD_DOCUMENTATION.md` - Build process documentation
- `CONVERSION_CHECKLIST.php` - Conversion progress tracking
- `FINAL_DELIVERY_REPORT.md` - Final delivery documentation
- `FOLDER_STRUCTURE.md` - Original folder structure reference
- `HEAD_ADMIN_SETUP.md` - Head admin setup guide
- `IMPLEMENTATION_SUMMARY.md` - Implementation summary
- `INDEX.md` - Documentation index
- `QUICKSTART.md` - Quick start guide
- `SETUP_GUIDE.md` - Complete setup guide
- `START_HERE.md` - Getting started guide

### `/import`
Data import functionality and scripts

### `/includes`
Reusable PHP components:
- `sidebar.php` - Standard user sidebar
- `head-admin-sidebar.php` - Head admin sidebar

### `/pages`
Main application pages:
- `dashboard.php` - User dashboard
- `user.php` - User management page
- `profile.php` - User profile page
- `address.php` - Address listing
- `add-address.php` - Add new address
- `amountpaid.php` - Amount paid management
- `monitoring-dashboard.php` - Monitoring dashboard
- `fetch-*.php` - Data fetching scripts for pages

### `/utils`
Utility scripts and development tools:
- `debug-session.php` - Session debugging tool
- `test-database.php` - Database connection testing
- `generate-password-hash.php` - Password hash generator
- `convert_admin_pages.py` - Python script for admin page conversion
- `convert_admin.ps1` - PowerShell script for admin conversion

## Navigation Changes

Due to the reorganization, file paths have changed:

### Authentication
- Login: `/auth/login.php`
- Logout: `/auth/logout.php`
- Register: `/auth/register.php`
- Admin Login: `/auth/admin-login.php`

### Main Pages
- Dashboard: `/pages/dashboard.php`
- Profile: `/pages/profile.php`
- Address Management: `/pages/address.php`

### API Endpoints
- All API endpoints remain at `/api/*`

## Important Notes

1. **Duplicate Files Removed**: Cleaned up duplicate API files with inconsistent naming (underscore vs dash)
2. **Consistent Naming**: All files now use dash-separated naming convention
3. **Logical Organization**: Files are grouped by function for easier maintenance
4. **Documentation Centralized**: All documentation is now in `/docs` folder
5. **Security**: Make sure `.env` file is never committed to version control

## Updating References

After this reorganization, you may need to update:
1. Include paths in PHP files
2. Redirect URLs after authentication
3. AJAX endpoint URLs in JavaScript
4. Form action URLs
5. Session handling paths

## Maintenance

When adding new files:
- **Authentication**: Add to `/auth`
- **API endpoints**: Add to `/api`
- **User pages**: Add to `/pages`
- **Admin pages**: Add to `/admin`
- **Documentation**: Add to `/docs`
- **Utilities**: Add to `/utils`

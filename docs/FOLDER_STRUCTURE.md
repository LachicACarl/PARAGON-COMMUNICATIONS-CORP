# PARAGON COMMUNICATIONS - Folder Structure

## Organized Directory Layout

### **Root Directory**
- `dashboard.php` - Main dashboard (all roles)
- `login.php`, `logout.php`, `register.php` - Authentication
- `profile.php` - User profile management
- `user.php`, `address.php`, `amountpaid.php` - Legacy root files

### **admin/head-admin/** - Head Admin Management Pages
All 9 head admin exclusive pages:
- `user-management.php` - Account of User Admin & Manager
- `address-management.php` - Address (Long Address)
- `amount-paid.php` - Amount Paid
- `installation-fee.php` - Installation Fee
- `call_out_status.php` - Call Out Status
- `pull_out_remarks.php` - Pull Out Remarks
- `status_input.php` - Status Input Channel
- `sales_category.php` - Sales Category
- `main_remarks.php` - Main Remarks

### **admin/** - Admin & Manager Pages
Regular admin and shared pages:
- **Admin Only:**
  - `monitoring.php` - Backend Monitoring
  - `backend-productivity.php` - Backend Productivity
  - `dormants.php` - Dormants
  - `recallouts.php` - Recallouts

- **Admin & Manager Shared:**
  - `pull-out.php` - Pull Out Report
  - `s25-report.php` - S25 Report
  - `daily-count.php` - Daily Count
  - `visit-remarks.php` - Visit Remarks

- **Utility:**
  - `call_out_status_readonly.php` - Read-only view
  - `setup-address-data.php` - Setup script

### **api/** - API Endpoints
All backend API files for CRUD operations:
- Add, fetch, update, delete operations for all data types

### **assets/** - Static Resources
- `style.css` - Custom styles
- `tailwind-compat.css` - Tailwind compatibility
- `image.png` - Logo
- `avatar.png` - Default avatar

### **config/** - Configuration & Database
- `config.php` - Application configuration
- `database.php` - Database connection
- `helpers.php` - Helper functions
- `authenticate.php` - Authentication logic
- `create-*.php` - Database table creation scripts
- `database_schema.sql` - Database schema

### **includes/** - Reusable Components
- `head-admin-sidebar.php` - Shared sidebar for all head admin pages

### **import/** - Data Import Scripts
Data import and migration utilities

---

## Path References

### Sidebar Navigation
- Head Admin pages: `BASE_URL . 'admin/head-admin/{page}.php'`
- Admin pages: `BASE_URL . 'admin/{page}.php'`
- Root pages: `BASE_URL . '{page}.php'`

### Include Files
- Sidebar: `__DIR__ . '/includes/head-admin-sidebar.php'`
- Config: `__DIR__ . '/config/config.php'`

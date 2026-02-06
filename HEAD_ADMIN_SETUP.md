# HEAD ADMIN Setup Guide

## Creating Your Head Admin Account

### Step 1: Access the Create Head Admin Page
1. Start your XAMPP Apache and MySQL servers
2. Open your web browser and navigate to:
   ```
   http://localhost/paragon/PARAGON-COMMUNICATIONS-CORP/create-head-admin.php
   ```

### Step 2: Fill in the Head Admin Details
- **Email**: Enter your email address (e.g., admin@paragon.com)
- **First Name**: Enter your first name
- **Last Name**: Enter your last name
- **Google ID**: (Optional) Leave blank if not using Google OAuth

### Step 3: Submit and Login
1. Click "Create Head Admin Account"
2. After successful creation, navigate to the login page
3. Login with your Google account or email

### Step 4: Security (IMPORTANT!)
**Delete the `create-head-admin.php` file after creating your account for security purposes!**

---

## Head Admin Dashboard Features

Once logged in as Head Admin, you will see:

### Dashboard Statistics Cards
- **Total Users**: Count of all registered users
- **Client Accounts**: Total number of clients
- **Dormant Accounts**: Number of dormant client accounts
- **Amount Paid**: Total amount collected from all clients

### Client Accounts Overview Table

The Head Admin dashboard displays a comprehensive table with all client accounts showing:

1. **Client Name** - Name of the client
2. **Account of User Admin/Manager** - Shows who created and manages the account
   - Displays the name and role (Admin/Manager)
   - If different people created and manage the account, both are shown
3. **Address** - Full long address of the client
4. **Amount Paid** - Total amount paid by the client (in PHP currency)
5. **Installation Fee** - Installation fee charged
6. **Call Out Status** - Current status with color-coded badges:
   - 🟢 Active (Green)
   - 🟡 Dormant (Yellow)
   - 🔴 Inactive (Red)
   - 🔵 Pending (Blue)
7. **Pull Out Remarks** - Notes about pull-out situations
8. **Status Input Channel** - Channel through which status was updated
9. **Sales Category** - Category of the sale
10. **Main Remarks** - General remarks and notes about the client

### Recent System Activities
- View the last 10 system actions
- See which users made changes
- Track table modifications and timestamps

---

## Head Admin Menu Options

The HEAD ADMIN role has access to these exclusive features:

- **MONITORING** - System monitoring tools
- **BACKEND DAILY PRODUCTIVITY** - Track daily performance
- **PULL OUT REPORT** - View pull-out reports
- **DORMANTS PER AREA** - Area-wise dormant account reports
- **RECALLOUTS REMARKS** - Manage recallout notes
- **VISIT REMARKS** - Track visit comments
- **DAILY COUNT STATUS** - Daily status counts
- **S25 PLAN REPORT** - S25 plan reporting

---

## Next Steps

1. Create your Head Admin account using the create-head-admin.php page
2. Login to the system
3. Start adding client accounts through the appropriate admin/manager accounts
4. Monitor all activities from your Head Admin dashboard

---

## Database Schema

All client fields are stored in the `client_accounts` table:
- `client_name` - Client's name
- `address` - Long address (LONGTEXT)
- `amount_paid` - DECIMAL(15,2)
- `installation_fee` - DECIMAL(15,2)
- `call_out_status` - ENUM('active','dormant','inactive','pending')
- `pull_out_remarks` - LONGTEXT
- `status_input_channel` - VARCHAR(100)
- `sales_category` - VARCHAR(100)
- `main_remarks` - LONGTEXT
- `created_by` - User ID who created the account
- `managed_by` - User ID who manages the account

---

## Troubleshooting

### Can't Create Head Admin
- Ensure your database is set up correctly
- Run the `config/database_schema.sql` file first
- Check that MySQL is running in XAMPP

### Dashboard Not Showing Client Accounts
- Ensure you have client accounts in the database
- Verify you're logged in as head_admin role
- Check that the client_accounts table has data

### Status Badges Not Showing Correctly
- Clear your browser cache
- Ensure the CSS is loading properly
- Check browser console for errors

---

**For additional support, refer to the other documentation files:**
- `START_HERE.md` - Initial setup guide
- `SETUP_GUIDE.md` - Complete installation guide
- `QUICKSTART.md` - Quick start instructions

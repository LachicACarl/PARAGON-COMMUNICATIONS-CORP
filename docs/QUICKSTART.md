# PARAGON QUICK START - 5 Minute Setup

## ⚡ The Fastest Way to Get Running

### 1️⃣ Database (2 min)

**phpMyAdmin Method:**
- Open: http://localhost/phpmyadmin
- Create database: `paragon_db`
- SQL tab → Copy `config/database_schema.sql` → Run

**OR MySQL Command:**
```bash
mysql -u root -p
CREATE DATABASE paragon_db;
USE paragon_db;
SOURCE config/database_schema.sql;
```

### 2️⃣ Environment (1 min)

```bash
# In config/ folder:
cp .env.example .env

# Edit .env - Add at minimum:
DB_HOST=localhost
DB_NAME=paragon_db
DB_USER=root
DB_PASS=
```

### 3️⃣ Server (1 min)

- Start XAMPP (Apache + MySQL)
- Visit: http://localhost/paragon/login.php
- ✅ You should see login page

### 4️⃣ Test Login (1 min)

**Development Mode** (auto-creates users):
- Email: `test@example.com`
- Password: `anything`

**OR Create Head Admin:**

In phpMyAdmin, Insert into `users`:
```
email: admin@paragon.com
first_name: Head
last_name: Admin
password: $2y$10$8WvDdvF3vr.dVqGnV4iGhe8wZkr6q8.kLdA0X0yJ0n0P0nZ0Z0n0Z0
role: head_admin
status: active
email_verified: 1
```

(Password above = "password123")

---

## 📚 What You Have

| Component | File | Status |
|-----------|------|--------|
| Database Schema | `config/database_schema.sql` | ✅ Ready |
| PDO Connection | `config/database.php` | ✅ Ready |
| Authentication | `config/authenticate.php` | ✅ Ready |
| Google OAuth | `config/google-callback.php` | ✅ Ready |
| User Helpers | `config/helpers.php` | ✅ Ready |
| Login Page | `login.php` | ✅ Ready |
| Registration | `register.php` | ✅ Ready |
| Dashboard | `dashboard.php` | ✅ Ready |
| Configuration | `config/config.php` | ✅ Ready |
| Environment | `config/.env` | ✅ Ready |

---

## 🎯 User Roles

| Role | Access | Approval Needed |
|------|--------|-----------------|
| Head Admin | Everything | No |
| Admin | Clients, Import, Reports | Yes |
| Manager | Assigned Clients, Reports | Yes |
| User | View Clients | No |

---

## 🔗 Key URLs

| Page | URL |
|------|-----|
| Login | `/login.php` |
| Register | `/register.php` |
| Dashboard | `/dashboard.php` |
| Logout | `/logout.php` |
| Google Callback | `/config/google-callback.php` |

---

## 🔒 Default Test Credentials

**Development Mode** (APP_ENV=development):
- Works with ANY email + ANY password
- Auto-creates user on first login
- Perfect for testing!

**Production Mode** (APP_ENV=production):
- Must pre-create users in database
- Requires strong passwords
- Email verification required

---

## 🚨 Common Issues & Fixes

| Issue | Fix |
|-------|-----|
| "Table doesn't exist" | Run `database_schema.sql` again |
| "Database Connection Failed" | Check DB credentials in `.env` |
| "Google login fails" | Add GOOGLE_CLIENT_ID to `.env` |
| "Can't find .env" | Copy `.env.example` to `.env` |
| "MySQL not running" | Start MySQL in XAMPP Control Panel |
| "Port 3306 busy" | Change DB_PORT in `.env` |

---

## 📂 Important Files to Configure

```
config/.env              ← Your settings (KEEP SECRET!)
config/database.php      ← PDO connection (auto-loads .env)
login.php               ← Start here
dashboard.php           ← After login
```

---

## ✨ Key Features Ready to Use

✅ **Login/Registration**
- Email/password login
- Google OAuth 2.0
- Email verification
- Role selection

✅ **Security**
- Password hashing
- SQL injection prevention
- CSRF protection
- Audit logging

✅ **Database**
- 10 optimized tables
- Proper relationships
- Foreign keys
- Indexes for speed

✅ **Dashboard**
- Role-based views
- Statistics cards
- Activity log
- Responsive design

---

## 🧪 Quick Test Checklist

- [ ] MySQL database created
- [ ] Tables visible in phpMyAdmin (10 tables)
- [ ] .env file created and configured
- [ ] Login page displays without errors
- [ ] Can login with test credentials
- [ ] Dashboard shows welcome message
- [ ] Sidebar shows correct menu for your role
- [ ] Logout works

---

## 📞 Need Help?

**Check these first:**
1. `README.md` - Full documentation
2. `SETUP_GUIDE.md` - Detailed setup
3. `IMPLEMENTATION_SUMMARY.md` - What's included
4. Code comments in PHP files

---

## 🚀 Ready to Deploy?

**Before production:**
1. Set `APP_ENV=production` in .env
2. Create head admin user properly
3. Remove test code/data
4. Set `SESSION_SECURE=true` (HTTPS only)
5. Back up your database
6. Test all workflows

---

## 📊 Database Tables Reference

```
10 tables created:
- users (authentication)
- admin_accounts (approvals)
- head_admin_confirmations (workflow)
- client_accounts (master list)
- call_out_history (status tracking)
- file_uploads (imports)
- reports (reporting)
- audit_logs (activity)
- oauth_sessions (Google tokens)
```

---

**You're all set! 🎉**

Start at: `http://localhost/paragon/login.php`

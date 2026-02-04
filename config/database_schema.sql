-- PARAGON MANAGEMENT SYSTEM DATABASE SCHEMA
-- Created: February 2026

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS paragon_db;
USE paragon_db;

-- Users table with Google OAuth integration
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    google_id VARCHAR(255) UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    password VARCHAR(255),
    profile_picture VARCHAR(500),
    role ENUM('head_admin', 'admin', 'manager', 'user') DEFAULT 'user',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'inactive',
    email_verified BOOLEAN DEFAULT FALSE,
    verification_token VARCHAR(255),
    verification_token_expires DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login DATETIME,
    INDEX (email),
    INDEX (google_id),
    INDEX (role),
    INDEX (status)
);

-- Admin & Manager Accounts table
CREATE TABLE IF NOT EXISTS admin_accounts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL UNIQUE,
    department VARCHAR(100),
    phone VARCHAR(20),
    address LONGTEXT,
    city VARCHAR(100),
    province VARCHAR(100),
    postal_code VARCHAR(20),
    approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by INT,
    approval_date DATETIME,
    notes LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id),
    INDEX (approval_status)
);

-- Head Admin Confirmations table
CREATE TABLE IF NOT EXISTS head_admin_confirmations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_account_id INT NOT NULL,
    head_admin_id INT NOT NULL,
    confirmation_status ENUM('pending', 'confirmed', 'rejected') DEFAULT 'pending',
    confirmation_date DATETIME,
    remarks LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_account_id) REFERENCES admin_accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (head_admin_id) REFERENCES users(id),
    INDEX (confirmation_status)
);

-- Master List / Client Accounts table
CREATE TABLE IF NOT EXISTS client_accounts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(20),
    address LONGTEXT NOT NULL,
    city VARCHAR(100),
    province VARCHAR(100),
    postal_code VARCHAR(20),
    contact_person VARCHAR(255),
    amount_paid DECIMAL(15, 2) DEFAULT 0.00,
    installation_fee DECIMAL(15, 2) DEFAULT 0.00,
    call_out_status ENUM('active', 'dormant', 'inactive', 'pending') DEFAULT 'pending',
    pull_out_remarks LONGTEXT,
    status_input_channel VARCHAR(100),
    sales_category VARCHAR(100),
    main_remarks LONGTEXT,
    created_by INT,
    managed_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (managed_by) REFERENCES users(id),
    INDEX (call_out_status),
    INDEX (sales_category),
    FULLTEXT INDEX ft_client_name (client_name)
);

-- Call Out History table
CREATE TABLE IF NOT EXISTS call_out_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_account_id INT NOT NULL,
    call_out_date DATETIME,
    status_before ENUM('active', 'dormant', 'inactive', 'pending'),
    status_after ENUM('active', 'dormant', 'inactive', 'pending'),
    remarks LONGTEXT,
    updated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_account_id) REFERENCES client_accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (updated_by) REFERENCES users(id),
    INDEX (call_out_date)
);

-- File Uploads (Excel Masterlist) table
CREATE TABLE IF NOT EXISTS file_uploads (
    id INT PRIMARY KEY AUTO_INCREMENT,
    filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT,
    total_records INT,
    successful_imports INT,
    failed_imports INT,
    upload_status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    error_message LONGTEXT,
    uploaded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id),
    INDEX (upload_status),
    INDEX (created_at)
);

-- Reports table
CREATE TABLE IF NOT EXISTS reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    report_type VARCHAR(100) NOT NULL,
    generated_by INT NOT NULL,
    start_date DATE,
    end_date DATE,
    report_data LONGTEXT,
    file_path VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (generated_by) REFERENCES users(id),
    INDEX (report_type),
    INDEX (created_at)
);

-- Audit Log table
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    table_name VARCHAR(100),
    record_id INT,
    old_values LONGTEXT,
    new_values LONGTEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX (user_id),
    INDEX (created_at),
    INDEX (action)
);

-- OAuth Sessions table for Google Login
CREATE TABLE IF NOT EXISTS oauth_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    access_token TEXT NOT NULL,
    refresh_token TEXT,
    token_expires DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (user_id)
);

-- Create default Head Admin user (password: admin123)
-- Note: Replace with actual secure password hash in production
INSERT INTO users (email, first_name, last_name, password, role, status, email_verified) 
VALUES ('admin@paragon.com', 'Head', 'Admin', '$2y$10$...', 'head_admin', 'active', TRUE)
ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id);

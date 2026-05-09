-- ============================================
-- RVM Database Schema
-- Database: rvm_db
-- Laragon + phpMyAdmin
-- ============================================

CREATE DATABASE IF NOT EXISTS rvm_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE rvm_db;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE,
    phone VARCHAR(20) UNIQUE,
    password_hash VARCHAR(255),
    google_id VARCHAR(100) UNIQUE,
    avatar_url VARCHAR(255),
    total_points INT DEFAULT 0,
    role ENUM('user','admin') DEFAULT 'user',
    is_verified TINYINT(1) DEFAULT 0,
    otp_code VARCHAR(6) NULL,
    otp_expires_at DATETIME NULL,
    preferred_language ENUM('en','id') DEFAULT 'en',
    theme_preference ENUM('dark','light') DEFAULT 'dark',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- RVM Machines table
CREATE TABLE rvm_machines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    machine_code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    location_name VARCHAR(200),
    latitude DECIMAL(10,8) DEFAULT NULL,
    longitude DECIMAL(11,8) DEFAULT NULL,
    status ENUM('active','inactive','maintenance') DEFAULT 'active',
    aluminum_level INT DEFAULT 0 COMMENT '0-100 percent full',
    plastic_level INT DEFAULT 0,
    glass_level INT DEFAULT 0,
    paper_level INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- QR Sessions (temporary tokens for RVM machine)
CREATE TABLE qr_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    machine_id INT NOT NULL,
    qr_token VARCHAR(100) UNIQUE NOT NULL,
    status ENUM('pending','scanned','expired') DEFAULT 'pending',
    scanned_by INT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (machine_id) REFERENCES rvm_machines(id) ON DELETE CASCADE,
    FOREIGN KEY (scanned_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Recycling Sessions table
CREATE TABLE recycling_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_code VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    machine_id INT NOT NULL,
    status ENUM('active','completed','cancelled') DEFAULT 'active',
    start_points INT DEFAULT 0,
    end_points INT DEFAULT 0,
    points_earned INT DEFAULT 0,
    total_items INT DEFAULT 0,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ended_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (machine_id) REFERENCES rvm_machines(id) ON DELETE CASCADE
);

-- Transactions table
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    user_id INT NOT NULL,
    machine_id INT NOT NULL,
    material_selected ENUM('aluminum','plastic','glass','paper') NOT NULL,
    ai_detected_type VARCHAR(50) NULL,
    is_valid TINYINT(1) DEFAULT 1,
    weight_grams DECIMAL(8,2) DEFAULT 0,
    points_earned INT DEFAULT 0,
    points_deducted INT DEFAULT 0,
    image_path VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES recycling_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (machine_id) REFERENCES rvm_machines(id) ON DELETE CASCADE
);

-- Points history table
CREATE TABLE points_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    transaction_id INT NULL,
    session_id INT NULL,
    points_change INT NOT NULL,
    balance_after INT NOT NULL,
    type ENUM('earned','deducted','bonus','redeemed') DEFAULT 'earned',
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE SET NULL,
    FOREIGN KEY (session_id) REFERENCES recycling_sessions(id) ON DELETE SET NULL
);

-- Admin logs
CREATE TABLE admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    target_type VARCHAR(50),
    target_id INT,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================
-- SEED DATA
-- ============================================

-- Default admin user (password: Admin@123)
INSERT INTO users (name, email, password_hash, role, is_verified, total_points) VALUES
('Administrator', 'admin@rvm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, 0),
('Emma Wilson', 'emma@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 1, 244),
('Ahmad Rizki', 'ahmad@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 1, 150);

-- Sample RVM Machines
INSERT INTO rvm_machines (machine_code, name, location_name, latitude, longitude, status, aluminum_level, plastic_level, glass_level, paper_level) VALUES
('RVM-001', 'RVM Kuantan Mall', 'Kuantan Parade, Kuantan, Pahang', 3.8077, 103.3260, 'active', 45, 30, 80, 100),
('RVM-002', 'RVM UMP', 'Universiti Malaysia Pahang, Gambang', 3.7081, 103.2478, 'active', 20, 60, 15, 40),
('RVM-003', 'RVM Giant Kuantan', 'Giant Hypermarket, Kuantan', 3.8200, 103.3300, 'maintenance', 70, 85, 50, 30);

-- ============================================================
-- EasyBiz Database Schema
-- Run this file in phpMyAdmin or MySQL to create all tables
-- ============================================================

-- Create the database (change name if needed)
CREATE DATABASE IF NOT EXISTS easybiz_db
  CHARACTER SET utf8mb4        -- supports all characters including emojis
  COLLATE utf8mb4_unicode_ci;

-- Tell MySQL to use this database
USE easybiz_db;

-- ============================================================
-- TABLE: users
-- Stores all registered user accounts
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
  id           INT AUTO_INCREMENT PRIMARY KEY, -- unique ID for each user
  name         VARCHAR(100)  NOT NULL,          -- user's full name
  phone        VARCHAR(20)   NOT NULL UNIQUE,   -- phone number used to login (must be unique)
  password     VARCHAR(255)  NOT NULL,          -- hashed password (never store plain text)
  plan         ENUM('free','premium') DEFAULT 'free', -- subscription plan type
  trial_used   INT DEFAULT 0,                   -- how many orders used in free trial
  sub_start    DATE NULL,                       -- date subscription started
  sub_end      DATE NULL,                       -- date subscription expires
  created_at   DATETIME DEFAULT CURRENT_TIMESTAMP -- when the account was created
);

-- ============================================================
-- TABLE: business_profiles
-- Each user can set up one business profile
-- ============================================================
CREATE TABLE IF NOT EXISTS business_profiles (
  id            INT AUTO_INCREMENT PRIMARY KEY, -- unique ID
  user_id       INT NOT NULL UNIQUE,            -- links to users table (one profile per user)
  business_name VARCHAR(150) NOT NULL,          -- name of the business
  address       VARCHAR(255),                   -- physical or delivery address
  country       VARCHAR(100),                   -- country (e.g. Cameroon)
  momo_number   VARCHAR(20),                    -- MTN MoMo payment number
  om_number     VARCHAR(20),                    -- Orange Money payment number
  updated_at    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE -- delete profile if user deleted
);

-- ============================================================
-- TABLE: orders
-- Stores all customer orders created by each user
-- ============================================================
CREATE TABLE IF NOT EXISTS orders (
  id             INT AUTO_INCREMENT PRIMARY KEY, -- unique order ID
  user_id        INT NOT NULL,                   -- which user created this order
  customer_name  VARCHAR(100) NOT NULL,          -- name of the customer
  product_name   VARCHAR(150) NOT NULL,          -- name of the product
  quantity       INT NOT NULL,                   -- number of units ordered
  unit_price     DECIMAL(10,2) NOT NULL,         -- price per single unit in FCFA
  total_price    DECIMAL(10,2) NOT NULL,         -- auto-calculated: quantity × unit_price
  status         ENUM('pending','paid') DEFAULT 'pending', -- order payment status
  order_date     DATE NOT NULL,                  -- date of the order
  created_at     DATETIME DEFAULT CURRENT_TIMESTAMP, -- when record was saved
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- TABLE: payments
-- Records every upgrade payment request submitted by users
-- ============================================================
CREATE TABLE IF NOT EXISTS payments (
  id          INT AUTO_INCREMENT PRIMARY KEY, -- unique payment ID
  user_id     INT NOT NULL,                   -- who submitted the payment
  method      ENUM('mtn','orange') NOT NULL,  -- which payment method was used
  amount      DECIMAL(10,2) DEFAULT 3000.00,  -- amount paid (3000 FCFA)
  status      ENUM('pending','confirmed','rejected') DEFAULT 'pending', -- admin review status
  submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP, -- when user clicked "I Have Paid"
  confirmed_at DATETIME NULL,                -- when admin approved/rejected
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- TABLE: otp_codes
-- Stores one-time password codes for password recovery
-- ============================================================
CREATE TABLE IF NOT EXISTS otp_codes (
  id         INT AUTO_INCREMENT PRIMARY KEY, -- unique ID
  phone      VARCHAR(20) NOT NULL,           -- phone number that requested the OTP
  code       VARCHAR(10) NOT NULL,           -- the 4-digit OTP code
  used       TINYINT(1) DEFAULT 0,           -- 0 = not used, 1 = already used
  expires_at DATETIME NOT NULL,              -- OTP becomes invalid after this time
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE: admins
-- Admin accounts (separate from regular users)
-- ============================================================
CREATE TABLE IF NOT EXISTS admins (
  id       INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50)  NOT NULL UNIQUE,  -- admin login username
  password VARCHAR(255) NOT NULL,         -- hashed password
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- Insert default admin account
-- Username: admin | Password: admin123 (change this immediately!)
-- The password below is the bcrypt hash of "admin123"
-- ============================================================
INSERT INTO admins (username, password)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- NOTE: After setup, login as admin and change this password!

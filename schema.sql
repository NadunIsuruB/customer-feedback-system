-- create database
CREATE DATABASE IF NOT EXISTS feedback_sys
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
USE feedback_sys;

-- admins/users table
CREATE TABLE IF NOT EXISTS users (
user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(80) NOT NULL UNIQUE,
password_hash VARCHAR(255) NOT NULL,
created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- customers
CREATE TABLE customers (
customer_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(120) NOT NULL,
email VARCHAR(190) NOT NULL,
phone VARCHAR(40) NULL,
created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
UNIQUE KEY uk_customers_email (email)
) ENGINE=InnoDB;


-- products
CREATE TABLE products (
product_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(160) NOT NULL,
category VARCHAR(120) NOT NULL,
price_cents INT UNSIGNED NOT NULL,
created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
KEY ix_category (category)
) ENGINE=InnoDB;


-- feedback
CREATE TABLE feedback (
feedback_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
customer_id INT UNSIGNED NOT NULL,
product_id INT UNSIGNED NOT NULL,
rating TINYINT UNSIGNED NOT NULL,
comment TEXT NULL,
created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
CONSTRAINT fk_fb_customer FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT fk_fb_product FOREIGN KEY (product_id) REFERENCES products(product_id)
ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT ck_rating CHECK (rating BETWEEN 1 AND 5),
KEY ix_feedback_product (product_id),
KEY ix_feedback_customer (customer_id),
KEY ix_feedback_created (created_at)
) ENGINE=InnoDB;


-- seed one admin
INSERT INTO users (username, password_hash)
VALUES ('admin', '$2y$10$pPrAeQ0sgvUvYXIokE6xr.gDdWOWho2aqvTeYBXtqf3x6dqFcaoGK');
-- The password: Admin@123.

-- some products
INSERT INTO products (name, category, price_cents) VALUES
('Wireless Mouse', 'Accessories', 2499),
('Mechanical Keyboard', 'Accessories', 8999),
('USB-C Charger 65W', 'Power', 5499);
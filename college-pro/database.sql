-- Bella Italia Database Schema
-- Run this file in phpMyAdmin or MySQL command line to create the database

CREATE DATABASE IF NOT EXISTS bella_italia;
USE bella_italia;

-- Users table (customers and admins)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    description TEXT,
    category_id INT NOT NULL,
    image VARCHAR(255) DEFAULT 'placeholder.jpg',
    availability ENUM('available', 'unavailable') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    delivery_address TEXT,
    delivery_type ENUM('delivery', 'collection') DEFAULT 'delivery',
    delivery_instructions TEXT,
    status ENUM('pending', 'preparing', 'on_the_way', 'delivered', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Payments table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    payment_method ENUM('cash_on_delivery', 'card', 'esewa', 'khalti') DEFAULT 'cash_on_delivery',
    payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Insert sample categories
INSERT INTO categories (name, description) VALUES
('Pizza', 'Traditional Italian pizzas'),
('Carbonara', 'Creamy pasta dishes'),
('Tiramisu', 'Classic Italian desserts'),
('Lasagna', 'Layered pasta dishes'),
('Drinks', 'Beverages and refreshments'),
('Specials', 'Chef special items');

-- Insert sample products
INSERT INTO products (name, price, description, category_id, image, availability) VALUES
-- Pizza
('Margherita Pizza', 12.99, 'Classic pizza with tomato sauce, mozzarella, and basil', 1, 'margherita.jpg', 'available'),
('Pepperoni Pizza', 14.99, 'Spicy pepperoni with mozzarella cheese', 1, 'pepperoni.jpg', 'available'),
('Hawaiian Pizza', 15.99, 'Ham, pineapple, and mozzarella', 1, 'hawaiian.jpg', 'available'),
('Vegetarian Pizza', 13.99, 'Mixed vegetables with mozzarella', 1, 'vegetarian.jpg', 'available'),
-- Carbonara
('Classic Carbonara', 16.99, 'Traditional carbonara with eggs, bacon, and parmesan', 2, 'carbonara.jpg', 'available'),
('Chicken Carbonara', 18.99, 'Creamy carbonara with grilled chicken', 2, 'chicken_carbonara.jpg', 'available'),
('Seafood Carbonara', 20.99, 'Carbonara with fresh seafood', 2, 'seafood_carbonara.jpg', 'available'),
-- Tiramisu
('Classic Tiramisu', 8.99, 'Traditional Italian dessert with coffee and mascarpone', 3, 'tiramisu.jpg', 'available'),
('Chocolate Tiramisu', 9.99, 'Tiramisu with extra chocolate', 3, 'chocolate_tiramisu.jpg', 'available'),
('Berry Tiramisu', 10.99, 'Tiramisu with fresh berries', 3, 'berry_tiramisu.jpg', 'available'),
-- Lasagna
('Classic Lasagna', 17.99, 'Traditional lasagna with meat sauce and cheese', 4, 'lasagna.jpg', 'available'),
('Vegetarian Lasagna', 16.99, 'Lasagna with vegetables and cheese', 4, 'vegetarian_lasagna.jpg', 'available'),
('Spinach Lasagna', 18.99, 'Lasagna with spinach and ricotta', 4, 'spinach_lasagna.jpg', 'available'),
-- Drinks
('Italian Soda', 3.99, 'Refreshing Italian soda', 5, 'soda.jpg', 'available'),
('Espresso', 2.99, 'Strong Italian coffee', 5, 'espresso.jpg', 'available'),
('Wine (Glass)', 6.99, 'Red or white wine', 5, 'wine.jpg', 'available'),
-- Specials
('Chef Special Pizza', 19.99, 'Chef special pizza with premium ingredients', 6, 'chef_pizza.jpg', 'available'),
('Truffle Carbonara', 24.99, 'Premium carbonara with truffle', 6, 'truffle_carbonara.jpg', 'available');

-- Insert sample admin user (password: admin123)
-- NOTE: The password hashes below are placeholders and won't work.
-- To set up passwords, you have two options:
-- 
-- OPTION 1: Generate password hashes (Recommended for testing)
--   1. Run: http://localhost/college-pro/generate_password_hash.php
--   2. Copy the generated hashes and update the INSERT statements below
--
-- OPTION 2: Register through the website (Recommended for production)
--   1. Go to: http://localhost/college-pro/auth/register.php
--   2. Create new accounts (passwords will be hashed automatically)
--   3. You can delete these sample INSERT statements if not needed
--
-- Sample admin user (password: admin123)
-- IMPORTANT: Before importing, you must generate password hashes:
--   1. Open: http://localhost/college-pro/generate_password_hash.php
--   2. Copy the generated hash for 'admin123'
--   3. Replace 'REPLACE_WITH_HASH_ADMIN' below with the hash
--   OR simply register a new admin through the website!
INSERT INTO users (first_name, last_name, email, phone, password, role, status) VALUES
('Admin', 'User', 'admin@bellaitalia.com', '1234567890', 'REPLACE_WITH_HASH_ADMIN', 'admin', 'active');

-- Sample customer user (password: customer123)
-- IMPORTANT: Before importing, you must generate password hashes:
--   1. Open: http://localhost/college-pro/generate_password_hash.php
--   2. Copy the generated hash for 'customer123'
--   3. Replace 'REPLACE_WITH_HASH_CUSTOMER' below with the hash
--   OR simply register a new customer through the website!
INSERT INTO users (first_name, last_name, email, phone, password, role, status) VALUES
('John', 'Doe', 'customer@example.com', '9876543210', 'REPLACE_WITH_HASH_CUSTOMER', 'customer', 'active');

-- ALTERNATIVE: You can skip these sample users and register through the website instead!


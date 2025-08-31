-- GlamCart Database Schema
-- Makeup and Cosmetics Shop Management System

-- Drop database if exists and create new one
DROP DATABASE IF EXISTS glam_cart;
CREATE DATABASE glam_cart;
USE glam_cart;

-- Users table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    user_f_name VARCHAR(50) NOT NULL,
    user_l_name VARCHAR(50) NOT NULL,
    user_email VARCHAR(100) UNIQUE NOT NULL,
    user_password VARCHAR(255) NOT NULL,
    user_phone VARCHAR(20),
    user_address TEXT,
    user_city VARCHAR(50),
    user_state VARCHAR(50),
    user_zip VARCHAR(10),
    user_role ENUM('customer', 'admin') DEFAULT 'customer',
    user_status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Brand table
CREATE TABLE brand (
    brand_id INT PRIMARY KEY AUTO_INCREMENT,
    brand_name VARCHAR(100) NOT NULL UNIQUE,
    brand_description TEXT,
    brand_logo VARCHAR(255),
    brand_status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Category table
CREATE TABLE category (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    category_description TEXT,
    category_image VARCHAR(255),
    category_status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Product table
CREATE TABLE product (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    product_name VARCHAR(200) NOT NULL,
    product_description TEXT,
    product_code VARCHAR(50) UNIQUE NOT NULL,
    product_price DECIMAL(10,2) NOT NULL,
    product_sale_price DECIMAL(10,2),
    product_cost DECIMAL(10,2),
    product_sku VARCHAR(50),
    product_brand_id INT,
    product_category_id INT,
    product_image VARCHAR(255),
    product_gallery TEXT,
    product_stock INT DEFAULT 0,
    product_min_stock INT DEFAULT 5,
    product_weight DECIMAL(8,2),
    product_dimensions VARCHAR(100),
    product_status ENUM('active', 'inactive', 'out_of_stock') DEFAULT 'active',
    product_featured BOOLEAN DEFAULT FALSE,
    product_entry_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_brand_id) REFERENCES brand(brand_id) ON DELETE SET NULL,
    FOREIGN KEY (product_category_id) REFERENCES category(category_id) ON DELETE SET NULL
);

-- Store Product table (for inventory management)
CREATE TABLE store_product (
    store_product_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    store_quantity INT DEFAULT 0,
    store_location VARCHAR(100),
    store_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES product(product_id) ON DELETE CASCADE
);

-- Spend Product table (for tracking sold items)
CREATE TABLE spend_product (
    spend_product_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    spend_quantity INT DEFAULT 0,
    spend_date DATE,
    spend_reason VARCHAR(200),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES product(product_id) ON DELETE CASCADE
);

-- Cart table
CREATE TABLE cart (
    cart_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES product(product_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- Wishlist table
CREATE TABLE wishlist (
    wishlist_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES product(product_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_wishlist (user_id, product_id)
);

-- Discounts table
CREATE TABLE discounts (
    discount_id INT PRIMARY KEY AUTO_INCREMENT,
    discount_code VARCHAR(50) UNIQUE NOT NULL,
    discount_name VARCHAR(100) NOT NULL,
    discount_type ENUM('percentage', 'fixed') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    discount_min_amount DECIMAL(10,2) DEFAULT 0,
    discount_max_uses INT,
    discount_used_count INT DEFAULT 0,
    discount_start_date DATE,
    discount_end_date DATE,
    discount_status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    order_status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    order_total DECIMAL(10,2) NOT NULL,
    order_subtotal DECIMAL(10,2) NOT NULL,
    order_tax DECIMAL(10,2) DEFAULT 0,
    order_shipping DECIMAL(10,2) DEFAULT 0,
    order_discount DECIMAL(10,2) DEFAULT 0,
    discount_code VARCHAR(50),
    shipping_address TEXT NOT NULL,
    shipping_city VARCHAR(50) NOT NULL,
    shipping_state VARCHAR(50) NOT NULL,
    shipping_zip VARCHAR(10) NOT NULL,
    shipping_phone VARCHAR(20),
    payment_method ENUM('credit_card', 'paypal', 'cash_on_delivery') NOT NULL,
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    order_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (discount_code) REFERENCES discounts(discount_code) ON DELETE SET NULL
);

-- Order Items table
CREATE TABLE order_items (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    product_price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES product(product_id) ON DELETE CASCADE
);

-- Admin Logs table
CREATE TABLE admin_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values TEXT,
    new_values TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Create indexes for better performance
CREATE INDEX idx_product_brand ON product(product_brand_id);
CREATE INDEX idx_product_category ON product(product_category_id);
CREATE INDEX idx_product_status ON product(product_status);
CREATE INDEX idx_order_user ON orders(user_id);
CREATE INDEX idx_order_status ON orders(order_status);
CREATE INDEX idx_cart_user ON cart(user_id);
CREATE INDEX idx_wishlist_user ON wishlist(user_id);
CREATE INDEX idx_discount_code ON discounts(discount_code);
CREATE INDEX idx_discount_status ON discounts(discount_status);

-- Insert sample data

-- Insert admin user
INSERT INTO users (user_f_name, user_l_name, user_email, user_password, user_role) VALUES
('Admin', 'User', 'admin@glamcart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample brands
INSERT INTO brand (brand_name, brand_description) VALUES
('MAC Cosmetics', 'Professional makeup and cosmetics'),
('L\'Oreal Paris', 'Affordable luxury beauty products'),
('Maybelline', 'Drugstore beauty favorites'),
('Revlon', 'Classic beauty and cosmetics'),
('NYX Professional Makeup', 'Professional quality at affordable prices');

-- Insert sample categories
INSERT INTO category (category_name, category_description) VALUES
('Foundation', 'Base makeup for flawless complexion'),
('Lipstick', 'Colorful lip products'),
('Eyeshadow', 'Eye makeup and palettes'),
('Mascara', 'Lash enhancing products'),
('Skincare', 'Facial care and treatment products'),
('Brushes', 'Makeup application tools');

-- Insert sample products
INSERT INTO product (product_name, product_description, product_code, product_price, product_brand_id, product_category_id, product_stock, product_entry_date) VALUES
('MAC Studio Fix Fluid', 'Long-wearing foundation with SPF 15', 'MAC001', 35.00, 1, 1, 50, '2024-01-15'),
('L\'Oreal Infallible Pro-Matte', '24-hour matte foundation', 'LOR001', 12.99, 2, 1, 75, '2024-01-15'),
('Maybelline Great Lash Mascara', 'Iconic volumizing mascara', 'MAY001', 8.99, 3, 4, 100, '2024-01-15'),
('Revlon ColorStay Lipstick', 'Long-lasting lip color', 'REV001', 9.99, 4, 2, 60, '2024-01-15'),
('NYX Professional Eyeshadow Palette', '16-color eyeshadow palette', 'NYX001', 18.00, 5, 3, 40, '2024-01-15'),
('MAC Ruby Woo Lipstick', 'Iconic red matte lipstick', 'MAC002', 19.00, 1, 2, 30, '2024-01-15'),
('L\'Oreal Voluminous Mascara', 'Volumizing and lengthening mascara', 'LOR002', 10.99, 2, 4, 80, '2024-01-15'),
('Maybelline Fit Me Foundation', 'Natural-looking foundation', 'MAY002', 7.99, 3, 1, 90, '2024-01-15');

-- Insert sample discounts
INSERT INTO discounts (discount_code, discount_name, discount_type, discount_value, discount_min_amount, discount_max_uses, discount_start_date, discount_end_date) VALUES
('WELCOME10', 'Welcome Discount', 'percentage', 10.00, 25.00, 100, '2024-01-01', '2024-12-31'),
('SAVE5', 'Save $5', 'fixed', 5.00, 30.00, 50, '2024-01-01', '2024-06-30'),
('SUMMER20', 'Summer Sale', 'percentage', 20.00, 50.00, 200, '2024-06-01', '2024-08-31');

-- Insert sample store products
INSERT INTO store_product (product_id, store_quantity, store_location) VALUES
(1, 50, 'Warehouse A'),
(2, 75, 'Warehouse A'),
(3, 100, 'Warehouse B'),
(4, 60, 'Warehouse A'),
(5, 40, 'Warehouse B'),
(6, 30, 'Warehouse A'),
(7, 80, 'Warehouse B'),
(8, 90, 'Warehouse A');

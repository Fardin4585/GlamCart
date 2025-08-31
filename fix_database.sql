-- Fix Database Schema for GlamCart
-- Add missing fields to existing tables

-- Add missing fields to users table
ALTER TABLE users 
ADD COLUMN user_role ENUM('customer', 'admin') DEFAULT 'customer' AFTER user_password,
ADD COLUMN user_status ENUM('active', 'inactive') DEFAULT 'active' AFTER user_role,
ADD COLUMN user_city VARCHAR(50) AFTER user_address,
ADD COLUMN user_state VARCHAR(50) AFTER user_city,
ADD COLUMN user_zip VARCHAR(10) AFTER user_state,
ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER user_zip,
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Add missing fields to product table
ALTER TABLE product 
ADD COLUMN product_stock INT DEFAULT 0 AFTER product_sale_price,
ADD COLUMN product_min_stock INT DEFAULT 5 AFTER product_stock,
ADD COLUMN product_description TEXT AFTER product_min_stock;

-- Add missing fields to brand table
ALTER TABLE brand 
ADD COLUMN brand_status ENUM('active', 'inactive') DEFAULT 'active' AFTER brand_origin;

-- Add missing fields to category table
ALTER TABLE category 
ADD COLUMN category_status ENUM('active', 'inactive') DEFAULT 'active' AFTER Category_entry_date,
ADD COLUMN category_description TEXT AFTER category_status;

-- Create order_items table if it doesn't exist
CREATE TABLE IF NOT EXISTS order_items (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (product_id) REFERENCES product(product_id)
);

-- Insert an admin user
INSERT INTO users (user_f_name, user_l_name, user_email, user_password, user_role, user_status) 
VALUES ('Admin', 'User', 'admin@glamcart.com', 'admin123', 'admin', 'active');

-- Update existing products with stock information
UPDATE product SET product_stock = 100 WHERE product_stock = 0;

-- Update existing categories with descriptions
UPDATE category SET category_description = 'Professional makeup products for face application' WHERE Category_id = 1;
UPDATE category SET category_description = 'Skincare products for healthy and glowing skin' WHERE Category_id = 2;
UPDATE category SET category_description = 'Eye makeup products for stunning eye looks' WHERE Category_id = 3;
UPDATE category SET category_description = 'Hair care products for beautiful and healthy hair' WHERE Category_id = 4;

-- Update existing brands with status
UPDATE brand SET brand_status = 'active' WHERE brand_status IS NULL;

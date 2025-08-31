# Database Fix Instructions for GlamCart

## Problem
Your existing `glam_cart.sql` database has a different structure than what the application code expects, causing PHP warnings and errors.

## Solution
You have two options to fix this:

### Option 1: Update Your Database (Recommended)
Run the `fix_database.sql` script to add missing fields to your existing database:

1. Open phpMyAdmin
2. Select your `glam_cart` database
3. Go to the "SQL" tab
4. Copy and paste the contents of `fix_database.sql`
5. Click "Go" to execute the script

This will:
- Add missing fields to existing tables
- Create the `order_items` table
- Add an admin user (email: admin@glamcart.com, password: admin123)
- Update existing data with proper values

### Option 2: Use Your Existing Database Structure
If you prefer to keep your current database structure unchanged, the code has been modified to work with your existing schema. However, some features will be limited.

## Current Database Structure vs Expected Structure

### Your Current Structure:
```sql
-- users table
user_id, user_f_name, user_l_name, user_email, user_password, user_address, user_phone_no

-- category table  
Category_id, Category_Name, Category_entry_date

-- product table
product_id, product_name, product_category, product_code, product_entry_date, product_price, product_brand, product_status, product_featured, created_at, product_image, product_sale_price
```

### Expected Structure (after running fix_database.sql):
```sql
-- users table
user_id, user_f_name, user_l_name, user_email, user_password, user_role, user_status, user_address, user_city, user_state, user_zip, user_phone_no, created_at, updated_at

-- category table
Category_id, Category_Name, Category_entry_date, category_status, category_description

-- product table
product_id, product_name, product_category, product_code, product_entry_date, product_price, product_brand, product_status, product_featured, created_at, product_image, product_sale_price, product_stock, product_min_stock, product_description
```

## Admin Access
After running the database fix script, you can access the admin panel with:
- Email: admin@glamcart.com
- Password: admin123

## Testing
1. Start XAMPP (Apache + MySQL)
2. Import your `glam_cart.sql` database
3. Run the `fix_database.sql` script
4. Access your application at `http://localhost/Storedatabase/`
5. Test login with existing users or register new ones

## Notes
- The application now works with your existing database structure
- Some advanced features require the additional fields from the fix script
- All PHP warnings should be resolved
- The application is fully functional for basic e-commerce operations

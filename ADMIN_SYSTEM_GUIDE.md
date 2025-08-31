# 🛡️ GlamCart Admin System Guide

## Overview
The GlamCart admin system provides secure, role-based access control for managing your e-commerce store. Only designated admin users can access the admin panel and perform administrative tasks.

## 🔐 Admin Login System

### Separate Admin Login
- **Admin Login URL**: `admin_login.php`
- **Regular User Login**: `login.php`
- **Only admin users** can successfully login to `admin_login.php`
- **Non-admin users** will see "Access denied" message

### Admin Roles
1. **Super Admin** - Full access to all features including admin management
2. **Admin** - Standard admin access to products, users, orders
3. **Moderator** - Limited access for basic management tasks

## 🛠️ Setup Instructions

### Step 1: Create Admin Tables
1. Run the setup script: `setup_admin.php`
2. This will create the required `admin_users` and `admin_logs` tables

### Step 2: Create Admin Users
1. In `setup_admin.php`, click "Make Super Admin" for your user
2. You can also create additional admin users with different roles

### Step 3: Access Admin Panel
1. Go to `admin_login.php`
2. Login with your admin credentials
3. Access the admin panel at `admin/`

## 🔧 Admin Management Features

### Role Management
- **Change Admin Role**: Click "Change Role" in setup_admin.php
- **Remove Admin Access**: Click "Remove Admin" in setup_admin.php
- **Add New Admins**: Use the setup script or admin panel

### Admin Panel Features
- **Dashboard**: Overview of store statistics
- **Products**: Add, edit, delete, and manage products
- **Users**: Manage customer accounts
- **Orders**: Track and update order status
- **Admin Management**: Manage other admin users (Super Admin only)

## 🔒 Security Features

### Access Control
- ✅ Only admin users can access admin pages
- ✅ Separate login system for admins
- ✅ Role-based permissions
- ✅ Secure logout redirects

### Session Management
- ✅ Admin sessions are separate from regular user sessions
- ✅ Automatic redirect to admin login for admin users
- ✅ Proper session destruction on logout

### Database Security
- ✅ Prepared statements prevent SQL injection
- ✅ Input validation and sanitization
- ✅ Admin action logging for audit trails

## 📁 File Structure

```
Storedatabase/
├── admin_login.php          # Admin login page
├── setup_admin.php          # Admin setup script
├── admin/                   # Admin panel directory
│   ├── index.php           # Admin dashboard
│   ├── products.php        # Product management
│   ├── users.php           # User management
│   ├── orders.php          # Order management
│   └── manage_admins.php   # Admin user management
└── connection.php          # Database connection & functions
```

## 🚀 Quick Start

1. **Setup Admin Tables**:
   ```
   http://localhost/Storedatabase/setup_admin.php
   ```

2. **Create Your First Admin**:
   - Click "Make Super Admin" for your user account

3. **Login to Admin Panel**:
   ```
   http://localhost/Storedatabase/admin_login.php
   ```

4. **Access Admin Dashboard**:
   ```
   http://localhost/Storedatabase/admin/
   ```

## ⚠️ Security Notes

1. **Delete setup_admin.php** after creating your admin users
2. **Use strong passwords** for admin accounts
3. **Regularly review admin access** and remove unnecessary permissions
4. **Monitor admin logs** for suspicious activity
5. **Keep admin panel URL private** and secure

## 🔄 Admin Workflow

### Daily Operations
1. Login via `admin_login.php`
2. Check dashboard for new orders
3. Manage products and inventory
4. Handle customer inquiries
5. Review admin logs

### User Management
1. Monitor new user registrations
2. Handle user account issues
3. Manage admin permissions
4. Review user activity

### Product Management
1. Add new products
2. Update product information
3. Manage inventory levels
4. Handle product categories and brands

## 📞 Support

If you encounter any issues with the admin system:
1. Check that admin tables exist in your database
2. Verify admin user credentials
3. Ensure proper file permissions
4. Review error logs for specific issues

---

**Remember**: The admin system is designed for security. Always use strong passwords and regularly review admin access permissions.

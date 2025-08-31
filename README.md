# GlamCart - Makeup and Cosmetics Shop Management System

A complete e-commerce web application for managing a makeup and cosmetics shop. Built with PHP, MySQL, HTML, CSS, and JavaScript.

## Features

### Customer Features
- **User Registration & Authentication**: Secure user accounts with password hashing
- **Product Browsing**: Browse products with search, filters, and pagination
- **Shopping Cart**: Add/remove items, update quantities, view cart total
- **Wishlist**: Save favorite products for later
- **Order Management**: Place orders, view order history, track status
- **Responsive Design**: Works perfectly on desktop and mobile devices

### Admin Features
- **Dashboard**: Overview with statistics, recent orders, and quick actions
- **Product Management**: Add, edit, delete products with images and categories
- **Order Management**: Process orders, update status, view order details
- **User Management**: Manage customer accounts and admin users
- **Inventory Management**: Track stock levels, low stock alerts
- **Discount System**: Create and manage promotional codes
- **Reports**: Sales analytics and performance insights

### Technical Features
- **Security**: SQL injection prevention, XSS protection, password hashing
- **Database**: Optimized MySQL database with proper relationships
- **Responsive UI**: Modern, clean design with CSS Grid and Flexbox
- **Search & Filters**: Advanced product search and filtering
- **Pagination**: Efficient data loading for large catalogs
- **Form Validation**: Client-side and server-side validation

## Requirements

- **Web Server**: Apache (XAMPP recommended)
- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher
- **Browser**: Modern web browser with JavaScript enabled

## Installation

### 1. Setup XAMPP
1. Download and install XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Start Apache and MySQL services
3. Place the project files in `htdocs/Storedatabase/` directory

### 2. Database Setup
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create a new database named `glam_cart`
3. Import the database schema:
   ```sql
   -- Run the contents of database_schema.sql
   ```

### 3. Configuration
1. Open `connection.php` and verify database settings:
   ```php
   $hostname = 'localhost';
   $username = 'root';
   $password = '';
   $dbname = 'glam_cart';
   ```

### 4. Access the Application
1. Open your browser and navigate to: `http://localhost/Storedatabase/`
2. The application should now be running!

## Default Admin Account

After importing the database, you can login with:
- **Email**: admin@glamcart.com
- **Password**: password

**Important**: Change the default admin password after first login!

## Project Structure

```
Storedatabase/
├── assets/
│   ├── css/
│   │   └── style.css          # Main stylesheet
│   ├── js/
│   │   └── script.js          # JavaScript functionality
│   └── images/                # Product images and icons
├── admin/
│   ├── dashboard.php          # Admin dashboard
│   ├── products.php           # Product management
│   ├── orders.php             # Order management
│   ├── users.php              # User management
│   └── ...                    # Other admin pages
├── connection.php             # Database connection
├── index.php                  # Homepage
├── login.php                  # User login
├── register.php               # User registration
├── shop.php                   # Product catalog
├── cart.php                   # Shopping cart
├── checkout.php               # Checkout process
├── database_schema.sql        # Database structure
└── README.md                  # This file
```

## Database Schema

### Core Tables
- **users**: Customer and admin user accounts
- **product**: Product catalog with details
- **brand**: Product brands
- **category**: Product categories
- **orders**: Customer orders
- **order_items**: Individual items in orders
- **cart**: Shopping cart items
- **wishlist**: User wishlist items
- **discounts**: Promotional codes and discounts

### Management Tables
- **store_product**: Inventory management
- **spend_product**: Sales tracking
- **admin_logs**: Admin activity logging

## Key Features Explained

### Security Implementation
- **Password Hashing**: Uses PHP's `password_hash()` and `password_verify()`
- **SQL Injection Prevention**: Prepared statements for all database queries
- **XSS Protection**: `htmlspecialchars()` for output sanitization
- **Session Management**: Secure session handling with proper validation

### Responsive Design
- **Mobile-First**: Designed for mobile devices first
- **CSS Grid & Flexbox**: Modern layout techniques
- **Progressive Enhancement**: Works without JavaScript
- **Touch-Friendly**: Optimized for touch interfaces

### Performance Optimization
- **Database Indexing**: Proper indexes on frequently queried columns
- **Pagination**: Limits data loading for better performance
- **Image Optimization**: Responsive images with proper sizing
- **Caching**: Browser caching for static assets

## Customization

### Styling
- Modify `assets/css/style.css` to change the appearance
- CSS variables are used for easy color scheme changes
- Responsive breakpoints can be adjusted in the CSS

### Functionality
- JavaScript functions are in `assets/js/script.js`
- PHP helper functions are in `connection.php`
- Database queries can be optimized in individual files

### Adding Features
- New admin pages should follow the existing pattern in the `admin/` directory
- Customer pages should maintain the same header/footer structure
- Always use prepared statements for database operations

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Verify XAMPP is running
   - Check database credentials in `connection.php`
   - Ensure database `glam_cart` exists

2. **Page Not Found (404)**
   - Check file paths and permissions
   - Verify .htaccess configuration
   - Ensure Apache mod_rewrite is enabled

3. **Images Not Loading**
   - Check `assets/images/` directory exists
   - Verify file permissions
   - Check image file paths in database

4. **Login Issues**
   - Clear browser cookies and cache
   - Verify database has admin user
   - Check session configuration

### Debug Mode
To enable debug mode, add this to the top of PHP files:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is open source and available under the [MIT License](LICENSE).

## Support

For support and questions:
- Check the troubleshooting section above
- Review the code comments for implementation details
- Create an issue in the repository

## Changelog

### Version 1.0.0
- Initial release
- Complete e-commerce functionality
- Admin dashboard
- Responsive design
- Security features

---

**GlamCart** - Making beauty shopping beautiful! ✨

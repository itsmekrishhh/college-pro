# Bella Italia - Italian Food Ordering Platform

A fully functional e-commerce website for ordering Italian food (Pizza, Carbonara, Tiramisu, Lasagna). Built with PHP, MySQL, HTML, CSS, and JavaScript. Designed to run on XAMPP.

## Features

- **User Authentication**: Registration, login, and role-based access (Customer/Admin)
- **Product Management**: Browse products by category, search, filter, and view details
- **Shopping Cart**: Add items, update quantities, remove items
- **Order System**: Complete checkout flow with delivery/collection options
- **Customer Dashboard**: View orders, track order status, view reports
- **Admin Dashboard**: Manage products, orders, customers, and generate reports
- **Responsive Design**: Clean, simple black and white design

## Requirements

- XAMPP (Apache + MySQL)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser

## Installation & Setup

### Step 1: Start XAMPP

1. Open XAMPP Control Panel
2. Start **Apache** service
3. Start **MySQL** service

### Step 2: Database Setup

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Click on "New" to create a database (optional - the SQL file will create it)
3. Click on "Import" tab
4. Choose the file: `database.sql`
5. Click "Go" to import

**OR** manually:

1. Create a database named `bella_italia`
2. Select the database
3. Go to SQL tab
4. Copy and paste the contents of `database.sql`
5. Click "Go"

### Step 3: Configure Database Connection

1. Open `config/db.php`
2. Verify database credentials (default XAMPP settings):
   ```php
   DB_HOST = 'localhost'
   DB_USER = 'root'
   DB_PASS = '' (empty for default XAMPP)
   DB_NAME = 'bella_italia'
   ```

### Step 4: Set Up User Accounts

**Option A: Generate Password Hashes (For Sample Users)**

1. Open in browser: `http://localhost/college-pro/generate_password_hash.php`
2. Copy the generated password hashes
3. Update the `users` table in phpMyAdmin:
   - Go to `bella_italia` database → `users` table
   - Edit the admin and customer user records
   - Replace the password field with the generated hash
   - Default passwords:
     - Admin: `admin123`
     - Customer: `customer123`

**Option B: Register New Users (Recommended)**

1. Go to: `http://localhost/college-pro/auth/register.php`
2. Create your own accounts
3. The password will be automatically hashed

### Step 5: Access the Website

1. **Customer Site**: `http://localhost/college-pro/`
2. **Login Page**: `http://localhost/college-pro/auth/login.php`
3. **Admin Dashboard**: `http://localhost/college-pro/admin/dashboard.php`

## Project Structure

```
college-pro/
│
├── admin/                 # Admin panel
│   ├── dashboard.php
│   ├── products.php
│   ├── add_product.php
│   ├── edit_product.php
│   ├── delete_product.php
│   ├── orders.php
│   ├── customers.php
│   └── reports.php
│
├── customer/              # Customer dashboard
│   ├── dashboard.php
│   ├── orders.php
│   ├── order_status.php
│   └── reports.php
│
├── auth/                  # Authentication
│   ├── login.php
│   ├── register.php
│   └── logout.php
│
├── cart/                  # Shopping cart system
│   ├── cart.php
│   ├── checkout.php
│   ├── payment.php
│   └── place_order.php
│
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── script.js
│   └── images/
│
├── config/
│   └── db.php            # Database configuration
│
├── index.php             # Home page
├── products.php          # Product listing
├── product_details.php   # Product details
├── thank_you.php         # Order confirmation
├── database.sql          # Database schema
└── generate_password_hash.php  # Password hash generator
```

## Default Accounts

After setting up password hashes:

**Admin Account:**
- Email: `admin@bellaitalia.com`
- Password: `admin123` (after generating hash)

**Customer Account:**
- Email: `customer@example.com`
- Password: `customer123` (after generating hash)

## Usage Guide

### For Customers:

1. **Register/Login**: Create an account or login
2. **Browse Products**: View products by category or search
3. **Add to Cart**: Click on product to view details and add to cart
4. **Checkout**: Review cart, proceed to checkout
5. **Payment**: Select payment method (Cash on Delivery available)
6. **Track Orders**: View order status in your dashboard

### For Admins:

1. **Login**: Use admin credentials
2. **Dashboard**: View statistics and recent orders
3. **Manage Products**: Add, edit, or delete products
4. **Manage Orders**: View and update order statuses
5. **Manage Customers**: View and manage customer accounts
6. **Reports**: Generate sales and product reports

## Features Overview

### Customer Features:
- User registration and authentication
- Product browsing with categories
- Search and filter products
- Shopping cart management
- Order placement (delivery/collection)
- Order tracking
- Order history and reports

### Admin Features:
- Product CRUD operations
- Order management with status updates
- Customer management
- Sales reports and statistics
- Dashboard with key metrics

## Database Schema

**Tables:**
- `users` - User accounts (customers and admins)
- `categories` - Product categories
- `products` - Product information
- `orders` - Order records
- `order_items` - Order line items
- `payments` - Payment records

**Relationships:**
- Products → Categories (Foreign Key)
- Orders → Users (Foreign Key)
- Order Items → Orders & Products (Foreign Keys)
- Payments → Orders (Foreign Key)

## Troubleshooting

### Database Connection Error
- Check if MySQL is running in XAMPP
- Verify database credentials in `config/db.php`
- Ensure database `bella_italia` exists

### Password Not Working
- Generate new password hash using `generate_password_hash.php`
- Update the password field in `users` table
- Or register a new account through the website

### Images Not Showing
- Image uploads are placeholders (functionality can be added)
- Product images show as placeholders with dashed borders

### Page Not Found (404)
- Ensure Apache is running in XAMPP
- Check that files are in `htdocs/college-pro/` directory
- Verify URL paths (should be `http://localhost/college-pro/...`)

## Technologies Used

- **Backend**: PHP (Procedural)
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Server**: XAMPP (Apache)
- **Password Security**: PHP `password_hash()` and `password_verify()`

## Security Features

- Password hashing using `password_hash()`
- Prepared statements to prevent SQL injection
- Session-based authentication
- Role-based access control
- Input validation and sanitization

## Notes

- Image uploads are placeholders (you can add file upload functionality)
- Payment methods: Cash on Delivery (active), others are placeholders
- All pages are functional and interconnected
- Code is commented and beginner-friendly
- Designed as a college project with clean, simple structure

## License

This is a college project for educational purposes.

## Support

For issues or questions:
1. Check the troubleshooting section
2. Verify all requirements are met
3. Ensure XAMPP services are running
4. Check database connection settings

---

**Happy Coding! 🍕🍝🍰**


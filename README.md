# 🍽️ Premium Bistro - Modern Restaurant Website

A modern, premium restaurant website built with **React.js (via CDN)**, **Tailwind CSS**, **PHP**, and **MySQL** featuring a stunning glassmorphism design and comprehensive restaurant management system.

## ✨ Features

### 🎨 User Interface (React + HTML)
- **Modern Glassmorphism Design** with blurred cards and transparent effects
- **Premium Fonts**: Poppins, Montserrat, Inter for elegant typography
- **Fully Mobile Responsive** design that works on all devices
- **Bottom Glassmorphism Navigation Bar** with smooth animations
- **Hero Section** with floating animations and call-to-action
- **Interactive Menu** with real-time cart management
- **Smart Shopping Cart** with localStorage integration
- **Secure Checkout Process** with order placement
- **User Authentication** (Login/Register)
- **User Profile** with order history

### 🛠️ Admin Panel (PHP + Tailwind)
- **Secure Admin Authentication**
- **Dashboard** with comprehensive statistics
- **Dish Management**: Add, edit, delete, and toggle visibility
- **Order Management**: Accept, reject, and track orders
- **User Management**: View customer information and analytics
- **Settings Panel**: Configure restaurant information and branding
- **Real-time Statistics**: Users, orders, revenue, and more

### 💾 Backend (PHP + MySQL)
- **RESTful API Architecture** with proper error handling
- **Secure Authentication** with password hashing and JWT-like tokens
- **Database Relationships** with foreign key constraints
- **Transaction Management** for order processing
- **CORS Support** for frontend-backend communication

## 🛠️ Tech Stack

- **Frontend**: React.js (via CDN), HTML5, CSS3, JavaScript ES6+
- **Styling**: Tailwind CSS (via CDN), Custom CSS for glassmorphism effects
- **Backend**: PHP 7.4+, cURL for API communication
- **Database**: MySQL 5.7+
- **Icons**: Font Awesome 6.4.0
- **Fonts**: Google Fonts (Poppins, Montserrat, Inter)

## 📁 Project Structure

```
restaurant-website/
├── index.html              # Main user interface (React)
├── database.sql            # Database schema and sample data
├── README.md              # Project documentation
├── config/
│   └── database.php       # Database configuration
├── api/                   # Backend API endpoints
│   ├── auth.php          # Authentication API
│   ├── dishes.php        # Menu management API
│   ├── cart.php          # Shopping cart API
│   ├── orders.php        # Order management API
│   └── admin.php         # Admin panel API
├── admin/                 # Admin panel pages
│   ├── index.php         # Admin login
│   ├── dashboard.php     # Admin dashboard
│   ├── dishes.php        # Dish management
│   ├── orders.php        # Order management
│   ├── users.php         # User management
│   └── settings.php      # Restaurant settings
├── uploads/              # File upload directory
└── assets/               # Static assets
    ├── css/
    ├── js/
    └── dishes/           # Dish images
```

## 🚀 Installation & Setup

### Prerequisites
- **Web Server** (Apache/Nginx)
- **PHP 7.4** or higher
- **MySQL 5.7** or higher
- **Modern Web Browser**

### Step 1: Download & Extract
```bash
# Clone or download the project
git clone <repository-url>
cd restaurant-website
```

### Step 2: Database Setup
1. Create a MySQL database named `restaurant_db`
2. Import the database schema:
```sql
mysql -u your_username -p restaurant_db < database.sql
```

### Step 3: Configure Database Connection
Edit `config/database.php` and update the database credentials:
```php
private $host = 'localhost';
private $db_name = 'restaurant_db';
private $username = 'your_mysql_username';
private $password = 'your_mysql_password';
```

### Step 4: Set Permissions
```bash
# Make uploads directory writable
chmod 755 uploads/
chmod 755 assets/
```

### Step 5: Access the Website
- **Customer Interface**: `http://your-domain.com/index.html`
- **Admin Panel**: `http://your-domain.com/admin/`

## 🔐 Default Login Credentials

### Admin Panel
- **Username**: `admin`
- **Password**: `admin123`

## 🎯 User Guide

### For Customers
1. **Browse Menu**: View available dishes with prices and descriptions
2. **Add to Cart**: Click "Add to Cart" on any dish
3. **Manage Cart**: Adjust quantities or remove items
4. **Register/Login**: Create an account or login to existing account
5. **Checkout**: Enter delivery address and place order
6. **Track Orders**: View order history and status in profile

### For Administrators
1. **Login**: Access admin panel with credentials
2. **Dashboard**: View overall statistics and system status
3. **Manage Dishes**: Add new dishes, edit existing ones, toggle visibility
4. **Process Orders**: Accept/reject orders, mark as delivered
5. **View Customers**: Monitor customer registrations and activity
6. **Configure Settings**: Update restaurant name, logo, and theme

## 📱 Mobile Responsiveness

The website is fully responsive and optimized for:
- **Desktop** (1920px+)
- **Laptop** (1024px-1919px)
- **Tablet** (768px-1023px)
- **Mobile** (320px-767px)

## 🎨 Design Features

### Glassmorphism Effects
- **Transparent backgrounds** with backdrop blur
- **Subtle borders** and shadows
- **Layered depth** for modern aesthetics
- **Smooth animations** and transitions

### Color Scheme
- **Primary**: Purple to Blue gradients (#667eea to #764ba2)
- **Accents**: Green, Yellow, Red for status indicators
- **Text**: White on dark backgrounds for contrast
- **Glassmorphism**: Semi-transparent whites and blacks

## 🔧 API Endpoints

### Authentication
- `POST /api/auth.php?action=register` - User registration
- `POST /api/auth.php?action=login` - User login
- `GET /api/auth.php?action=profile` - Get user profile

### Menu
- `GET /api/dishes.php` - Get visible dishes

### Cart
- `GET /api/cart.php` - Get cart items
- `POST /api/cart.php` - Add item to cart
- `PUT /api/cart.php` - Update cart item
- `DELETE /api/cart.php` - Remove from cart

### Orders
- `GET /api/orders.php` - Get user orders
- `POST /api/orders.php` - Create new order

### Admin
- `POST /api/admin.php?action=login` - Admin login
- `GET /api/admin.php?action=dashboard` - Dashboard stats
- `GET /api/admin.php?action=dishes` - All dishes
- `GET /api/admin.php?action=orders` - All orders
- `GET /api/admin.php?action=users` - All users

## 🔒 Security Features

- **Password Hashing** using PHP's `password_hash()`
- **SQL Injection Protection** with prepared statements
- **XSS Prevention** with input sanitization
- **CSRF Protection** with token validation
- **Session Management** for admin authentication
- **Input Validation** on both frontend and backend

## 🎯 Performance Optimizations

- **CDN Usage** for external libraries
- **Lightweight Assets** with optimized images
- **Efficient Database Queries** with proper indexing
- **Caching Headers** for static assets
- **Minified CSS/JS** for faster loading

## 🚀 Future Enhancements

- **Real-time Notifications** for order updates
- **Payment Gateway Integration** (Stripe, PayPal)
- **Email Notifications** for orders and registration
- **Multi-language Support** (i18n)
- **Advanced Analytics** and reporting
- **Mobile App** development
- **Social Media Integration**
- **Review and Rating System**

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `config/database.php`
   - Ensure MySQL service is running
   - Verify database name exists

2. **Permission Denied**
   - Check file permissions for uploads directory
   - Ensure web server has write access

3. **API Errors**
   - Check PHP error logs
   - Verify PHP version compatibility
   - Enable error reporting for debugging

4. **Styling Issues**
   - Clear browser cache
   - Check internet connection for CDN resources
   - Verify Tailwind CSS CDN link

## 📞 Support

For technical support or questions:
- Check the troubleshooting section
- Review PHP error logs
- Verify database connectivity
- Ensure all dependencies are properly installed

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

---

**Built with ❤️ for modern restaurants seeking a premium online presence.**
-- Restaurant Website Database Schema
CREATE DATABASE IF NOT EXISTS restaurant_db;
USE restaurant_db;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Dishes table
CREATE TABLE dishes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    visible BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cart table
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    dish_id INT,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (dish_id) REFERENCES dishes(id) ON DELETE CASCADE
);

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total DECIMAL(10,2) NOT NULL,
    address TEXT NOT NULL,
    status ENUM('pending', 'accepted', 'rejected', 'delivered') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    dish_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (dish_id) REFERENCES dishes(id) ON DELETE CASCADE
);

-- Admin table
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Site settings table
CREATE TABLE site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_name VARCHAR(100) DEFAULT 'Premium Restaurant',
    logo_url VARCHAR(255) DEFAULT 'assets/logo.png',
    theme_color VARCHAR(7) DEFAULT '#6366f1'
);

-- Insert default admin (password: admin123)
INSERT INTO admin (username, password) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert default site settings
INSERT INTO site_settings (restaurant_name, logo_url, theme_color) VALUES 
('Premium Bistro', 'assets/logo.png', '#6366f1');

-- Insert sample dishes
INSERT INTO dishes (name, description, price, image, visible) VALUES
('Grilled Salmon', 'Fresh Atlantic salmon with herbs and lemon', 24.99, 'assets/dishes/salmon.jpg', 1),
('Ribeye Steak', 'Premium aged ribeye with garlic butter', 32.99, 'assets/dishes/steak.jpg', 1),
('Truffle Pasta', 'Handmade pasta with black truffle and parmesan', 28.99, 'assets/dishes/pasta.jpg', 1),
('Caesar Salad', 'Fresh romaine with house-made dressing', 12.99, 'assets/dishes/salad.jpg', 1),
('Chocolate Soufflé', 'Warm chocolate soufflé with vanilla ice cream', 14.99, 'assets/dishes/souffle.jpg', 1),
('Lobster Bisque', 'Rich and creamy lobster soup', 18.99, 'assets/dishes/bisque.jpg', 1);
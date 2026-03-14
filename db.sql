-- Create database (if not exists)
CREATE DATABASE IF NOT EXISTS simple_ecommerce;
USE simple_ecommerce;

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(255),
    stock INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    state VARCHAR(100),
    city VARCHAR(100),
    pincode VARCHAR(10),
    photo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Admin table
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total_price DECIMAL(10, 2) NOT NULL,
    status ENUM('Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Pending',
    restocked TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Order Items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- Homepage hero/offer content (single row, id = 1)
CREATE TABLE IF NOT EXISTS hero_content (
    id INT PRIMARY KEY,
    headline VARCHAR(150) NOT NULL,
    subheadline VARCHAR(255) NOT NULL,
    cta_text VARCHAR(80) NOT NULL,
    cta_url VARCHAR(255) NOT NULL,
    offer_text VARCHAR(120) DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Seed default hero content
INSERT INTO hero_content (id, headline, subheadline, cta_text, cta_url, offer_text, image)
VALUES (1, 'Simple E-commerce', 'A clean PHP & MySQL store template—easy to browse, easy to extend.', 'Shop Now', 'products.php', 'Spring sale: up to 40% off select items', '1773396492_DL-Technology.jpg')
ON DUPLICATE KEY UPDATE
    headline = VALUES(headline),
    subheadline = VALUES(subheadline),
    cta_text = VALUES(cta_text),
    cta_url = VALUES(cta_url),
    offer_text = VALUES(offer_text);

-- Insert sample categories
INSERT INTO categories (name, description) VALUES 
('Electronics', 'Gadgets and electronic devices'),
('Fashion', 'Clothing and apparel'),
('Home & Garden', 'Items for your home and garden');

-- Default admin password: changeme123 (please reset after install)
INSERT INTO admin (username, password) VALUES ('admin', '$2y$10$pFtbi5aVPpZSIkTBlv/RWughderBNMQwnzjfP053OwoXzTJ99/mPC');

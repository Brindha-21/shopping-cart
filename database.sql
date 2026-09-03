CREATE DATABASE IF NOT EXISTS shopping_cart_db;
USE shopping_cart_db;


CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) NOT NULL,
    category VARCHAR(50) DEFAULT 'General',
    stock INT DEFAULT 20,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(50) NOT NULL,
    zip_code VARCHAR(20) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'Cash on Delivery',
    status VARCHAR(50) DEFAULT 'Placed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);


INSERT INTO products (name, description, price, image, category, stock) VALUES
('Wireless ANC Headphones', 'High-fidelity active noise-canceling over-ear wireless headphones with 30h battery life.', 129.99, 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=600&q=80', 'Electronics', 15),
('Ultra-Smart Watch Pro', 'Fitness tracking smartwatch with AMOLED display, heart rate monitor, and GPS.', 199.50, 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=600&q=80', 'Electronics', 12),
('Ergonomic Mechanical Keyboard', 'RGB backlit tactile mechanical keyboard designed for high productivity and gaming.', 89.00, 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?auto=format&fit=crop&w=600&q=80', 'Electronics', 25),
('Minimalist Canvas Backpack', 'Water-resistant durable laptop canvas backpack with USB charging port.', 49.99, 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=600&q=80', 'Fashion', 30),
('Precision Optical Wireless Mouse', 'Sleek ergonomic rechargeable wireless mouse with hyper-scroll functionality.', 34.99, 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?auto=format&fit=crop&w=600&q=80', 'Electronics', 40),
('Stainless Steel Thermal Bottle', 'Vacuum insulated double-wall 32oz water bottle that keeps drinks cold for 24 hours.', 24.50, 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?auto=format&fit=crop&w=600&q=80', 'Home & Living', 50),
('Studio Quality USB Condenser Mic', 'Crystal-clear audio microphone ideal for podcasting, streaming, and voice recording.', 75.00, 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=600&q=80', 'Electronics', 18),
('Modern Desk LED Lamp', 'Dimmable eye-caring desk lamp with wireless smartphone charging pad base.', 42.00, 'https://images.unsplash.com/photo-1534353436294-0dbd4bdac845?auto=format&fit=crop&w=600&q=80', 'Home & Living', 22);

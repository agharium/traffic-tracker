-- Traffic Tracker Database Schema
-- Run this SQL to create the complete database structure

-- Drop tables if they exist (in reverse order due to foreign keys)
DROP TABLE IF EXISTS traffic_logs;
DROP TABLE IF EXISTS websites;
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create websites table
CREATE TABLE websites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    domain VARCHAR(255) NOT NULL,
    api_key VARCHAR(64) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create traffic_logs table
CREATE TABLE traffic_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    website_id INT NOT NULL,
    page_url TEXT NOT NULL,
    referrer TEXT,
    user_agent TEXT,
    ip_address VARCHAR(45),
    session_hash VARCHAR(64),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (website_id) REFERENCES websites(id) ON DELETE CASCADE,
    INDEX idx_website_timestamp (website_id, timestamp),
    INDEX idx_session_hash (session_hash),
    INDEX idx_timestamp (timestamp)
);

-- Insert default admin user
-- Default credentials: admin@example.com / admin / admin
INSERT INTO users (email, username, password, name) VALUES 
('admin@example.com', 'admin', '$2y$12$40Mi.e/aI3bU5iy9lN6EyuD7dCrLJCICSeYt/fvVp4JE9xtNF2W52', 'Administrator');

-- Insert sample website for the admin user
INSERT INTO websites (user_id, name, domain, api_key) VALUES 
(1, 'Example Site', 'example.com', 'sample_api_key_replace_with_real_one_12345678901234567890123456');

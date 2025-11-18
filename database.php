<?php
/**
 * DATABASE SETUP SCRIPT
 * Run this once to create all required tables for Vasha Tech System
 * This script will create the database and all necessary tables
 */

// Database connection - First connect without selecting database
$host = 'localhost';
$user = 'root';
$password = '';

// Create connection without database
$conn = new mysqli($host, $user, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Starting database setup for Vasha Tech System...<br>";

// Create database if it doesn't exist
$database = 'database_vasha';
$create_db = "CREATE DATABASE IF NOT EXISTS $database";

if ($conn->query($create_db) === TRUE) {
    echo "✓ Database 'database_vasha' created or already exists<br>";
} else {
    die("✗ Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($database);
echo "✓ Using database: database_vasha<br>";

// Create devices table
$devices_table = "
CREATE TABLE IF NOT EXISTS devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_type VARCHAR(50) NOT NULL,
    model VARCHAR(100) NOT NULL,
    serial_number VARCHAR(100) UNIQUE NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    processor VARCHAR(100) NOT NULL,
    ram VARCHAR(50) NOT NULL,
    storage VARCHAR(100) NOT NULL,
    graphics_card VARCHAR(100) NOT NULL,
    device_condition VARCHAR(50) NOT NULL,
    additional_details TEXT,
    prev_first_name VARCHAR(100),
    prev_last_name VARCHAR(100),
    prev_id_number VARCHAR(50),
    prev_phone VARCHAR(20),
    prev_usage VARCHAR(50),
    repair_history TEXT,
    status ENUM('available', 'sold') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($devices_table) === TRUE) {
    echo "✓ Devices table created successfully<br>";
} else {
    echo "✗ Error creating devices table: " . $conn->error . "<br>";
}

// Create device_images table
$images_table = "
CREATE TABLE IF NOT EXISTS device_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id INT NOT NULL,
    image_type ENUM('face', 'back', 'side') NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE
)";

if ($conn->query($images_table) === TRUE) {
    echo "✓ Device images table created successfully<br>";
} else {
    echo "✗ Error creating device images table: " . $conn->error . "<br>";
}

// Create warranties table - UPDATED WITH MISSING FIELDS
$warranties_table = "
CREATE TABLE IF NOT EXISTS warranties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id INT,
    customer_name VARCHAR(100) NOT NULL,
    product_model VARCHAR(100) NOT NULL,
    serial_number VARCHAR(100) NOT NULL,
    purchase_date DATE NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    warranty_type ENUM('standard', 'extended', 'premium') NOT NULL,
    customer_email VARCHAR(100),
    customer_phone VARCHAR(20) NOT NULL,
    customer_address TEXT,
    terms_agreed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE SET NULL
)";

if ($conn->query($warranties_table) === TRUE) {
    echo "✓ Warranties table created successfully<br>";
} else {
    echo "✗ Error creating warranties table: " . $conn->error . "<br>";
}

// CREATE SEPARATE TABLES FOR ADMINS AND CLIENTS

// Create admins table
$admins_table = "
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstName VARCHAR(100) NOT NULL,
    lastName VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phoneNumber VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    admin_level ENUM('super_admin', 'manager', 'staff') DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($admins_table) === TRUE) {
    echo "✓ Admins table created successfully<br>";
} else {
    echo "Note: Admins table - " . $conn->error . "<br>";
}

// Create clients table
$clients_table = "
CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstName VARCHAR(100) NOT NULL,
    lastName VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phoneNumber VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($clients_table) === TRUE) {
    echo "✓ Clients table created successfully<br>";
} else {
    echo "Note: Clients table - " . $conn->error . "<br>";
}

// Drop the old registration table since we're using separate tables now
$drop_old_table = "DROP TABLE IF EXISTS registration";
if ($conn->query($drop_old_table) === TRUE) {
    echo "✓ Old registration table removed<br>";
}

echo "<br>✅ Database setup completed successfully!<br>";
echo "You can now use the Vasha Tech Tracking System with separate admin and client tables.<br>";

$conn->close();
?>
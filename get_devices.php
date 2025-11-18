<?php
/**
 * GET DEVICES - Returns all available devices for dropdown and client display
 * Used by both admin mainpage and Vasha Tech client page
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'database_vasha';

try {
    $conn = new mysqli($host, $user, $password, $database);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Get all available devices ordered by newest first
    $sql = "SELECT id, device_type, model, serial_number, price, processor, ram, 
                   storage, graphics_card, device_condition, created_at
            FROM devices 
            WHERE status = 'available' 
            ORDER BY created_at DESC";
    
    $result = $conn->query($sql);
    
    $devices = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $devices[] = $row;
        }
    }
    
    echo json_encode($devices);
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Failed to fetch devices: ' . $e->getMessage()]);
}
?>
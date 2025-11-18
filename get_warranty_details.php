<?php
/**
 * GET WARRANTY DETAILS FOR A DEVICE
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'database_vasha';

header('Content-Type: application/json');

try {
    $conn = new mysqli($host, $user, $password, $database);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Get device ID from request
    $device_id = isset($_GET['device_id']) ? intval($_GET['device_id']) : 0;
    
    if (!$device_id) {
        throw new Exception("Device ID is required");
    }
    
    // First, get the device serial number
    $device_sql = "SELECT serial_number FROM devices WHERE id = ?";
    $stmt = $conn->prepare($device_sql);
    $stmt->bind_param("i", $device_id);
    $stmt->execute();
    $device_result = $stmt->get_result();
    
    if ($device_result->num_rows === 0) {
        throw new Exception("Device not found");
    }
    
    $device = $device_result->fetch_assoc();
    $serial_number = $device['serial_number'];
    
    // Now get warranty details using the serial number
    $warranty_sql = "SELECT * FROM warranties WHERE serial_number = ? ORDER BY created_at DESC LIMIT 1";
    $stmt = $conn->prepare($warranty_sql);
    $stmt->bind_param("s", $serial_number);
    $stmt->execute();
    $warranty_result = $stmt->get_result();
    
    if ($warranty_result->num_rows > 0) {
        $warranty = $warranty_result->fetch_assoc();
        
        // Format dates for better display
        $warranty['purchase_date'] = date('Y-m-d', strtotime($warranty['purchase_date']));
        $warranty['start_date'] = date('Y-m-d', strtotime($warranty['start_date']));
        $warranty['end_date'] = date('Y-m-d', strtotime($warranty['end_date']));
        
        // Format warranty type for display
        $warranty_types = [
            'standard' => 'Standard Warranty (3 Months)',
            'extended' => 'Extended Warranty (6 Months)',
            'premium' => 'Premium Warranty (1 Year)'
        ];
        $warranty['warranty_type_display'] = $warranty_types[$warranty['warranty_type']] ?? $warranty['warranty_type'];
        
        echo json_encode([
            'success' => true,
            'warranty' => $warranty
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No warranty found for this device'
        ]);
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
<?php
/**
 * GET DEVICE DETAILS - Returns detailed information for a specific device
 */

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
    
    if (!isset($_GET['device_id']) || empty($_GET['device_id'])) {
        echo json_encode(['success' => false, 'error' => 'No device ID provided']);
        exit();
    }
    
    $device_id = $conn->real_escape_string($_GET['device_id']);
    
    // Get device details
    $device_sql = "SELECT * FROM devices WHERE id = '$device_id'";
    $device_result = $conn->query($device_sql);
    
    if ($device_result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Device not found']);
        exit();
    }
    
    $device = $device_result->fetch_assoc();
    
    // Get device images
    $images_sql = "SELECT image_path, image_type FROM device_images WHERE device_id = '$device_id'";
    $images_result = $conn->query($images_sql);
    $images = [];
    
    if ($images_result->num_rows > 0) {
        while($row = $images_result->fetch_assoc()) {
            $images[] = $row;
        }
    }
    
    // Prepare response
    $response = [
        'success' => true,
        'device' => $device,
        'images' => $images
    ];
    
    echo json_encode($response);
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Failed to fetch device details: ' . $e->getMessage()]);
}
?>
<?php
/**
 * SAVE WARRANTY DETAILS
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
    
    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }
    
    // Get and validate form data
    $customer_name = $conn->real_escape_string(trim($_POST['customer_name'] ?? ''));
    $product_model = $conn->real_escape_string(trim($_POST['product_model'] ?? ''));
    $serial_number = $conn->real_escape_string(trim($_POST['serial_number'] ?? ''));
    $purchase_date = $conn->real_escape_string(trim($_POST['purchase_date'] ?? ''));
    $start_date = $conn->real_escape_string(trim($_POST['start_date'] ?? ''));
    $end_date = $conn->real_escape_string(trim($_POST['end_date'] ?? ''));
    $warranty_type = $conn->real_escape_string(trim($_POST['warranty_type'] ?? ''));
    $customer_email = $conn->real_escape_string(trim($_POST['customer_email'] ?? ''));
    $customer_phone = $conn->real_escape_string(trim($_POST['customer_phone'] ?? ''));
    $customer_address = $conn->real_escape_string(trim($_POST['customer_address'] ?? ''));
    $terms_agreed = isset($_POST['agree_terms']) ? 1 : 0;
    
    // Validate required fields
    if (empty($customer_name) || empty($product_model) || empty($serial_number) || 
        empty($purchase_date) || empty($start_date) || empty($end_date) || empty($warranty_type)) {
        throw new Exception("All required fields must be filled");
    }
    
    // Check if terms were agreed
    if (!$terms_agreed) {
        throw new Exception("You must agree to the terms and conditions");
    }
    
    // Validate dates
    if (!strtotime($purchase_date) || !strtotime($start_date) || !strtotime($end_date)) {
        throw new Exception("Invalid date format");
    }
    
    // Check if warranty already exists for this serial number
    // REMOVED status check since it doesn't exist in the table
    $check_sql = "SELECT id FROM warranties WHERE serial_number = '$serial_number'";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows > 0) {
        throw new Exception("A warranty already exists for this serial number");
    }
    
    // Find device_id from serial number (optional)
    $device_id = null;
    $device_query = "SELECT id FROM devices WHERE serial_number = '$serial_number'";
    $device_result = $conn->query($device_query);
    if ($device_result->num_rows > 0) {
        $device = $device_result->fetch_assoc();
        $device_id = $device['id'];
    }
    
    // Insert warranty into database - UPDATED TO MATCH ACTUAL TABLE STRUCTURE
    $sql = "INSERT INTO warranties (
        device_id, customer_name, product_model, serial_number, purchase_date, 
        start_date, end_date, warranty_type, customer_email, 
        customer_phone, customer_address, terms_agreed
    ) VALUES (
        " . ($device_id ? "'$device_id'" : "NULL") . ",
        '$customer_name', '$product_model', '$serial_number', '$purchase_date',
        '$start_date', '$end_date', '$warranty_type', '$customer_email',
        '$customer_phone', '$customer_address', '$terms_agreed'
    )";
    
    if ($conn->query($sql) === TRUE) {
        $warranty_id = $conn->insert_id;
        
        echo json_encode([
            'success' => true,
            'message' => 'Warranty saved successfully!',
            'warranty_id' => $warranty_id,
            'data' => [
                'customer_name' => $customer_name,
                'product_model' => $product_model,
                'serial_number' => $serial_number,
                'warranty_type' => $warranty_type,
                'end_date' => $end_date
            ]
        ]);
    } else {
        throw new Exception("Error saving warranty: " . $conn->error);
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
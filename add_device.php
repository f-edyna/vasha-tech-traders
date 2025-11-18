<?php
/**
 * ADD DEVICE - Handles adding new devices to the system from admin panel
 * Processes form data and inserts into devices table
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json');

// Check if user is logged in (basic session check)
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

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

    // Only process POST requests
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        
        // DEBUG: Log all received POST data
        error_log("Received POST data: " . print_r($_POST, true));
        
        // Get form data - using exact field names from your HTML form
        $device_type = $conn->real_escape_string($_POST['type'] ?? '');
        $model = $conn->real_escape_string($_POST['model'] ?? '');
        $serial_number = $conn->real_escape_string($_POST['serial_number'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $processor = $conn->real_escape_string($_POST['processor'] ?? '');
        $ram = $conn->real_escape_string($_POST['ram'] ?? '');
        $storage = $conn->real_escape_string($_POST['storage'] ?? '');
        $graphics_card = $conn->real_escape_string($_POST['graphics_card'] ?? '');
        $device_condition = $conn->real_escape_string($_POST['device_condition'] ?? '');
        $additional_details = $conn->real_escape_string($_POST['additional_details'] ?? '');
        
        // Previous owner details
        $prev_first_name = $conn->real_escape_string($_POST['prev_first_name'] ?? '');
        $prev_last_name = $conn->real_escape_string($_POST['prev_last_name'] ?? '');
        $prev_id_number = $conn->real_escape_string($_POST['prev_id_number'] ?? '');
        $prev_phone = $conn->real_escape_string($_POST['prev_phone'] ?? '');
        $prev_usage = $conn->real_escape_string($_POST['prev_usage'] ?? '');
        $repair_history = $conn->real_escape_string($_POST['repair_history'] ?? '');

        // DEBUG: Log the extracted values
        error_log("Extracted values - Model: $model, Serial: $serial_number, Price: $price");

        // Validate required fields
        if (empty($model) || empty($serial_number) || $price <= 0) {
            error_log("Validation failed - Model: '$model', Serial: '$serial_number', Price: '$price'");
            echo json_encode([
                'success' => false, 
                'message' => 'Please fill all required fields: Model, Serial Number, and valid Price',
                'debug' => [
                    'model' => $model,
                    'serial_number' => $serial_number,
                    'price' => $price
                ]
            ]);
            exit();
        }

        // Check if serial number already exists
        $check_sql = "SELECT id FROM devices WHERE serial_number = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $serial_number);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Serial number already exists in the system']);
            $check_stmt->close();
            exit();
        }
        $check_stmt->close();

        // Insert device into database
        $sql = "INSERT INTO devices (
            device_type, model, serial_number, price, processor, ram, storage, 
            graphics_card, device_condition, additional_details, prev_first_name, 
            prev_last_name, prev_id_number, prev_phone, prev_usage, repair_history
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param(
            "sssdssssssssssss", 
            $device_type, $model, $serial_number, $price, $processor, $ram,
            $storage, $graphics_card, $device_condition, $additional_details,
            $prev_first_name, $prev_last_name, $prev_id_number, $prev_phone,
            $prev_usage, $repair_history
        );
        
        if ($stmt->execute()) {
            $device_id = $stmt->insert_id;
            
            // Handle file uploads for images
            $uploaded_images = [];
            $image_types = ['face_image', 'back_image', 'side_image'];
            
            foreach ($image_types as $image_type) {
                if (isset($_FILES[$image_type]) && $_FILES[$image_type]['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES[$image_type];
                    $upload_dir = 'uploads/';
                    
                    // Create uploads directory if it doesn't exist
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    // Generate unique filename
                    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $new_filename = uniqid() . '_' . $image_type . '.' . $file_extension;
                    $destination = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($file['tmp_name'], $destination)) {
                        // Insert image record into database
                        $image_sql = "INSERT INTO device_images (device_id, image_type, image_path) VALUES (?, ?, ?)";
                        $image_stmt = $conn->prepare($image_sql);
                        $image_type_clean = str_replace('_image', '', $image_type); // Convert 'face_image' to 'face'
                        $image_stmt->bind_param("iss", $device_id, $image_type_clean, $destination);
                        $image_stmt->execute();
                        $image_stmt->close();
                        
                        $uploaded_images[] = $image_type;
                    }
                }
            }
            
            echo json_encode([
                'success' => true, 
                'message' => 'Device added successfully!' . (count($uploaded_images) > 0 ? ' Images uploaded: ' . implode(', ', $uploaded_images) : ''),
                'device_id' => $device_id,
                'device_model' => $model
            ]);
            
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method. Use POST.']);
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error adding device: ' . $e->getMessage()]);
}
?>
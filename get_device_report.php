<?php
/**
 * GET DEVICE REPORT - Returns HTML for device report (used by both admin and client)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

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
        echo "<p>Error: No device ID provided</p>";
        exit();
    }
    
    $device_id = $conn->real_escape_string($_GET['device_id']);
    
    // Get device details
    $device_sql = "SELECT * FROM devices WHERE id = '$device_id'";
    $device_result = $conn->query($device_sql);
    
    if ($device_result->num_rows === 0) {
        echo "<p>Error: Device not found</p>";
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
    
    // Generate the report HTML
    ?>
    <div class="device-report">
        <h2>Device Report - <?php echo htmlspecialchars($device['model']); ?></h2>
        
        <!-- Device Images -->
        <?php if (!empty($images)): ?>
        <div class="report-images" style="display: flex; gap: 10px; margin: 20px 0;">
            <?php foreach($images as $image): ?>
                <img src="<?php echo htmlspecialchars($image['image_path']); ?>" alt="<?php echo htmlspecialchars($image['image_type']); ?>" style="max-width: 200px; max-height: 150px; border: 1px solid #ddd; padding: 5px;">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <!-- Device Details -->
        <div class="report-section">
            <h3>Device Specifications</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div><strong>Model:</strong> <?php echo htmlspecialchars($device['model']); ?></div>
                <div><strong>Serial Number:</strong> <?php echo htmlspecialchars($device['serial_number']); ?></div>
                <div><strong>Type:</strong> <?php echo htmlspecialchars($device['device_type']); ?></div>
                <div><strong>Condition:</strong> <?php echo htmlspecialchars($device['device_condition']); ?></div>
                <div><strong>Price:</strong> ksh<?php echo number_format($device['price'], 2); ?></div>
                <div><strong>Processor:</strong> <?php echo htmlspecialchars($device['processor']); ?></div>
                <div><strong>RAM:</strong> <?php echo htmlspecialchars($device['ram']); ?></div>
                <div><strong>Storage:</strong> <?php echo htmlspecialchars($device['storage']); ?></div>
                <div><strong>Graphics Card:</strong> <?php echo htmlspecialchars($device['graphics_card']); ?></div>
            </div>
        </div>
        
        <!-- Previous Owner Details -->
        <?php if (!empty($device['prev_first_name'])): ?>
        <div class="report-section">
            <h3>Previous Owner Information</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div><strong>Name:</strong> <?php echo htmlspecialchars($device['prev_first_name'] . ' ' . $device['prev_last_name']); ?></div>
                <div><strong>ID Number:</strong> <?php echo htmlspecialchars($device['prev_id_number']); ?></div>
                <div><strong>Phone:</strong> <?php echo htmlspecialchars($device['prev_phone']); ?></div>
                <div><strong>Previous Usage:</strong> <?php echo htmlspecialchars($device['prev_usage']); ?></div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Additional Details -->
        <?php if (!empty($device['additional_details'])): ?>
        <div class="report-section">
            <h3>Additional Details</h3>
            <p><?php echo nl2br(htmlspecialchars($device['additional_details'])); ?></p>
        </div>
        <?php endif; ?>
        
        <!-- Repair History -->
        <?php if (!empty($device['repair_history'])): ?>
        <div class="report-section">
            <h3>Repair History</h3>
            <p><?php echo nl2br(htmlspecialchars($device['repair_history'])); ?></p>
        </div>
        <?php endif; ?>
        
        <!-- Action Buttons -->
        <div class="report-actions" style="margin-top: 20px; display: flex; gap: 10px;">
            <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">Print Report</button>
            <button onclick="addToCart(<?php echo $device['id']; ?>)" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;">Add to Cart</button>
        </div>
    </div>
    
    <style>
    .device-report {
        font-family: Arial, sans-serif;
    }
    .report-section {
        margin: 20px 0;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }
    .report-section h3 {
        margin-top: 0;
        color: #333;
        border-bottom: 2px solid #007bff;
        padding-bottom: 5px;
    }
    </style>
    <?php
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p>Error loading device report: " . $e->getMessage() . "</p>";
}
?>
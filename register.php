<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config.php';

// Collect and trim form data
$userType = trim($_POST['select_user'] ?? '');
$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phoneNumber = trim($_POST['phone_number'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Debug: Log received data
error_log("Registration attempt - UserType: $userType, First: $firstName, Last: $lastName, Email: $email, Phone: $phoneNumber");

// Validate required fields
if (empty($userType) || empty($firstName) || empty($lastName) || empty($email) || empty($phoneNumber) || empty($password)) {
    echo "<script>alert('All required fields must be filled!'); window.history.back();</script>";
    exit();
}

// Validate user type
if (!in_array($userType, ['admin', 'client'])) {
    echo "<script>alert('Invalid user type selected!'); window.history.back();</script>";
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('Please enter a valid email address!'); window.history.back();</script>";
    exit();
}

// Password confirmation
if ($password !== $confirmPassword) {
    echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
    exit();
}

// Validate password strength
if (strlen($password) < 6) {
    echo "<script>alert('Password should be at least 6 characters long!'); window.history.back();</script>";
    exit();
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

if ($userType === 'admin') {
    // Check for duplicate email in admins table
    $check = $conn->prepare("SELECT id FROM admins WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<script>alert('Email already registered as admin!'); window.history.back();</script>";
        $check->close();
        $conn->close();
        exit();
    }
    $check->close();

    // Insert into admins table
    $stmt = $conn->prepare("INSERT INTO admins (firstName, lastName, email, phoneNumber, password, admin_level) VALUES (?, ?, ?, ?, ?, 'staff')");
    
    if ($stmt === false) {
        echo "<script>alert('Database prepare error: " . $conn->error . "'); window.history.back();</script>";
        exit();
    }
    
    $stmt->bind_param("sssss", $firstName, $lastName, $email, $phoneNumber, $hashed_password);

} else {
    // Check for duplicate email in clients table
    $check = $conn->prepare("SELECT id FROM clients WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<script>alert('Email already registered as client!'); window.history.back();</script>";
        $check->close();
        $conn->close();
        exit();
    }
    $check->close();

    // Insert into clients table
    $stmt = $conn->prepare("INSERT INTO clients (firstName, lastName, email, phoneNumber, password) VALUES (?, ?, ?, ?, ?)");
    
    if ($stmt === false) {
        echo "<script>alert('Database prepare error: " . $conn->error . "'); window.history.back();</script>";
        exit();
    }
    
    $stmt->bind_param("sssss", $firstName, $lastName, $email, $phoneNumber, $hashed_password);
}

if ($stmt->execute()) {
    echo "<script>
        alert('Registration successful as $userType! Please login now.');
        window.location.href = 'login.html';
    </script>";
} else {
    echo "<script>alert('Database error: " . addslashes($stmt->error) . "'); window.history.back();</script>";
}

$stmt->close();
$conn->close();
?>
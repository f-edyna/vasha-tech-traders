<?php
/**
 * ADMIN REGISTRATION HANDLER
 * Only accessible by existing admins
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config.php';

// Check if current user is admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    echo "<script>alert('Access denied! Admins only.'); window.location.href='login.html';</script>";
    exit();
}

// Get form data
$firstName = $_POST['firstName'] ?? '';
$lastName = $_POST['lastName'] ?? '';
$email = $_POST['email'] ?? '';
$phoneNumber = $_POST['phoneNumber'] ?? '';
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';
$admin_level = $_POST['admin_level'] ?? 'staff';

// Password check
if ($password !== $confirmPassword) {
    echo "<script>alert('Error: Passwords do not match!'); window.history.back();</script>";
    exit();
}

// Check if email exists
$check_email_sql = "SELECT email FROM admins WHERE email = ?";
$check_stmt = $conn->prepare($check_email_sql);
$check_stmt->bind_param("s", $email);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    echo "<script>alert('Error: Admin email already exists!'); window.history.back();</script>";
    $check_stmt->close();
    $conn->close();
    exit();
}
$check_stmt->close();

// Encrypt password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert into ADMINS table
$stmt = $conn->prepare("INSERT INTO admins (firstName, lastName, email, phoneNumber, password, admin_level) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $firstName, $lastName, $email, $phoneNumber, $hashed_password, $admin_level);

if ($stmt->execute()) {
    echo "<script>
        alert('Admin account created successfully!');
        window.location.href='admin_dashboard.html';
    </script>";
} else {
    echo "<script>
        alert('Error: " . addslashes($stmt->error) . "');
        window.history.back();
    </script>";
}

$stmt->close();
$conn->close();
?>
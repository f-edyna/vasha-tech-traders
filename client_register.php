<?php
/**
 * CLIENT REGISTRATION HANDLER
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config.php';

// Get form data
$firstName = $_POST['firstName'] ?? '';
$lastName = $_POST['lastName'] ?? '';
$email = $_POST['email'] ?? '';
$phoneNumber = $_POST['phoneNumber'] ?? '';
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';
$address = $_POST['address'] ?? '';

// Password check
if ($password !== $confirmPassword) {
    echo "<script>alert('Error: Passwords do not match!'); window.history.back();</script>";
    exit();
}

// Check if email exists in clients table
$check_email_sql = "SELECT email FROM clients WHERE email = ?";
$check_stmt = $conn->prepare($check_email_sql);
$check_stmt->bind_param("s", $email);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    echo "<script>alert('Error: Email already registered!'); window.history.back();</script>";
    $check_stmt->close();
    $conn->close();
    exit();
}
$check_stmt->close();

// Encrypt password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert into CLIENTS table
$stmt = $conn->prepare("INSERT INTO clients (firstName, lastName, email, phoneNumber, password, address) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $firstName, $lastName, $email, $phoneNumber, $hashed_password, $address);

if ($stmt->execute()) {
    echo "<script>
        alert('Registration successful! Please login.');
        window.location.href='login.html';
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
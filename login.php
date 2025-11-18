<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config.php';

// Debug session
error_log("Login attempt - Email: " . ($_POST['email'] ?? 'none'));

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo "<script>alert('Please enter both email and password!'); window.history.back();</script>";
    exit();
}

// --- Check Admins Table ---
$stmt = $conn->prepare("SELECT * FROM admins WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    if (password_verify($password, $admin['password'])) {
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['user'] = $admin['email'];
        $_SESSION['user_type'] = 'admin';
        $_SESSION['first_name'] = $admin['firstName'];
        $_SESSION['logged_in'] = true;

        error_log("Admin login successful: " . $admin['email']);
        header("Location: mainpage.html");
        exit();
    }
}
$stmt->close();

// --- Check Clients Table ---
$stmt = $conn->prepare("SELECT * FROM clients WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $client = $result->fetch_assoc();
    if (password_verify($password, $client['password'])) {
        $_SESSION['user_id'] = $client['id'];
        $_SESSION['user'] = $client['email'];
        $_SESSION['user_type'] = 'client';
        $_SESSION['first_name'] = $client['firstName'];
        $_SESSION['logged_in'] = true;

        error_log("Client login successful: " . $client['email']);
        header("Location: vasha tech.html");
        exit();
    }
}
$stmt->close();

echo "<script>alert('Invalid email or password!'); window.history.back();</script>";
$conn->close();
?>
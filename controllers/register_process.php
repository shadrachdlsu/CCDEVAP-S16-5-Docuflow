<?php
require_once '../config/connections.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $office_id = $_POST['office_id'];

    if (strpos($email, '@') === false || strpos($email, '.com') === false) {
        header("Location: ../views/register.php?type=error&msg=invalid_email");
        exit;
    }
    
    if ($password !== $confirmPassword) {
        header("Location: ../views/register.php?type=error&msg=mismatch");
        exit;
    }

    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        header("Location: ../views/register.php?type=error&msg=exists");
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Assign role_id = 3 (Member) by default for self-registered users
    $role_id = 3; 

    try {
        $insertStmt = $pdo->prepare("
            INSERT INTO users (role_id, office_id, full_name, email, password_hash, is_active, registration_status) 
            VALUES (:role_id, :office_id, :full_name, :email, :password_hash, 1, 'Pending')
        "); // REGISTERING SETS THE REGISTRATION_STATUS TO PENDING. CHANGE TO "Approved" IF YOU WANT TO QUICK TEST
        
        $insertStmt->execute([
            'role_id' => $role_id,
            'office_id' => $office_id,
            'full_name' => $full_name,
            'email' => $email,
            'password_hash' => $password_hash
        ]);

        header("Location: ../views/register.php?type=success&msg=success");
        exit;

    } catch (PDOException $e) {
        error_log("Registration Error: " . $e->getMessage());
        header("Location: ../views/register.php?type=error&msg=error");
        exit;
    }
} else {
    header("Location: ../views/register.php");
    exit;
}
?>
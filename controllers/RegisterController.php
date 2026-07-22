<?php
require_once '../config/connections.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $office_id = $_POST['office_id'];

    // COMMENTED OUT BECAUSE EMAILS IN THE DB USE .gov and .local
    // if (strpos($email, '@') === false || strpos($email, '.com') === false) {
    //     header("Location: ../views/register.php?type=error&msg=invalid_email");
    //     exit;
    // }
    
    if ($password !== $confirmPassword) {
        header("Location: ../views/register.php?type=error&msg=mismatch");
        exit;
    }

    require_once '../models/user.php';
    $userModel = new User();
    if ($userModel->emailExists($email)) {
        header("Location: ../views/register.php?type=error&msg=exists");
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Assign role_id = 3 (Member) by default for self-registered users
    $role_id = 3; 

    try {
        $userModel->create(
            $role_id, 
            $office_id, 
            $full_name, 
            $email, 
            $password_hash, 
            0, 
            'Pending'
        );

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
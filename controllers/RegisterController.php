<?php

require_once __DIR__ . "/../config/connections.php";
require_once __DIR__ . "/../models/user.php";
require_once __DIR__ . "/../models/setting.php";

/*
|--------------------------------------------------------------------------
| REGISTRATION CONTROLLER
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirmPassword"] ?? "";
    $office_id = $_POST["office_id"] ?? null;

    if ($password !== $confirmPassword) {
        header("Location: ../views/register.php?type=error&msg=mismatch");
        exit;
    }

    $passwordErr = User::validatePasswordComplexity($password);
    if ($passwordErr !== null) {
        header("Location: ../views/register.php?type=error&msg=weak_password");
        exit;
    }

    $userModel = new User();
    $settingModel = new Setting();

    if ($userModel->emailExists($email)) {
        header("Location: ../views/register.php?type=error&msg=exists");
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Assign role_id = 3 (Member) by default for self-registered users
    $role_id = 3; 

    // Check system setting for admin approval requirement
    $requireApproval = $settingModel->isRequireAdminApproval();
    $is_active = $requireApproval ? 0 : 1;
    $registration_status = $requireApproval ? "Pending" : "Approved";
    $successMsg = $requireApproval ? "pending" : "success";

    try {

        $userModel->create(
            $role_id, 
            $office_id, 
            $full_name, 
            $email, 
            $password_hash, 
            $is_active, 
            $registration_status
        );

        header("Location: ../views/register.php?type=success&msg=" . $successMsg);
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
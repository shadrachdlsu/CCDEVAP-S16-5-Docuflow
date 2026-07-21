<?php
session_start();
require_once '../config/connections.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Please fill in all fields.";
        header("Location: ../views/login.php?error=blank");
        exit;
    }

    // COMMENTED OUT BECAUSE EMAILS IN THE DB USE .gov and .local
    // if (strpos($email, '@') === false || strpos($email, '.com') === false) { //From Ms.'s feedback, email should have @ and .com
    //     $_SESSION['error'] = "Invalid email or password.";
    //     header("Location: ../views/login.php?=invalid");
    //     exit;
    // }

    try {
        $stmt = $pdo->prepare("SELECT u.user_id, u.role_id, u.full_name, u.email, u.password_hash, u.is_active, u.registration_status, u.office_id, o.office_name FROM users u LEFT JOIN offices o ON u.office_id = o.office_id WHERE u.email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['is_active'] == 1 && $user['registration_status'] === 'Approved') {
                
                // Set session variables to keep the user logged in
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role_id'] = $user['role_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                if ($user['office_id']) {
                    $_SESSION['office_id'] = $user['office_id'];
                    $_SESSION['office_name'] = $user['office_name'];
                }

                // Route to the correct dashboard based on role_id
                switch ($user['role_id']) {
                    case 1:
                        header("Location: ../views/admin-dashboard.php");
                        break;
                    case 2:
                        header("Location: ../views/secretary-dashboard.php");
                        break;
                    case 3:
                        header("Location: ../views/member-dashboard.php");
                        break;
                    default:
                        header("Location: ../views/login.php");
                }
                exit;
            } else {
                $_SESSION['error'] = "Your account is inactive or pending approval.";
                header("Location: ../views/login.php?error=inactive");
                exit;
            }
        } else {
            $_SESSION['error'] = "Invalid email or password.";
            header("Location: ../views/login.php?error=invalid");
            exit;
        }
    } catch (PDOException $e) {
        error_log("Login Error: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred during login. Please try again later.";
        header("Location: ../views/login.php?error");
        exit;
    }
} else {
    header("Location: login.php");
    exit;
}
?>
<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = trim($_POST['role'] ?? 'learner');

    // Validation
    if (empty($fullname) || empty($email) || empty($password)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: register.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Please provide a valid email address.";
        header("Location: register.php");
        exit();
    }

    if (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long.";
        header("Location: register.php");
        exit();
    }

    if (!in_array($role, ['learner', 'tutor'])) {
        $role = 'learner';
    }

    try {
        // Check if email is already taken
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);

        if ($stmt->fetch()) {
            $_SESSION['error'] = "An account with this email already exists.";
            header("Location: register.php");
            exit();
        }

        // Hash password securely
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert new user
        $insertStmt = $pdo->prepare("INSERT INTO users (fullname, email, password, role) VALUES (:fullname, :email, :password, :role)");
        $insertStmt->execute([
            ':fullname' => $fullname,
            ':email'    => $email,
            ':password' => $hashed_password,
            ':role'     => $role
        ]);

        $_SESSION['success'] = "Account registered successfully! Please log in.";
        header("Location: login.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = "An unexpected error occurred. Please try again.";
        header("Location: register.php");
        exit();
    }
} else {
    header("Location: register.php");
    exit();
}
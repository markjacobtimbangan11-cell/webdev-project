<?php

header("Content-Type: application/json");

$pdo = require "config/database.php";


// =========================================
// POST ONLY
// =========================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "success" => false,
        "message" => "Invalid request."
    ]);

    exit;
}


// =========================================
// GET EMAIL
// =========================================

$email =
    trim($_POST["email"] ?? "");


// =========================================
// VALIDATION
// =========================================

if ($email === "") {

    echo json_encode([
        "success" => false,
        "message" =>
            "Please enter your email address."
    ]);

    exit;
}


if (
    !filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    )
) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Please enter a valid email address."
    ]);

    exit;
}


if (strlen($email) > 150) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Email address is too long."
    ]);

    exit;
}


// =========================================
// CHECK EXISTING SUBSCRIBER
// =========================================

try {

    $check =
        $pdo->prepare(
            "SELECT id
             FROM subscribers
             WHERE email = :email
             LIMIT 1"
        );


    $check->execute([
        "email" => $email
    ]);


    if ($check->fetch()) {

        echo json_encode([
            "success" => false,
            "message" =>
                "This email is already subscribed."
        ]);

        exit;
    }


    // =====================================
    // SAVE SUBSCRIBER
    // =====================================

    $stmt =
        $pdo->prepare(
            "INSERT INTO subscribers
            (
                email
            )
            VALUES
            (
                :email
            )"
        );


    $stmt->execute([
        "email" => $email
    ]);


    echo json_encode([
        "success" => true,
        "message" =>
            "Thank you for subscribing!"
    ]);

}

catch (PDOException $e) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Unable to subscribe right now. Please try again."
    ]);
}
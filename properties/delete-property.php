<?php

session_start();

$pdo = require "../config/database.php";


// =========================================
// NORMAL USER ACCESS ONLY
// =========================================

if (!isset($_SESSION["user_id"])) {

    header("Location: /myhome/login.php");

    exit;
}


if (
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "user"
) {

    header("Location: /myhome/index.php");

    exit;
}


$user_id =
    (int) $_SESSION["user_id"];

$property_id =
    isset($_GET["id"])
        ? (int) $_GET["id"]
        : 0;


// =========================================
// VALID PROPERTY ID
// =========================================

if ($property_id <= 0) {

    header(
        "Location: /myhome/properties/my-listings.php"
    );

    exit;
}


// =========================================
// DELETE PROPERTY
// ONLY IF IT BELONGS TO LOGGED-IN USER
// =========================================

try {

    $stmt = $pdo->prepare(
        "DELETE FROM properties
         WHERE id = :property_id
         AND user_id = :user_id"
    );


    $stmt->execute([
        "property_id" => $property_id,
        "user_id" => $user_id
    ]);


    header(
        "Location: /myhome/properties/my-listings.php?deleted=1"
    );

    exit;

}

catch (PDOException $e) {

    header(
        "Location: /myhome/properties/my-listings.php?error=1"
    );

    exit;
}
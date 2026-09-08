<?php

session_start();

$pdo = require "config/database.php";


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


$user_id = (int) $_SESSION["user_id"];


// =========================================
// POST REQUEST ONLY
// =========================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: /myhome/favorites.php");

    exit;
}


$property_id =
    isset($_POST["property_id"])
        ? (int) $_POST["property_id"]
        : 0;

$action =
    trim($_POST["action"] ?? "");


// =========================================
// VALIDATE PROPERTY
// =========================================

if ($property_id <= 0) {

    header("Location: /myhome/favorites.php");

    exit;
}


// =========================================
// REMOVE FAVORITE
// =========================================

if ($action === "remove") {

    try {

        $stmt = $pdo->prepare(
            "DELETE FROM favorites
             WHERE user_id = :user_id
             AND property_id = :property_id"
        );

        $stmt->execute([
            "user_id" => $user_id,
            "property_id" => $property_id
        ]);


        header(
            "Location: /myhome/favorites.php?removed=1"
        );

        exit;

    } catch (PDOException $e) {

        header(
            "Location: /myhome/favorites.php?error=1"
        );

        exit;
    }
}


// =========================================
// INVALID ACTION
// =========================================

header("Location: /myhome/favorites.php");

exit;
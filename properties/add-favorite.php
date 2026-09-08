<?php

session_start();

$pdo = require "../config/database.php";


// =========================
// NORMAL USER ACCESS ONLY
// =========================

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


// =========================
// VALID PROPERTY ID
// =========================

if ($property_id <= 0) {

    header(
        "Location: /myhome/properties/listings.php"
    );

    exit;
}


// =========================
// CHECK PROPERTY EXISTS
// =========================

try {

    $stmt = $pdo->prepare(
        "SELECT
            id,
            user_id
         FROM properties
         WHERE id = :property_id
         LIMIT 1"
    );


    $stmt->execute([
        "property_id" => $property_id
    ]);


    $property =
        $stmt->fetch();


    if (!$property) {

        header(
            "Location: /myhome/properties/listings.php"
        );

        exit;
    }

}

catch (PDOException $e) {

    header(
        "Location: /myhome/properties/listings.php"
    );

    exit;
}


// =========================
// PREVENT FAVORITING
// OWN PROPERTY
// =========================

if (
    (int) $property["user_id"] ===
    $user_id
) {

    header(
        "Location: /myhome/properties/view-property.php?id="
        . $property_id
    );

    exit;
}


// =========================
// ADD FAVORITE
// =========================

try {

    $stmt = $pdo->prepare(
        "INSERT IGNORE INTO favorites
        (
            user_id,
            property_id
        )
        VALUES
        (
            :user_id,
            :property_id
        )"
    );


    $stmt->execute([
        "user_id" => $user_id,
        "property_id" => $property_id
    ]);


    header(
        "Location: /myhome/properties/view-property.php?id="
        . $property_id
        . "&favorited=1"
    );

    exit;

}

catch (PDOException $e) {

    header(
        "Location: /myhome/properties/view-property.php?id="
        . $property_id
        . "&favorite_error=1"
    );

    exit;
}
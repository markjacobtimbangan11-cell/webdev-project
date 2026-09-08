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
    isset($_POST["property_id"])
        ? (int) $_POST["property_id"]
        : 0;


$message =
    trim($_POST["message"] ?? "");


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
// CHECK PROPERTY
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
// PREVENT SELF-INQUIRY
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
// VALIDATE MESSAGE
// =========================

if ($message === "") {

    header(
        "Location: /myhome/properties/view-property.php?id="
        . $property_id
        . "&inquiry_error=empty"
    );

    exit;
}


if (strlen($message) < 5) {

    header(
        "Location: /myhome/properties/view-property.php?id="
        . $property_id
        . "&inquiry_error=short"
    );

    exit;
}


if (strlen($message) > 1000) {

    header(
        "Location: /myhome/properties/view-property.php?id="
        . $property_id
        . "&inquiry_error=long"
    );

    exit;
}


// =========================
// INSERT INQUIRY
// =========================

try {

    $stmt = $pdo->prepare(
        "INSERT INTO inquiries
        (
            user_id,
            property_id,
            message
        )
        VALUES
        (
            :user_id,
            :property_id,
            :message
        )"
    );


    $stmt->execute([
        "user_id" => $user_id,
        "property_id" => $property_id,
        "message" => $message
    ]);


    header(
        "Location: /myhome/properties/view-property.php?id="
        . $property_id
        . "&inquiry_sent=1"
    );

    exit;

}

catch (PDOException $e) {

    header(
        "Location: /myhome/properties/view-property.php?id="
        . $property_id
        . "&inquiry_error=failed"
    );

    exit;
}

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
        "Location: /myhome/favorites.php"
    );

    exit;
}


// =========================
// REMOVE FAVORITE
// =========================

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

}

catch (PDOException $e) {

    header(
        "Location: /myhome/favorites.php?error=1"
    );

    exit;
}


// =========================
// WHERE TO RETURN
// =========================

$return =
    $_GET["return"] ?? "view";


if ($return === "favorites") {

    header(
        "Location: /myhome/favorites.php?removed=1"
    );

} else {

    header(
        "Location: /myhome/properties/view-property.php?id="
        . $property_id
        . "&removed=1"
    );
}


exit;
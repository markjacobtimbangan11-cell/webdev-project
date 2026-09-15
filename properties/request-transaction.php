<?php

session_start();

$pdo = require "../config/database.php";


/* =========================
   CHECK LOGIN
========================= */

if (!isset($_SESSION["user_id"])) {

    header("Location: /myhome/login.php");
    exit;
}


/* =========================
   ONLY NORMAL USERS
========================= */

if (
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "user"
) {

    header("Location: /myhome/index.php");
    exit;
}


/* =========================
   POST REQUEST ONLY
========================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header(
        "Location: /myhome/properties/listings.php"
    );

    exit;
}


/* =========================
   GET PROPERTY ID
========================= */

$property_id =
    isset($_POST["property_id"])
        ? (int) $_POST["property_id"]
        : 0;

$user_id =
    (int) $_SESSION["user_id"];


if ($property_id <= 0) {

    header(
        "Location: /myhome/properties/listings.php"
    );

    exit;
}


/* =========================
   GET PROPERTY
========================= */

$stmt = $pdo->prepare(
    "SELECT
        id,
        user_id,
        listing_type,
        status
     FROM properties
     WHERE id = :property_id
     LIMIT 1"
);

$stmt->execute([
    "property_id" => $property_id
]);

$property = $stmt->fetch();


/* =========================
   PROPERTY NOT FOUND
========================= */

if (!$property) {

    header(
        "Location: /myhome/properties/listings.php"
    );

    exit;
}


/* =========================
   CANNOT REQUEST OWN PROPERTY
========================= */

if ((int) $property["user_id"] === $user_id) {

    header(
        "Location: /myhome/properties/view-property.php?id=" .
        $property_id .
        "&transaction=own"
    );

    exit;
}


/* =========================
   PROPERTY MUST BE AVAILABLE
========================= */

if ($property["status"] !== "available") {

    header(
        "Location: /myhome/properties/view-property.php?id=" .
        $property_id .
        "&transaction=unavailable"
    );

    exit;
}


/* =========================
   VALIDATE LISTING TYPE
========================= */

if (
    $property["listing_type"] !== "rent" &&
    $property["listing_type"] !== "sale"
) {

    header(
        "Location: /myhome/properties/view-property.php?id=" .
        $property_id .
        "&transaction=invalid"
    );

    exit;
}


/* =========================
   CHECK EXISTING REQUEST
========================= */

$check = $pdo->prepare(
    "SELECT id
     FROM transactions
     WHERE property_id = :property_id
       AND buyer_renter_id = :buyer_renter_id
       AND status = 'pending'
     LIMIT 1"
);

$check->execute([
    "property_id" =>
        $property_id,

    "buyer_renter_id" =>
        $user_id
]);


/* =========================
   ALREADY REQUESTED
========================= */

if ($check->fetch()) {

    header(
        "Location: /myhome/transactions.php?message=exists"
    );

    exit;
}


/* =========================
   CREATE TRANSACTION
========================= */

$stmt = $pdo->prepare(
    "INSERT INTO transactions
    (
        property_id,
        buyer_renter_id,
        owner_id,
        transaction_type,
        status
    )
    VALUES
    (
        :property_id,
        :buyer_renter_id,
        :owner_id,
        :transaction_type,
        'pending'
    )"
);

$stmt->execute([

    "property_id" =>
        $property_id,

    "buyer_renter_id" =>
        $user_id,

    "owner_id" =>
        (int) $property["user_id"],

    "transaction_type" =>
        $property["listing_type"]
]);


/* =========================
   SUCCESS
========================= */

header(
    "Location: /myhome/transactions.php?message=requested"
);

exit;
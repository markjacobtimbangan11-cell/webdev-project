<?php

session_start();

$pdo = require "../config/database.php";


// =========================================
// ADMIN ACCESS ONLY
// =========================================

if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "admin"
) {

    header("Location: /myhome/login.php");

    exit;
}


// =========================================
// POST REQUEST ONLY
// =========================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header(
        "Location: /myhome/admin/properties.php"
    );

    exit;
}


// =========================================
// GET FORM DATA
// =========================================

$property_id =
    isset($_POST["property_id"])
        ? (int) $_POST["property_id"]
        : 0;

$action =
    $_POST["action"] ?? "";


// =========================================
// INVALID PROPERTY ID
// =========================================

if ($property_id <= 0) {

    header(
        "Location: /myhome/admin/properties.php?error=notfound"
    );

    exit;
}


// =========================================
// GET PROPERTY
// =========================================

try {

    $stmt = $pdo->prepare(
        "SELECT
            id,
            listing_type,
            status
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
            "Location: /myhome/admin/properties.php?error=notfound"
        );

        exit;
    }

}

catch (PDOException $e) {

    header(
        "Location: /myhome/admin/properties.php?error=notfound"
    );

    exit;
}


// =========================================
// CHANGE STATUS
// =========================================

if ($action === "change_status") {

    $new_status =
        $_POST["new_status"] ?? "";


    // =====================================
    // ALLOWED STATUS VALUES
    // =====================================

    $allowed_statuses = [
        "available",
        "sold",
        "rented"
    ];


    if (
        !in_array(
            $new_status,
            $allowed_statuses,
            true
        )
    ) {

        header(
            "Location: /myhome/admin/properties.php?error=invalid_status"
        );

        exit;
    }


    // =====================================
    // SALE LISTING RULE
    // =====================================

    if (
        $property["listing_type"] === "sale" &&
        $new_status === "rented"
    ) {

        header(
            "Location: /myhome/admin/properties.php?error=invalid_status"
        );

        exit;
    }


    // =====================================
    // RENT LISTING RULE
    // =====================================

    if (
        $property["listing_type"] === "rent" &&
        $new_status === "sold"
    ) {

        header(
            "Location: /myhome/admin/properties.php?error=invalid_status"
        );

        exit;
    }


    // =====================================
    // UPDATE STATUS
    // =====================================

    try {

        $stmt = $pdo->prepare(
            "UPDATE properties
             SET status = :status
             WHERE id = :property_id"
        );


        $stmt->execute([
            "status" => $new_status,
            "property_id" => $property_id
        ]);


        header(
            "Location: /myhome/admin/properties.php?success=status"
        );

        exit;

    }

    catch (PDOException $e) {

        header(
            "Location: /myhome/admin/properties.php?error=invalid_status"
        );

        exit;
    }
}


// =========================================
// DELETE PROPERTY
// =========================================

if ($action === "delete") {

    try {

        $stmt = $pdo->prepare(
            "DELETE FROM properties
             WHERE id = :property_id"
        );


        $stmt->execute([
            "property_id" => $property_id
        ]);


        header(
            "Location: /myhome/admin/properties.php?success=deleted"
        );

        exit;

    }

    catch (PDOException $e) {

        header(
            "Location: /myhome/admin/properties.php?error=invalid"
        );

        exit;
    }
}


// =========================================
// INVALID ACTION
// =========================================

header(
    "Location: /myhome/admin/properties.php"
);

exit;
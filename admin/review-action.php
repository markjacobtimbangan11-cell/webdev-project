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
// POST ONLY
// =========================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header(
        "Location: /myhome/admin/reviews.php"
    );

    exit;
}


// =========================================
// GET VALUES
// =========================================

$review_id =
    isset($_POST["review_id"])
        ? (int) $_POST["review_id"]
        : 0;

$action =
    $_POST["action"] ?? "";


// =========================================
// INVALID REVIEW ID
// =========================================

if ($review_id <= 0) {

    header(
        "Location: /myhome/admin/reviews.php?error=invalid"
    );

    exit;
}


// =========================================
// APPROVE
// =========================================

if ($action === "approve") {

    try {

        $stmt = $pdo->prepare(
            "UPDATE reviews
             SET status = 'approved'
             WHERE id = :review_id"
        );


        $stmt->execute([
            "review_id" => $review_id
        ]);


        header(
            "Location: /myhome/admin/reviews.php?success=approved"
        );

        exit;

    }

    catch (PDOException $e) {

        header(
            "Location: /myhome/admin/reviews.php?error=update"
        );

        exit;
    }
}


// =========================================
// REJECT
// =========================================

if ($action === "reject") {

    try {

        $stmt = $pdo->prepare(
            "UPDATE reviews
             SET status = 'rejected'
             WHERE id = :review_id"
        );


        $stmt->execute([
            "review_id" => $review_id
        ]);


        header(
            "Location: /myhome/admin/reviews.php?success=rejected"
        );

        exit;

    }

    catch (PDOException $e) {

        header(
            "Location: /myhome/admin/reviews.php?error=update"
        );

        exit;
    }
}


// =========================================
// DELETE
// =========================================

if ($action === "delete") {

    try {

        $stmt = $pdo->prepare(
            "DELETE FROM reviews
             WHERE id = :review_id"
        );


        $stmt->execute([
            "review_id" => $review_id
        ]);


        header(
            "Location: /myhome/admin/reviews.php?success=deleted"
        );

        exit;

    }

    catch (PDOException $e) {

        header(
            "Location: /myhome/admin/reviews.php?error=delete"
        );

        exit;
    }
}


// =========================================
// INVALID ACTION
// =========================================

header(
    "Location: /myhome/admin/reviews.php?error=invalid"
);

exit;
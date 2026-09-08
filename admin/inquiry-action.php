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
        "Location: /myhome/admin/inquiries.php"
    );

    exit;
}


// =========================================
// GET DATA
// =========================================

$inquiry_id =
    isset($_POST["inquiry_id"])
        ? (int) $_POST["inquiry_id"]
        : 0;

$action =
    $_POST["action"] ?? "";


// =========================================
// VALIDATE ID
// =========================================

if ($inquiry_id <= 0) {

    header(
        "Location: /myhome/admin/inquiries.php?error=notfound"
    );

    exit;
}


// =========================================
// DELETE INQUIRY
// =========================================

if ($action === "delete") {

    try {

        // =====================================
        // CHECK IF INQUIRY EXISTS
        // =====================================

        $stmt = $pdo->prepare(
            "SELECT id
             FROM inquiries
             WHERE id = :inquiry_id
             LIMIT 1"
        );


        $stmt->execute([
            "inquiry_id" => $inquiry_id
        ]);


        $inquiry =
            $stmt->fetch();


        if (!$inquiry) {

            header(
                "Location: /myhome/admin/inquiries.php?error=notfound"
            );

            exit;
        }


        // =====================================
        // DELETE INQUIRY
        // =====================================

        $stmt = $pdo->prepare(
            "DELETE FROM inquiries
             WHERE id = :inquiry_id"
        );


        $stmt->execute([
            "inquiry_id" => $inquiry_id
        ]);


        header(
            "Location: /myhome/admin/inquiries.php?success=deleted"
        );

        exit;

    }

    catch (PDOException $e) {

        header(
            "Location: /myhome/admin/inquiries.php?error=notfound"
        );

        exit;
    }
}


// =========================================
// INVALID ACTION
// =========================================

header(
    "Location: /myhome/admin/inquiries.php"
);

exit;
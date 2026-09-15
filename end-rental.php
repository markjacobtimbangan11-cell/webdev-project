<?php

session_start();

$pdo = require "config/database.php";


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
   POST ONLY
========================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: /myhome/transactions.php");
    exit;
}


/* =========================
   GET TRANSACTION ID
========================= */

$transaction_id =
    isset($_POST["transaction_id"])
        ? (int) $_POST["transaction_id"]
        : 0;

$owner_id =
    (int) $_SESSION["user_id"];


if ($transaction_id <= 0) {

    header(
        "Location: /myhome/transactions.php?message=invalid"
    );

    exit;
}


/* =========================
   START DATABASE TRANSACTION
========================= */

try {

    $pdo->beginTransaction();


    /* =========================
       GET RENTAL TRANSACTION
    ========================= */

    $stmt = $pdo->prepare(
        "SELECT
            transactions.id,
            transactions.property_id,
            transactions.owner_id,
            transactions.transaction_type,
            transactions.status,

            properties.status AS property_status,
            properties.listing_type

         FROM transactions

         INNER JOIN properties
            ON transactions.property_id = properties.id

         WHERE transactions.id = :transaction_id

         LIMIT 1

         FOR UPDATE"
    );


    $stmt->execute([
        "transaction_id" => $transaction_id
    ]);


    $transaction =
        $stmt->fetch();


    /* =========================
       NOT FOUND
    ========================= */

    if (!$transaction) {

        $pdo->rollBack();

        header(
            "Location: /myhome/transactions.php?message=notfound"
        );

        exit;
    }


    /* =========================
       CHECK OWNER
    ========================= */

    if (
        (int) $transaction["owner_id"]
        !== $owner_id
    ) {

        $pdo->rollBack();

        header(
            "Location: /myhome/transactions.php?message=unauthorized"
        );

        exit;
    }


    /* =========================
       MUST BE RENT
    ========================= */

    if (
        $transaction["transaction_type"]
        !== "rent"
    ) {

        $pdo->rollBack();

        header(
            "Location: /myhome/transactions.php?message=invalid"
        );

        exit;
    }


    /* =========================
       MUST BE ACCEPTED
    ========================= */

    if (
        $transaction["status"]
        !== "accepted"
    ) {

        $pdo->rollBack();

        header(
            "Location: /myhome/transactions.php?message=processed"
        );

        exit;
    }


    /* =========================
       PROPERTY MUST BE RENTED
    ========================= */

    if (
        $transaction["property_status"]
        !== "rented"
    ) {

        $pdo->rollBack();

        header(
            "Location: /myhome/transactions.php?message=invalid"
        );

        exit;
    }


    /* =========================
       COMPLETE TRANSACTION
    ========================= */

    $complete_stmt = $pdo->prepare(
    "UPDATE transactions

     SET
        status = 'completed',
        completed_at = NOW()

     WHERE id = :transaction_id
       AND owner_id = :owner_id
       AND status = 'accepted'
       AND transaction_type = 'rent'"
);


    $complete_stmt->execute([
        "transaction_id" =>
            $transaction_id,

        "owner_id" =>
            $owner_id
    ]);


    /* =========================
       MAKE PROPERTY AVAILABLE
    ========================= */

    $property_stmt = $pdo->prepare(
        "UPDATE properties

         SET status = 'available'

         WHERE id = :property_id
           AND user_id = :owner_id
           AND status = 'rented'"
    );


    $property_stmt->execute([
        "property_id" =>
            (int) $transaction["property_id"],

        "owner_id" =>
            $owner_id
    ]);


    /* =========================
       SAVE
    ========================= */

    $pdo->commit();


    /* =========================
       SUCCESS
    ========================= */

    header(
        "Location: /myhome/transactions.php?message=completed"
    );

    exit;


} catch (PDOException $e) {


    if ($pdo->inTransaction()) {

        $pdo->rollBack();
    }


    header(
        "Location: /myhome/transactions.php?message=error"
    );

    exit;
}
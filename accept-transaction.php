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
   POST REQUEST ONLY
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
   BEGIN DATABASE TRANSACTION
========================= */

try {

    $pdo->beginTransaction();


    /* =========================
       GET TRANSACTION REQUEST
    ========================= */

    $stmt = $pdo->prepare(
        "SELECT
            transactions.id,
            transactions.property_id,
            transactions.owner_id,
            transactions.buyer_renter_id,
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
        "transaction_id" =>
            $transaction_id
    ]);


    $transaction =
        $stmt->fetch();


    /* =========================
       TRANSACTION NOT FOUND
    ========================= */

    if (!$transaction) {

        $pdo->rollBack();

        header(
            "Location: /myhome/transactions.php?message=notfound"
        );

        exit;
    }


    /* =========================
       VERIFY PROPERTY OWNER
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
       MUST STILL BE PENDING
    ========================= */

    if (
        $transaction["status"]
        !== "pending"
    ) {

        $pdo->rollBack();

        header(
            "Location: /myhome/transactions.php?message=processed"
        );

        exit;
    }


    /* =========================
       PROPERTY MUST BE AVAILABLE
    ========================= */

    if (
        $transaction["property_status"]
        !== "available"
    ) {

        $pdo->rollBack();

        header(
            "Location: /myhome/transactions.php?message=unavailable"
        );

        exit;
    }


    /* =========================
       DETERMINE NEW PROPERTY STATUS
    ========================= */

    if (
        $transaction["transaction_type"]
        === "rent"
    ) {

        $new_property_status =
            "rented";

    } elseif (
        $transaction["transaction_type"]
        === "sale"
    ) {

        $new_property_status =
            "sold";

    } else {

        $pdo->rollBack();

        header(
            "Location: /myhome/transactions.php?message=invalid"
        );

        exit;
    }


    /* =========================
       ACCEPT SELECTED REQUEST
       + SAVE ACCEPTED DATE
    ========================= */

    $accept = $pdo->prepare(
        "UPDATE transactions

         SET
            status = 'accepted',
            accepted_at = NOW()

         WHERE id = :transaction_id
           AND owner_id = :owner_id
           AND status = 'pending'"
    );


    $accept->execute([
        "transaction_id" =>
            $transaction_id,

        "owner_id" =>
            $owner_id
    ]);


    /* =========================
       UPDATE PROPERTY STATUS
    ========================= */

    $property_update = $pdo->prepare(
        "UPDATE properties

         SET status = :property_status

         WHERE id = :property_id
           AND user_id = :owner_id
           AND status = 'available'"
    );


    $property_update->execute([
        "property_status" =>
            $new_property_status,

        "property_id" =>
            (int) $transaction["property_id"],

        "owner_id" =>
            $owner_id
    ]);


    /* =========================
       REJECT OTHER PENDING REQUESTS
    ========================= */

    $reject_others = $pdo->prepare(
        "UPDATE transactions

         SET status = 'rejected'

         WHERE property_id = :property_id
           AND id != :transaction_id
           AND status = 'pending'"
    );


    $reject_others->execute([
        "property_id" =>
            (int) $transaction["property_id"],

        "transaction_id" =>
            $transaction_id
    ]);


    /* =========================
       SAVE EVERYTHING
    ========================= */

    $pdo->commit();


    /* =========================
       SUCCESS
    ========================= */

    header(
        "Location: /myhome/transactions.php?message=accepted"
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
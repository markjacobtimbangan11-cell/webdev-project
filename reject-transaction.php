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
   GET TRANSACTION
========================= */

$stmt = $pdo->prepare(
    "SELECT
        id,
        owner_id,
        status

     FROM transactions

     WHERE id = :transaction_id

     LIMIT 1"
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

    header(
        "Location: /myhome/transactions.php?message=notfound"
    );

    exit;
}


/* =========================
   VERIFY OWNER
========================= */

if (
    (int) $transaction["owner_id"]
    !== $owner_id
) {

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

    header(
        "Location: /myhome/transactions.php?message=processed"
    );

    exit;
}


/* =========================
   REJECT REQUEST
========================= */

$stmt = $pdo->prepare(
    "UPDATE transactions

     SET status = 'rejected'

     WHERE id = :transaction_id
       AND owner_id = :owner_id
       AND status = 'pending'"
);

$stmt->execute([
    "transaction_id" =>
        $transaction_id,

    "owner_id" =>
        $owner_id
]);


/* =========================
   SUCCESS
========================= */

header(
    "Location: /myhome/transactions.php?message=rejected"
);

exit;
<?php

session_start();

$pdo = require "config/database.php";

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

$user_id = (int) $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: /myhome/transactions.php?message=invalid");
    exit;
}

$transaction_id = isset($_POST["transaction_id"])
    ? (int) $_POST["transaction_id"]
    : 0;

if ($transaction_id <= 0) {
    header("Location: /myhome/transactions.php?message=invalid");
    exit;
}

$stmt = $pdo->prepare(
    "SELECT id, buyer_renter_id, status
     FROM transactions
     WHERE id = :transaction_id
     LIMIT 1"
);

$stmt->execute([
    "transaction_id" => $transaction_id
]);

$transaction = $stmt->fetch();

if (!$transaction) {
    header("Location: /myhome/transactions.php?message=notfound");
    exit;
}

if ((int) $transaction["buyer_renter_id"] !== $user_id) {
    header("Location: /myhome/transactions.php?message=unauthorized");
    exit;
}

if ($transaction["status"] !== "pending") {
    header("Location: /myhome/transactions.php?message=processed");
    exit;
}

try {
    $update = $pdo->prepare(
        "UPDATE transactions
         SET status = 'cancelled'
         WHERE id = :transaction_id
           AND buyer_renter_id = :buyer_renter_id
           AND status = 'pending'"
    );

    $update->execute([
        "transaction_id" => $transaction_id,
        "buyer_renter_id" => $user_id
    ]);

    if ($update->rowCount() !== 1) {
        header("Location: /myhome/transactions.php?message=processed");
        exit;
    }

    header("Location: /myhome/transactions.php?message=cancelled");
    exit;

} catch (PDOException $e) {
    header("Location: /myhome/transactions.php?message=error");
    exit;
}

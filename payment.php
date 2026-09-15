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


$user_id = (int) $_SESSION["user_id"];


/* =========================
   GET TRANSACTION ID
========================= */

$transaction_id =
    isset($_GET["transaction_id"])
        ? (int) $_GET["transaction_id"]
        : 0;


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
        transactions.id,
        transactions.buyer_renter_id,
        transactions.owner_id,
        transactions.transaction_type,
        transactions.status,

        properties.id AS property_id,
        properties.title,
        properties.price,
        properties.listing_type,

        users.full_name AS owner_name

     FROM transactions

     INNER JOIN properties
        ON transactions.property_id = properties.id

     INNER JOIN users
        ON transactions.owner_id = users.id

     WHERE transactions.id = :transaction_id

     LIMIT 1"
);

$stmt->execute([
    "transaction_id" => $transaction_id
]);

$transaction = $stmt->fetch();


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
   VERIFY REQUESTER
========================= */

if (
    (int) $transaction["buyer_renter_id"]
    !== $user_id
) {

    header(
        "Location: /myhome/transactions.php?message=unauthorized"
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

    header(
        "Location: /myhome/transactions.php?message=processed"
    );

    exit;
}


/* =========================
   CHECK EXISTING PAYMENT
========================= */

$payment_stmt = $pdo->prepare(
    "SELECT *
     FROM payments
     WHERE transaction_id = :transaction_id
       AND payer_id = :payer_id
     LIMIT 1"
);

$payment_stmt->execute([
    "transaction_id" => $transaction_id,
    "payer_id" => $user_id
]);

$existing_payment =
    $payment_stmt->fetch();


/* =========================
   ALREADY PAID
========================= */

if (
    $existing_payment &&
    $existing_payment["status"] === "paid"
) {

    header(
        "Location: /myhome/transactions.php?message=payment_success"
    );

    exit;
}


/* =========================
   HANDLE PAYMENT
========================= */

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $payment_method =
        $_POST["payment_method"] ?? "";

    $allowed_methods = [
        "gcash",
        "cash",
        "bank_transfer"
    ];


    if (
        !in_array(
            $payment_method,
            $allowed_methods,
            true
        )
    ) {

        $error =
            "Please select a valid payment method.";

    } else {

        try {

            $pdo->beginTransaction();


            /* =========================
               CREATE OR UPDATE PAYMENT
            ========================= */

            if ($existing_payment) {

                $update_payment =
                    $pdo->prepare(
                        "UPDATE payments

                         SET
                            amount = :amount,
                            payment_method = :payment_method,
                            status = 'paid',
                            paid_at = NOW()

                         WHERE id = :payment_id
                           AND payer_id = :payer_id"
                    );


                $update_payment->execute([

                    "amount" =>
                        $transaction["price"],

                    "payment_method" =>
                        $payment_method,

                    "payment_id" =>
                        (int) $existing_payment["id"],

                    "payer_id" =>
                        $user_id
                ]);

            } else {

                $insert_payment =
                    $pdo->prepare(
                        "INSERT INTO payments
                        (
                            transaction_id,
                            payer_id,
                            amount,
                            payment_method,
                            status,
                            paid_at
                        )
                        VALUES
                        (
                            :transaction_id,
                            :payer_id,
                            :amount,
                            :payment_method,
                            'paid',
                            NOW()
                        )"
                    );


                $insert_payment->execute([

                    "transaction_id" =>
                        $transaction_id,

                    "payer_id" =>
                        $user_id,

                    "amount" =>
                        $transaction["price"],

                    "payment_method" =>
                        $payment_method
                ]);
            }


            $pdo->commit();


            header(
                "Location: /myhome/transactions.php?message=payment_success"
            );

            exit;


        } catch (PDOException $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error =
                "Payment could not be processed. Please try again.";
        }
    }
}


/* =========================
   PAGE TITLE
========================= */

$page_title =
    "Payment | MyHome";


include "includes/header.php";
include "includes/navbar.php";

?>


<style>

.payment-page {
    min-height: 75vh;

    padding: 65px 5%;

    background: #f4f7f6;
}

.payment-container {
    width: min(760px, 100%);

    margin: 0 auto;
}

.payment-header {
    margin-bottom: 30px;
}

.payment-label {
    margin-bottom: 6px;

    font-family: "Barlow Condensed", sans-serif;

    font-size: 14px;
    font-weight: 700;

    letter-spacing: 1px;

    color: #00605e;
}

.payment-header h1 {
    margin: 0 0 8px;

    font-family: "Barlow Condensed", sans-serif;

    font-size: 45px;

    color: #0c3033;
}

.payment-header p {
    margin: 0;

    color: #718180;

    font-size: 13px;
}

.payment-card {
    padding: 30px;

    background: #ffffff;

    border-radius: 18px;

    box-shadow:
        0 10px 30px
        rgba(12, 48, 51, 0.07);
}

.payment-summary {
    margin-bottom: 30px;

    padding-bottom: 25px;

    border-bottom: 1px solid #e4eceb;
}

.payment-summary h2 {
    margin: 0 0 18px;

    font-family: "Barlow Condensed", sans-serif;

    font-size: 27px;

    color: #0c3033;
}

.payment-info-row {
    display: flex;

    justify-content: space-between;

    gap: 20px;

    margin-bottom: 12px;
}

.payment-info-row span {
    color: #7a8988;

    font-size: 12px;
}

.payment-info-row strong {
    color: #0c3033;

    font-size: 13px;

    text-align: right;
}

.payment-total {
    margin-top: 20px;

    padding-top: 18px;

    border-top: 1px solid #e4eceb;
}

.payment-total strong {
    font-size: 23px;

    color: #00605e;
}

.payment-form h3 {
    margin-bottom: 15px;

    color: #0c3033;

    font-family: "Barlow Condensed", sans-serif;

    font-size: 24px;
}

.payment-methods {
    display: grid;

    gap: 12px;

    margin-bottom: 25px;
}

.payment-option {
    display: flex;

    align-items: center;

    gap: 12px;

    padding: 15px;

    border: 1px solid #d8e3e2;

    border-radius: 12px;

    cursor: pointer;

    transition: 0.2s ease;
}

.payment-option:hover {
    border-color: #00605e;

    background: #f7fbfb;
}

.payment-option input {
    accent-color: #00605e;
}

.payment-option i {
    width: 22px;

    color: #00605e;

    font-size: 18px;
}

.payment-option span {
    color: #0c3033;

    font-size: 13px;

    font-weight: 600;
}

.payment-submit {
    width: 100%;

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 8px;

    padding: 13px 18px;

    border: none;

    border-radius: 999px;

    background: #00605e;

    color: white;

    font-family: "Poppins", sans-serif;

    font-size: 12px;

    font-weight: 600;

    cursor: pointer;

    transition: 0.2s ease;
}

.payment-submit:hover {
    background: #008080;

    transform: translateY(-1px);
}

.payment-error {
    margin-bottom: 20px;

    padding: 13px 15px;

    border: 1px solid #efc4c4;

    border-radius: 10px;

    background: #fff0f0;

    color: #b83d3d;

    font-size: 12px;

    font-weight: 600;
}

.payment-back {
    display: inline-flex;

    align-items: center;

    gap: 7px;

    margin-bottom: 20px;

    color: #00605e;

    font-size: 12px;

    font-weight: 600;

    text-decoration: none;
}

.payment-back:hover {
    color: #008080;
}

.payment-note {
    margin-top: 15px;

    color: #869392;

    font-size: 11px;

    line-height: 1.6;

    text-align: center;
}

@media (max-width: 600px) {

    .payment-page {
        padding: 40px 5%;
    }

    .payment-card {
        padding: 22px;
    }

    .payment-header h1 {
        font-size: 38px;
    }

}

</style>


<main class="payment-page">

    <div class="payment-container">


        <a
            href="/myhome/transactions.php"
            class="payment-back"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Back to Transactions

        </a>


        <div class="payment-header">

            <p class="payment-label">
                PROPERTY PAYMENT
            </p>

            <h1>
                Payment
            </h1>

            <p>
                Review the property transaction and
                choose your preferred payment method.
            </p>

        </div>


        <div class="payment-card">


            <?php if ($error !== ""): ?>

                <div class="payment-error">

                    <i class="fa-solid fa-circle-exclamation"></i>

                    <?= htmlspecialchars($error) ?>

                </div>

            <?php endif; ?>


            <!-- PAYMENT SUMMARY -->

            <div class="payment-summary">

                <h2>
                    Transaction Summary
                </h2>


                <div class="payment-info-row">

                    <span>
                        Property
                    </span>

                    <strong>
                        <?= htmlspecialchars(
                            $transaction["title"]
                        ) ?>
                    </strong>

                </div>


                <div class="payment-info-row">

                    <span>
                        Owner
                    </span>

                    <strong>
                        <?= htmlspecialchars(
                            $transaction["owner_name"]
                        ) ?>
                    </strong>

                </div>


                <div class="payment-info-row">

                    <span>
                        Transaction Type
                    </span>

                    <strong>

                        <?php if (
                            $transaction["transaction_type"]
                            === "rent"
                        ): ?>

                            Rent

                        <?php else: ?>

                            Buy

                        <?php endif; ?>

                    </strong>

                </div>


                <div class="payment-info-row payment-total">

                    <span>
                        Amount
                    </span>

                    <strong>

                        ₱<?= number_format(
                            (float) $transaction["price"],
                            2
                        ) ?>

                        <?php if (
                            $transaction["listing_type"]
                            === "rent"
                        ): ?>

                            / month

                        <?php endif; ?>

                    </strong>

                </div>

            </div>


            <!-- PAYMENT FORM -->

            <form
                method="POST"
                class="payment-form"
            >

                <h3>
                    Select Payment Method
                </h3>


                <div class="payment-methods">


                    <label class="payment-option">

                        <input
                            type="radio"
                            name="payment_method"
                            value="gcash"
                            required
                        >

                        <i class="fa-solid fa-mobile-screen-button"></i>

                        <span>
                            GCash
                        </span>

                    </label>


                    <label class="payment-option">

                        <input
                            type="radio"
                            name="payment_method"
                            value="bank_transfer"
                            required
                        >

                        <i class="fa-solid fa-building-columns"></i>

                        <span>
                            Bank Transfer
                        </span>

                    </label>


                    <label class="payment-option">

                        <input
                            type="radio"
                            name="payment_method"
                            value="cash"
                            required
                        >

                        <i class="fa-solid fa-money-bill-wave"></i>

                        <span>
                            Cash
                        </span>

                    </label>


                </div>


                <button
                    type="submit"
                    class="payment-submit"
                    onclick="return confirm('Confirm this payment?');"
                >

                    <i class="fa-solid fa-lock"></i>

                    Confirm Payment

                </button>


                <p class="payment-note">

                    This payment feature is a simulated
                    payment process for the MyHome system.
                    No real money will be transferred.

                </p>


            </form>


        </div>


    </div>

</main>


<?php

include "includes/footer.php";

?>
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
   GET PAYMENT ID
========================= */

$payment_id =
    isset($_GET["payment_id"])
        ? (int) $_GET["payment_id"]
        : 0;


if ($payment_id <= 0) {
    header("Location: /myhome/transactions.php?message=invalid");
    exit;
}


/* =========================
   GET PAYMENT / TRANSACTION
========================= */

$stmt = $pdo->prepare(
    "SELECT
        payments.id AS payment_id,
        payments.amount,
        payments.payment_method,
        payments.status AS payment_status,
        payments.paid_at,
        payments.created_at AS payment_created_at,

        transactions.id AS transaction_id,
        transactions.transaction_type,
        transactions.status AS transaction_status,
        transactions.created_at AS requested_at,
        transactions.accepted_at,
        transactions.completed_at,
        transactions.buyer_renter_id,
        transactions.owner_id,

        properties.id AS property_id,
        properties.title,
        properties.location,
        properties.listing_type,

        payer.full_name AS payer_name,
        payer.email AS payer_email,

        owner.full_name AS owner_name,
        owner.email AS owner_email

     FROM payments

     INNER JOIN transactions
        ON payments.transaction_id = transactions.id

     INNER JOIN properties
        ON transactions.property_id = properties.id

     INNER JOIN users AS payer
        ON payments.payer_id = payer.id

     INNER JOIN users AS owner
        ON transactions.owner_id = owner.id

     WHERE payments.id = :payment_id

     LIMIT 1"
);

$stmt->execute([
    "payment_id" => $payment_id
]);

$receipt = $stmt->fetch();


/* =========================
   RECEIPT NOT FOUND
========================= */

if (!$receipt) {
    header("Location: /myhome/transactions.php?message=notfound");
    exit;
}


/* =========================
   AUTHORIZATION
   REQUESTER OR OWNER ONLY
========================= */

$is_payer =
    (int) $receipt["buyer_renter_id"] === $user_id;

$is_owner =
    (int) $receipt["owner_id"] === $user_id;


if (!$is_payer && !$is_owner) {
    header("Location: /myhome/transactions.php?message=unauthorized");
    exit;
}


/* =========================
   ONLY PAID RECEIPTS
========================= */

if ($receipt["payment_status"] !== "paid") {
    header("Location: /myhome/transactions.php?message=processed");
    exit;
}


/* =========================
   RECEIPT NUMBER
========================= */

$receipt_number =
    "MH-" .
    str_pad(
        (string) $receipt["payment_id"],
        6,
        "0",
        STR_PAD_LEFT
    );


/* =========================
   FORMAT METHOD
========================= */

$payment_method =
    ucwords(
        str_replace(
            "_",
            " ",
            $receipt["payment_method"]
        )
    );


/* =========================
   PAGE TITLE
========================= */

$page_title = "Payment Receipt | MyHome";

include "includes/header.php";
include "includes/navbar.php";

?>


<style>

.receipt-page {
    min-height: 75vh;
    padding: 60px 5%;
    background: #f4f7f6;
}

.receipt-container {
    width: min(820px, 100%);
    margin: 0 auto;
}

.receipt-back {
    display: inline-flex;
    align-items: center;
    gap: 7px;

    margin-bottom: 20px;

    color: #00605e;

    font-size: 12px;
    font-weight: 600;

    text-decoration: none;
}

.receipt-back:hover {
    color: #008080;
}

.receipt-card {
    padding: 38px;

    background: #ffffff;

    border-radius: 18px;

    box-shadow:
        0 10px 30px
        rgba(12, 48, 51, 0.08);
}

.receipt-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 25px;

    padding-bottom: 25px;
    margin-bottom: 28px;

    border-bottom: 1px solid #e1e9e8;
}

.receipt-brand-label {
    margin: 0 0 5px;

    color: #00605e;

    font-family: "Barlow Condensed", sans-serif;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 1px;
}

.receipt-top h1 {
    margin: 0 0 6px;

    color: #0c3033;

    font-family: "Barlow Condensed", sans-serif;
    font-size: 38px;
    line-height: 1;
}

.receipt-top p {
    margin: 0;

    color: #7b8b8d;

    font-size: 12px;
}

.receipt-paid-badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;

    padding: 9px 14px;

    border-radius: 999px;

    background: #e8f6ef;
    color: #17834f;

    font-size: 11px;
    font-weight: 700;
}

.receipt-number-box {
    margin-bottom: 28px;
    padding: 16px 18px;

    border: 1px solid #dfe9e8;
    border-radius: 12px;

    background: #f8fbfa;
}

.receipt-number-box small {
    display: block;

    margin-bottom: 4px;

    color: #7b8b8d;

    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.8px;

    text-transform: uppercase;
}

.receipt-number-box strong {
    color: #0c3033;

    font-size: 17px;
}

.receipt-section {
    margin-bottom: 28px;
}

.receipt-section h2 {
    margin: 0 0 15px;

    color: #0c3033;

    font-family: "Barlow Condensed", sans-serif;
    font-size: 24px;
}

.receipt-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 25px;

    padding: 10px 0;

    border-bottom: 1px solid #edf2f2;
}

.receipt-row:last-child {
    border-bottom: none;
}

.receipt-row span {
    color: #7b8b8d;

    font-size: 12px;
}

.receipt-row strong {
    max-width: 60%;

    color: #0c3033;

    font-size: 12px;
    font-weight: 600;

    text-align: right;
}

.receipt-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;

    margin-top: 30px;
    padding: 22px;

    border-radius: 14px;

    background: #f1f8f7;
}

.receipt-total span {
    color: #48605f;

    font-size: 13px;
    font-weight: 600;
}

.receipt-total strong {
    color: #00605e;

    font-family: "Barlow Condensed", sans-serif;
    font-size: 30px;
}

.receipt-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    flex-wrap: wrap;

    margin-top: 28px;
}

.receipt-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;

    padding: 11px 17px;

    border-radius: 999px;

    font-family: "Poppins", sans-serif;
    font-size: 11px;
    font-weight: 600;

    text-decoration: none;
    cursor: pointer;

    transition: 0.2s ease;
}

.receipt-btn.secondary {
    border: 1px solid #cfdada;

    background: #ffffff;
    color: #0c3033;
}

.receipt-btn.primary {
    border: 1px solid #00605e;

    background: #00605e;
    color: #ffffff;
}

.receipt-btn:hover {
    transform: translateY(-1px);
}

.receipt-note {
    margin-top: 25px;

    color: #8a9796;

    font-size: 10px;
    line-height: 1.6;

    text-align: center;
}

@media (max-width: 650px) {

    .receipt-page {
        padding: 40px 5%;
    }

    .receipt-card {
        padding: 24px;
    }

    .receipt-top {
        flex-direction: column;
    }

    .receipt-row strong {
        max-width: 55%;
    }

    .receipt-actions {
        justify-content: stretch;
    }

    .receipt-btn {
        flex: 1;
    }
}

@media print {

    body {
        background: #ffffff !important;
    }

    header,
    nav,
    footer,
    .receipt-back,
    .receipt-actions,
    .receipt-note {
        display: none !important;
    }

    .receipt-page {
        padding: 0;
        background: #ffffff;
    }

    .receipt-container {
        width: 100%;
    }

    .receipt-card {
        box-shadow: none;
        border-radius: 0;
        padding: 20px;
    }
}

</style>


<main class="receipt-page">

    <div class="receipt-container">


        <a
            href="/myhome/transactions.php"
            class="receipt-back"
        >
            <i class="fa-solid fa-arrow-left"></i>
            Back to Transactions
        </a>


        <div class="receipt-card">


            <div class="receipt-top">

                <div>

                    <p class="receipt-brand-label">
                        MYHOME
                    </p>

                    <h1>
                        Payment Receipt
                    </h1>

                    <p>
                        Official transaction payment record
                    </p>

                </div>


                <div class="receipt-paid-badge">

                    <i class="fa-solid fa-circle-check"></i>

                    PAID

                </div>

            </div>


            <div class="receipt-number-box">

                <small>
                    Receipt Number
                </small>

                <strong>
                    <?= htmlspecialchars($receipt_number) ?>
                </strong>

            </div>


            <div class="receipt-section">

                <h2>
                    Transaction Details
                </h2>


                <div class="receipt-row">

                    <span>
                        Property
                    </span>

                    <strong>
                        <?= htmlspecialchars($receipt["title"]) ?>
                    </strong>

                </div>


                <div class="receipt-row">

                    <span>
                        Property Location
                    </span>

                    <strong>
                        <?= htmlspecialchars($receipt["location"]) ?>
                    </strong>

                </div>


                <div class="receipt-row">

                    <span>
                        Transaction Type
                    </span>

                    <strong>

                        <?= $receipt["transaction_type"] === "rent"
                            ? "Rent"
                            : "Purchase" ?>

                    </strong>

                </div>


                <div class="receipt-row">

                    <span>
                        Transaction ID
                    </span>

                    <strong>
                        #<?= (int) $receipt["transaction_id"] ?>
                    </strong>

                </div>

            </div>


            <div class="receipt-section">

                <h2>
                    Parties
                </h2>


                <div class="receipt-row">

                    <span>
                        <?= $receipt["transaction_type"] === "rent"
                            ? "Renter"
                            : "Buyer" ?>
                    </span>

                    <strong>

                        <?= htmlspecialchars($receipt["payer_name"]) ?>

                        <br>

                        <?= htmlspecialchars($receipt["payer_email"]) ?>

                    </strong>

                </div>


                <div class="receipt-row">

                    <span>
                        Property Owner
                    </span>

                    <strong>

                        <?= htmlspecialchars($receipt["owner_name"]) ?>

                        <br>

                        <?= htmlspecialchars($receipt["owner_email"]) ?>

                    </strong>

                </div>

            </div>


            <div class="receipt-section">

                <h2>
                    Payment Details
                </h2>


                <div class="receipt-row">

                    <span>
                        Payment Method
                    </span>

                    <strong>
                        <?= htmlspecialchars($payment_method) ?>
                    </strong>

                </div>


                <div class="receipt-row">

                    <span>
                        Payment Status
                    </span>

                    <strong>
                        Paid
                    </strong>

                </div>


                <div class="receipt-row">

                    <span>
                        Payment Date
                    </span>

                    <strong>

                        <?php if (!empty($receipt["paid_at"])): ?>

                            <?= date(
                                "M d, Y - h:i A",
                                strtotime($receipt["paid_at"])
                            ) ?>

                        <?php else: ?>

                            —

                        <?php endif; ?>

                    </strong>

                </div>

            </div>


            <div class="receipt-total">

                <span>
                    Amount Paid
                </span>

                <strong>

                    ₱<?= number_format(
                        (float) $receipt["amount"],
                        2
                    ) ?>

                    <?php if (
                        $receipt["listing_type"] === "rent"
                    ): ?>

                        <small style="font-size: 12px;">
                            / month
                        </small>

                    <?php endif; ?>

                </strong>

            </div>


            <div class="receipt-actions">

                <a
                    href="/myhome/transactions.php"
                    class="receipt-btn secondary"
                >
                    <i class="fa-solid fa-arrow-left"></i>
                    Transactions
                </a>


                <button
                    type="button"
                    class="receipt-btn primary"
                    onclick="window.print();"
                >
                    <i class="fa-solid fa-print"></i>
                    Print Receipt
                </button>

            </div>


            <p class="receipt-note">

                This receipt is generated by the MyHome
                simulated payment system. No real payment
                gateway transaction is performed.

            </p>


        </div>

    </div>

</main>


<?php

include "includes/footer.php";

?>
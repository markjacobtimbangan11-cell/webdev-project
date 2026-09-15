<?php

session_start();

$pdo = require "../config/database.php";


/* =========================
   ADMIN ACCESS ONLY
========================= */

if (!isset($_SESSION["user_id"])) {
    header("Location: /myhome/login.php");
    exit;
}

if (
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "admin"
) {
    header("Location: /myhome/index.php");
    exit;
}


/* =========================
   PAYMENT ID
========================= */

$payment_id =
    isset($_GET["payment_id"])
        ? (int) $_GET["payment_id"]
        : 0;

if ($payment_id <= 0) {
    header("Location: /myhome/admin/transactions.php");
    exit;
}


/* =========================
   GET RECEIPT
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

if (!$receipt) {
    header("Location: /myhome/admin/transactions.php");
    exit;
}

if ($receipt["payment_status"] !== "paid") {
    header("Location: /myhome/admin/transactions.php");
    exit;
}


/* =========================
   FORMAT VALUES
========================= */

$receipt_number =
    "MH-" .
    str_pad(
        (string) $receipt["payment_id"],
        6,
        "0",
        STR_PAD_LEFT
    );

$payment_method =
    ucwords(
        str_replace(
            "_",
            " ",
            $receipt["payment_method"]
        )
    );

$page_title = "Receipt | MyHome Admin";

include "../includes/header.php";

?>


<style>

.admin-receipt-card {
    padding: 22px 26px;

    background: #ffffff;
    border-radius: 14px;

    box-shadow:
        0 10px 30px
        rgba(12, 48, 51, 0.07);
}

.admin-receipt-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;

    padding-bottom: 14px;
    margin-bottom: 16px;

    border-bottom: 1px solid #e1e9e8;
}

.admin-receipt-top h1 {
    margin: 0 0 6px;

    color: #0c3033;

    font-family: "Barlow Condensed", sans-serif;
    font-size: 28px;
}

.admin-receipt-top p {
    margin: 0;

    color: #7b8b8d;
    font-size: 9px;
}

.admin-receipt-paid {
    display: inline-flex;
    align-items: center;
    gap: 6px;

    padding: 6px 10px;

    border-radius: 999px;

    background: #e8f6ef;
    color: #17834f;

    font-size: 10px;
    font-weight: 700;
}

.admin-receipt-number {
    padding: 10px 12px;
    margin-bottom: 16px;

    background: #f7faf9;

    border: 1px solid #dfe9e8;
    border-radius: 12px;
}

.admin-receipt-number small {
    display: block;

    margin-bottom: 3px;

    color: #7b8b8d;

    font-size: 9px;
    font-weight: 700;

    text-transform: uppercase;
}

.admin-receipt-number strong {
    color: #0c3033;
    font-size: 14px;
}

.admin-receipt-section {
    margin-bottom: 16px;
}

.admin-receipt-section h2 {
    margin: 0 0 13px;

    color: #0c3033;

    font-family: "Barlow Condensed", sans-serif;
    font-size: 19px;
}

.admin-receipt-row {
    display: flex;
    justify-content: space-between;
    gap: 25px;

    padding: 6px 0;

    border-bottom: 1px solid #edf2f2;
}

.admin-receipt-row:last-child {
    border-bottom: none;
}

.admin-receipt-row span {
    color: #7b8b8d;
    font-size: 10px;
}

.admin-receipt-row strong {
    max-width: 62%;

    color: #0c3033;

    font-size: 11px;
    font-weight: 600;

    text-align: right;
}

.admin-receipt-total {
    display: flex;
    justify-content: space-between;
    align-items: center;

    margin-top: 16px;
    padding: 14px 16px;

    background: #f1f8f7;
    border-radius: 13px;
}

.admin-receipt-total span {
    color: #48605f;

    font-size: 12px;
    font-weight: 600;
}

.admin-receipt-total strong {
    color: #00605e;

    font-family: "Barlow Condensed", sans-serif;
    font-size: 23px;
}

.admin-receipt-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;

    margin-top: 16px;
}

.admin-receipt-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;

    padding: 8px 12px;

    border-radius: 999px;

    font-family: "Poppins", sans-serif;
    font-size: 10px;
    font-weight: 600;

    text-decoration: none;
    cursor: pointer;
}

.admin-receipt-btn.secondary {
    border: 1px solid #cfdada;

    background: #ffffff;
    color: #0c3033;
}

.admin-receipt-btn.primary {
    border: 1px solid #00605e;

    background: #00605e;
    color: #ffffff;
}

.admin-receipt-note {
    margin-top: 14px;

    color: #899796;
    font-size: 9px;

    text-align: center;
}

@media print {

    .admin-sidebar,
    .admin-header,
    .admin-receipt-actions,
    .admin-receipt-note {
        display: none !important;
    }

    .admin-page,
    .admin-layout,
    .admin-content {
        display: block !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #ffffff !important;
    }

    .admin-receipt-card {
        box-shadow: none;
        border-radius: 0;
    }
}


.admin-content {
    padding-top: 26px !important;
}

.admin-header {
    margin-bottom: 18px !important;
}

.admin-header h1 {
    font-size: 34px !important;
}

.admin-header p {
    font-size: 11px !important;
}

.admin-receipt-card {
    width: min(760px, 100%);
    margin-left: auto;
    margin-right: auto;
}

</style>


<main class="admin-page">

    <div class="admin-layout">


        <?php include "../includes/admin-sidebar.php"; ?>


        <section class="admin-content">


            <div class="admin-header">

                <div>

                    <p class="section-label">
                        PAYMENT RECORD
                    </p>

                    <h1>
                        Receipt
                    </h1>

                    <p>
                        View a completed payment transaction.
                    </p>

                </div>


                <div class="admin-user">

                    <i class="fa-solid fa-circle-user"></i>

                    <span>
                        <?= htmlspecialchars(
                            $_SESSION["full_name"] ?? "Admin"
                        ) ?>
                    </span>

                </div>

            </div>


            <div class="admin-receipt-card">


                <div class="admin-receipt-top">

                    <div>

                        <h1>
                            Payment Receipt
                        </h1>

                        <p>
                            MyHome transaction payment record
                        </p>

                    </div>


                    <span class="admin-receipt-paid">

                        <i class="fa-solid fa-circle-check"></i>

                        PAID

                    </span>

                </div>


                <div class="admin-receipt-number">

                    <small>
                        Receipt Number
                    </small>

                    <strong>
                        <?= htmlspecialchars($receipt_number) ?>
                    </strong>

                </div>


                <div class="admin-receipt-section">

                    <h2>
                        Transaction Details
                    </h2>


                    <div class="admin-receipt-row">

                        <span>
                            Transaction ID
                        </span>

                        <strong>
                            #<?= (int) $receipt["transaction_id"] ?>
                        </strong>

                    </div>


                    <div class="admin-receipt-row">

                        <span>
                            Property
                        </span>

                        <strong>
                            <?= htmlspecialchars($receipt["title"]) ?>
                        </strong>

                    </div>


                    <div class="admin-receipt-row">

                        <span>
                            Location
                        </span>

                        <strong>
                            <?= htmlspecialchars($receipt["location"]) ?>
                        </strong>

                    </div>


                    <div class="admin-receipt-row">

                        <span>
                            Transaction Type
                        </span>

                        <strong>

                            <?= $receipt["transaction_type"] === "rent"
                                ? "Rent"
                                : "Purchase" ?>

                        </strong>

                    </div>

                </div>


                <div class="admin-receipt-section">

                    <h2>
                        Buyer / Renter and Owner
                    </h2>


                    <div class="admin-receipt-row">

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


                    <div class="admin-receipt-row">

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


                <div class="admin-receipt-section">

                    <h2>
                        Payment Details
                    </h2>


                    <div class="admin-receipt-row">

                        <span>
                            Payment Method
                        </span>

                        <strong>
                            <?= htmlspecialchars($payment_method) ?>
                        </strong>

                    </div>


                    <div class="admin-receipt-row">

                        <span>
                            Payment Status
                        </span>

                        <strong>
                            Paid
                        </strong>

                    </div>


                    <div class="admin-receipt-row">

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


                <div class="admin-receipt-total">

                    <span>
                        Amount Paid
                    </span>

                    <strong>

                        ₱<?= number_format(
                            (float) $receipt["amount"],
                            2
                        ) ?>

                    </strong>

                </div>


                <div class="admin-receipt-actions">

                    <a
                        href="/myhome/admin/transactions.php"
                        class="admin-receipt-btn secondary"
                    >
                        <i class="fa-solid fa-arrow-left"></i>
                        Back to Transactions
                    </a>


                    <button
                        type="button"
                        class="admin-receipt-btn primary"
                        onclick="window.print();"
                    >
                        <i class="fa-solid fa-print"></i>
                        Print Receipt
                    </button>

                </div>


                <p class="admin-receipt-note">

                    Admin view only. This receipt is generated
                    from the MyHome simulated payment system.

                </p>


            </div>


        </section>

    </div>

</main>


<?php

include "../includes/footer.php";

?>

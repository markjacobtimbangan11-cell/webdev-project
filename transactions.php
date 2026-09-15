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
   REQUESTS RECEIVED
========================= */

$received_stmt = $pdo->prepare(
    "SELECT
        transactions.id,
        transactions.property_id,
        transactions.transaction_type,
        transactions.status,
        transactions.created_at,
        transactions.accepted_at,
        transactions.completed_at,

        properties.title,
        properties.price,
        properties.listing_type,

        users.full_name AS requester_name,
        users.email AS requester_email,

        payments.id AS payment_id,
        payments.status AS payment_status,
        payments.payment_method,
        payments.paid_at

     FROM transactions

     INNER JOIN properties
        ON transactions.property_id = properties.id

     INNER JOIN users
        ON transactions.buyer_renter_id = users.id

     LEFT JOIN payments
        ON transactions.id = payments.transaction_id

     WHERE transactions.owner_id = :owner_id

     ORDER BY transactions.created_at DESC"
);

$received_stmt->execute([
    "owner_id" => $user_id
]);

$received_requests = $received_stmt->fetchAll();


/* =========================
   MY REQUESTS
========================= */

$my_stmt = $pdo->prepare(
    "SELECT
        transactions.id,
        transactions.property_id,
        transactions.transaction_type,
        transactions.status,
        transactions.created_at,
        transactions.accepted_at,
        transactions.completed_at,

        properties.title,
        properties.price,
        properties.listing_type,

        users.full_name AS owner_name,
        users.email AS owner_email,

        payments.id AS payment_id,
        payments.status AS payment_status,
        payments.payment_method,
        payments.paid_at

     FROM transactions

     INNER JOIN properties
        ON transactions.property_id = properties.id

     INNER JOIN users
        ON transactions.owner_id = users.id

     LEFT JOIN payments
        ON transactions.id = payments.transaction_id
        AND payments.payer_id = :payer_id

     WHERE transactions.buyer_renter_id = :buyer_renter_id

     ORDER BY transactions.created_at DESC"
);

$my_stmt->execute([
    "payer_id" => $user_id,
    "buyer_renter_id" => $user_id
]);

$my_requests = $my_stmt->fetchAll();


/* =========================
   MESSAGE
========================= */

$message = $_GET["message"] ?? "";


/* =========================
   HEADER + NAVBAR
========================= */

include "includes/header.php";
include "includes/navbar.php";

?>


<style>

/* =========================
   TRANSACTIONS PAGE
========================= */

.transactions-page {
    min-height: 75vh;
    padding: 65px 5%;
    background: #f4f7f6;
}

.transactions-container {
    width: min(1200px, 100%);
    margin: 0 auto;
}


/* PAGE HEADER */

.transactions-header {
    margin-bottom: 35px;
}

.transactions-label {
    margin-bottom: 7px;

    font-family: "Barlow Condensed", sans-serif;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 1px;

    color: #00605e;
}

.transactions-header h1 {
    margin: 0 0 8px;

    font-family: "Barlow Condensed", sans-serif;
    font-size: 46px;
    line-height: 1;

    color: #0c3033;
}

.transactions-header > p:last-child {
    color: #718180;
    font-size: 13px;
}


/* SECTION CARD */

.transactions-section {
    margin-bottom: 30px;
    padding: 28px;

    background: #ffffff;

    border-radius: 18px;

    box-shadow:
        0 10px 30px
        rgba(12, 48, 51, 0.07);
}

.transactions-section-header {
    margin-bottom: 24px;
}

.transactions-section-header h2 {
    margin: 0 0 5px;

    font-family: "Barlow Condensed", sans-serif;
    font-size: 29px;

    color: #0c3033;
}

.transactions-section-header p {
    margin: 0;

    color: #7b8b8d;
    font-size: 13px;
}


/* TABLE */

.transactions-table-wrapper {
    width: 100%;
    overflow-x: auto;
}

.transactions-table {
    width: 100%;
    border-collapse: collapse;
}

.transactions-table th {
    padding: 14px 16px;

    text-align: left;

    color: #7b8b8d;

    font-size: 11px;
    font-weight: 700;

    text-transform: uppercase;

    background: #f5f8f8;

    border-bottom: 1px solid #dfe9e8;
}

.transactions-table td {
    padding: 18px 16px;

    vertical-align: middle;

    color: #0c3033;

    font-size: 13px;

    border-bottom: 1px solid #edf2f2;
}

.transactions-table tbody tr:last-child td {
    border-bottom: none;
}

.transactions-table tbody tr:hover {
    background: #fafcfc;
}


/* PROPERTY */

.transaction-property-link {
    display: block;

    margin-bottom: 4px;

    color: #005b59;

    font-size: 14px;
    font-weight: 700;

    text-decoration: none;
}

.transaction-property-link:hover {
    color: #008080;
}

.transactions-table td small {
    display: block;

    margin-top: 3px;

    color: #82908f;

    font-size: 11px;
}

.transactions-table td strong {
    display: block;

    margin-bottom: 3px;

    color: #0c3033;

    font-size: 13px;
}


/* TIMELINE */

.transaction-timeline {
    min-width: 145px;
}

.transaction-timeline-item {
    display: flex;
    align-items: center;
    gap: 6px;

    margin-bottom: 5px;

    font-size: 11px;
    color: #718180;
}

.transaction-timeline-item:last-child {
    margin-bottom: 0;
}

.transaction-timeline-item i {
    width: 14px;

    text-align: center;

    color: #00605e;

    font-size: 10px;
}

.transaction-timeline-item strong {
    display: inline !important;

    margin: 0 !important;

    font-size: 11px !important;
}


/* TYPE BADGES */

.transaction-type {
    display: inline-flex;

    padding: 6px 11px;

    border-radius: 999px;

    font-size: 11px;
    font-weight: 700;
}

.transaction-type.rent {
    background: #e7f1ff;
    color: #2864a8;
}

.transaction-type.sale {
    background: #e8f6ef;
    color: #17834f;
}


/* STATUS BADGES */

.transaction-status {
    display: inline-flex;
    align-items: center;

    padding: 6px 11px;

    border-radius: 999px;

    font-size: 11px;
    font-weight: 700;
}

.transaction-status.pending {
    background: #fff5d6;
    color: #9a7100;
}

.transaction-status.accepted {
    background: #e8f6ef;
    color: #17834f;
}

.transaction-status.rejected {
    background: #ffeaea;
    color: #b83d3d;
}

.transaction-status.cancelled {
    background: #eeeeee;
    color: #666666;
}

.transaction-status.completed {
    background: #e8f1ff;
    color: #295f9b;
}


/* ACTION BUTTONS */

.transaction-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.transaction-actions form {
    margin: 0;
}

.transaction-accept-btn,
.transaction-reject-btn,
.transaction-end-btn,
.transaction-payment-btn,
.transaction-cancel-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    gap: 6px;

    padding: 9px 14px;

    border: none;
    border-radius: 999px;

    font-family: "Poppins", sans-serif;

    font-size: 11px;
    font-weight: 600;

    cursor: pointer;

    transition: 0.25s ease;

    text-decoration: none;
}

.transaction-accept-btn {
    background: #00605e;
    color: white;
}

.transaction-accept-btn:hover {
    background: #008080;
    transform: translateY(-1px);
}

.transaction-reject-btn {
    background: #fff0f0;
    color: #b83d3d;

    border: 1px solid #f0cccc;
}

.transaction-reject-btn:hover {
    background: #f8dede;
    transform: translateY(-1px);
}

.transaction-end-btn {
    background: #eef3f3;
    color: #0c3033;

    border: 1px solid #cfdada;
}

.transaction-end-btn:hover {
    background: #dfe9e8;
    transform: translateY(-1px);
}

.transaction-cancel-btn {
    background: #fff0f0;
    color: #b83d3d;
    border: 1px solid #f0cccc;
}

.transaction-cancel-btn:hover {
    background: #f8dede;
    transform: translateY(-1px);
}


/* PAYMENT BUTTON */

.transaction-payment-btn {
    background: #00605e;
    color: #ffffff;

    white-space: nowrap;
}

.transaction-payment-btn:hover {
    background: #008080;
    color: #ffffff;
    transform: translateY(-1px);
}

.transaction-receipt-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;

    margin-top: 8px;
    padding: 7px 11px;

    border: 1px solid #cfdada;
    border-radius: 999px;

    background: #ffffff;
    color: #00605e;

    font-family: "Poppins", sans-serif;
    font-size: 10px;
    font-weight: 600;

    text-decoration: none;
    white-space: nowrap;

    transition: 0.25s ease;
}

.transaction-receipt-btn:hover {
    background: #eef7f6;
    border-color: #00605e;
    transform: translateY(-1px);
}

.payment-method-text {
    margin-top: 6px !important;
    text-transform: capitalize;
}


/* EMPTY STATE */

.transactions-empty {
    padding: 50px 20px;

    text-align: center;

    color: #7b8b8d;
}

.transactions-empty > i {
    margin-bottom: 14px;

    color: #80a7a5;

    font-size: 40px;
}

.transactions-empty h3 {
    margin-bottom: 5px;

    color: #0c3033;

    font-family: "Barlow Condensed", sans-serif;

    font-size: 24px;
}

.transactions-empty p {
    margin: 0;

    color: #7b8b8d;

    font-size: 12px;
}


/* ALERT */

.transaction-message {
    margin-bottom: 25px;
}

.transaction-alert {
    display: flex;
    align-items: center;

    gap: 10px;

    padding: 14px 17px;

    border-radius: 10px;

    font-size: 12px;
    font-weight: 600;
}

.transaction-alert.success {
    background: #e9f7f1;

    border: 1px solid #b9e2cf;

    color: #16784b;
}

.transaction-alert.error {
    background: #fff0f0;

    border: 1px solid #efc4c4;

    color: #b83d3d;
}


/* RESPONSIVE */

@media (max-width: 800px) {

    .transactions-page {
        padding: 45px 5%;
    }

    .transactions-header h1 {
        font-size: 38px;
    }

    .transactions-section {
        padding: 20px;
    }

    .transactions-table {
        min-width: 1050px;
    }

}

</style>


<main class="transactions-page">

    <div class="transactions-container">


        <!-- HEADER -->

        <div class="transactions-header">

            <p class="transactions-label">
                PROPERTY TRANSACTIONS
            </p>

            <h1>
                Transactions
            </h1>

            <p>
                Manage requests you received and
                track the properties you requested.
            </p>

        </div>


        <!-- MESSAGE -->

        <?php if ($message !== ""): ?>

            <div class="transaction-message">


                <?php if ($message === "requested"): ?>

                    <div class="transaction-alert success">

                        <i class="fa-solid fa-circle-check"></i>

                        <span>
                            Transaction request submitted successfully.
                        </span>

                    </div>


                <?php elseif ($message === "exists"): ?>

                    <div class="transaction-alert error">

                        <i class="fa-solid fa-circle-exclamation"></i>

                        <span>
                            You already have an active request for this property.
                        </span>

                    </div>


                <?php elseif ($message === "accepted"): ?>

                    <div class="transaction-alert success">

                        <i class="fa-solid fa-circle-check"></i>

                        <span>
                            Transaction request accepted successfully.
                        </span>

                    </div>


                <?php elseif ($message === "rejected"): ?>

                    <div class="transaction-alert success">

                        <i class="fa-solid fa-circle-check"></i>

                        <span>
                            Transaction request rejected successfully.
                        </span>

                    </div>


                <?php elseif ($message === "completed"): ?>

                    <div class="transaction-alert success">

                        <i class="fa-solid fa-circle-check"></i>

                        <span>
                            Rental completed successfully. The property is available again.
                        </span>

                    </div>


                <?php elseif ($message === "payment_success"): ?>

                    <div class="transaction-alert success">

                        <i class="fa-solid fa-circle-check"></i>

                        <span>
                            Payment completed successfully.
                        </span>

                    </div>


                <?php elseif ($message === "cancelled"): ?>

                    <div class="transaction-alert success">

                        <i class="fa-solid fa-circle-check"></i>

                        <span>
                            Transaction request cancelled successfully.
                        </span>

                    </div>


                <?php elseif ($message === "unauthorized"): ?>

                    <div class="transaction-alert error">

                        <i class="fa-solid fa-circle-exclamation"></i>

                        <span>
                            You are not authorized to manage this request.
                        </span>

                    </div>


                <?php elseif ($message === "unavailable"): ?>

                    <div class="transaction-alert error">

                        <i class="fa-solid fa-circle-exclamation"></i>

                        <span>
                            This property is no longer available.
                        </span>

                    </div>


                <?php elseif ($message === "processed"): ?>

                    <div class="transaction-alert error">

                        <i class="fa-solid fa-circle-exclamation"></i>

                        <span>
                            This transaction has already been processed.
                        </span>

                    </div>


                <?php elseif ($message === "notfound"): ?>

                    <div class="transaction-alert error">

                        <i class="fa-solid fa-circle-exclamation"></i>

                        <span>
                            Transaction request was not found.
                        </span>

                    </div>


                <?php elseif ($message === "invalid"): ?>

                    <div class="transaction-alert error">

                        <i class="fa-solid fa-circle-exclamation"></i>

                        <span>
                            Invalid transaction request.
                        </span>

                    </div>


                <?php elseif ($message === "error"): ?>

                    <div class="transaction-alert error">

                        <i class="fa-solid fa-circle-exclamation"></i>

                        <span>
                            Something went wrong. Please try again.
                        </span>

                    </div>

                <?php endif; ?>

            </div>

        <?php endif; ?>


        <!-- =========================
             REQUESTS RECEIVED
        ========================== -->

        <section class="transactions-section">

            <div class="transactions-section-header">

                <h2>
                    Requests Received
                </h2>

                <p>
                    Requests from users interested
                    in your properties.
                </p>

            </div>


            <?php if (
                count($received_requests) > 0
            ): ?>

                <div class="transactions-table-wrapper">

                    <table class="transactions-table">

                        <thead>

                            <tr>

                                <th>Property</th>
                                <th>Requested By</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Timeline</th>
                                <th>Payment</th>
                                <th>Action</th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach (
                                $received_requests
                                as $transaction
                            ): ?>

                                <tr>


                                    <!-- PROPERTY -->

                                    <td>

                                        <a
                                            href="/myhome/properties/view-property.php?id=<?= (int) $transaction["property_id"] ?>"
                                            class="transaction-property-link"
                                        >

                                            <?= htmlspecialchars(
                                                $transaction["title"]
                                            ) ?>

                                        </a>


                                        <small>

                                            ₱<?= number_format(
                                                (float) $transaction["price"],
                                                2
                                            ) ?>

                                            <?php if (
                                                $transaction["listing_type"] === "rent"
                                            ): ?>

                                                / month

                                            <?php endif; ?>

                                        </small>

                                    </td>


                                    <!-- REQUESTER -->

                                    <td>

                                        <strong>

                                            <?= htmlspecialchars(
                                                $transaction["requester_name"]
                                            ) ?>

                                        </strong>

                                        <small>

                                            <?= htmlspecialchars(
                                                $transaction["requester_email"]
                                            ) ?>

                                        </small>

                                    </td>


                                    <!-- TYPE -->

                                    <td>

                                        <?php if (
                                            $transaction["transaction_type"] === "rent"
                                        ): ?>

                                            <span class="transaction-type rent">
                                                Rent
                                            </span>

                                        <?php else: ?>

                                            <span class="transaction-type sale">
                                                Buy
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- STATUS -->

                                    <td>

                                        <span
                                            class="transaction-status <?= htmlspecialchars(
                                                $transaction["status"]
                                            ) ?>"
                                        >

                                            <?= ucfirst(
                                                htmlspecialchars(
                                                    $transaction["status"]
                                                )
                                            ) ?>

                                        </span>

                                    </td>


                                    <!-- TIMELINE -->

                                    <td>

                                        <div class="transaction-timeline">


                                            <div class="transaction-timeline-item">

                                                <i class="fa-regular fa-clock"></i>

                                                <span>

                                                    <strong>
                                                        Requested:
                                                    </strong>

                                                    <?= date(
                                                        "M d, Y",
                                                        strtotime(
                                                            $transaction["created_at"]
                                                        )
                                                    ) ?>

                                                </span>

                                            </div>


                                            <?php if (
                                                !empty(
                                                    $transaction["accepted_at"]
                                                )
                                            ): ?>

                                                <div class="transaction-timeline-item">

                                                    <i class="fa-solid fa-check"></i>

                                                    <span>

                                                        <strong>
                                                            Accepted:
                                                        </strong>

                                                        <?= date(
                                                            "M d, Y",
                                                            strtotime(
                                                                $transaction["accepted_at"]
                                                            )
                                                        ) ?>

                                                    </span>

                                                </div>

                                            <?php endif; ?>


                                            <?php if (
                                                !empty(
                                                    $transaction["completed_at"]
                                                )
                                            ): ?>

                                                <div class="transaction-timeline-item">

                                                    <i class="fa-solid fa-flag-checkered"></i>

                                                    <span>

                                                        <strong>
                                                            Completed:
                                                        </strong>

                                                        <?= date(
                                                            "M d, Y",
                                                            strtotime(
                                                                $transaction["completed_at"]
                                                            )
                                                        ) ?>

                                                    </span>

                                                </div>

                                            <?php endif; ?>


                                        </div>

                                    </td>


                                    <!-- PAYMENT -->

                                    <td>

                                        <?php if (
                                            !empty($transaction["payment_id"]) &&
                                            $transaction["payment_status"] === "paid"
                                        ): ?>

                                            <span class="transaction-status accepted">

                                                <i
                                                    class="fa-solid fa-circle-check"
                                                    style="margin-right: 5px;"
                                                ></i>

                                                Paid

                                            </span>

                                            <?php if (
                                                !empty($transaction["payment_method"])
                                            ): ?>

                                                <small class="payment-method-text">

                                                    <?= ucfirst(
                                                        str_replace(
                                                            "_",
                                                            " ",
                                                            htmlspecialchars(
                                                                $transaction["payment_method"]
                                                            )
                                                        )
                                                    ) ?>

                                                </small>

                                            <?php endif; ?>

                                            <?php if (
                                                !empty($transaction["paid_at"])
                                            ): ?>

                                                <small>

                                                    <?= date(
                                                        "M d, Y",
                                                        strtotime(
                                                            $transaction["paid_at"]
                                                        )
                                                    ) ?>

                                                </small>

                                            <?php endif; ?>

                                            <a
                                                href="/myhome/receipt.php?payment_id=<?= (int) $transaction["payment_id"] ?>"
                                                class="transaction-receipt-btn"
                                            >
                                                <i class="fa-solid fa-receipt"></i>
                                                View Receipt
                                            </a>


                                        <?php elseif (
                                            $transaction["status"] === "accepted"
                                        ): ?>

                                            <span class="transaction-status pending">
                                                Not Paid
                                            </span>


                                        <?php else: ?>

                                            —

                                        <?php endif; ?>

                                    </td>


                                    <!-- ACTION -->

                                    <td>


                                        <?php if (
                                            $transaction["status"] === "pending"
                                        ): ?>


                                            <div class="transaction-actions">


                                                <!-- ACCEPT -->

                                                <form
                                                    action="/myhome/accept-transaction.php"
                                                    method="POST"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="transaction_id"
                                                        value="<?= (int) $transaction["id"] ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="transaction-accept-btn"
                                                        onclick="return confirm('Are you sure you want to accept this request?');"
                                                    >

                                                        <i class="fa-solid fa-check"></i>

                                                        Accept

                                                    </button>

                                                </form>


                                                <!-- REJECT -->

                                                <form
                                                    action="/myhome/reject-transaction.php"
                                                    method="POST"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="transaction_id"
                                                        value="<?= (int) $transaction["id"] ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="transaction-reject-btn"
                                                        onclick="return confirm('Are you sure you want to reject this request?');"
                                                    >

                                                        <i class="fa-solid fa-xmark"></i>

                                                        Reject

                                                    </button>

                                                </form>


                                            </div>


                                        <?php elseif (
                                            $transaction["status"] === "accepted" &&
                                            $transaction["transaction_type"] === "rent"
                                        ): ?>


                                            <!-- END RENTAL -->

                                            <div class="transaction-actions">

                                                <form
                                                    action="/myhome/end-rental.php"
                                                    method="POST"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="transaction_id"
                                                        value="<?= (int) $transaction["id"] ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="transaction-end-btn"
                                                        onclick="return confirm('Are you sure you want to end this rental? The property will become available again.');"
                                                    >

                                                        <i class="fa-solid fa-key"></i>

                                                        End Rental

                                                    </button>

                                                </form>

                                            </div>


                                        <?php else: ?>

                                            —

                                        <?php endif; ?>


                                    </td>


                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>


            <?php else: ?>


                <div class="transactions-empty">

                    <i class="fa-solid fa-inbox"></i>

                    <h3>
                        No requests received
                    </h3>

                    <p>
                        You do not have any transaction requests yet.
                    </p>

                </div>


            <?php endif; ?>

        </section>


        <!-- =========================
             MY REQUESTS
        ========================== -->

        <section class="transactions-section">

            <div class="transactions-section-header">

                <h2>
                    My Requests
                </h2>

                <p>
                    Properties you requested to rent or buy.
                </p>

            </div>


            <?php if (
                count($my_requests) > 0
            ): ?>


                <div class="transactions-table-wrapper">

                    <table class="transactions-table">

                        <thead>

                            <tr>

                                <th>Property</th>
                                <th>Owner</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Timeline</th>
                                <th>Payment</th>
                                <th>Action</th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach (
                                $my_requests
                                as $transaction
                            ): ?>

                                <tr>


                                    <!-- PROPERTY -->

                                    <td>

                                        <a
                                            href="/myhome/properties/view-property.php?id=<?= (int) $transaction["property_id"] ?>"
                                            class="transaction-property-link"
                                        >

                                            <?= htmlspecialchars(
                                                $transaction["title"]
                                            ) ?>

                                        </a>


                                        <small>

                                            ₱<?= number_format(
                                                (float) $transaction["price"],
                                                2
                                            ) ?>

                                            <?php if (
                                                $transaction["listing_type"] === "rent"
                                            ): ?>

                                                / month

                                            <?php endif; ?>

                                        </small>

                                    </td>


                                    <!-- OWNER -->

                                    <td>

                                        <strong>

                                            <?= htmlspecialchars(
                                                $transaction["owner_name"]
                                            ) ?>

                                        </strong>

                                        <small>

                                            <?= htmlspecialchars(
                                                $transaction["owner_email"]
                                            ) ?>

                                        </small>

                                    </td>


                                    <!-- TYPE -->

                                    <td>

                                        <?php if (
                                            $transaction["transaction_type"] === "rent"
                                        ): ?>

                                            <span class="transaction-type rent">
                                                Rent
                                            </span>

                                        <?php else: ?>

                                            <span class="transaction-type sale">
                                                Buy
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- STATUS -->

                                    <td>

                                        <span
                                            class="transaction-status <?= htmlspecialchars(
                                                $transaction["status"]
                                            ) ?>"
                                        >

                                            <?= ucfirst(
                                                htmlspecialchars(
                                                    $transaction["status"]
                                                )
                                            ) ?>

                                        </span>

                                    </td>


                                    <!-- TIMELINE -->

                                    <td>

                                        <div class="transaction-timeline">


                                            <div class="transaction-timeline-item">

                                                <i class="fa-regular fa-clock"></i>

                                                <span>

                                                    <strong>
                                                        Requested:
                                                    </strong>

                                                    <?= date(
                                                        "M d, Y",
                                                        strtotime(
                                                            $transaction["created_at"]
                                                        )
                                                    ) ?>

                                                </span>

                                            </div>


                                            <?php if (
                                                !empty(
                                                    $transaction["accepted_at"]
                                                )
                                            ): ?>

                                                <div class="transaction-timeline-item">

                                                    <i class="fa-solid fa-check"></i>

                                                    <span>

                                                        <strong>
                                                            Accepted:
                                                        </strong>

                                                        <?= date(
                                                            "M d, Y",
                                                            strtotime(
                                                                $transaction["accepted_at"]
                                                            )
                                                        ) ?>

                                                    </span>

                                                </div>

                                            <?php endif; ?>


                                            <?php if (
                                                !empty(
                                                    $transaction["completed_at"]
                                                )
                                            ): ?>

                                                <div class="transaction-timeline-item">

                                                    <i class="fa-solid fa-flag-checkered"></i>

                                                    <span>

                                                        <strong>
                                                            Completed:
                                                        </strong>

                                                        <?= date(
                                                            "M d, Y",
                                                            strtotime(
                                                                $transaction["completed_at"]
                                                            )
                                                        ) ?>

                                                    </span>

                                                </div>

                                            <?php endif; ?>


                                        </div>

                                    </td>


                                    <!-- PAYMENT -->

                                    <td>


                                        <?php if (
                                            $transaction["status"] === "accepted"
                                        ): ?>


                                            <?php if (
                                                empty(
                                                    $transaction["payment_id"]
                                                )
                                            ): ?>


                                                <a
                                                    href="/myhome/payment.php?transaction_id=<?= (int) $transaction["id"] ?>"
                                                    class="transaction-payment-btn"
                                                >

                                                    <i class="fa-solid fa-credit-card"></i>

                                                    Proceed to Payment

                                                </a>


                                            <?php elseif (
                                                $transaction["payment_status"] === "paid"
                                            ): ?>


                                                <span class="transaction-status accepted">

                                                    <i
                                                        class="fa-solid fa-circle-check"
                                                        style="margin-right: 5px;"
                                                    ></i>

                                                    Paid

                                                </span>


                                                <?php if (
                                                    !empty(
                                                        $transaction["payment_method"]
                                                    )
                                                ): ?>

                                                    <small class="payment-method-text">

                                                        <?= ucfirst(
                                                            str_replace(
                                                                "_",
                                                                " ",
                                                                htmlspecialchars(
                                                                    $transaction["payment_method"]
                                                                )
                                                            )
                                                        ) ?>

                                                    </small>

                                                <?php endif; ?>

                                                <a
                                                    href="/myhome/receipt.php?payment_id=<?= (int) $transaction["payment_id"] ?>"
                                                    class="transaction-receipt-btn"
                                                >
                                                    <i class="fa-solid fa-receipt"></i>
                                                    View Receipt
                                                </a>


                                            <?php else: ?>


                                                <span class="transaction-status pending">
                                                    Payment Pending
                                                </span>


                                            <?php endif; ?>


                                        <?php elseif (
                                            $transaction["status"] === "pending"
                                        ): ?>


                                            <small>
                                                Waiting for owner approval
                                            </small>


                                        <?php elseif (
                                            $transaction["status"] === "completed"
                                        ): ?>


                                            <?php if (
                                                $transaction["payment_status"] === "paid"
                                            ): ?>

                                                <span class="transaction-status accepted">

                                                    <i
                                                        class="fa-solid fa-circle-check"
                                                        style="margin-right: 5px;"
                                                    ></i>

                                                    Paid

                                                </span>


                                                <?php if (
                                                    !empty(
                                                        $transaction["payment_method"]
                                                    )
                                                ): ?>

                                                    <small class="payment-method-text">

                                                        <?= ucfirst(
                                                            str_replace(
                                                                "_",
                                                                " ",
                                                                htmlspecialchars(
                                                                    $transaction["payment_method"]
                                                                )
                                                            )
                                                        ) ?>

                                                    </small>

                                                <?php endif; ?>

                                                <a
                                                    href="/myhome/receipt.php?payment_id=<?= (int) $transaction["payment_id"] ?>"
                                                    class="transaction-receipt-btn"
                                                >
                                                    <i class="fa-solid fa-receipt"></i>
                                                    View Receipt
                                                </a>


                                            <?php else: ?>

                                                —

                                            <?php endif; ?>


                                        <?php else: ?>

                                            —

                                        <?php endif; ?>


                                    </td>


                                    <!-- ACTION -->

                                    <td>

                                        <?php if (
                                            $transaction["status"] === "pending"
                                        ): ?>

                                            <form
                                                action="/myhome/cancel-transaction.php"
                                                method="POST"
                                                onsubmit="return confirm('Are you sure you want to cancel this request?');"
                                            >
                                                <input
                                                    type="hidden"
                                                    name="transaction_id"
                                                    value="<?= (int) $transaction["id"] ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="transaction-cancel-btn"
                                                >
                                                    <i class="fa-solid fa-xmark"></i>
                                                    Cancel Request
                                                </button>
                                            </form>

                                        <?php else: ?>

                                            —

                                        <?php endif; ?>

                                    </td>


                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>


            <?php else: ?>


                <div class="transactions-empty">

                    <i class="fa-solid fa-house"></i>

                    <h3>
                        No requests yet
                    </h3>

                    <p>
                        You have not requested any property yet.
                    </p>

                </div>


            <?php endif; ?>

        </section>


    </div>

</main>


<?php

include "includes/footer.php";

?>
<?php

session_start();

$pdo = require "../config/database.php";

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

try {

    $stmt = $pdo->query(
        "SELECT
            transactions.id,
            transactions.property_id,
            transactions.transaction_type,
            transactions.status,
            transactions.created_at,
            transactions.accepted_at,
            transactions.completed_at,

            properties.title AS property_title,
            properties.price,
            properties.listing_type,

            requester.full_name AS requester_name,
            requester.email AS requester_email,

            owner.full_name AS owner_name,
            owner.email AS owner_email,

            payments.id AS payment_id,
            payments.amount AS payment_amount,
            payments.payment_method,
            payments.status AS payment_status,
            payments.paid_at

         FROM transactions

         INNER JOIN properties
            ON transactions.property_id = properties.id

         INNER JOIN users AS requester
            ON transactions.buyer_renter_id = requester.id

         INNER JOIN users AS owner
            ON transactions.owner_id = owner.id

         LEFT JOIN payments
            ON transactions.id = payments.transaction_id
            AND payments.payer_id = transactions.buyer_renter_id

         ORDER BY transactions.created_at DESC"
    );

    $transactions = $stmt->fetchAll();

} catch (PDOException $e) {

    $transactions = [];
}

$total_transactions = count($transactions);

$pending_count = 0;
$accepted_count = 0;
$completed_count = 0;
$paid_count = 0;

foreach ($transactions as $transaction) {

    if ($transaction["status"] === "pending") {
        $pending_count++;
    }

    if ($transaction["status"] === "accepted") {
        $accepted_count++;
    }

    if ($transaction["status"] === "completed") {
        $completed_count++;
    }

    if ($transaction["payment_status"] === "paid") {
        $paid_count++;
    }
}

$page_title = "Transactions | MyHome Admin";

include "../includes/header.php";

?>

<style>

.admin-transaction-stats {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 18px;
}

.admin-transaction-stat {
    padding: 14px 16px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(12, 48, 51, 0.06);
}

.admin-transaction-stat i {
    margin-bottom: 8px;
    color: #00605e;
    font-size: 16px;
}

.admin-transaction-stat strong {
    display: block;
    margin-bottom: 3px;
    color: #0c3033;
    font-family: "Barlow Condensed", sans-serif;
    font-size: 22px;
}

.admin-transaction-stat span {
    color: #7b8b8d;
    font-size: 11px;
}

.admin-transactions-card {
    padding: 18px;
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 8px 24px rgba(12, 48, 51, 0.06);
}

.admin-transactions-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 22px;
}

.admin-transactions-card-header h2 {
    margin: 0 0 4px;
    color: #0c3033;
    font-family: "Barlow Condensed", sans-serif;
    font-size: 23px;
}

.admin-transactions-card-header p {
    margin: 0;
    color: #7b8b8d;
    font-size: 12px;
}

.admin-transactions-table-wrapper {
    width: 100%;
    overflow-x: auto;
}

.admin-transactions-table {
    width: 100%;
    min-width: 1080px;
    border-collapse: collapse;
}

.admin-transactions-table th {
    padding: 10px 10px;
    background: #f5f8f8;
    border-bottom: 1px solid #dfe9e8;
    color: #708180;
    font-size: 10px;
    font-weight: 700;
    text-align: left;
    text-transform: uppercase;
}

.admin-transactions-table td {
    padding: 11px 10px;
    border-bottom: 1px solid #edf2f2;
    color: #0c3033;
    font-size: 12px;
    vertical-align: middle;
}

.admin-transactions-table tbody tr:last-child td {
    border-bottom: none;
}

.admin-transactions-table tbody tr:hover {
    background: #fafcfc;
}

.admin-transaction-property {
    color: #00605e;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
}

.admin-transaction-property:hover {
    color: #008080;
}

.admin-transaction-person strong {
    display: block;
    margin-bottom: 2px;
    font-size: 12px;
}

.admin-transaction-person small,
.admin-transaction-date small,
.admin-transaction-payment small,
.admin-transactions-table td small {
    display: block;
    margin-top: 3px;
    color: #82908f;
    font-size: 10px;
}

.admin-transaction-type,
.admin-transaction-status,
.admin-payment-status {
    display: inline-flex;
    align-items: center;
    padding: 6px 10px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 700;
}

.admin-transaction-type.rent {
    background: #e7f1ff;
    color: #2864a8;
}

.admin-transaction-type.sale {
    background: #e8f6ef;
    color: #17834f;
}

.admin-transaction-status.pending {
    background: #fff5d6;
    color: #9a7100;
}

.admin-transaction-status.accepted {
    background: #e8f6ef;
    color: #17834f;
}

.admin-transaction-status.rejected {
    background: #ffeaea;
    color: #b83d3d;
}

.admin-transaction-status.cancelled {
    background: #eeeeee;
    color: #666666;
}

.admin-transaction-status.completed {
    background: #e8f1ff;
    color: #295f9b;
}

.admin-payment-status.paid {
    background: #e8f6ef;
    color: #17834f;
}

.admin-payment-status.unpaid {
    background: #fff5d6;
    color: #9a7100;
}

.admin-receipt-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    margin-top: 7px;
    padding: 6px 10px;
    border: 1px solid #cfdada;
    border-radius: 999px;
    background: #ffffff;
    color: #00605e;
    font-size: 9px;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
    transition: 0.2s ease;
}

.admin-receipt-btn:hover {
    background: #eef7f6;
    border-color: #00605e;
    transform: translateY(-1px);
}

.admin-transactions-empty {
    padding: 60px 20px;
    text-align: center;
    color: #7b8b8d;
}

.admin-transactions-empty i {
    margin-bottom: 12px;
    color: #80a7a5;
    font-size: 38px;
}

.admin-transactions-empty h3 {
    margin: 0 0 5px;
    color: #0c3033;
    font-family: "Barlow Condensed", sans-serif;
    font-size: 23px;
}

.admin-transactions-empty p {
    margin: 0;
    font-size: 11px;
}

@media (max-width: 1050px) {
    .admin-transaction-stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 650px) {
    .admin-transaction-stats {
        grid-template-columns: 1fr;
    }

    .admin-transactions-card {
        padding: 18px;
    }
}


.admin-content {
    padding-top: 28px !important;
}

.admin-header {
    margin-bottom: 20px !important;
}

.admin-header h1 {
    font-size: 34px !important;
}

.admin-header p {
    font-size: 11px !important;
}

.admin-transactions-table th {
    font-size: 9px;
}

.admin-transactions-table td {
    font-size: 10px;
}

.admin-transaction-person strong,
.admin-transaction-property {
    font-size: 10px;
}

.admin-transaction-person small,
.admin-transaction-date small,
.admin-transaction-payment small,
.admin-transactions-table td small {
    font-size: 9px;
}

.admin-transaction-type,
.admin-transaction-status,
.admin-payment-status {
    padding: 5px 8px;
    font-size: 9px;
}

</style>

<main class="admin-page">

    <div class="admin-layout">

        <?php include "../includes/admin-sidebar.php"; ?>

        <section class="admin-content">

            <div class="admin-header">

                <div>

                    <p class="section-label">
                        TRANSACTION MONITORING
                    </p>

                    <h1>
                        Transactions
                    </h1>

                    <p>
                        Monitor all property rent and sale transactions on MyHome.
                    </p>

                </div>

                <div class="admin-user">

                    <i class="fa-solid fa-circle-user"></i>

                    <span>
                        <?= htmlspecialchars($_SESSION["full_name"] ?? "Admin") ?>
                    </span>

                </div>

            </div>

            <div class="admin-transaction-stats">

                <div class="admin-transaction-stat">
                    <i class="fa-solid fa-right-left"></i>
                    <strong><?= $total_transactions ?></strong>
                    <span>Total Transactions</span>
                </div>

                <div class="admin-transaction-stat">
                    <i class="fa-regular fa-clock"></i>
                    <strong><?= $pending_count ?></strong>
                    <span>Pending Requests</span>
                </div>

                <div class="admin-transaction-stat">
                    <i class="fa-solid fa-circle-check"></i>
                    <strong><?= $accepted_count + $completed_count ?></strong>
                    <span>Accepted / Completed</span>
                </div>

                <div class="admin-transaction-stat">
                    <i class="fa-solid fa-credit-card"></i>
                    <strong><?= $paid_count ?></strong>
                    <span>Paid Transactions</span>
                </div>

            </div>

            <section class="admin-transactions-card">

                <div class="admin-transactions-card-header">

                    <div>

                        <h2>
                            All Transactions
                        </h2>

                        <p>
                            <?= $total_transactions ?>
                            <?= $total_transactions === 1
                                ? "transaction found."
                                : "transactions found." ?>
                        </p>

                    </div>

                </div>

                <?php if ($total_transactions > 0): ?>

                    <div class="admin-transactions-table-wrapper">

                        <table class="admin-transactions-table">

                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Property</th>
                                    <th>Buyer / Renter</th>
                                    <th>Owner</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Amount</th>
                                    <th>Requested</th>
                                    <th>Accepted</th>
                                    <th>Completed</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php foreach ($transactions as $transaction): ?>

                                    <tr>

                                        <td>
                                            #<?= (int) $transaction["id"] ?>
                                        </td>

                                        <td>

                                            <a
                                                href="/myhome/properties/view-property.php?id=<?= (int) $transaction["property_id"] ?>"
                                                class="admin-transaction-property"
                                            >
                                                <?= htmlspecialchars($transaction["property_title"]) ?>
                                            </a>

                                            <small>

                                                ₱<?= number_format((float) $transaction["price"], 2) ?>

                                                <?php if ($transaction["listing_type"] === "rent"): ?>
                                                    / month
                                                <?php endif; ?>

                                            </small>

                                        </td>

                                        <td class="admin-transaction-person">

                                            <strong>
                                                <?= htmlspecialchars($transaction["requester_name"]) ?>
                                            </strong>

                                            <small>
                                                <?= htmlspecialchars($transaction["requester_email"]) ?>
                                            </small>

                                        </td>

                                        <td class="admin-transaction-person">

                                            <strong>
                                                <?= htmlspecialchars($transaction["owner_name"]) ?>
                                            </strong>

                                            <small>
                                                <?= htmlspecialchars($transaction["owner_email"]) ?>
                                            </small>

                                        </td>

                                        <td>

                                            <?php if ($transaction["transaction_type"] === "rent"): ?>

                                                <span class="admin-transaction-type rent">
                                                    Rent
                                                </span>

                                            <?php else: ?>

                                                <span class="admin-transaction-type sale">
                                                    Buy
                                                </span>

                                            <?php endif; ?>

                                        </td>

                                        <td>

                                            <span class="admin-transaction-status <?= htmlspecialchars($transaction["status"]) ?>">
                                                <?= ucfirst(htmlspecialchars($transaction["status"])) ?>
                                            </span>

                                        </td>

                                        <td class="admin-transaction-payment">

                                            <?php if ($transaction["payment_status"] === "paid"): ?>

                                                <span class="admin-payment-status paid">
                                                    <i
                                                        class="fa-solid fa-circle-check"
                                                        style="margin-right:5px;"
                                                    ></i>
                                                    Paid
                                                </span>

                                                <?php if (!empty($transaction["payment_method"])): ?>

                                                    <small>
                                                        <?= ucwords(
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

                                                <?php if (!empty($transaction["paid_at"])): ?>

                                                    <small>
                                                        <?= date(
                                                            "M d, Y",
                                                            strtotime($transaction["paid_at"])
                                                        ) ?>
                                                    </small>

                                                <?php endif; ?>

                                                <a
                                                    href="/myhome/admin/receipt.php?payment_id=<?= (int) $transaction["payment_id"] ?>"
                                                    class="admin-receipt-btn"
                                                >
                                                    <i class="fa-solid fa-receipt"></i>
                                                    View Receipt
                                                </a>

                                            <?php elseif ($transaction["status"] === "accepted"): ?>

                                                <span class="admin-payment-status unpaid">
                                                    Not Paid
                                                </span>

                                            <?php else: ?>

                                                —

                                            <?php endif; ?>

                                        </td>

                                        <td>

                                            <?php if (
                                                $transaction["payment_status"] === "paid" &&
                                                $transaction["payment_amount"] !== null
                                            ): ?>

                                                <strong>
                                                    ₱<?= number_format(
                                                        (float) $transaction["payment_amount"],
                                                        2
                                                    ) ?>
                                                </strong>

                                            <?php else: ?>

                                                —

                                            <?php endif; ?>

                                        </td>

                                        <td class="admin-transaction-date">

                                            <?= date(
                                                "M d, Y",
                                                strtotime($transaction["created_at"])
                                            ) ?>

                                            <small>
                                                <?= date(
                                                    "h:i A",
                                                    strtotime($transaction["created_at"])
                                                ) ?>
                                            </small>

                                        </td>

                                        <td class="admin-transaction-date">

                                            <?php if (!empty($transaction["accepted_at"])): ?>

                                                <?= date(
                                                    "M d, Y",
                                                    strtotime($transaction["accepted_at"])
                                                ) ?>

                                            <?php else: ?>

                                                —

                                            <?php endif; ?>

                                        </td>

                                        <td class="admin-transaction-date">

                                            <?php if (!empty($transaction["completed_at"])): ?>

                                                <?= date(
                                                    "M d, Y",
                                                    strtotime($transaction["completed_at"])
                                                ) ?>

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

                    <div class="admin-transactions-empty">

                        <i class="fa-solid fa-right-left"></i>

                        <h3>
                            No transactions yet
                        </h3>

                        <p>
                            Property transactions will appear here once users start sending requests.
                        </p>

                    </div>

                <?php endif; ?>

            </section>

        </section>

    </div>

</main>

<?php

include "../includes/footer.php";

?>


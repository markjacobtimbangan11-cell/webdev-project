<?php

session_start();

$pdo = require "../config/database.php";


// =========================================
// ADMIN ACCESS ONLY
// =========================================

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


// =========================================
// GET ALL REVIEWS
// =========================================

try {

    $stmt = $pdo->prepare(
        "SELECT
            r.id,
            r.rating,
            r.review,
            r.status,
            r.created_at,
            u.full_name,
            u.email
         FROM reviews r
         INNER JOIN users u
            ON u.id = r.user_id
         ORDER BY
            CASE
                WHEN r.status = 'pending' THEN 1
                WHEN r.status = 'approved' THEN 2
                ELSE 3
            END,
            r.created_at DESC"
    );


    $stmt->execute();


    $reviews =
        $stmt->fetchAll();


    $total_reviews =
        count($reviews);

}

catch (PDOException $e) {

    $reviews = [];

    $total_reviews = 0;
}


// =========================================
// PAGE TITLE
// =========================================

$page_title =
    "Reviews | MyHome Admin";


include "../includes/header.php";

?>


<main class="admin-page">

    <div class="admin-layout">


        <!-- =================================
             SIDEBAR
        ================================== -->

        <?php
        include "../includes/admin-sidebar.php";
        ?>


        <!-- =================================
             CONTENT
        ================================== -->

        <section class="admin-content">


            <!-- =================================
                 HEADER
            ================================== -->

            <div class="admin-header">

                <div>

                    <p class="section-label">
                        REVIEW MANAGEMENT
                    </p>

                    <h1>
                        Reviews
                    </h1>

                    <p>
                        Approve or remove user reviews
                        submitted to MyHome.
                    </p>

                </div>


                <div class="admin-user">

                    <i class="fa-solid fa-circle-user"></i>

                    <span>

                        <?php
                        echo htmlspecialchars(
                            $_SESSION["full_name"]
                        );
                        ?>

                    </span>

                </div>

            </div>


            <!-- =================================
                 SUCCESS MESSAGE
            ================================== -->

            <?php if (isset($_GET["success"])): ?>

                <div class="admin-alert success">

                    <i class="fa-solid fa-circle-check"></i>

                    <?php if (
                        $_GET["success"] === "approved"
                    ): ?>

                        Review approved successfully.

                    <?php elseif (
                        $_GET["success"] === "rejected"
                    ): ?>

                        Review rejected successfully.

                    <?php elseif (
                        $_GET["success"] === "deleted"
                    ): ?>

                        Review deleted successfully.

                    <?php endif; ?>

                </div>

            <?php endif; ?>


            <!-- =================================
                 ERROR MESSAGE
            ================================== -->

            <?php if (isset($_GET["error"])): ?>

                <div class="admin-alert error">

                    <i class="fa-solid fa-circle-exclamation"></i>

                    Something went wrong.
                    Please try again.

                </div>

            <?php endif; ?>


            <!-- =================================
                 REVIEWS PANEL
            ================================== -->

            <div class="admin-panel admin-reviews-panel">


                <div class="admin-panel-heading">

                    <div>

                        <h2>
                            All Reviews
                        </h2>

                        <p>

                            <?php
                            echo $total_reviews;
                            ?>

                            <?php
                            echo $total_reviews === 1
                                ? "review found."
                                : "reviews found.";
                            ?>

                        </p>

                    </div>

                </div>


                <!-- =================================
                     REVIEW LIST
                ================================== -->

                <?php if ($total_reviews > 0): ?>


                    <div class="admin-reviews-list">


                        <?php foreach ($reviews as $review): ?>


                            <div class="admin-review-item">


                                <!-- USER -->

                                <div class="admin-review-user">

                                    <div class="admin-review-avatar">

                                        <i class="fa-solid fa-user"></i>

                                    </div>


                                    <div>

                                        <strong>

                                            <?php
                                            echo htmlspecialchars(
                                                $review["full_name"]
                                            );
                                            ?>

                                        </strong>

                                        <span>

                                            <?php
                                            echo htmlspecialchars(
                                                $review["email"]
                                            );
                                            ?>

                                        </span>

                                    </div>

                                </div>


                                <!-- =================================
                                     STARS
                                ================================== -->

                                <div class="admin-review-rating">

                                    <?php
                                    for (
                                        $i = 1;
                                        $i <= 5;
                                        $i++
                                    ):
                                    ?>

                                        <i
                                            class="<?php
                                            echo $i <= (int) $review["rating"]
                                                ? "fa-solid fa-star"
                                                : "fa-regular fa-star";
                                            ?>"
                                        ></i>

                                    <?php endfor; ?>

                                    <span>

                                        <?php
                                        echo (int) $review["rating"];
                                        ?>/5

                                    </span>

                                </div>


                                <!-- =================================
                                     REVIEW MESSAGE
                                ================================== -->

                                <div class="admin-review-message">

                                    <?php
                                    echo nl2br(
                                        htmlspecialchars(
                                            $review["review"]
                                        )
                                    );
                                    ?>

                                </div>


                                <!-- =================================
                                     META
                                ================================== -->

                                <div class="admin-review-meta">

                                    <span
                                        class="
                                            admin-review-status
                                            <?php
                                            echo htmlspecialchars(
                                                $review["status"]
                                            );
                                            ?>
                                        "
                                    >

                                        <?php
                                        echo ucfirst(
                                            htmlspecialchars(
                                                $review["status"]
                                            )
                                        );
                                        ?>

                                    </span>


                                    <span class="admin-review-date">

                                        <i class="fa-regular fa-clock"></i>

                                        <?php
                                        echo date(
                                            "M d, Y",
                                            strtotime(
                                                $review["created_at"]
                                            )
                                        );
                                        ?>

                                    </span>

                                </div>


                                <!-- =================================
                                     ACTIONS
                                ================================== -->

                                <div class="admin-review-actions">


                                    <?php if (
                                        $review["status"] === "pending"
                                    ): ?>


                                        <!-- APPROVE -->

                                        <form
                                            action="/myhome/admin/review-action.php"
                                            method="POST"
                                        >

                                            <input
                                                type="hidden"
                                                name="review_id"
                                                value="<?php
                                                echo (int) $review["id"];
                                                ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="action"
                                                value="approve"
                                                class="review-action-btn approve"
                                            >

                                                <i class="fa-solid fa-check"></i>

                                                Approve

                                            </button>

                                        </form>


                                        <!-- REJECT -->

                                        <form
                                            action="/myhome/admin/review-action.php"
                                            method="POST"
                                        >

                                            <input
                                                type="hidden"
                                                name="review_id"
                                                value="<?php
                                                echo (int) $review["id"];
                                                ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="action"
                                                value="reject"
                                                class="review-action-btn reject"
                                            >

                                                <i class="fa-solid fa-xmark"></i>

                                                Reject

                                            </button>

                                        </form>


                                    <?php endif; ?>


                                    <!-- DELETE -->

                                    <form
                                        action="/myhome/admin/review-action.php"
                                        method="POST"
                                        onsubmit="return confirm('Delete this review permanently?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="review_id"
                                            value="<?php
                                            echo (int) $review["id"];
                                            ?>"
                                        >

                                        <button
                                            type="submit"
                                            name="action"
                                            value="delete"
                                            class="review-action-btn delete"
                                        >

                                            <i class="fa-solid fa-trash"></i>

                                            Delete

                                        </button>

                                    </form>


                                </div>


                            </div>


                        <?php endforeach; ?>


                    </div>


                <?php else: ?>


                    <!-- =================================
                         EMPTY STATE
                    ================================== -->

                    <div class="admin-empty-state">

                        <i class="fa-regular fa-star"></i>

                        <h3>
                            No reviews found
                        </h3>

                        <p>
                            User reviews will appear here.
                        </p>

                    </div>


                <?php endif; ?>


            </div>


        </section>


    </div>

</main>
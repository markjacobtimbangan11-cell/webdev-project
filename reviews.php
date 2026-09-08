<?php

session_start();

$pdo = require "config/database.php";


// =========================================
// NORMAL USER ACCESS ONLY
// =========================================

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


$user_id =
    (int) $_SESSION["user_id"];

$message = "";
$message_type = "";

$rating = 0;
$review = "";


// =========================================
// SUBMIT REVIEW
// =========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $rating =
        isset($_POST["rating"])
            ? (int) $_POST["rating"]
            : 0;

    $review =
        trim($_POST["review"] ?? "");


    // =====================================
    // VALIDATE RATING
    // =====================================

    if (
        $rating < 1 ||
        $rating > 5
    ) {

        $message =
            "Please select a rating from 1 to 5 stars.";

        $message_type =
            "error";

    }


    // =====================================
    // VALIDATE REVIEW
    // =====================================

    elseif ($review === "") {

        $message =
            "Please enter your review.";

        $message_type =
            "error";

    }

    elseif (strlen($review) > 500) {

        $message =
            "Review must not exceed 500 characters.";

        $message_type =
            "error";

    }

    else {

        try {


            // =================================
            // CHECK IF USER ALREADY HAS
            // A PENDING REVIEW
            // =================================

            $stmt = $pdo->prepare(
                "SELECT id
                 FROM reviews
                 WHERE user_id = :user_id
                 AND status = 'pending'
                 LIMIT 1"
            );


            $stmt->execute([
                "user_id" => $user_id
            ]);


            $existing =
                $stmt->fetch();


            if ($existing) {

                $message =
                    "You already have a review waiting for approval.";

                $message_type =
                    "error";

            }

            else {


                // =================================
                // INSERT REVIEW
                // =================================

                $stmt = $pdo->prepare(
                    "INSERT INTO reviews
                    (
                        user_id,
                        rating,
                        review,
                        status
                    )
                    VALUES
                    (
                        :user_id,
                        :rating,
                        :review,
                        'pending'
                    )"
                );


                $stmt->execute([
                    "user_id" =>
                        $user_id,

                    "rating" =>
                        $rating,

                    "review" =>
                        $review
                ]);


                $message =
                    "Thank you! Your review has been submitted for approval.";

                $message_type =
                    "success";


                // Clear form after successful submission

                $rating = 0;
                $review = "";
            }

        }

        catch (PDOException $e) {

            $message =
                "Something went wrong. Please try again.";

            $message_type =
                "error";
        }
    }
}


// =========================================
// PAGE TITLE
// =========================================

$page_title =
    "Leave a Review | MyHome";


include "includes/header.php";

include "includes/navbar.php";

?>


<main class="review-page">

    <section class="review-container">


        <div class="review-heading">

            <p class="section-label">
                SHARE YOUR EXPERIENCE
            </p>

            <h1>
                Leave a Review
            </h1>

            <p>
                Tell us about your experience using MyHome.
            </p>

        </div>


        <div class="review-card">


            <?php if ($message !== ""): ?>

                <div
                    class="<?php
                    echo $message_type === "success"
                        ? "review-alert success"
                        : "review-alert error";
                    ?>"
                >

                    <?php
                    echo htmlspecialchars(
                        $message
                    );
                    ?>

                </div>

            <?php endif; ?>


            <form
                action="/myhome/reviews.php"
                method="POST"
                class="review-form"
            >


                <!-- =========================
                     RATING
                ========================== -->

                <div class="review-field">

                    <label>
                        Your Rating
                    </label>


                    <div class="star-rating">


                        <input
                            type="radio"
                            name="rating"
                            id="star5"
                            value="5"
                            <?php
                            echo $rating === 5
                                ? "checked"
                                : "";
                            ?>
                        >

                        <label for="star5">
                            ★
                        </label>


                        <input
                            type="radio"
                            name="rating"
                            id="star4"
                            value="4"
                            <?php
                            echo $rating === 4
                                ? "checked"
                                : "";
                            ?>
                        >

                        <label for="star4">
                            ★
                        </label>


                        <input
                            type="radio"
                            name="rating"
                            id="star3"
                            value="3"
                            <?php
                            echo $rating === 3
                                ? "checked"
                                : "";
                            ?>
                        >

                        <label for="star3">
                            ★
                        </label>


                        <input
                            type="radio"
                            name="rating"
                            id="star2"
                            value="2"
                            <?php
                            echo $rating === 2
                                ? "checked"
                                : "";
                            ?>
                        >

                        <label for="star2">
                            ★
                        </label>


                        <input
                            type="radio"
                            name="rating"
                            id="star1"
                            value="1"
                            <?php
                            echo $rating === 1
                                ? "checked"
                                : "";
                            ?>
                        >

                        <label for="star1">
                            ★
                        </label>


                    </div>

                </div>


                <!-- =========================
                     REVIEW
                ========================== -->

                <div class="review-field">

                    <label for="review">
                        Your Review
                    </label>


                    <textarea
                        name="review"
                        id="review"
                        maxlength="500"
                        rows="6"
                        placeholder="Share your experience with MyHome..."
                        required
                    ><?php
                    echo htmlspecialchars(
                        $review
                    );
                    ?></textarea>


                    <small>
                        Maximum of 500 characters.
                    </small>

                </div>


                <!-- =========================
                     BUTTONS
                ========================== -->

                <div class="review-form-actions">


                    <button
                        type="submit"
                        class="review-submit"
                    >

                        Submit Review

                        <i class="fa-solid fa-arrow-right"></i>

                    </button>


                    <a
                        href="/myhome/index.php"
                        class="review-home-btn"
                    >

                        <i class="fa-solid fa-house"></i>

                        Back to Home

                    </a>


                </div>


            </form>


        </div>


    </section>

</main>


<?php

include "includes/footer.php";

?>
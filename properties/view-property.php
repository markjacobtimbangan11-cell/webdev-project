<?php

session_start();

$pdo = require "../config/database.php";


// =========================================
// PROPERTY ID
// =========================================

$property_id =
    isset($_GET["id"])
        ? (int) $_GET["id"]
        : 0;


if ($property_id <= 0) {

    header(
        "Location: /myhome/properties/listings.php"
    );

    exit;
}


// =========================================
// GET PROPERTY
// =========================================

try {

    $stmt = $pdo->prepare(
        "SELECT
            p.*,
            u.full_name AS owner_name,

            (
                SELECT pi.image_path
                FROM property_images pi
                WHERE pi.property_id = p.id
                ORDER BY pi.id ASC
                LIMIT 1
            ) AS main_image

         FROM properties p

         INNER JOIN users u
            ON p.user_id = u.id

         WHERE p.id = :property_id

         LIMIT 1"
    );


    $stmt->execute([
        "property_id" => $property_id
    ]);


    $property =
        $stmt->fetch();


    if (!$property) {

        header(
            "Location: /myhome/properties/listings.php"
        );

        exit;
    }

}

catch (PDOException $e) {

    header(
        "Location: /myhome/properties/listings.php"
    );

    exit;
}


// =========================================
// CHECK IF PROPERTY IS FAVORITED
// =========================================

$is_favorite = false;

if (
    isset($_SESSION["user_id"]) &&
    isset($_SESSION["role"]) &&
    $_SESSION["role"] === "user" &&
    (int) $_SESSION["user_id"] !==
        (int) $property["user_id"]
) {

    try {

        $favorite_stmt = $pdo->prepare(
            "SELECT id
             FROM favorites
             WHERE user_id = :user_id
             AND property_id = :property_id
             LIMIT 1"
        );


        $favorite_stmt->execute([
            "user_id" =>
                (int) $_SESSION["user_id"],

            "property_id" =>
                $property_id
        ]);


        $is_favorite =
            (bool) $favorite_stmt->fetch();

    }

    catch (PDOException $e) {

        $is_favorite = false;
    }
}


// =========================================
// OWNER CHECK
// =========================================

$is_owner =
    isset($_SESSION["user_id"]) &&
    (int) $_SESSION["user_id"] ===
    (int) $property["user_id"];


// =========================================
// PAGE TITLE
// =========================================

$page_title =
    $property["title"] . " | MyHome";


include "../includes/header.php";

include "../includes/navbar.php";

?>


<main class="view-property-page">


    <?php if (
        isset($_GET["inquiry_sent"]) &&
        $_GET["inquiry_sent"] === "1"
    ): ?>

        <div class="inquiry-success-message">

            <i class="fa-solid fa-circle-check"></i>

            <span>
                Your inquiry has been sent successfully.
            </span>

        </div>

    <?php endif; ?>


    <section class="view-property-container">


        <a
            href="/myhome/properties/listings.php"
            class="property-back-link"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Back to Listings

        </a>


        <div class="view-property-layout">


            <!-- =========================
                 LEFT SIDE
            ========================== -->

            <div class="view-property-main">


                <!-- IMAGE -->

                <?php if (
                    !empty($property["main_image"])
                ): ?>

                    <div class="view-property-image">

                        <img
                            src="/myhome/<?php
                            echo htmlspecialchars(
                                $property["main_image"]
                            );
                            ?>"
                            alt="<?php
                            echo htmlspecialchars(
                                $property["title"]
                            );
                            ?>"
                        >

                    </div>

                <?php else: ?>

                    <div class="view-property-image no-image">

                        <i class="fa-solid fa-house"></i>

                    </div>

                <?php endif; ?>


                <!-- TITLE AREA -->

                <div class="view-property-heading">

                    <div>


                        <div class="view-property-badges">


                            <span
                                class="listing-type-badge <?php
                                echo htmlspecialchars(
                                    $property["listing_type"]
                                );
                                ?>"
                            >

                                <?php
                                echo strtoupper(
                                    htmlspecialchars(
                                        $property["listing_type"]
                                    )
                                );
                                ?>

                            </span>


                            <span class="view-property-status">

                                <?php
                                echo ucfirst(
                                    htmlspecialchars(
                                        $property["status"]
                                    )
                                );
                                ?>

                            </span>


                        </div>


                        <h1>

                            <?php
                            echo htmlspecialchars(
                                $property["title"]
                            );
                            ?>

                        </h1>


                        <p class="view-property-location">

                            <i class="fa-solid fa-location-dot"></i>

                            <?php
                            echo htmlspecialchars(
                                $property["location"]
                            );
                            ?>

                        </p>


                    </div>


                    <div class="view-property-price">

                        ₱<?php
                        echo number_format(
                            (float) $property["price"],
                            2
                        );
                        ?>


                        <?php if (
                            $property["listing_type"] === "rent"
                        ): ?>

                            <small>
                                / month
                            </small>

                        <?php endif; ?>

                    </div>


                </div>


                <!-- DETAILS -->

                <div class="view-property-details">


                    <div>

                        <i class="fa-solid fa-bed"></i>

                        <span>
                            <?php
                            echo (int) $property["bedrooms"];
                            ?>
                        </span>

                        <small>
                            Bedrooms
                        </small>

                    </div>


                    <div>

                        <i class="fa-solid fa-bath"></i>

                        <span>
                            <?php
                            echo (int) $property["bathrooms"];
                            ?>
                        </span>

                        <small>
                            Bathrooms
                        </small>

                    </div>


                    <div>

                        <i class="fa-solid fa-ruler-combined"></i>

                        <span>
                            <?php
                            echo htmlspecialchars(
                                $property["area"]
                            );
                            ?>
                        </span>

                        <small>
                            m² Floor Area
                        </small>

                    </div>


                    <div>

                        <i class="fa-solid fa-house"></i>

                        <span>
                            <?php
                            echo htmlspecialchars(
                                $property["property_type"]
                            );
                            ?>
                        </span>

                        <small>
                            Property Type
                        </small>

                    </div>


                </div>


                <!-- DESCRIPTION -->

                <div class="view-property-section">

                    <h2>
                        Property Description
                    </h2>

                    <p>

                        <?php
                        echo nl2br(
                            htmlspecialchars(
                                $property["description"]
                            )
                        );
                        ?>

                    </p>

                </div>


            </div>


            <!-- =========================
                 RIGHT SIDE
            ========================== -->

            <aside class="property-contact-card">


                <p class="section-label">
                    INTERESTED?
                </p>


                <h2>
                    Contact About This Property
                </h2>


                <!-- OWNER -->

                <div class="property-owner">

                    <div class="property-owner-icon">

                        <i class="fa-solid fa-user"></i>

                    </div>


                    <div>

                        <small>
                            Listed by
                        </small>

                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $property["owner_name"]
                            );
                            ?>

                        </strong>

                    </div>

                </div>


                <!-- =========================
                     LOGGED IN
                ========================== -->

                <?php if (
                    isset($_SESSION["user_id"])
                ): ?>


                    <?php if (
                        isset($_SESSION["role"]) &&
                        $_SESSION["role"] === "user"
                    ): ?>


                        <!-- =========================
                             OWNER
                        ========================== -->

                        <?php if ($is_owner): ?>


                            <div class="own-property-notice">

                                <i class="fa-solid fa-house-user"></i>

                                <span>
                                    This is your property listing.
                                </span>

                            </div>


                            <?php if (
                                $property["status"] === "sold"
                            ): ?>

                                <div
                                    class="own-property-notice"
                                    style="margin-top: 12px;"
                                >

                                    <i class="fa-solid fa-circle-check"></i>

                                    <span>
                                        This property has been sold.
                                    </span>

                                </div>


                            <?php elseif (
                                $property["status"] === "rented"
                            ): ?>

                                <div
                                    class="own-property-notice"
                                    style="margin-top: 12px;"
                                >

                                    <i class="fa-solid fa-key"></i>

                                    <span>
                                        This property is currently rented.
                                    </span>

                                </div>

                            <?php endif; ?>


                            <a
                                href="/myhome/properties/edit-property.php?id=<?php
                                echo (int) $property["id"];
                                ?>"
                                class="property-inquiry-btn"
                            >

                                <i class="fa-solid fa-pen"></i>

                                Edit Your Property

                            </a>


                        <?php else: ?>


                            <!-- =========================
                                 SOLD
                            ========================== -->

                            <?php if (
                                $property["status"] === "sold"
                            ): ?>

                                <div class="own-property-notice">

                                    <i class="fa-solid fa-circle-check"></i>

                                    <span>
                                        This property has already been sold.
                                    </span>

                                </div>


                            <!-- =========================
                                 RENTED
                            ========================== -->

                            <?php elseif (
                                $property["status"] === "rented"
                            ): ?>

                                <div class="own-property-notice">

                                    <i class="fa-solid fa-key"></i>

                                    <span>
                                        This property is currently rented.
                                    </span>

                                </div>


                            <!-- =========================
                                 AVAILABLE
                            ========================== -->

                            <?php else: ?>


                                <!-- INQUIRY FORM -->

                                <form
                                    method="POST"
                                    action="/myhome/properties/send-inquiry.php"
                                    class="property-inquiry-form"
                                >


                                    <input
                                        type="hidden"
                                        name="property_id"
                                        value="<?php
                                        echo (int) $property["id"];
                                        ?>"
                                    >


                                    <label for="inquiry_message">
                                        Message
                                    </label>


                                    <textarea
                                        id="inquiry_message"
                                        name="message"
                                        rows="5"
                                        maxlength="1000"
                                        placeholder="Hi, I'm interested in this property. Is it still available?"
                                        required
                                    ></textarea>


                                    <button
                                        type="submit"
                                        class="property-inquiry-btn"
                                    >

                                        <i class="fa-solid fa-envelope"></i>

                                        Send Inquiry

                                    </button>


                                </form>


                                <!-- FAVORITES -->

                                <?php if ($is_favorite): ?>


                                    <a
                                        href="/myhome/properties/remove-favorite.php?id=<?php
                                        echo (int) $property["id"];
                                        ?>"
                                        class="property-favorite-btn"
                                    >

                                        <i class="fa-solid fa-heart"></i>

                                        Remove from Favorites

                                    </a>


                                <?php else: ?>


                                    <a
                                        href="/myhome/properties/add-favorite.php?id=<?php
                                        echo (int) $property["id"];
                                        ?>"
                                        class="property-favorite-btn"
                                    >

                                        <i class="fa-regular fa-heart"></i>

                                        Add to Favorites

                                    </a>


                                <?php endif; ?>


                                <!-- TRANSACTION REQUEST -->

                                <form
                                    action="/myhome/properties/request-transaction.php"
                                    method="POST"
                                    style="margin-top: 15px;"
                                >

                                    <input
                                        type="hidden"
                                        name="property_id"
                                        value="<?php
                                        echo (int) $property["id"];
                                        ?>"
                                    >


                                    <button
                                        type="submit"
                                        class="property-inquiry-btn"
                                    >


                                        <?php if (
                                            $property["listing_type"] === "rent"
                                        ): ?>

                                            <i class="fa-solid fa-house"></i>

                                            Request to Rent


                                        <?php else: ?>

                                            <i class="fa-solid fa-cart-shopping"></i>

                                            Request to Buy


                                        <?php endif; ?>


                                    </button>

                                </form>


                            <?php endif; ?>


                        <?php endif; ?>


                    <?php else: ?>


                        <!-- =========================
                             ADMIN
                        ========================== -->

                        <div class="own-property-notice">

                            <i class="fa-solid fa-shield-halved"></i>

                            <span>
                                You are viewing this property as an administrator.
                            </span>

                        </div>


                        <?php if (
                            $property["status"] === "sold"
                        ): ?>

                            <div
                                class="own-property-notice"
                                style="margin-top: 12px;"
                            >

                                <i class="fa-solid fa-circle-check"></i>

                                <span>
                                    This property has been sold.
                                </span>

                            </div>


                        <?php elseif (
                            $property["status"] === "rented"
                        ): ?>

                            <div
                                class="own-property-notice"
                                style="margin-top: 12px;"
                            >

                                <i class="fa-solid fa-key"></i>

                                <span>
                                    This property is currently rented.
                                </span>

                            </div>

                        <?php endif; ?>


                    <?php endif; ?>


                <?php else: ?>


                    <!-- =========================
                         GUEST
                    ========================== -->


                    <?php if (
                        $property["status"] === "sold"
                    ): ?>

                        <div class="own-property-notice">

                            <i class="fa-solid fa-circle-check"></i>

                            <span>
                                This property has already been sold.
                            </span>

                        </div>


                    <?php elseif (
                        $property["status"] === "rented"
                    ): ?>

                        <div class="own-property-notice">

                            <i class="fa-solid fa-key"></i>

                            <span>
                                This property is currently rented.
                            </span>

                        </div>


                    <?php else: ?>

                        <a
                            href="/myhome/login.php"
                            class="property-inquiry-btn"
                        >

                            <i class="fa-solid fa-right-to-bracket"></i>

                            Login to Contact

                        </a>

                    <?php endif; ?>


                <?php endif; ?>


            </aside>


        </div>


    </section>


</main>


<?php

include "../includes/footer.php";

?>

                   
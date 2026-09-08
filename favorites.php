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


$page_title = "Favorites | MyHome";

$user_id = (int) $_SESSION["user_id"];


// =========================================
// GET FAVORITE PROPERTIES
// =========================================

try {

    $stmt = $pdo->prepare(
        "SELECT
            p.*,
            f.id AS favorite_id,
            f.created_at AS favorited_at,

            (
                SELECT pi.image_path
                FROM property_images pi
                WHERE pi.property_id = p.id
                ORDER BY pi.id ASC
                LIMIT 1
            ) AS main_image

         FROM favorites f

         INNER JOIN properties p
            ON p.id = f.property_id

         WHERE f.user_id = :user_id

         ORDER BY f.created_at DESC"
    );


    $stmt->execute([
        "user_id" => $user_id
    ]);


    $favorites =
        $stmt->fetchAll();


    $total_favorites =
        count($favorites);

}

catch (PDOException $e) {

    $favorites = [];

    $total_favorites = 0;

}


// =========================================
// HEADER
// =========================================

include "includes/header.php";

include "includes/navbar.php";

?>


<main class="public-listings-page">

    <section class="public-listings-container">


        <!-- =================================
             HEADER
        ================================== -->

        <div class="public-listings-header">

            <div>

                <p class="section-label">
                    SAVED PROPERTIES
                </p>

                <h1>
                    My Favorites
                </h1>

                <p>
                    Properties you saved for later.
                </p>

            </div>

        </div>


        <!-- =================================
             REMOVED MESSAGE
        ================================== -->

        <?php if (isset($_GET["removed"])): ?>

            <div class="favorite-success-message">

                <i class="fa-solid fa-circle-check"></i>

                Property removed from your favorites.

            </div>

        <?php endif; ?>


        <!-- =================================
             ERROR MESSAGE
        ================================== -->

        <?php if (isset($_GET["error"])): ?>

            <div class="property-form-message error">

                <i class="fa-solid fa-circle-exclamation"></i>

                <span>
                    Something went wrong. Please try again.
                </span>

            </div>

        <?php endif; ?>


        <!-- =================================
             FAVORITE COUNT
        ================================== -->

        <?php if ($total_favorites > 0): ?>

            <div class="favorites-count">

                <strong>
                    <?php echo $total_favorites; ?>
                </strong>

                <?php
                echo $total_favorites === 1
                    ? "saved property"
                    : "saved properties";
                ?>

            </div>

        <?php endif; ?>


        <!-- =================================
             FAVORITES
        ================================== -->

        <?php if ($total_favorites > 0): ?>


            <div class="public-listings-grid">


                <?php foreach ($favorites as $property): ?>


                    <article class="public-property-card">


                        <!-- =========================
                             IMAGE
                        ========================== -->

                        <div class="public-property-image">


                            <?php if (!empty($property["main_image"])): ?>

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

                            <?php else: ?>

                                <div class="public-property-no-image">

                                    <i class="fa-solid fa-house"></i>

                                </div>

                            <?php endif; ?>


                        </div>


                        <!-- =========================
                             CONTENT
                        ========================== -->

                        <div class="public-property-content">


                            <!-- TYPE + STATUS -->

                            <div class="public-property-top">


                                <span
                                    class="listing-type-badge <?php
                                    echo htmlspecialchars(
                                        $property["listing_type"]
                                    );
                                    ?>"
                                >

                                    <?php
                                    echo $property["listing_type"] === "rent"
                                        ? "FOR RENT"
                                        : "FOR SALE";
                                    ?>

                                </span>


                                <span
                                    class="listing-status <?php
                                    echo htmlspecialchars(
                                        $property["status"]
                                    );
                                    ?>"
                                >

                                    <?php
                                    echo ucfirst(
                                        htmlspecialchars(
                                            $property["status"]
                                        )
                                    );
                                    ?>

                                </span>


                            </div>


                            <!-- TITLE -->

                            <h2>

                                <?php
                                echo htmlspecialchars(
                                    $property["title"]
                                );
                                ?>

                            </h2>


                            <!-- PROPERTY TYPE -->

                            <p class="public-property-type">

                                <i class="fa-solid fa-house"></i>

                                <?php
                                echo htmlspecialchars(
                                    $property["property_type"]
                                );
                                ?>

                            </p>


                            <!-- LOCATION -->

                            <p class="public-property-location">

                                <i class="fa-solid fa-location-dot"></i>

                                <?php
                                echo htmlspecialchars(
                                    $property["location"]
                                );
                                ?>

                            </p>


                            <!-- DETAILS -->

                            <div class="public-property-details">


                                <span>

                                    <i class="fa-solid fa-bed"></i>

                                    <?php
                                    echo (int) $property["bedrooms"];
                                    ?>

                                    Beds

                                </span>


                                <span>

                                    <i class="fa-solid fa-bath"></i>

                                    <?php
                                    echo (int) $property["bathrooms"];
                                    ?>

                                    Baths

                                </span>


                                <span>

                                    <i class="fa-solid fa-ruler-combined"></i>

                                    <?php
                                    echo number_format(
                                        (float) $property["area"],
                                        0
                                    );
                                    ?>

                                    m²

                                </span>


                            </div>


                            <!-- =========================
                                 PRICE
                            ========================== -->

                            <div class="public-property-bottom">


                                <strong>

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


                                </strong>


                                <?php if (
                                    $property["status"] === "available"
                                ): ?>

                                    <a
                                        href="/myhome/properties/view-property.php?id=<?php
                                        echo (int) $property["id"];
                                        ?>"
                                        class="view-property-btn"
                                    >
                                        View Property
                                    </a>

                                <?php else: ?>

                                    <span class="favorite-unavailable">

                                        <?php
                                        echo $property["status"] === "sold"
                                            ? "Sold"
                                            : "Rented";
                                        ?>

                                    </span>

                                <?php endif; ?>


                            </div>


                            <!-- =========================
                                 REMOVE FAVORITE
                            ========================== -->

                            <div class="favorite-card-actions">

                                <button
                                    type="button"
                                    class="remove-favorite-btn"
                                    onclick='openFavoriteModal(
                                        <?php
                                        echo (int) $property["id"];
                                        ?>,
                                        <?php
                                        echo json_encode(
                                            $property["title"],
                                            JSON_HEX_APOS |
                                            JSON_HEX_QUOT |
                                            JSON_HEX_AMP |
                                            JSON_HEX_TAG
                                        );
                                        ?>
                                    )'
                                >

                                    <i class="fa-solid fa-heart-crack"></i>

                                    Remove from Favorites

                                </button>

                            </div>


                        </div>


                    </article>


                <?php endforeach; ?>


            </div>


        <?php else: ?>


            <!-- =================================
                 EMPTY STATE
            ================================== -->

            <div class="empty-listings">


                <i class="fa-regular fa-heart"></i>


                <h2>
                    No favorites yet
                </h2>


                <p>
                    Save properties you like and they will appear here.
                </p>


                <a
                    href="/myhome/properties/listings.php"
                    class="add-property-btn"
                >
                    Browse Properties
                </a>


            </div>


        <?php endif; ?>


    </section>

</main>


<!-- =========================================
     REMOVE FAVORITE MODAL
========================================= -->

<div
    class="favorite-modal-overlay"
    id="favoriteModal"
>

    <div class="favorite-modal">


        <div class="favorite-modal-icon">

            <i class="fa-solid fa-heart-crack"></i>

        </div>


        <h2>
            Remove Favorite?
        </h2>


        <p>

            Are you sure you want to remove

            <strong
                id="favoritePropertyTitle"
            ></strong>

            from your favorites?

        </p>


        <p class="favorite-modal-note">
            You can add it again anytime.
        </p>


        <div class="favorite-modal-actions">


            <button
                type="button"
                class="favorite-modal-cancel"
                onclick="closeFavoriteModal()"
            >
                Cancel
            </button>


            <form
                action="/myhome/favorite-action.php"
                method="POST"
            >

                <input
                    type="hidden"
                    name="property_id"
                    id="favoritePropertyId"
                >


                <button
                    type="submit"
                    name="action"
                    value="remove"
                    class="favorite-modal-remove"
                >

                    <i class="fa-solid fa-trash"></i>

                    Remove

                </button>

            </form>


        </div>


    </div>

</div>


<?php

include "includes/footer.php";

?>
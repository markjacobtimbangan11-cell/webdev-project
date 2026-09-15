<?php

session_start();

$pdo = require "../config/database.php";


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


// =========================================
// SUCCESS MESSAGE
// =========================================

$success_message = "";

if (
    isset($_GET["added"]) &&
    $_GET["added"] === "1"
) {

    $success_message =
        "Property added successfully.";
}

elseif (
    isset($_GET["updated"]) &&
    $_GET["updated"] === "1"
) {

    $success_message =
        "Property updated successfully.";
}

elseif (
    isset($_GET["deleted"]) &&
    $_GET["deleted"] === "1"
) {

    $success_message =
        "Property deleted successfully.";
}


// =========================================
// PAGE TITLE
// =========================================

$page_title = "My Listings | MyHome";

$user_id = (int) $_SESSION["user_id"];


// =========================================
// GET USER PROPERTIES
// =========================================

try {

    $stmt = $pdo->prepare(
        "SELECT
            p.*,

            (
                SELECT pi.image_path
                FROM property_images pi
                WHERE pi.property_id = p.id
                ORDER BY pi.id ASC
                LIMIT 1
            ) AS main_image

         FROM properties p

         WHERE p.user_id = :user_id

         ORDER BY p.created_at DESC"
    );


    $stmt->execute([
        "user_id" => $user_id
    ]);


    $properties =
        $stmt->fetchAll();


    $total_properties =
        count($properties);

}

catch (PDOException $e) {

    $properties = [];

    $total_properties = 0;
}


// =========================================
// HEADER
// =========================================

include "../includes/header.php";

include "../includes/navbar.php";

?>


<main class="my-listings-page">

    <section class="my-listings-container">


        <?php if ($success_message !== ""): ?>

            <div class="listing-success-message">

                <i class="fa-solid fa-circle-check"></i>

                <span>
                    <?php
                    echo htmlspecialchars(
                        $success_message
                    );
                    ?>
                </span>

            </div>

        <?php endif; ?>


        <div class="my-listings-header">

            <div>

                <p class="section-label">
                    PROPERTY MANAGEMENT
                </p>

                <h1>
                    My Listings
                </h1>

                <p>
                    Manage the properties you have listed on MyHome.
                </p>

            </div>


            <a
                href="add-property.php"
                class="add-property-btn"
            >

                <i class="fa-solid fa-plus"></i>

                Add Property

            </a>

        </div>


        <?php if ($total_properties > 0): ?>


            <div class="my-listings-grid">


                <?php foreach ($properties as $property): ?>


                    <article class="my-listing-card">


                        <?php if (!empty($property["main_image"])): ?>

                            <div class="my-listing-image">

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

                        <?php endif; ?>


                        <div class="my-listing-content">


                            <div class="my-listing-top">


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


                                <span class="listing-status">

                                    <?php
                                    echo ucfirst(
                                        htmlspecialchars(
                                            $property["status"]
                                        )
                                    );
                                    ?>

                                </span>


                            </div>


                            <h2>

                                <?php
                                echo htmlspecialchars(
                                    $property["title"]
                                );
                                ?>

                            </h2>


                            <p class="my-listing-location">

                                <i class="fa-solid fa-location-dot"></i>

                                <?php
                                echo htmlspecialchars(
                                    $property["location"]
                                );
                                ?>

                            </p>


                            <div class="my-listing-details">


                                <span>

                                    <div class="property-features">

    <?php
        $propertyType =
            strtolower(
                trim(
                    $property["property_type"]
                )
            );

        $isLot =
            $propertyType === "lot" ||
            $propertyType === "land";
    ?>


    <?php if (!$isLot): ?>

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

    <?php endif; ?>


    <span>
        <i class="fa-solid fa-ruler-combined"></i>

        <?php if ((float) $property["area"] > 0): ?>

            <?php
                echo number_format(
                    $property["area"],
                    2
                );
            ?>

            m²

        <?php else: ?>

            Not specified

        <?php endif; ?>
    </span>

</div>

                                </span>


                            </div>


                            <div class="my-listing-bottom">


                                <strong>

                                    ₱<?php
                                    echo number_format(
                                        (float) $property["price"],
                                        2
                                    );
                                    ?>

                                </strong>


                                <div class="my-listing-actions">


                                    <a
                                        href="edit-property.php?id=<?php
                                        echo (int) $property["id"];
                                        ?>"
                                        class="edit-property-btn"
                                    >

                                        <i class="fa-solid fa-pen"></i>

                                        Edit

                                    </a>


                                    <button
                                        type="button"
                                        class="delete-property-btn"
                                        onclick='openDeleteModal(
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

                                        <i class="fa-solid fa-trash"></i>

                                        Delete

                                    </button>


                                </div>


                            </div>


                        </div>


                    </article>


                <?php endforeach; ?>


            </div>


        <?php else: ?>


            <div class="empty-listings">

                <i class="fa-solid fa-house-circle-exclamation"></i>

                <h2>
                    No properties yet
                </h2>

                <p>
                    You haven't listed any properties yet.
                    Add your first property to get started.
                </p>

                <a
                    href="add-property.php"
                    class="add-property-btn"
                >

                    <i class="fa-solid fa-plus"></i>

                    Add Your First Property

                </a>

            </div>


        <?php endif; ?>


    </section>

</main>


<!-- =========================
     DELETE CONFIRMATION MODAL
========================= -->

<div
    class="delete-modal-overlay"
    id="deleteModal"
>

    <div class="delete-modal">


        <div class="delete-modal-icon">

            <i class="fa-solid fa-trash"></i>

        </div>


        <h2>
            Delete Property?
        </h2>


        <p>

            Are you sure you want to delete

            <strong
                id="deletePropertyName"
            ></strong>?

        </p>


        <p class="delete-warning">
            This action cannot be undone.
        </p>


        <div class="delete-modal-actions">


            <button
                type="button"
                class="delete-cancel-btn"
                onclick="closeDeleteModal()"
            >

                Cancel

            </button>


            <a
                href="#"
                class="delete-confirm-btn"
                id="confirmDeleteBtn"
            >

                <i class="fa-solid fa-trash"></i>

                Delete Property

            </a>


        </div>


    </div>

</div>


<?php

include "../includes/footer.php";

?>
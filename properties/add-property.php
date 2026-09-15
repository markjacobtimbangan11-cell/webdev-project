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


$page_title = "Add Property | MyHome";

$message = "";
$message_type = "";

$title = "";
$description = "";
$property_type = "";
$listing_type = "";
$price = "";
$location = "";
$bedrooms = "";
$bathrooms = "";
$area = "";


// =========================================
// FORM SUBMISSION
// =========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $user_id =
        (int) $_SESSION["user_id"];


    $title =
        trim($_POST["title"] ?? "");

    $description =
        trim($_POST["description"] ?? "");

    $property_type =
        trim($_POST["property_type"] ?? "");

    $listing_type =
        trim($_POST["listing_type"] ?? "");

    $price =
        trim($_POST["price"] ?? "");

    $location =
        trim($_POST["location"] ?? "");

    $bedrooms =
        trim($_POST["bedrooms"] ?? "");

    $bathrooms =
        trim($_POST["bathrooms"] ?? "");

    $area =
        trim($_POST["area"] ?? "");

    $image =
        $_FILES["property_image"] ?? null;


    // =========================================
    // LOT DOES NOT USE BEDROOMS / BATHROOMS
    // =========================================

    if ($property_type === "Lot") {

        $bedrooms = "0";
        $bathrooms = "0";
    }


    // =========================================
    // BASIC VALIDATION
    // =========================================

    if (
        $title === "" ||
        $description === "" ||
        $property_type === "" ||
        $listing_type === "" ||
        $price === "" ||
        $location === ""
    ) {

        $message =
            "Please fill in all required fields.";

        $message_type =
            "error";
    }

    elseif (
        strlen($title) < 5 ||
        strlen($title) > 150
    ) {

        $message =
            "Property title must be between 5 and 150 characters.";

        $message_type =
            "error";
    }

    elseif (
        !in_array(
            $property_type,
            [
                "House",
                "Apartment",
                "Condo",
                "Townhouse",
                "Lot"
            ],
            true
        )
    ) {

        $message =
            "Please select a valid property type.";

        $message_type =
            "error";
    }

    elseif (
        !in_array(
            $listing_type,
            ["rent", "sale"],
            true
        )
    ) {

        $message =
            "Please select a valid listing type.";

        $message_type =
            "error";
    }

    elseif (
        !is_numeric($price) ||
        (float) $price <= 0
    ) {

        $message =
            "Please enter a valid price.";

        $message_type =
            "error";
    }

    elseif (
        $property_type !== "Lot" &&
        $bedrooms !== "" &&
        (
            !ctype_digit($bedrooms) ||
            (int) $bedrooms < 0
        )
    ) {

        $message =
            "Bedrooms must be a valid whole number.";

        $message_type =
            "error";
    }

    elseif (
        $property_type !== "Lot" &&
        $bathrooms !== "" &&
        (
            !ctype_digit($bathrooms) ||
            (int) $bathrooms < 0
        )
    ) {

        $message =
            "Bathrooms must be a valid whole number.";

        $message_type =
            "error";
    }

    elseif (
        $area !== "" &&
        (
            !is_numeric($area) ||
            (float) $area < 0
        )
    ) {

        $message =
            "Area must be a valid number.";

        $message_type =
            "error";
    }


    // =========================================
    // IMAGE REQUIRED
    // =========================================

    elseif (
        !$image ||
        $image["error"] !== UPLOAD_ERR_OK
    ) {

        $message =
            "Please upload a property image.";

        $message_type =
            "error";
    }


    // =========================================
    // IMAGE SIZE
    // =========================================

    elseif (
        $image["size"] >
        5 * 1024 * 1024
    ) {

        $message =
            "Property image must not exceed 5MB.";

        $message_type =
            "error";
    }


    // =========================================
    // CONTINUE
    // =========================================

    else {


        // =====================================
        // CHECK ACTUAL IMAGE TYPE
        // =====================================

        $allowed_types = [
            "image/jpeg" => "jpg",
            "image/png" => "png",
            "image/webp" => "webp"
        ];


        $finfo =
            new finfo(
                FILEINFO_MIME_TYPE
            );


        $file_type =
            $finfo->file(
                $image["tmp_name"]
            );


        if (
            !isset(
                $allowed_types[$file_type]
            )
        ) {

            $message =
                "Only JPG, JPEG, PNG and WEBP images are allowed.";

            $message_type =
                "error";
        }

        else {


            // =================================
            // PREPARE IMAGE NAME
            // =================================

            $extension =
                $allowed_types[$file_type];


            $file_name =
                "property_"
                . bin2hex(
                    random_bytes(8)
                )
                . "."
                . $extension;


            // Physical location

            $upload_directory =
                __DIR__
                . "/../uploads/properties/";


            $upload_path =
                $upload_directory
                . $file_name;


            // Path stored in database

            $database_image_path =
                "uploads/properties/"
                . $file_name;


            // =================================
            // MAKE SURE FOLDER EXISTS
            // =================================

            if (!is_dir($upload_directory)) {

                mkdir(
                    $upload_directory,
                    0775,
                    true
                );
            }


            // =================================
            // VALUES
            // =================================

            $price_value =
                (float) $price;


            if ($property_type === "Lot") {

                $bedrooms_value = 0;
                $bathrooms_value = 0;

            } else {

                $bedrooms_value =
                    $bedrooms === ""
                        ? 0
                        : (int) $bedrooms;


                $bathrooms_value =
                    $bathrooms === ""
                        ? 0
                        : (int) $bathrooms;
            }


            $area_value =
                $area === ""
                    ? 0
                    : (float) $area;


            // =================================
            // INSERT PROPERTY
            // =================================

            try {

                $pdo->beginTransaction();


                $stmt =
                    $pdo->prepare(
                        "INSERT INTO properties
                        (
                            user_id,
                            title,
                            description,
                            property_type,
                            listing_type,
                            price,
                            location,
                            bedrooms,
                            bathrooms,
                            area
                        )
                        VALUES
                        (
                            :user_id,
                            :title,
                            :description,
                            :property_type,
                            :listing_type,
                            :price,
                            :location,
                            :bedrooms,
                            :bathrooms,
                            :area
                        )"
                    );


                $stmt->execute([
                    "user_id" =>
                        $user_id,

                    "title" =>
                        $title,

                    "description" =>
                        $description,

                    "property_type" =>
                        $property_type,

                    "listing_type" =>
                        $listing_type,

                    "price" =>
                        $price_value,

                    "location" =>
                        $location,

                    "bedrooms" =>
                        $bedrooms_value,

                    "bathrooms" =>
                        $bathrooms_value,

                    "area" =>
                        $area_value
                ]);


                $property_id =
                    (int) $pdo->lastInsertId();


                // =================================
                // MOVE IMAGE
                // =================================

                if (
                    !move_uploaded_file(
                        $image["tmp_name"],
                        $upload_path
                    )
                ) {

                    throw new Exception(
                        "Image upload failed."
                    );
                }


                // =================================
                // SAVE IMAGE PATH
                // =================================

                $image_stmt =
                    $pdo->prepare(
                        "INSERT INTO property_images
                        (
                            property_id,
                            image_path
                        )
                        VALUES
                        (
                            :property_id,
                            :image_path
                        )"
                    );


                $image_stmt->execute([
                    "property_id" =>
                        $property_id,

                    "image_path" =>
                        $database_image_path
                ]);


                // =================================
                // COMPLETE TRANSACTION
                // =================================

                $pdo->commit();


                header(
                    "Location: /myhome/properties/my-listings.php?added=1"
                );

                exit;

            }

            catch (Throwable $e) {


                // =================================
                // ROLLBACK DATABASE
                // =================================

                if ($pdo->inTransaction()) {

                    $pdo->rollBack();
                }


                // =================================
                // REMOVE UPLOADED FILE IF NEEDED
                // =================================

                if (
                    isset($upload_path) &&
                    file_exists($upload_path)
                ) {

                    unlink($upload_path);
                }


                $message =
                    "Failed to add property. Please try again.";

                $message_type =
                    "error";
            }
        }
    }
}


// =========================================
// HEADER
// =========================================

include "../includes/header.php";

include "../includes/navbar.php";

?>


<main class="property-form-page">

    <section class="property-form-container">


        <!-- PAGE HEADER -->

        <div class="property-form-heading">

            <div>

                <p class="section-label">
                    PROPERTY MANAGEMENT
                </p>

                <h1>
                    Add Property
                </h1>

                <p>
                    Add a new property listing to MyHome.
                </p>

            </div>


            <a
                href="/myhome/properties/my-listings.php"
                class="back-listings-btn"
            >

                <i class="fa-solid fa-arrow-left"></i>

                Back to My Listings

            </a>

        </div>


        <!-- FORM CARD -->

        <div class="property-form-card">


            <!-- MESSAGE -->

            <?php if ($message !== ""): ?>

                <div
                    class="property-form-message <?php
                    echo htmlspecialchars(
                        $message_type
                    );
                    ?>"
                >

                    <?php if ($message_type === "error"): ?>

                        <i
                            class="fa-solid fa-circle-exclamation"
                        ></i>

                    <?php else: ?>

                        <i
                            class="fa-solid fa-circle-check"
                        ></i>

                    <?php endif; ?>


                    <span>

                        <?php
                        echo htmlspecialchars(
                            $message
                        );
                        ?>

                    </span>

                </div>

            <?php endif; ?>


            <!-- FORM -->

            <form
                method="POST"
                action=""
                class="property-form"
                enctype="multipart/form-data"
            >


                <!-- PROPERTY TITLE -->

                <div class="property-form-group full-width">

                    <label for="title">

                        Property Title

                        <span>*</span>

                    </label>


                    <input
                        type="text"
                        id="title"
                        name="title"
                        placeholder="Example: Modern Family Residence"
                        value="<?php
                        echo htmlspecialchars(
                            $title
                        );
                        ?>"
                        minlength="5"
                        maxlength="150"
                        required
                    >

                </div>


                <!-- PROPERTY TYPE -->

                <div class="property-form-group">

                    <label for="property_type">

                        Property Type

                        <span>*</span>

                    </label>


                    <select
                        id="property_type"
                        name="property_type"
                        required
                        onchange="toggleLotFields()"
                    >

                        <option value="">
                            Select property type
                        </option>


                        <option
                            value="House"
                            <?php
                            echo $property_type === "House"
                                ? "selected"
                                : "";
                            ?>
                        >
                            House
                        </option>


                        <option
                            value="Apartment"
                            <?php
                            echo $property_type === "Apartment"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Apartment
                        </option>


                        <option
                            value="Condo"
                            <?php
                            echo $property_type === "Condo"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Condo
                        </option>


                        <option
                            value="Townhouse"
                            <?php
                            echo $property_type === "Townhouse"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Townhouse
                        </option>


                        <option
                            value="Lot"
                            <?php
                            echo $property_type === "Lot"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Lot
                        </option>

                    </select>

                </div>


                <!-- LISTING TYPE -->

                <div class="property-form-group">

                    <label for="listing_type">

                        Listing Type

                        <span>*</span>

                    </label>


                    <select
                        id="listing_type"
                        name="listing_type"
                        required
                    >

                        <option value="">
                            Select listing type
                        </option>


                        <option
                            value="rent"
                            <?php
                            echo $listing_type === "rent"
                                ? "selected"
                                : "";
                            ?>
                        >
                            For Rent
                        </option>


                        <option
                            value="sale"
                            <?php
                            echo $listing_type === "sale"
                                ? "selected"
                                : "";
                            ?>
                        >
                            For Sale
                        </option>

                    </select>

                </div>


                <!-- DESCRIPTION -->

                <div class="property-form-group full-width">

                    <label for="description">

                        Description

                        <span>*</span>

                    </label>


                    <textarea
                        id="description"
                        name="description"
                        rows="6"
                        placeholder="Describe the property, features, nearby locations, and other important details."
                        required
                    ><?php
                    echo htmlspecialchars(
                        $description
                    );
                    ?></textarea>

                </div>


                <!-- PRICE -->

                <div class="property-form-group">

                    <label for="price">

                        Price

                        <span>*</span>

                    </label>


                    <div class="price-input">

                        <span>₱</span>

                        <input
                            type="number"
                            id="price"
                            name="price"
                            placeholder="0.00"
                            value="<?php
                            echo htmlspecialchars(
                                $price
                            );
                            ?>"
                            min="0.01"
                            step="0.01"
                            required
                        >

                    </div>

                </div>


                <!-- LOCATION -->

                <div class="property-form-group">

                    <label for="location">

                        Location

                        <span>*</span>

                    </label>


                    <input
                        type="text"
                        id="location"
                        name="location"
                        placeholder="Example: Quezon City, Philippines"
                        value="<?php
                        echo htmlspecialchars(
                            $location
                        );
                        ?>"
                        maxlength="255"
                        required
                    >

                </div>


                <!-- BEDROOMS -->

                <div
                    class="property-form-group"
                    id="bedroomsGroup"
                >

                    <label for="bedrooms">
                        Bedrooms
                    </label>


                    <input
                        type="number"
                        id="bedrooms"
                        name="bedrooms"
                        placeholder="0"
                        value="<?php
                        echo htmlspecialchars(
                            $bedrooms
                        );
                        ?>"
                        min="0"
                        step="1"
                    >

                </div>


                <!-- BATHROOMS -->

                <div
                    class="property-form-group"
                    id="bathroomsGroup"
                >

                    <label for="bathrooms">
                        Bathrooms
                    </label>


                    <input
                        type="number"
                        id="bathrooms"
                        name="bathrooms"
                        placeholder="0"
                        value="<?php
                        echo htmlspecialchars(
                            $bathrooms
                        );
                        ?>"
                        min="0"
                        step="1"
                    >

                </div>


                <!-- AREA -->

                <div class="property-form-group full-width">

                    <label for="area">
                        Area (m²)
                    </label>


                    <input
                        type="number"
                        id="area"
                        name="area"
                        placeholder="Example: 250"
                        value="<?php
                        echo htmlspecialchars(
                            $area
                        );
                        ?>"
                        min="0"
                        step="0.01"
                    >

                </div>


                <!-- PROPERTY IMAGE -->

                <div class="property-form-group full-width">

                    <label for="property_image">

                        Main Property Image

                        <span>*</span>

                    </label>


                    <input
                        type="file"
                        id="property_image"
                        name="property_image"
                        accept=".jpg,.jpeg,.png,.webp"
                        required
                    >


                    <small class="property-upload-hint">

                        JPG, JPEG, PNG or WEBP.
                        Maximum file size: 5MB.

                    </small>

                </div>


                <!-- BUTTONS -->

                <div class="property-form-actions full-width">


                    <a
                        href="/myhome/properties/my-listings.php"
                        class="cancel-property-btn"
                    >
                        Cancel
                    </a>


                    <button
                        type="submit"
                        class="save-property-btn"
                    >

                        <i class="fa-solid fa-plus"></i>

                        Add Property

                    </button>


                </div>


            </form>


        </div>


    </section>

</main>


<!-- =========================================
     PROPERTY TYPE BEHAVIOR
========================================= -->

<script>

function toggleLotFields() {

    const propertyType =
        document.getElementById(
            "property_type"
        );

    const bedroomsGroup =
        document.getElementById(
            "bedroomsGroup"
        );

    const bathroomsGroup =
        document.getElementById(
            "bathroomsGroup"
        );

    const bedrooms =
        document.getElementById(
            "bedrooms"
        );

    const bathrooms =
        document.getElementById(
            "bathrooms"
        );


    if (
        !propertyType ||
        !bedroomsGroup ||
        !bathroomsGroup ||
        !bedrooms ||
        !bathrooms
    ) {

        return;
    }


    if (
        propertyType.value
            .trim()
            .toLowerCase()
        === "lot"
    ) {

        bedroomsGroup.style.display =
            "none";

        bathroomsGroup.style.display =
            "none";

        bedrooms.value = "0";

        bathrooms.value = "0";

    } else {

        bedroomsGroup.style.display =
            "";

        bathroomsGroup.style.display =
            "";
    }
}


// Run immediately after the form exists
toggleLotFields();

</script>


<?php

include "../includes/footer.php";

?>
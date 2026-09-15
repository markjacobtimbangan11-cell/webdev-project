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
// PROPERTY ID
// =========================================

$user_id =
    (int) $_SESSION["user_id"];

$property_id =
    isset($_GET["id"])
        ? (int) $_GET["id"]
        : 0;


if ($property_id <= 0) {

    header(
        "Location: /myhome/properties/my-listings.php"
    );

    exit;
}


// =========================================
// GET PROPERTY
// =========================================

try {

    $stmt = $pdo->prepare(
        "SELECT *
         FROM properties
         WHERE id = :property_id
         AND user_id = :user_id
         LIMIT 1"
    );


    $stmt->execute([
        "property_id" => $property_id,
        "user_id" => $user_id
    ]);


    $property =
        $stmt->fetch();


    if (!$property) {

        header(
            "Location: /myhome/properties/my-listings.php"
        );

        exit;
    }

}
catch (PDOException $e) {

    header(
        "Location: /myhome/properties/my-listings.php"
    );

    exit;
}


// =========================================
// GET CURRENT IMAGE
// =========================================

try {

    $image_stmt = $pdo->prepare(
        "SELECT
            id,
            image_path
         FROM property_images
         WHERE property_id = :property_id
         ORDER BY id ASC
         LIMIT 1"
    );


    $image_stmt->execute([
        "property_id" => $property_id
    ]);


    $current_image =
        $image_stmt->fetch();

}
catch (PDOException $e) {

    $current_image = false;
}


// =========================================
// INITIAL VALUES
// =========================================

$title =
    $property["title"];

$description =
    $property["description"];

$property_type =
    $property["property_type"];

$listing_type =
    $property["listing_type"];

$price =
    $property["price"];

$location =
    $property["location"];

$bedrooms =
    $property["bedrooms"];

$bathrooms =
    $property["bathrooms"];

$area =
    $property["area"];

$status =
    $property["status"];


$message = "";
$message_type = "";


// =========================================
// UPDATE PROPERTY
// =========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {


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

    $status =
        trim($_POST["status"] ?? "");


    // =========================================
    // LOT DOES NOT USE BEDROOMS / BATHROOMS
    // =========================================

    if ($property_type === "Lot") {

        $bedrooms = "0";
        $bathrooms = "0";
    }


    // =========================================
    // VALIDATION
    // =========================================

    if (
        $title === "" ||
        $description === "" ||
        $property_type === "" ||
        $listing_type === "" ||
        $price === "" ||
        $location === "" ||
        $status === ""
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
        !in_array(
            $status,
            ["available", "sold", "rented"],
            true
        )
    ) {

        $message =
            "Please select a valid property status.";

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

    else {


        // =========================================
        // VALUES
        // =========================================

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


        // =========================================
        // CHECK NEW IMAGE FIRST
        // =========================================

        $new_image_uploaded = false;

        $new_image_path = null;

        $destination = null;


        if (
            isset($_FILES["property_image"]) &&
            $_FILES["property_image"]["error"]
                === UPLOAD_ERR_OK
        ) {

            $file =
                $_FILES["property_image"];


            $allowed_types = [
                "image/jpeg" => "jpg",
                "image/png" => "png",
                "image/webp" => "webp"
            ];


            $max_size =
                5 * 1024 * 1024;


            $finfo =
                new finfo(
                    FILEINFO_MIME_TYPE
                );


            $file_type =
                $finfo->file(
                    $file["tmp_name"]
                );


            // INVALID TYPE

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


            // FILE TOO LARGE

            elseif (
                $file["size"] > $max_size
            ) {

                $message =
                    "Image must not exceed 5MB.";

                $message_type =
                    "error";
            }

            else {

                $extension =
                    $allowed_types[$file_type];


                $new_filename =
                    "property_"
                    . bin2hex(
                        random_bytes(8)
                    )
                    . "."
                    . $extension;


                $upload_directory =
                    __DIR__
                    . "/../uploads/properties/";


                if (!is_dir($upload_directory)) {

                    mkdir(
                        $upload_directory,
                        0775,
                        true
                    );
                }


                $destination =
                    $upload_directory
                    . $new_filename;


                $new_image_path =
                    "uploads/properties/"
                    . $new_filename;


                $new_image_uploaded = true;
            }
        }


        // =========================================
        // CONTINUE IF NO VALIDATION ERROR
        // =========================================

        if ($message_type !== "error") {

            try {

                $pdo->beginTransaction();


                // =================================
                // UPDATE PROPERTY
                // =================================

                $update =
                    $pdo->prepare(
                        "UPDATE properties

                         SET
                            title = :title,
                            description = :description,
                            property_type = :property_type,
                            listing_type = :listing_type,
                            price = :price,
                            location = :location,
                            bedrooms = :bedrooms,
                            bathrooms = :bathrooms,
                            area = :area,
                            status = :status

                         WHERE id = :property_id
                         AND user_id = :user_id"
                    );


                $update->execute([
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
                        $area_value,

                    "status" =>
                        $status,

                    "property_id" =>
                        $property_id,

                    "user_id" =>
                        $user_id
                ]);


                // =================================
                // NEW IMAGE
                // =================================

                if ($new_image_uploaded) {


                    // MOVE NEW FILE

                    if (
                        !move_uploaded_file(
                            $file["tmp_name"],
                            $destination
                        )
                    ) {

                        throw new Exception(
                            "Failed to upload the new image."
                        );
                    }


                    // =================================
                    // EXISTING IMAGE
                    // =================================

                    if ($current_image) {

                        $update_image =
                            $pdo->prepare(
                                "UPDATE property_images
                                 SET image_path = :image_path
                                 WHERE id = :image_id
                                 AND property_id = :property_id"
                            );


                        $update_image->execute([
                            "image_path" =>
                                $new_image_path,

                            "image_id" =>
                                (int) $current_image["id"],

                            "property_id" =>
                                $property_id
                        ]);
                    }


                    // =================================
                    // NO EXISTING IMAGE
                    // =================================

                    else {

                        $insert_image =
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


                        $insert_image->execute([
                            "property_id" =>
                                $property_id,

                            "image_path" =>
                                $new_image_path
                        ]);
                    }
                }


                // =================================
                // COMMIT
                // =================================

                $pdo->commit();


                // =================================
                // DELETE OLD IMAGE AFTER COMMIT
                // =================================

                if (
                    $new_image_uploaded &&
                    $current_image &&
                    !empty(
                        $current_image["image_path"]
                    )
                ) {

                    $old_file =
                        __DIR__
                        . "/../"
                        . $current_image["image_path"];


                    if (
                        file_exists($old_file) &&
                        is_file($old_file)
                    ) {

                        unlink($old_file);
                    }
                }


                header(
                    "Location: /myhome/properties/my-listings.php?updated=1"
                );

                exit;

            }
            catch (Throwable $e) {


                // =================================
                // ROLLBACK
                // =================================

                if ($pdo->inTransaction()) {

                    $pdo->rollBack();
                }


                // =================================
                // REMOVE NEW FILE IF SAVED
                // =================================

                if (
                    $new_image_uploaded &&
                    $destination &&
                    file_exists($destination)
                ) {

                    unlink($destination);
                }


                $message =
                    "Failed to update property. Please try again.";

                $message_type =
                    "error";
            }
        }
    }
}


// =========================================
// PAGE TITLE
// =========================================

$page_title =
    "Edit Property | MyHome";


include "../includes/header.php";

include "../includes/navbar.php";

?>


<main class="property-form-page">

    <section class="property-form-container">


        <!-- =========================
             PAGE HEADER
        ========================== -->

        <div class="property-form-heading">

            <div>

                <p class="section-label">
                    PROPERTY MANAGEMENT
                </p>

                <h1>
                    Edit Property
                </h1>

                <p>
                    Update your property information.
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


        <!-- =========================
             FORM CARD
        ========================== -->

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

                    <i
                        class="fa-solid fa-circle-exclamation"
                    ></i>


                    <span>

                        <?php
                        echo htmlspecialchars(
                            $message
                        );
                        ?>

                    </span>

                </div>

            <?php endif; ?>


            <!-- =========================
                 FORM
            ========================== -->

            <form
                method="POST"
                action=""
                enctype="multipart/form-data"
                class="property-form"
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

                        <?php

                        $types = [
                            "House",
                            "Apartment",
                            "Condo",
                            "Townhouse",
                            "Lot"
                        ];

                        foreach ($types as $type):

                        ?>

                            <option
                                value="<?php
                                echo htmlspecialchars(
                                    $type
                                );
                                ?>"
                                <?php
                                echo $property_type === $type
                                    ? "selected"
                                    : "";
                                ?>
                            >

                                <?php
                                echo htmlspecialchars(
                                    $type
                                );
                                ?>

                            </option>

                        <?php endforeach; ?>

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

                <div class="property-form-group">

                    <label for="area">
                        Area (m²)
                    </label>


                    <input
                        type="number"
                        id="area"
                        name="area"
                        value="<?php
                        echo htmlspecialchars(
                            $area
                        );
                        ?>"
                        min="0"
                        step="0.01"
                    >

                </div>


                <!-- STATUS -->

                <div class="property-form-group">

                    <label for="status">

                        Status

                        <span>*</span>

                    </label>


                    <select
                        id="status"
                        name="status"
                        required
                    >

                        <option
                            value="available"
                            <?php
                            echo $status === "available"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Available
                        </option>


                        <option
                            value="sold"
                            <?php
                            echo $status === "sold"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Sold
                        </option>


                        <option
                            value="rented"
                            <?php
                            echo $status === "rented"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Rented
                        </option>

                    </select>

                </div>


                <!-- =========================
                     CURRENT IMAGE
                ========================== -->

                <div class="property-form-group full-width">

                    <label>
                        Current Property Image
                    </label>


                    <?php if (
                        $current_image &&
                        !empty(
                            $current_image["image_path"]
                        )
                    ): ?>

                        <div class="edit-current-image">

                            <img
                                src="/myhome/<?php
                                echo htmlspecialchars(
                                    $current_image["image_path"]
                                );
                                ?>"
                                alt="Current property image"
                            >

                        </div>

                    <?php else: ?>

                        <small>
                            No property image uploaded.
                        </small>

                    <?php endif; ?>

                </div>


                <!-- =========================
                     REPLACE IMAGE
                ========================== -->

                <div class="property-form-group full-width">

                    <label for="property_image">
                        Replace Property Image
                    </label>


                    <input
                        type="file"
                        id="property_image"
                        name="property_image"
                        accept=".jpg,.jpeg,.png,.webp"
                    >


                    <small class="property-upload-hint">

                        Leave empty to keep the current image.
                        JPG, JPEG, PNG or WEBP.
                        Maximum 5MB.

                    </small>

                </div>


                <!-- =========================
                     BUTTONS
                ========================== -->

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

                        <i class="fa-solid fa-floppy-disk"></i>

                        Save Changes

                    </button>


                </div>


            </form>


        </div>


    </section>

</main>


<!-- =========================================
     LOT FIELD BEHAVIOR
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


    const isLot =
        propertyType.value
            .trim()
            .toLowerCase()
        === "lot";


    if (isLot) {

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


// Run immediately because the form
// already exists above this script.

toggleLotFields();

</script>


<?php

include "../includes/footer.php";

?>
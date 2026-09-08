<?php

session_start();

$pdo = require "../config/database.php";

$page_title = "Property Listings | MyHome";


// =========================================
// GET FILTER VALUES
// =========================================

$search = trim($_GET["search"] ?? "");

$type = trim($_GET["type"] ?? "");

$property_type = trim($_GET["property_type"] ?? "");

$min_price = trim($_GET["min_price"] ?? "");

$max_price = trim($_GET["max_price"] ?? "");

$bedrooms = trim($_GET["bedrooms"] ?? "");

$sort = trim($_GET["sort"] ?? "newest");


// =========================================
// ALLOWED VALUES
// =========================================

$property_types = [
    "House",
    "Apartment",
    "Condo",
    "Townhouse",
    "Lot"
];

$allowed_sorts = [
    "newest",
    "oldest",
    "price_low",
    "price_high"
];


// =========================================
// BASE QUERY
// =========================================

$sql = "
    SELECT
        p.*,

        (
            SELECT pi.image_path
            FROM property_images pi
            WHERE pi.property_id = p.id
            ORDER BY pi.id ASC
            LIMIT 1
        ) AS main_image

    FROM properties p

    WHERE p.status = 'available'
";


// PDO PARAMETERS

$params = [];


// =========================================
// SEARCH
// =========================================

if ($search !== "") {

    $sql .= "
        AND (
            p.title LIKE :search_title
            OR p.location LIKE :search_location
            OR p.property_type LIKE :search_property_type
        )
    ";

    $search_value =
        "%" . $search . "%";

    $params["search_title"] =
        $search_value;

    $params["search_location"] =
        $search_value;

    $params["search_property_type"] =
        $search_value;
}


// =========================================
// LISTING TYPE
// =========================================

if (
    in_array(
        $type,
        ["rent", "sale"],
        true
    )
) {

    $sql .= "
        AND p.listing_type = :listing_type
    ";

    $params["listing_type"] =
        $type;
}


// =========================================
// PROPERTY TYPE
// =========================================

if (
    $property_type !== "" &&
    in_array(
        $property_type,
        $property_types,
        true
    )
) {

    $sql .= "
        AND p.property_type = :property_type
    ";

    $params["property_type"] =
        $property_type;
}


// =========================================
// MINIMUM PRICE
// =========================================

if (
    $min_price !== "" &&
    is_numeric($min_price) &&
    (float) $min_price >= 0
) {

    $sql .= "
        AND p.price >= :min_price
    ";

    $params["min_price"] =
        (float) $min_price;
}


// =========================================
// MAXIMUM PRICE
// =========================================

if (
    $max_price !== "" &&
    is_numeric($max_price) &&
    (float) $max_price >= 0
) {

    $sql .= "
        AND p.price <= :max_price
    ";

    $params["max_price"] =
        (float) $max_price;
}


// =========================================
// BEDROOMS
// =========================================

if (
    $bedrooms !== "" &&
    ctype_digit($bedrooms) &&
    (int) $bedrooms >= 1
) {

    $sql .= "
        AND p.bedrooms >= :bedrooms
    ";

    $params["bedrooms"] =
        (int) $bedrooms;
}


// =========================================
// SORTING
// =========================================

if (!in_array($sort, $allowed_sorts, true)) {

    $sort = "newest";
}


switch ($sort) {

    case "oldest":

        $sql .= "
            ORDER BY p.created_at ASC
        ";

        break;


    case "price_low":

        $sql .= "
            ORDER BY p.price ASC
        ";

        break;


    case "price_high":

        $sql .= "
            ORDER BY p.price DESC
        ";

        break;


    case "newest":
    default:

        $sql .= "
            ORDER BY p.created_at DESC
        ";

        break;
}


// =========================================
// PREPARE + EXECUTE QUERY
// =========================================

try {

    $stmt = $pdo->prepare($sql);

    $stmt->execute($params);

    $properties =
        $stmt->fetchAll();

    $total_results =
        count($properties);

}

catch (PDOException $e) {

    $properties = [];

    $total_results = 0;
}


// =========================================
// HEADER
// =========================================

include "../includes/header.php";

include "../includes/navbar.php";

?>


<main class="public-listings-page">

    <section class="public-listings-container">


        <!-- =================================
             PAGE HEADER
        ================================== -->

        <div class="public-listings-header">

            <div>

                <p class="section-label">
                    EXPLORE PROPERTIES
                </p>

                <h1>
                    Property Listings
                </h1>

                <p>
                    Browse available homes for rent and sale.
                </p>

            </div>

        </div>


        <!-- =================================
             FILTER FORM
        ================================== -->

        <form
            method="GET"
            action="/myhome/properties/listings.php"
            class="property-filter-form"
        >


            <!-- SEARCH -->

            <div class="property-filter-search">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="text"
                    name="search"
                    placeholder="Search title, location, or property type"
                    value="<?php
                    echo htmlspecialchars($search);
                    ?>"
                >

            </div>


            <!-- LISTING TYPE -->

            <select name="type">

                <option value="">
                    All Listing Types
                </option>

                <option
                    value="rent"
                    <?php
                    echo $type === "rent"
                        ? "selected"
                        : "";
                    ?>
                >
                    For Rent
                </option>

                <option
                    value="sale"
                    <?php
                    echo $type === "sale"
                        ? "selected"
                        : "";
                    ?>
                >
                    For Sale
                </option>

            </select>


            <!-- PROPERTY TYPE -->

            <select name="property_type">

                <option value="">
                    All Property Types
                </option>


                <?php foreach ($property_types as $option): ?>

                    <option
                        value="<?php
                        echo htmlspecialchars($option);
                        ?>"
                        <?php
                        echo $property_type === $option
                            ? "selected"
                            : "";
                        ?>
                    >

                        <?php
                        echo htmlspecialchars($option);
                        ?>

                    </option>

                <?php endforeach; ?>


            </select>


            <!-- BEDROOMS -->

            <select name="bedrooms">

                <option value="">
                    Any Bedrooms
                </option>

                <?php for ($i = 1; $i <= 5; $i++): ?>

                    <option
                        value="<?php echo $i; ?>"
                        <?php
                        echo $bedrooms === (string) $i
                            ? "selected"
                            : "";
                        ?>
                    >

                        <?php echo $i; ?>+ Bedrooms

                    </option>

                <?php endfor; ?>

            </select>


            <!-- MINIMUM PRICE -->

            <input
                type="number"
                name="min_price"
                placeholder="Min Price"
                min="0"
                step="0.01"
                value="<?php
                echo htmlspecialchars($min_price);
                ?>"
            >


            <!-- MAXIMUM PRICE -->

            <input
                type="number"
                name="max_price"
                placeholder="Max Price"
                min="0"
                step="0.01"
                value="<?php
                echo htmlspecialchars($max_price);
                ?>"
            >


            <!-- SORT -->

            <select name="sort">

                <option
                    value="newest"
                    <?php
                    echo $sort === "newest"
                        ? "selected"
                        : "";
                    ?>
                >
                    Newest First
                </option>

                <option
                    value="oldest"
                    <?php
                    echo $sort === "oldest"
                        ? "selected"
                        : "";
                    ?>
                >
                    Oldest First
                </option>

                <option
                    value="price_low"
                    <?php
                    echo $sort === "price_low"
                        ? "selected"
                        : "";
                    ?>
                >
                    Price: Low to High
                </option>

                <option
                    value="price_high"
                    <?php
                    echo $sort === "price_high"
                        ? "selected"
                        : "";
                    ?>
                >
                    Price: High to Low
                </option>

            </select>


            <!-- FILTER BUTTONS -->

            <div class="property-filter-actions">

                <button type="submit">

                    <i class="fa-solid fa-filter"></i>

                    Filter

                </button>


                <a
                    href="/myhome/properties/listings.php"
                    class="clear-filter-btn"
                >

                    <i class="fa-solid fa-rotate-left"></i>

                    Clear

                </a>

            </div>


        </form>


        <!-- =================================
             RESULTS INFORMATION
        ================================== -->

        <div class="listing-results-info">

            <p>

                <strong>
                    <?php echo (int) $total_results; ?>
                </strong>

                <?php
                echo $total_results === 1
                    ? "property found"
                    : "properties found";
                ?>

            </p>

        </div>


        <!-- =================================
             PROPERTY RESULTS
        ================================== -->

        <?php if ($total_results > 0): ?>


            <div class="public-listings-grid">


                <?php foreach ($properties as $property): ?>


                    <article class="public-property-card">


                        <!-- PROPERTY IMAGE -->

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


                        <!-- PROPERTY CONTENT -->

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


                            <!-- PRICE + VIEW -->

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


                                <a
                                    href="/myhome/properties/view-property.php?id=<?php
                                    echo (int) $property["id"];
                                    ?>"
                                    class="view-property-btn"
                                >

                                    View Property

                                </a>


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

                <i class="fa-solid fa-house-circle-xmark"></i>

                <h2>
                    No properties found
                </h2>

                <p>
                    Try adjusting your search or filters.
                </p>


                <a
                    href="/myhome/properties/listings.php"
                    class="clear-filter-btn"
                >
                    Clear All Filters
                </a>

            </div>


        <?php endif; ?>


    </section>

</main>


<?php

include "../includes/footer.php";

?>
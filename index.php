<?php

session_start();

$pdo = require "config/database.php";


// =========================================
// GET LATEST AVAILABLE PROPERTIES
// =========================================

try {

    $property_stmt = $pdo->prepare(
        "SELECT
            p.id,
            p.title,
            p.listing_type,
            p.property_type,
            p.price,
            p.location,
            p.bedrooms,
            p.bathrooms,
            p.area,

            (
                SELECT pi.image_path
                FROM property_images pi
                WHERE pi.property_id = p.id
                ORDER BY pi.id ASC
                LIMIT 1
            ) AS main_image

         FROM properties p

         WHERE p.status = 'available'

         ORDER BY p.created_at DESC

         LIMIT 3"
    );

    $property_stmt->execute();

    $featured_properties =
        $property_stmt->fetchAll();

}

catch (PDOException $e) {

    $featured_properties = [];

}


// =========================================
// GET LATEST APPROVED REVIEWS
// =========================================

try {

    $review_stmt = $pdo->prepare(
        "SELECT
            r.rating,
            r.review,
            r.created_at,
            u.full_name

         FROM reviews r

         INNER JOIN users u
            ON u.id = r.user_id

         WHERE r.status = 'approved'

         ORDER BY r.created_at DESC

         LIMIT 3"
    );

    $review_stmt->execute();

    $approved_reviews =
        $review_stmt->fetchAll();

}

catch (PDOException $e) {

    $approved_reviews = [];

}


// =========================================
// PAGE TITLE
// =========================================

$page_title = "MyHome | Rent & Sale";

include "includes/header.php";
include "includes/navbar.php";

?>


<!-- =========================
     HERO SECTION
========================= -->

<section class="hero" id="home">

    <img
        src="/myhome/images/homepage.png"
        alt="Luxury modern house"
        class="hero-image"
    >

    <div class="hero-overlay"></div>


    <div class="hero-content">

        <p class="hero-small">
            FIND YOUR NEXT HOME
        </p>

        <h1>
            DISCOVER PREMIUM<br>
            PROPERTIES FOR<br>
            RENT AND SALE,
            <span>TAILORED TO YOUR LIFESTYLE.</span>
        </h1>

        <p class="hero-description">
            MyHome offers a trusted marketplace for premium rental
            and homes for sale. From comfortable family residences
            to luxury spaces, explore our latest listings and find
            the perfect place for your next chapter.
        </p>

        <a
            href="#listing"
            class="primary-btn"
        >
            RESERVE NOW
        </a>

    </div>

</section>



<!-- =========================
     INTRO SECTION
========================= -->

<section class="intro-section" id="about">

    <div class="decor-circle circle-one"></div>
    <div class="decor-circle circle-two"></div>


    <div class="section-heading">

        <p class="section-label">
            MYHOME RENT & SALE
        </p>

        <h2>
            TIRED OF ENDLESS LISTING AND<br>
            HIDDEN FEES?
        </h2>

        <p>
            Finding a home shouldn't be a full-time job.
            Skip the hidden fees, outdated listings, and endless hassle.
            At MyHome, we offer verified rentals and sales
            with complete price transparency.
        </p>

    </div>


    <!-- =========================
         FEATURE 1
    ========================== -->

    <div class="feature feature-left">

        <div class="feature-image">

            <img
                src="/myhome/images/p2.jpg"
                alt="Modern house"
            >

        </div>


        <div class="feature-content">

            <h3>
                FIND YOUR NEXT HOME WITH
                <span>COMPLETE TRUST AND TRANSPARENCY.</span>
            </h3>

            <p>
                Every property listing shows the complete details
                you need, including the price, location, and key
                features upfront with no hidden fees, outdated
                information, or unexpected costs when finding
                your next home.
            </p>


            <div class="check-grid">

                <div class="check-item">
                    <span>✓</span>
                    <p>Verified Property Listings</p>
                </div>

                <div class="check-item">
                    <span>✓</span>
                    <p>Easy Property Search</p>
                </div>

                <div class="check-item">
                    <span>✓</span>
                    <p>No Hidden Fees</p>
                </div>

                <div class="check-item">
                    <span>✓</span>
                    <p>Trusted Property Information</p>
                </div>

                <div class="check-item">
                    <span>✓</span>
                    <p>Complete Price Transparency</p>
                </div>

                <div class="check-item">
                    <span>✓</span>
                    <p>Faster Home Buying</p>
                </div>

            </div>

        </div>

    </div>



    <!-- =========================
         FEATURE 2
    ========================== -->

    <div class="feature feature-right">

        <div class="feature-content">

            <p class="feature-number">
                143+ VERIFIED HOMES THAT ARE READY FOR YOU
            </p>

            <h3>
                CAN'T FIND A HOME THAT FITS YOUR NEEDS?
                <span>NOT ON MYHOME.</span>
            </h3>

            <p>
                Every property is carefully reviewed, verified,
                and detailed before it reaches you, giving you
                accurate information and complete transparency
                every single time.
            </p>

            <a
                href="#listing"
                class="primary-btn"
            >
                RESERVE NOW
            </a>

        </div>


        <div class="feature-image">

            <img
                src="/myhome/images/p3.jpg"
                alt="Beautiful family home"
            >

        </div>

    </div>

</section>



<!-- =========================
     FAMILY / CTA SECTION
========================= -->

<section class="family-section">

    <div class="family-images">

        <img
            src="/myhome/images/family2.jpg"
            alt="Happy family"
            class="family-image image-one"
        >

        <img
            src="/myhome/images/family 1.jpg"
            alt="Family outside their home"
            class="family-image image-two"
        >

    </div>


    <div class="family-content">

        <p class="section-label">
            NO MORE WASTING TIME ON OUTDATED LISTINGS
        </p>

        <h2>
            FIND YOUR PERFECT HOME TODAY AND
            <span>START YOUR NEXT CHAPTER.</span>
        </h2>

        <p>
            Discover verified properties designed to match
            your needs, lifestyle, and budget.
        </p>

        <a
            href="#listing"
            class="primary-btn"
        >
            RESERVE NOW
        </a>

    </div>

</section>



<!-- =========================
     PROPERTY LISTING
========================= -->

<section class="listing-section" id="listing">

    <div class="listing-heading">

        <p class="section-label">
            FEATURED PROPERTIES
        </p>

        <h2>
            FIND YOUR <span>NEW HOME</span>
        </h2>

        <p>
            Explore our newest available properties.
        </p>

    </div>


    <?php if (count($featured_properties) > 0): ?>


        <div class="property-grid">


            <?php foreach ($featured_properties as $property): ?>


                <article class="property-card">


                    <!-- PROPERTY IMAGE -->

                    <div class="property-image">


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

                            <div class="property-no-image">

                                <i class="fa-solid fa-house"></i>

                            </div>

                        <?php endif; ?>


                        <!-- RENT / SALE TAG -->

                        <span
                            class="property-tag <?php
                            echo $property["listing_type"] === "rent"
                                ? "rent"
                                : "";
                            ?>"
                        >

                            <?php
                            echo $property["listing_type"] === "rent"
                                ? "FOR RENT"
                                : "FOR SALE";
                            ?>

                        </span>


                    </div>


                    <!-- PROPERTY INFORMATION -->

                    <div class="property-info">


                        <h3>

                            <?php
                            echo htmlspecialchars(
                                $property["title"]
                            );
                            ?>

                        </h3>


                        <p class="location">

                            <i class="fa-solid fa-location-dot"></i>

                            <?php
                            echo htmlspecialchars(
                                $property["location"]
                            );
                            ?>

                        </p>


                        <!-- DETAILS -->

                        <div class="property-details">


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

                        <div class="property-bottom">


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
                                class="property-view-btn"
                            >
                                VIEW
                            </a>


                        </div>


                    </div>


                </article>


            <?php endforeach; ?>


        </div>


        <!-- VIEW ALL -->

        <div class="featured-view-all">

            <a
                href="/myhome/properties/listings.php"
                class="primary-btn"
            >
                VIEW ALL PROPERTIES
            </a>

        </div>


    <?php else: ?>


        <div class="featured-empty">

            <i class="fa-solid fa-house"></i>

            <h3>
                No properties available
            </h3>

            <p>
                New property listings will appear here.
            </p>

        </div>


    <?php endif; ?>


</section>



<!-- =========================
     USER REVIEWS
========================= -->

<section class="testimonials-section">

    <div class="testimonial-heading">

        <p class="section-label">
            USER REVIEWS
        </p>

        <h2>
            HERE'S WHAT OUR USERS HAVE TO SAY
        </h2>

        <p class="testimonial-subtitle">
            Real experiences shared by members of the MyHome community.
        </p>

    </div>


    <?php if (count($approved_reviews) > 0): ?>


        <div class="testimonial-grid">


            <?php foreach ($approved_reviews as $review): ?>


                <article class="testimonial user-review-card">


                    <!-- USER ICON -->

                    <div class="testimonial-user-icon">

                        <i class="fa-solid fa-user"></i>

                    </div>


                    <!-- STAR RATING -->

                    <div class="stars">

                        <?php for ($i = 1; $i <= 5; $i++): ?>

                            <?php if (
                                $i <= (int) $review["rating"]
                            ): ?>

                                <i class="fa-solid fa-star"></i>

                            <?php else: ?>

                                <i class="fa-regular fa-star"></i>

                            <?php endif; ?>

                        <?php endfor; ?>

                    </div>


                    <!-- REVIEW -->

                    <p class="testimonial-review">

                        “<?php
                        echo nl2br(
                            htmlspecialchars(
                                $review["review"]
                            )
                        );
                        ?>”

                    </p>


                    <!-- USER -->

                    <div class="testimonial-user">

                        <h4>

                            <?php
                            echo htmlspecialchars(
                                $review["full_name"]
                            );
                            ?>

                        </h4>


                        <small>

                            MyHome User

                            <span>•</span>

                            <?php
                            echo date(
                                "M d, Y",
                                strtotime(
                                    $review["created_at"]
                                )
                            );
                            ?>

                        </small>

                    </div>


                </article>


            <?php endforeach; ?>


        </div>


    <?php else: ?>


        <div class="testimonial-empty">

            <i class="fa-regular fa-star"></i>

            <h3>
                No reviews yet
            </h3>

            <p>
                Approved user reviews will appear here.
            </p>

        </div>


    <?php endif; ?>


</section>


<?php

include "includes/footer.php";

?>
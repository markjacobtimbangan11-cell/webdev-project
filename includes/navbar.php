<?php

$current_page = basename($_SERVER["PHP_SELF"]);
$current_type = $_GET["type"] ?? "";

?>

<!-- =========================
     NAVIGATION
========================= -->

<header class="navbar">

    <div class="nav-container">

        <!-- LOGO -->
        <a href="/myhome/index.php" class="logo">
            <img src="/myhome/images/logo.png" alt="MyHome Logo">
        </a>


        <!-- MAIN NAVIGATION -->
        <nav class="nav-menu" id="navMenu">

            <a
                href="/myhome/index.php"
                class="nav-link <?php echo $current_page === "index.php" ? "active" : ""; ?>"
            >
                HOME
            </a>

            <a
                href="/myhome/properties/listings.php?type=rent"
                class="nav-link <?php echo ($current_page === "listings.php" && $current_type === "rent") ? "active" : ""; ?>"
            >
                RENT
            </a>

            <a
                href="/myhome/properties/listings.php?type=sale"
                class="nav-link <?php echo ($current_page === "listings.php" && $current_type === "sale") ? "active" : ""; ?>"
            >
                SALE
            </a>

            <a
                href="/myhome/properties/listings.php"
                class="nav-link <?php echo ($current_page === "listings.php" && $current_type === "") ? "active" : ""; ?>"
            >
                LISTING
            </a>

            <a href="/myhome/index.php#about" class="nav-link">
                ABOUT US
            </a>

            <a href="/myhome/index.php#footer" class="nav-link">
                CONTACT
            </a>


            <!-- MOBILE ACCOUNT AREA -->
            <div class="mobile-buttons">

                <?php if (isset($_SESSION["user_id"])): ?>

                    <?php if (
                        isset($_SESSION["role"]) &&
                        $_SESSION["role"] === "admin"
                    ): ?>

                        <a
                            href="/myhome/admin/dashboard.php"
                            class="login-btn"
                        >
                            <i class="fa-solid fa-gauge-high"></i>
                            Admin Dashboard
                        </a>

                        <a
                            href="/myhome/profile.php"
                            class="login-btn"
                        >
                            <i class="fa-solid fa-user"></i>

                            <?php
                                echo htmlspecialchars(
                                    $_SESSION["full_name"]
                                );
                            ?>
                        </a>

                    <?php else: ?>

                        <a
                            href="/myhome/profile.php"
                            class="login-btn"
                        >
                            <i class="fa-solid fa-user"></i>

                            <?php
                                echo htmlspecialchars(
                                    $_SESSION["full_name"]
                                );
                            ?>
                        </a>

                        <a
                            href="/myhome/properties/my-listings.php"
                            class="login-btn"
                        >
                            <i class="fa-solid fa-house"></i>
                            My Listings
                        </a>

                        <a
                            href="/myhome/favorites.php"
                            class="login-btn"
                        >
                            <i class="fa-solid fa-heart"></i>
                            Favorites
                        </a>

                        <a
                            href="/myhome/inquiries.php"
                            class="login-btn"
                        >
                            <i class="fa-solid fa-envelope"></i>
                            Inquiries
                        </a>

                        <a
                            href="/myhome/reviews.php"
                            class="login-btn"
                        >
                            <i class="fa-solid fa-star"></i>
                            Leave a Review
                        </a>

                    <?php endif; ?>

                    <a
                        href="/myhome/logout.php"
                        class="register-btn"
                    >
                        <i class="fa-solid fa-right-from-bracket"></i>
                        Logout
                    </a>

                <?php else: ?>

                    <a
                        href="/myhome/login.php"
                        class="login-btn"
                    >
                        <i class="fa-solid fa-circle-user"></i>
                        Login
                    </a>

                    <a
                        href="/myhome/register.php"
                        class="register-btn"
                    >
                        <i class="fa-solid fa-user-plus"></i>
                        Register
                    </a>

                <?php endif; ?>

            </div>

        </nav>


        <!-- DESKTOP ACCOUNT AREA -->
        <div class="nav-buttons">

            <?php if (isset($_SESSION["user_id"])): ?>

                <div class="account-menu">

                    <details class="account-details">

                        <summary class="account-btn">

                            <i class="fa-solid fa-circle-user"></i>

                            <span>
                                <?php
                                    echo htmlspecialchars(
                                        $_SESSION["full_name"]
                                    );
                                ?>
                            </span>

                            <i class="fa-solid fa-chevron-down"></i>

                        </summary>


                        <div class="account-dropdown">

                            <?php if (
                                isset($_SESSION["role"]) &&
                                $_SESSION["role"] === "admin"
                            ): ?>

                                <a href="/myhome/admin/dashboard.php">
                                    <i class="fa-solid fa-gauge-high"></i>
                                    Admin Dashboard
                                </a>

                                <a href="/myhome/profile.php">
                                    <i class="fa-solid fa-user"></i>
                                    My Profile
                                </a>

                            <?php else: ?>

                                <a href="/myhome/profile.php">
                                    <i class="fa-solid fa-user"></i>
                                    My Profile
                                </a>

                                <a href="/myhome/properties/my-listings.php">
                                    <i class="fa-solid fa-house"></i>
                                    My Listings
                                </a>

                                <a href="/myhome/favorites.php">
                                    <i class="fa-solid fa-heart"></i>
                                    Favorites
                                </a>

                                <a href="/myhome/inquiries.php">
                                    <i class="fa-solid fa-envelope"></i>
                                    Inquiries
                                </a>

                                <a href="/myhome/reviews.php">
                                    <i class="fa-solid fa-star"></i>
                                    Leave a Review
                                </a>

                            <?php endif; ?>


                            <div class="dropdown-divider"></div>


                            <a
                                href="/myhome/logout.php"
                                class="logout-link"
                            >
                                <i class="fa-solid fa-right-from-bracket"></i>
                                Logout
                            </a>

                        </div>

                    </details>

                </div>

            <?php else: ?>

                <a
                    href="/myhome/login.php"
                    class="login-btn"
                >
                    <i class="fa-solid fa-circle-user"></i>
                    Login
                </a>

                <a
                    href="/myhome/register.php"
                    class="register-btn"
                >
                    <i class="fa-solid fa-user-plus"></i>
                    Register
                </a>

            <?php endif; ?>

        </div>


        <!-- MOBILE MENU BUTTON -->
        <button
            class="menu-toggle"
            id="menuToggle"
            type="button"
            aria-label="Open menu"
        >
            <i class="fa-solid fa-bars"></i>
        </button>

    </div>

</header>
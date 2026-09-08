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
// DASHBOARD COUNTS
// =========================================

try {


    // =====================================
    // TOTAL USERS
    // =====================================

    $stmt = $pdo->query(
        "SELECT COUNT(*)
         FROM users
         WHERE role = 'user'"
    );

    $total_users =
        (int) $stmt->fetchColumn();


    // =====================================
    // TOTAL PROPERTIES
    // =====================================

    $stmt = $pdo->query(
        "SELECT COUNT(*)
         FROM properties"
    );

    $total_properties =
        (int) $stmt->fetchColumn();


    // =====================================
    // AVAILABLE PROPERTIES
    // =====================================

    $stmt = $pdo->query(
        "SELECT COUNT(*)
         FROM properties
         WHERE status = 'available'"
    );

    $total_available =
        (int) $stmt->fetchColumn();


    // =====================================
    // TOTAL INQUIRIES
    // =====================================

    $stmt = $pdo->query(
        "SELECT COUNT(*)
         FROM inquiries"
    );

    $total_inquiries =
        (int) $stmt->fetchColumn();


}

catch (PDOException $e) {

    $total_users = 0;
    $total_properties = 0;
    $total_available = 0;
    $total_inquiries = 0;
}


// =========================================
// PAGE TITLE
// =========================================

$page_title =
    "Admin Dashboard | MyHome";


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
             MAIN CONTENT
        ================================== -->

        <section class="admin-content">


            <!-- =================================
                 HEADER
            ================================== -->

            <div class="admin-header">


                <div>

                    <p class="section-label">
                        OVERVIEW
                    </p>

                    <h1>
                        Admin Dashboard
                    </h1>

                    <p>
                        Manage MyHome users, properties,
                        and platform activity.
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
                 STAT CARDS
            ================================== -->

            <div class="admin-stats">


                <!-- TOTAL USERS -->

                <a
                    href="/myhome/admin/users.php"
                    class="admin-stat-card admin-stat-link"
                >

                    <div class="admin-stat-icon">

                        <i class="fa-solid fa-users"></i>

                    </div>


                    <div class="admin-stat-info">

                        <span>
                            Total Users
                        </span>

                        <strong>

                            <?php
                            echo $total_users;
                            ?>

                        </strong>


                        <small>

                            Manage users

                            <i class="fa-solid fa-arrow-right"></i>

                        </small>

                    </div>

                </a>


                <!-- TOTAL PROPERTIES -->

                <a
                    href="/myhome/admin/properties.php"
                    class="admin-stat-card admin-stat-link"
                >

                    <div class="admin-stat-icon">

                        <i class="fa-solid fa-house"></i>

                    </div>


                    <div class="admin-stat-info">

                        <span>
                            Total Properties
                        </span>

                        <strong>

                            <?php
                            echo $total_properties;
                            ?>

                        </strong>


                        <small>

                            Manage properties

                            <i class="fa-solid fa-arrow-right"></i>

                        </small>

                    </div>

                </a>


                <!-- AVAILABLE PROPERTIES -->

                <a
                    href="/myhome/admin/properties.php"
                    class="admin-stat-card admin-stat-link"
                >

                    <div class="admin-stat-icon">

                        <i class="fa-solid fa-circle-check"></i>

                    </div>


                    <div class="admin-stat-info">

                        <span>
                            Available
                        </span>

                        <strong>

                            <?php
                            echo $total_available;
                            ?>

                        </strong>


                        <small>

                            View listings

                            <i class="fa-solid fa-arrow-right"></i>

                        </small>

                    </div>

                </a>


                <!-- INQUIRIES -->

                <a
                    href="/myhome/admin/inquiries.php"
                    class="admin-stat-card admin-stat-link"
                >

                    <div class="admin-stat-icon">

                        <i class="fa-solid fa-envelope"></i>

                    </div>


                    <div class="admin-stat-info">

                        <span>
                            Inquiries
                        </span>

                        <strong>

                            <?php
                            echo $total_inquiries;
                            ?>

                        </strong>


                        <small>

                            View inquiries

                            <i class="fa-solid fa-arrow-right"></i>

                        </small>

                    </div>

                </a>


            </div>


            <!-- =================================
                 WELCOME PANEL
            ================================== -->

            <div class="admin-panel">


                <div class="admin-panel-heading">


                    <div>

                        <h2>
                            Welcome to MyHome Admin
                        </h2>

                        <p>
                            Use the dashboard or sidebar to manage
                            users, property listings, inquiries,
                            and reviews.
                        </p>

                    </div>


                </div>


            </div>


        </section>


    </div>

</main>
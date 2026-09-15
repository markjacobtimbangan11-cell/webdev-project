<?php

$current_page = basename($_SERVER["PHP_SELF"]);

?>

<aside class="admin-sidebar">

    <div class="admin-logo">

        <img
            src="/myhome/images/logo.png"
            alt="MyHome Logo"
        >

        <span>
            ADMIN PANEL
        </span>

    </div>


    <nav class="admin-nav">


        <!-- DASHBOARD -->

        <a
            href="/myhome/admin/dashboard.php"
            class="<?= $current_page === "dashboard.php"
                ? "active"
                : "" ?>"
        >
            <i class="fa-solid fa-chart-line"></i>
            Dashboard
        </a>


        <!-- USERS -->

        <a
            href="/myhome/admin/users.php"
            class="<?= $current_page === "users.php"
                ? "active"
                : "" ?>"
        >
            <i class="fa-solid fa-users"></i>
            Users
        </a>


        <!-- PROPERTIES -->

        <a
            href="/myhome/admin/properties.php"
            class="<?= $current_page === "properties.php"
                ? "active"
                : "" ?>"
        >
            <i class="fa-solid fa-house"></i>
            Properties
        </a>


        <!-- INQUIRIES -->

        <a
            href="/myhome/admin/inquiries.php"
            class="<?= $current_page === "inquiries.php"
                ? "active"
                : "" ?>"
        >
            <i class="fa-solid fa-envelope"></i>
            Inquiries
        </a>


        <!-- REVIEWS -->

        <a
            href="/myhome/admin/reviews.php"
            class="<?= $current_page === "reviews.php"
                ? "active"
                : "" ?>"
        >
            <i class="fa-solid fa-star"></i>
            Reviews
        </a>


        <!-- TRANSACTIONS -->

        <a
            href="/myhome/admin/transactions.php"
            class="<?= $current_page === "transactions.php"
                ? "active"
                : "" ?>"
        >
            <i class="fa-solid fa-right-left"></i>
            Transactions
        </a>


        <!-- VIEW WEBSITE -->

        <a href="/myhome/index.php">
            <i class="fa-solid fa-globe"></i>
            View Website
        </a>


    </nav>


    <!-- LOGOUT -->

    <a
        href="/myhome/logout.php"
        class="admin-logout"
    >
        <i class="fa-solid fa-right-from-bracket"></i>
        Logout
    </a>

</aside>

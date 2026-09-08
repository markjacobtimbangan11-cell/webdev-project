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

        <a
            href="/myhome/admin/dashboard.php"
            class="<?php
            echo $current_page === "dashboard.php"
                ? "active"
                : "";
            ?>"
        >

            <i class="fa-solid fa-chart-line"></i>

            Dashboard

        </a>


        <a
            href="/myhome/admin/users.php"
            class="<?php
            echo $current_page === "users.php"
                ? "active"
                : "";
            ?>"
        >

            <i class="fa-solid fa-users"></i>

            Users

        </a>


        <a
            href="/myhome/admin/properties.php"
            class="<?php
            echo $current_page === "properties.php"
                ? "active"
                : "";
            ?>"
        >

            <i class="fa-solid fa-house"></i>

            Properties

        </a>


        <a
            href="/myhome/admin/inquiries.php"
            class="<?php
            echo $current_page === "inquiries.php"
                ? "active"
                : "";
            ?>"
        >

            <i class="fa-solid fa-envelope"></i>

            Inquiries

        </a>


        <a
            href="/myhome/admin/reviews.php"
            class="<?php
            echo $current_page === "reviews.php"
                ? "active"
                : "";
            ?>"
        >

            <i class="fa-solid fa-star"></i>

            Reviews

        </a>


        <a href="/myhome/index.php">

            <i class="fa-solid fa-globe"></i>

            View Website

        </a>

    </nav>


    <a
        href="/myhome/logout.php"
        class="admin-logout"
    >

        <i class="fa-solid fa-right-from-bracket"></i>

        Logout

    </a>

</aside>
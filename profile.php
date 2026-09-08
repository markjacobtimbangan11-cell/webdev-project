<?php

session_start();

$pdo = require "config/database.php";


// =========================================
// LOGIN REQUIRED
// =========================================

if (!isset($_SESSION["user_id"])) {

    header("Location: /myhome/login.php");

    exit;
}


$user_id =
    (int) $_SESSION["user_id"];


// =========================================
// GET USER INFORMATION
// =========================================

try {

    $stmt = $pdo->prepare(
        "SELECT
            id,
            full_name,
            email,
            role,
            created_at
         FROM users
         WHERE id = :user_id
         LIMIT 1"
    );


    $stmt->execute([
        "user_id" => $user_id
    ]);


    $user =
        $stmt->fetch();


    if (!$user) {

        header(
            "Location: /myhome/logout.php"
        );

        exit;
    }

}

catch (PDOException $e) {

    header(
        "Location: /myhome/logout.php"
    );

    exit;
}


// =========================================
// PAGE TITLE
// =========================================

$page_title =
    "My Profile | MyHome";


include "includes/header.php";

include "includes/navbar.php";

?>


<main class="profile-page">


    <!-- =========================
         PROFILE UPDATED MESSAGE
    ========================== -->

    <?php if (
        isset($_GET["updated"]) &&
        $_GET["updated"] === "1"
    ): ?>

        <div class="profile-success-message">

            <i class="fa-solid fa-circle-check"></i>

            <span>
                Your profile has been updated successfully.
            </span>

        </div>

    <?php endif; ?>


    <!-- =========================
         PASSWORD CHANGED MESSAGE
    ========================== -->

    <?php if (
        isset($_GET["password_changed"]) &&
        $_GET["password_changed"] === "1"
    ): ?>

        <div class="profile-success-message">

            <i class="fa-solid fa-circle-check"></i>

            <span>
                Your password has been changed successfully.
            </span>

        </div>

    <?php endif; ?>


    <section class="profile-container">


        <!-- =========================
             PROFILE HEADER
        ========================== -->

        <div class="profile-header">

            <p class="section-label">
                ACCOUNT
            </p>

            <h1>
                My Profile
            </h1>

            <p>
                View and manage your MyHome account information.
            </p>

        </div>


        <div class="profile-grid">


            <!-- =========================
                 PROFILE CARD
            ========================== -->

            <div class="profile-card">


                <div class="profile-avatar">

                    <i class="fa-solid fa-user"></i>

                </div>


                <h2>

                    <?php
                    echo htmlspecialchars(
                        $user["full_name"]
                    );
                    ?>

                </h2>


                <p class="profile-email">

                    <?php
                    echo htmlspecialchars(
                        $user["email"]
                    );
                    ?>

                </p>


                <span class="profile-role">

                    <?php
                    echo ucfirst(
                        htmlspecialchars(
                            $user["role"]
                        )
                    );
                    ?>

                </span>


            </div>


            <!-- =========================
                 ACCOUNT INFORMATION
            ========================== -->

            <div class="profile-info-card">


                <div class="profile-info-heading">

                    <div>

                        <h2>
                            Account Information
                        </h2>

                        <p>
                            Your current account details.
                        </p>

                    </div>

                </div>


                <div class="profile-info-list">


                    <!-- FULL NAME -->

                    <div class="profile-info-row">

                        <div class="profile-info-icon">

                            <i class="fa-solid fa-user"></i>

                        </div>


                        <div>

                            <span>
                                Full Name
                            </span>

                            <strong>

                                <?php
                                echo htmlspecialchars(
                                    $user["full_name"]
                                );
                                ?>

                            </strong>

                        </div>

                    </div>


                    <!-- EMAIL -->

                    <div class="profile-info-row">

                        <div class="profile-info-icon">

                            <i class="fa-solid fa-envelope"></i>

                        </div>


                        <div>

                            <span>
                                Email Address
                            </span>

                            <strong>

                                <?php
                                echo htmlspecialchars(
                                    $user["email"]
                                );
                                ?>

                            </strong>

                        </div>

                    </div>


                    <!-- ROLE -->

                    <div class="profile-info-row">

                        <div class="profile-info-icon">

                            <i class="fa-solid fa-shield-halved"></i>

                        </div>


                        <div>

                            <span>
                                Account Role
                            </span>

                            <strong>

                                <?php
                                echo ucfirst(
                                    htmlspecialchars(
                                        $user["role"]
                                    )
                                );
                                ?>

                            </strong>

                        </div>

                    </div>


                    <!-- MEMBER SINCE -->

                    <div class="profile-info-row">

                        <div class="profile-info-icon">

                            <i class="fa-solid fa-calendar"></i>

                        </div>


                        <div>

                            <span>
                                Member Since
                            </span>

                            <strong>

                                <?php

                                $created_at =
                                    new DateTime(
                                        $user["created_at"]
                                    );

                                echo $created_at->format(
                                    "F j, Y"
                                );

                                ?>

                            </strong>

                        </div>

                    </div>


                </div>


                <!-- =========================
                     PROFILE ACTIONS
                ========================== -->

                <div class="profile-actions">


                    <a
                        href="/myhome/edit-profile.php"
                        class="profile-primary-btn"
                    >

                        <i class="fa-solid fa-pen"></i>

                        Edit Profile

                    </a>


                    <a
                        href="/myhome/change-password.php"
                        class="profile-secondary-btn"
                    >

                        <i class="fa-solid fa-lock"></i>

                        Change Password

                    </a>


                </div>


            </div>


        </div>


    </section>


</main>


<?php

include "includes/footer.php";

?>
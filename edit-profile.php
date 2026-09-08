<?php

session_start();

$pdo = require "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: /myhome/login.php");
    exit;
}

$user_id = (int) $_SESSION["user_id"];

$message = "";
$message_type = "";


// =========================
// GET CURRENT USER
// =========================

$stmt = $pdo->prepare(
    "SELECT
        full_name,
        email
     FROM users
     WHERE id = :id
     LIMIT 1"
);

$stmt->execute([
    "id" => $user_id
]);

$user = $stmt->fetch();


if (!$user) {

    header("Location: /myhome/logout.php");
    exit;
}


$full_name = $user["full_name"];
$email = $user["email"];


// =========================
// UPDATE PROFILE
// =========================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name =
        trim($_POST["full_name"] ?? "");

    $email =
        trim($_POST["email"] ?? "");


    // Remove repeated spaces

    $full_name = preg_replace(
        '/\s+/',
        ' ',
        $full_name
    );


    // BASIC VALIDATION

    if (
        $full_name === "" ||
        $email === ""
    ) {

        $message =
            "Please fill in all fields.";

        $message_type =
            "error";

    }

    elseif (
        strlen($full_name) < 2 ||
        strlen($full_name) > 20
    ) {

        $message =
            "Full name must be between 2 and 20 characters.";

        $message_type =
            "error";

    }

    elseif (
        !preg_match(
            "/^[A-Za-zÀ-ÿ' -]+$/u",
            $full_name
        )
    ) {

        $message =
            "Full name may only contain letters, spaces, apostrophes, and hyphens.";

        $message_type =
            "error";

    }

    elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $message =
            "Please enter a valid email address.";

        $message_type =
            "error";

    }

    else {

        try {


            // =========================
            // CHECK EMAIL
            // =========================

            $check = $pdo->prepare(
                "SELECT id
                 FROM users
                 WHERE email = :email
                 AND id != :id
                 LIMIT 1"
            );

            $check->execute([
                "email" => $email,
                "id" => $user_id
            ]);

            $existing_user =
                $check->fetch();


            if ($existing_user) {

                $message =
                    "That email address is already in use.";

                $message_type =
                    "error";

            }

            else {


                // =========================
                // UPDATE USER
                // =========================

                $update = $pdo->prepare(
                    "UPDATE users
                     SET
                        full_name = :full_name,
                        email = :email
                     WHERE id = :id"
                );

                $update->execute([
                    "full_name" => $full_name,
                    "email" => $email,
                    "id" => $user_id
                ]);


                // UPDATE SESSION TOO

                $_SESSION["full_name"] =
                    $full_name;

                $_SESSION["email"] =
                    $email;


                header(
                    "Location: /myhome/profile.php?updated=1"
                );

                exit;

            }

        }

        catch (PDOException $e) {

            $message =
                "Failed to update your profile. Please try again.";

            $message_type =
                "error";

        }

    }

}


$page_title = "Edit Profile | MyHome";

include "includes/header.php";
include "includes/navbar.php";

?>

<main class="profile-page">

    <section class="profile-container">

        <div class="profile-header">

            <p class="section-label">
                ACCOUNT
            </p>

            <h1>
                Edit Profile
            </h1>

            <p>
                Update your personal account information.
            </p>

        </div>


        <div class="profile-info-card">


            <?php if ($message !== ""): ?>

                <div
                    class="property-form-message
                    <?php echo $message_type; ?>"
                >

                    <i class="fa-solid fa-circle-exclamation"></i>

                    <span>
                        <?php
                        echo htmlspecialchars(
                            $message
                        );
                        ?>
                    </span>

                </div>

            <?php endif; ?>


            <form
                method="POST"
                action="/myhome/edit-profile.php"
                class="profile-edit-form"
            >


                <!-- FULL NAME -->

                <div class="profile-edit-group">

                    <label for="full_name">
                        Full Name
                        <span>*</span>
                    </label>

                    <input
                        type="text"
                        id="full_name"
                        name="full_name"
                        value="<?php
                        echo htmlspecialchars(
                            $full_name
                        );
                        ?>"
                        minlength="2"
                        maxlength="20"
                        required
                    >

                    <small>
                        Maximum of 20 characters.
                    </small>

                </div>


                <!-- EMAIL -->

                <div class="profile-edit-group">

                    <label for="email">
                        Email Address
                        <span>*</span>
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?php
                        echo htmlspecialchars(
                            $email
                        );
                        ?>"
                        required
                    >

                </div>


                <!-- BUTTONS -->

                <div class="profile-edit-actions">

                    <a
                        href="/myhome/profile.php"
                        class="profile-secondary-btn"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="profile-primary-btn"
                    >
                        <i class="fa-solid fa-floppy-disk"></i>
                        Save Changes
                    </button>

                </div>

            </form>

        </div>

    </section>

</main>


<?php

include "includes/footer.php";

?>
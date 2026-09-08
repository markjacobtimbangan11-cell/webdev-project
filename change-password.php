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
// CHANGE PASSWORD
// =========================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $current_password =
        $_POST["current_password"] ?? "";

    $new_password =
        $_POST["new_password"] ?? "";

    $confirm_password =
        $_POST["confirm_password"] ?? "";


    // =========================
    // BASIC VALIDATION
    // =========================

    if (
        $current_password === "" ||
        $new_password === "" ||
        $confirm_password === ""
    ) {

        $message =
            "Please fill in all password fields.";

        $message_type =
            "error";

    }

    elseif (strlen($new_password) < 8) {

        $message =
            "New password must be at least 8 characters.";

        $message_type =
            "error";

    }

    elseif (strlen($new_password) > 72) {

        $message =
            "New password must not exceed 72 characters.";

        $message_type =
            "error";

    }

    elseif (
        !preg_match(
            "/[A-Z]/",
            $new_password
        )
    ) {

        $message =
            "New password must contain at least one uppercase letter.";

        $message_type =
            "error";

    }

    elseif (
        !preg_match(
            "/[a-z]/",
            $new_password
        )
    ) {

        $message =
            "New password must contain at least one lowercase letter.";

        $message_type =
            "error";

    }

    elseif (
        !preg_match(
            "/[0-9]/",
            $new_password
        )
    ) {

        $message =
            "New password must contain at least one number.";

        $message_type =
            "error";

    }

    elseif (
        !preg_match(
            "/[^A-Za-z0-9]/",
            $new_password
        )
    ) {

        $message =
            "New password must contain at least one special character.";

        $message_type =
            "error";

    }

    elseif (
        $new_password !==
        $confirm_password
    ) {

        $message =
            "New password and confirmation do not match.";

        $message_type =
            "error";

    }

    else {

        try {


            // =========================
            // GET CURRENT PASSWORD HASH
            // =========================

            $stmt = $pdo->prepare(
                "SELECT password
                 FROM users
                 WHERE id = :id
                 LIMIT 1"
            );

            $stmt->execute([
                "id" => $user_id
            ]);

            $user = $stmt->fetch();


            if (!$user) {

                header(
                    "Location: /myhome/logout.php"
                );

                exit;
            }


            // =========================
            // VERIFY CURRENT PASSWORD
            // =========================

            if (
                !password_verify(
                    $current_password,
                    $user["password"]
                )
            ) {

                $message =
                    "Current password is incorrect.";

                $message_type =
                    "error";

            }

            elseif (
                password_verify(
                    $new_password,
                    $user["password"]
                )
            ) {

                $message =
                    "New password must be different from your current password.";

                $message_type =
                    "error";

            }

            else {


                // =========================
                // HASH NEW PASSWORD
                // =========================

                $new_password_hash =
                    password_hash(
                        $new_password,
                        PASSWORD_DEFAULT
                    );


                // =========================
                // UPDATE PASSWORD
                // =========================

                $update = $pdo->prepare(
                    "UPDATE users
                     SET password = :password
                     WHERE id = :id"
                );

                $update->execute([
                    "password" =>
                        $new_password_hash,

                    "id" =>
                        $user_id
                ]);


                header(
                    "Location: /myhome/profile.php?password_changed=1"
                );

                exit;
            }

        }

        catch (PDOException $e) {

            $message =
                "Failed to change password. Please try again.";

            $message_type =
                "error";
        }
    }
}


$page_title =
    "Change Password | MyHome";

include "includes/header.php";
include "includes/navbar.php";

?>


<main class="profile-page">

    <section class="profile-container">


        <div class="profile-header">

            <p class="section-label">
                ACCOUNT SECURITY
            </p>

            <h1>
                Change Password
            </h1>

            <p>
                Update your account password securely.
            </p>

        </div>


        <div class="profile-info-card">


            <?php if ($message !== ""): ?>

                <div
                    class="property-form-message
                    <?php echo $message_type; ?>"
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


            <form
                method="POST"
                action="/myhome/change-password.php"
                class="profile-edit-form"
            >


                <!-- CURRENT PASSWORD -->

                <div class="profile-edit-group">

                    <label for="current_password">

                        Current Password
                        <span>*</span>

                    </label>


                    <div class="password-field">

                        <input
                            type="password"
                            id="current_password"
                            name="current_password"
                            placeholder="Enter current password"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            data-target="current_password"
                            aria-label="Show password"
                        >

                            <i
                                class="fa-regular fa-eye"
                            ></i>

                        </button>

                    </div>

                </div>


                <!-- NEW PASSWORD -->

                <div class="profile-edit-group">

                    <label for="new_password">

                        New Password
                        <span>*</span>

                    </label>


                    <div class="password-field">

                        <input
                            type="password"
                            id="new_password"
                            name="new_password"
                            placeholder="Enter new password"
                            minlength="8"
                            maxlength="72"
                            autocomplete="new-password"
                            required
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            data-target="new_password"
                            aria-label="Show password"
                        >

                            <i
                                class="fa-regular fa-eye"
                            ></i>

                        </button>

                    </div>


                    <div class="password-requirements">

                        <span>
                            <i class="fa-solid fa-xmark"></i>
                            At least 8 characters
                        </span>

                        <span>
                            <i class="fa-solid fa-xmark"></i>
                            One uppercase letter
                        </span>

                        <span>
                            <i class="fa-solid fa-xmark"></i>
                            One lowercase letter
                        </span>

                        <span>
                            <i class="fa-solid fa-xmark"></i>
                            One number
                        </span>

                        <span>
                            <i class="fa-solid fa-xmark"></i>
                            One special character
                        </span>

                    </div>

                </div>


                <!-- CONFIRM PASSWORD -->

                <div class="profile-edit-group">

                    <label for="confirm_password">

                        Confirm New Password
                        <span>*</span>

                    </label>


                    <div class="password-field">

                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            placeholder="Confirm new password"
                            minlength="8"
                            maxlength="72"
                            autocomplete="new-password"
                            required
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            data-target="confirm_password"
                            aria-label="Show password"
                        >

                            <i
                                class="fa-regular fa-eye"
                            ></i>

                        </button>

                    </div>

                    <small
                        id="passwordMatchMessage"
                    ></small>

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

                        <i class="fa-solid fa-lock"></i>

                        Change Password

                    </button>

                </div>

            </form>

        </div>

    </section>

</main>


<script>

    // =========================
    // PASSWORD SHOW / HIDE
    // =========================

    const passwordToggles =
        document.querySelectorAll(
            ".password-toggle"
        );


    passwordToggles.forEach(
        function (button) {

            button.addEventListener(
                "click",
                function () {

                    const targetId =
                        button.getAttribute(
                            "data-target"
                        );

                    const input =
                        document.getElementById(
                            targetId
                        );

                    const icon =
                        button.querySelector("i");


                    if (
                        input.type === "password"
                    ) {

                        input.type = "text";

                        icon.className =
                            "fa-regular fa-eye-slash";

                    }

                    else {

                        input.type = "password";

                        icon.className =
                            "fa-regular fa-eye";

                    }

                }
            );

        }
    );


    // =========================
    // PASSWORD REQUIREMENTS
    // =========================

    const newPassword =
        document.getElementById(
            "new_password"
        );

    const confirmPassword =
        document.getElementById(
            "confirm_password"
        );

    const requirements =
        document.querySelectorAll(
            ".password-requirements span"
        );

    const passwordMatchMessage =
        document.getElementById(
            "passwordMatchMessage"
        );


    newPassword.addEventListener(
        "input",
        function () {

            const value =
                newPassword.value;


            updateRequirement(
                requirements[0],
                value.length >= 8
            );

            updateRequirement(
                requirements[1],
                /[A-Z]/.test(value)
            );

            updateRequirement(
                requirements[2],
                /[a-z]/.test(value)
            );

            updateRequirement(
                requirements[3],
                /[0-9]/.test(value)
            );

            updateRequirement(
                requirements[4],
                /[^A-Za-z0-9]/.test(value)
            );


            checkPasswordMatch();

        }
    );


    confirmPassword.addEventListener(
        "input",
        checkPasswordMatch
    );


    function updateRequirement(
        element,
        valid
    ) {

        const icon =
            element.querySelector("i");


        if (valid) {

            element.classList.add(
                "valid"
            );

            icon.className =
                "fa-solid fa-check";

        }

        else {

            element.classList.remove(
                "valid"
            );

            icon.className =
                "fa-solid fa-xmark";

        }

    }


    function checkPasswordMatch() {

        if (
            confirmPassword.value === ""
        ) {

            passwordMatchMessage.textContent =
                "";

            return;
        }


        if (
            newPassword.value ===
            confirmPassword.value
        ) {

            passwordMatchMessage.innerHTML =
                '<i class="fa-solid fa-check"></i> Passwords match';

            passwordMatchMessage.className =
                "password-match match";

        }

        else {

            passwordMatchMessage.innerHTML =
                '<i class="fa-solid fa-xmark"></i> Passwords do not match';

            passwordMatchMessage.className =
                "password-match no-match";

        }

    }

</script>


<?php

include "includes/footer.php";

?>
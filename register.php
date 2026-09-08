<?php

$pdo = require "config/database.php";
$message = "";
$message_type = "";

$full_name = "";
$email = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {


    // =========================
    // GET FORM DATA
    // =========================

    $full_name = trim(
        $_POST["full_name"] ?? ""
    );

    $email = trim(
        $_POST["email"] ?? ""
    );

    $password =
        $_POST["password"] ?? "";

    $confirm_password =
        $_POST["confirm_password"] ?? "";


    // Remove multiple spaces from name
    $full_name = preg_replace(
        '/\s+/',
        ' ',
        $full_name
    );


    // =========================
    // VALIDATION
    // =========================

    if (
        empty($full_name) ||
        empty($email) ||
        empty($password) ||
        empty($confirm_password)
    ) {

        $message =
            "Please fill in all fields.";

        $message_type =
            "error";

    }


    // FULL NAME LENGTH

    elseif (
        strlen($full_name) < 2 ||
        strlen($full_name) > 20
    ) {

        $message =
            "Full name must be between 2 and 20 characters.";

        $message_type =
            "error";

    }


    // FULL NAME CHARACTERS

    elseif (
        !preg_match(
            "/^[a-zA-ZÀ-ÿ' -]+$/u",
            $full_name
        )
    ) {

        $message =
            "Full name can only contain letters, spaces, apostrophes, and hyphens.";

        $message_type =
            "error";

    }


    // EMAIL

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


    // PASSWORD LENGTH

    elseif (strlen($password) < 8) {

        $message =
            "Password must be at least 8 characters.";

        $message_type =
            "error";

    }


    elseif (strlen($password) > 72) {

        $message =
            "Password must not exceed 72 characters.";

        $message_type =
            "error";

    }


    // UPPERCASE

    elseif (
        !preg_match(
            '/[A-Z]/',
            $password
        )
    ) {

        $message =
            "Password must contain at least one uppercase letter.";

        $message_type =
            "error";

    }


    // LOWERCASE

    elseif (
        !preg_match(
            '/[a-z]/',
            $password
        )
    ) {

        $message =
            "Password must contain at least one lowercase letter.";

        $message_type =
            "error";

    }


    // NUMBER

    elseif (
        !preg_match(
            '/[0-9]/',
            $password
        )
    ) {

        $message =
            "Password must contain at least one number.";

        $message_type =
            "error";

    }


    // SPECIAL CHARACTER

    elseif (
        !preg_match(
            '/[^A-Za-z0-9]/',
            $password
        )
    ) {

        $message =
            "Password must contain at least one special character.";

        $message_type =
            "error";

    }


    // CONFIRM PASSWORD

    elseif (
        $password !==
        $confirm_password
    ) {

        $message =
            "Passwords do not match.";

        $message_type =
            "error";

    }


    else {

        try {


            // =========================
            // CHECK EXISTING EMAIL
            // =========================

            $check = $pdo->prepare(
                "SELECT id
                 FROM users
                 WHERE email = :email
                 LIMIT 1"
            );


            $check->execute([
                "email" => $email
            ]);


            $existing_user =
                $check->fetch();


            if ($existing_user) {

                $message =
                    "This email is already registered.";

                $message_type =
                    "error";

            }


            else {


                // =========================
                // HASH PASSWORD
                // =========================

                $hashed_password =
                    password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );


                // =========================
                // INSERT USER
                // =========================

                $stmt = $pdo->prepare(
                    "INSERT INTO users
                    (
                        full_name,
                        email,
                        password
                    )
                    VALUES
                    (
                        :full_name,
                        :email,
                        :password
                    )"
                );


                $stmt->execute([
                    "full_name" =>
                        $full_name,

                    "email" =>
                        $email,

                    "password" =>
                        $hashed_password
                ]);


                header(
                    "Location: /myhome/login.php?registered=1"
                );

                exit;

            }


        } catch (PDOException $e) {

            $message =
                "Registration failed. Please try again.";

            $message_type =
                "error";

        }

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Create Account | MyHome
    </title>


    <link
        rel="stylesheet"
        href="/myhome/css/style.css"
    >

    <link
        rel="stylesheet"
        href="/myhome/css/auth.css"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >

</head>


<body class="register-page">


    <div class="register-wrapper">

        <div class="register-card">


            <!-- =========================
                 LOGO
            ========================== -->

            <a
                href="/myhome/index.php"
                class="register-logo"
            >

                <img
                    src="/myhome/images/logo.png"
                    alt="MyHome Logo"
                >

            </a>


            <!-- =========================
                 HEADER
            ========================== -->

            <div class="register-header">

                <h1>
                    Create Account
                </h1>

                <p>
                    Join MyHome and discover your
                    next place to call home.
                </p>

            </div>


            <!-- =========================
                 PHP MESSAGE
            ========================== -->

            <?php if (!empty($message)): ?>

                <div
                    class="form-notification
                    <?php echo $message_type; ?>"
                >

                    <?php if (
                        $message_type === "success"
                    ): ?>

                        <i
                            class="fa-solid fa-circle-check"
                        ></i>

                    <?php else: ?>

                        <i
                            class="fa-solid fa-circle-exclamation"
                        ></i>

                    <?php endif; ?>

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
                action="/myhome/register.php"
                method="POST"
                class="register-form"
            >


                <!-- FULL NAME -->

                <div class="register-form-group">

                    <label for="full_name">
                        Full Name
                    </label>

                    <div class="register-input-group">

                        <i class="fa-regular fa-user"></i>

                        <input
                            type="text"
                            id="full_name"
                            name="full_name"
                            placeholder="Enter your full name"
                            value="<?php
                            echo htmlspecialchars(
                                $full_name
                            );
                            ?>"
                            minlength="2"
                            maxlength="20"
                            autocomplete="name"
                            required
                        >

                    </div>

                    <small
                        class="input-hint"
                        id="nameWarning"
                    >
                        Maximum of 20 characters.
                    </small>

                </div>


                <!-- EMAIL -->

                <div class="register-form-group">

                    <label for="email">
                        Email Address
                    </label>

                    <div class="register-input-group">

                        <i class="fa-regular fa-envelope"></i>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="Enter your email"
                            value="<?php
                            echo htmlspecialchars(
                                $email
                            );
                            ?>"
                            autocomplete="email"
                            required
                        >

                    </div>

                </div>


                <!-- PASSWORD -->

                <div class="register-form-group">

                    <label for="password">
                        Password
                    </label>

                    <div
                        class="register-input-group password-field"
                    >

                        <i class="fa-solid fa-lock"></i>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Create a password"
                            minlength="8"
                            maxlength="72"
                            autocomplete="new-password"
                            required
                        >

                        <button
                            type="button"
                            class="password-toggle-icon"
                            id="togglePassword"
                            aria-label="Show password"
                        >

                            <i class="fa-regular fa-eye"></i>

                        </button>

                    </div>


                    <!-- PASSWORD REQUIREMENTS -->

                    <div
                        class="password-requirements"
                        id="passwordRequirements"
                    >

                        <p id="lengthRequirement">

                            <span>
                                <i class="fa-solid fa-xmark"></i>
                            </span>

                            At least 8 characters

                        </p>


                        <p id="uppercaseRequirement">

                            <span>
                                <i class="fa-solid fa-xmark"></i>
                            </span>

                            One uppercase letter

                        </p>


                        <p id="lowercaseRequirement">

                            <span>
                                <i class="fa-solid fa-xmark"></i>
                            </span>

                            One lowercase letter

                        </p>


                        <p id="numberRequirement">

                            <span>
                                <i class="fa-solid fa-xmark"></i>
                            </span>

                            One number

                        </p>


                        <p id="specialRequirement">

                            <span>
                                <i class="fa-solid fa-xmark"></i>
                            </span>

                            One special character

                        </p>

                    </div>

                </div>


                <!-- CONFIRM PASSWORD -->

                <div class="register-form-group">

                    <label for="confirm_password">
                        Confirm Password
                    </label>

                    <div
                        class="register-input-group password-field"
                    >

                        <i class="fa-solid fa-lock"></i>

                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            placeholder="Enter your password again"
                            minlength="8"
                            maxlength="72"
                            autocomplete="new-password"
                            required
                        >

                        <button
                            type="button"
                            class="password-toggle-icon"
                            id="toggleConfirmPassword"
                            aria-label="Show confirm password"
                        >

                            <i class="fa-regular fa-eye"></i>

                        </button>

                    </div>


                    <p
                        class="password-match"
                        id="passwordMatch"
                    ></p>

                </div>


                <!-- SUBMIT -->

                <button
                    type="submit"
                    class="register-submit"
                >

                    Create Account

                    <i class="fa-solid fa-arrow-right"></i>

                </button>

            </form>


            <!-- LOGIN -->

            <div class="register-login">

                <p>

                    Already have an account?

                    <a href="/myhome/login.php">
                        Login
                    </a>

                </p>

            </div>


            <!-- BACK HOME -->

            <a
                href="/myhome/index.php"
                class="register-back"
            >

                <i class="fa-solid fa-arrow-left"></i>

                Back to Home

            </a>


        </div>

    </div>


    <script>

        const password =
            document.getElementById(
                "password"
            );

        const confirmPassword =
            document.getElementById(
                "confirm_password"
            );

        const passwordMatch =
            document.getElementById(
                "passwordMatch"
            );


        const lengthRequirement =
            document.getElementById(
                "lengthRequirement"
            );

        const uppercaseRequirement =
            document.getElementById(
                "uppercaseRequirement"
            );

        const lowercaseRequirement =
            document.getElementById(
                "lowercaseRequirement"
            );

        const numberRequirement =
            document.getElementById(
                "numberRequirement"
            );

        const specialRequirement =
            document.getElementById(
                "specialRequirement"
            );


        password.addEventListener(
            "input",
            function () {

                const value =
                    password.value;


                updateRequirement(
                    lengthRequirement,
                    value.length >= 8
                );


                updateRequirement(
                    uppercaseRequirement,
                    /[A-Z]/.test(value)
                );


                updateRequirement(
                    lowercaseRequirement,
                    /[a-z]/.test(value)
                );


                updateRequirement(
                    numberRequirement,
                    /[0-9]/.test(value)
                );


                updateRequirement(
                    specialRequirement,
                    /[^A-Za-z0-9]/.test(value)
                );


                checkPasswordMatch();

            }
        );


        function updateRequirement(
            element,
            valid
        ) {

            const icon =
                element.querySelector(
                    "span i"
                );


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


        confirmPassword.addEventListener(
            "input",
            checkPasswordMatch
        );


        function checkPasswordMatch() {

            if (
                confirmPassword.value === ""
            ) {

                passwordMatch.textContent =
                    "";

                passwordMatch.className =
                    "password-match";

                return;

            }


            if (
                password.value ===
                confirmPassword.value
            ) {

                passwordMatch.innerHTML =
                    '<i class="fa-solid fa-check"></i> Passwords match';

                passwordMatch.className =
                    "password-match match";

            }

            else {

                passwordMatch.innerHTML =
                    '<i class="fa-solid fa-xmark"></i> Passwords do not match';

                passwordMatch.className =
                    "password-match no-match";

            }

        }


        const togglePassword =
            document.getElementById(
                "togglePassword"
            );

        const toggleConfirmPassword =
            document.getElementById(
                "toggleConfirmPassword"
            );


        togglePassword.addEventListener(
            "click",
            function () {

                togglePasswordVisibility(
                    password,
                    togglePassword
                );

            }
        );


        toggleConfirmPassword.addEventListener(
            "click",
            function () {

                togglePasswordVisibility(
                    confirmPassword,
                    toggleConfirmPassword
                );

            }
        );


        function togglePasswordVisibility(
            input,
            button
        ) {

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


        const fullName =
            document.getElementById(
                "full_name"
            );

        const nameWarning =
            document.getElementById(
                "nameWarning"
            );


        fullName.addEventListener(
            "input",
            function () {

                if (
                    fullName.value.length > 20
                ) {

                    nameWarning.textContent =
                        "Full name cannot exceed 20 characters.";

                    nameWarning.classList.add(
                        "name-error"
                    );

                }

                else {

                    nameWarning.textContent =
                        "Maximum of 20 characters.";

                    nameWarning.classList.remove(
                        "name-error"
                    );

                }

            }
        );

    </script>


</body>

</html>
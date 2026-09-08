<?php

session_start();

$pdo = require "config/database.php";

$message = "";
$message_type = "";

$email = "";


// =========================================
// REGISTRATION SUCCESS MESSAGE
// =========================================

if (isset($_GET["registered"])) {

    $message =
        "Account created successfully. You can now log in.";

    $message_type =
        "success";
}


// =========================================
// LOGIN FORM
// =========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email =
        trim(
            $_POST["email"] ?? ""
        );

    $password =
        $_POST["password"] ?? "";


    // =====================================
    // VALIDATION
    // =====================================

    if (
        empty($email) ||
        empty($password)
    ) {

        $message =
            "Please fill in all fields.";

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

            // =================================
            // FIND USER
            // =================================

            $stmt = $pdo->prepare(
                "SELECT
                    id,
                    full_name,
                    email,
                    password,
                    role
                 FROM users
                 WHERE email = :email
                 LIMIT 1"
            );


            $stmt->execute([
                "email" => $email
            ]);


            $user =
                $stmt->fetch();


            // =================================
            // CHECK USER + PASSWORD
            // =================================

            if (
                $user &&
                password_verify(
                    $password,
                    $user["password"]
                )
            ) {

                // =================================
                // SESSION
                // =================================

                $_SESSION["user_id"] =
                    $user["id"];

                $_SESSION["full_name"] =
                    $user["full_name"];

                $_SESSION["email"] =
                    $user["email"];

                $_SESSION["role"] =
                    $user["role"];


                // =================================
                // REDIRECT
                // =================================

                if (
                    $user["role"] === "admin"
                ) {

                    header(
                        "Location: /myhome/admin/dashboard.php"
                    );

                    exit;

                }

                else {

                    header(
                        "Location: /myhome/index.php"
                    );

                    exit;

                }

            }

            else {

                $message =
                    "Incorrect email or password.";

                $message_type =
                    "error";

            }

        }

        catch (PDOException $e) {

            $message =
                "Login failed. Please try again.";

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
        Login | MyHome
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


<body class="login-page">


    <div class="login-wrapper">

        <div class="login-card">


            <!-- =========================
                 LOGO
            ========================== -->

            <a
                href="/myhome/index.php"
                class="login-logo"
            >

                <img
                    src="/myhome/images/logo.png"
                    alt="MyHome Logo"
                >

            </a>


            <!-- =========================
                 HEADER
            ========================== -->

            <div class="login-header">

                <h1>
                    Welcome Back
                </h1>

                <p>
                    Login to continue to your MyHome account.
                </p>

            </div>


            <!-- =========================
                 MESSAGE
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
                 LOGIN FORM
            ========================== -->

            <form
                method="POST"
                action="/myhome/login.php"
                class="login-form"
            >


                <!-- EMAIL -->

                <div class="login-form-group">

                    <label for="email">
                        Email Address
                    </label>

                    <div class="login-input-group">

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

                <div class="login-form-group">

                    <label for="password">
                        Password
                    </label>

                    <div class="login-input-group">

                        <i class="fa-solid fa-lock"></i>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            class="login-password-toggle"
                            id="togglePassword"
                            aria-label="Show password"
                        >

                            <i class="fa-regular fa-eye"></i>

                        </button>

                    </div>

                </div>


                <!-- LOGIN BUTTON -->

                <button
                    type="submit"
                    class="login-submit"
                >

                    Login

                    <i class="fa-solid fa-arrow-right"></i>

                </button>


            </form>


            <!-- REGISTER LINK -->

            <div class="login-register">

                <p>

                    Don't have an account?

                    <a href="/myhome/register.php">
                        Create Account
                    </a>

                </p>

            </div>


            <!-- BACK HOME -->

            <a
                href="/myhome/index.php"
                class="login-back"
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

        const togglePassword =
            document.getElementById(
                "togglePassword"
            );


        togglePassword.addEventListener(
            "click",
            function () {

                const icon =
                    togglePassword.querySelector(
                        "i"
                    );


                if (
                    password.type === "password"
                ) {

                    password.type =
                        "text";

                    icon.className =
                        "fa-regular fa-eye-slash";

                }

                else {

                    password.type =
                        "password";

                    icon.className =
                        "fa-regular fa-eye";

                }

            }
        );

    </script>


</body>

</html>
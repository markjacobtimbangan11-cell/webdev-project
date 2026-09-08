<?php

session_start();

$pdo = require "../config/database.php";


// =========================================
// ADMIN ACCESS ONLY
// =========================================

if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "admin"
) {

    header("Location: /myhome/login.php");

    exit;
}


// =========================================
// POST REQUEST ONLY
// =========================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header(
        "Location: /myhome/admin/users.php"
    );

    exit;
}


// =========================================
// GET DATA
// =========================================

$user_id =
    isset($_POST["user_id"])
        ? (int) $_POST["user_id"]
        : 0;

$action =
    $_POST["action"] ?? "";


// =========================================
// INVALID USER
// =========================================

if ($user_id <= 0) {

    header(
        "Location: /myhome/admin/users.php?error=invalid"
    );

    exit;
}


// =========================================
// PROTECT CURRENT ADMIN
// =========================================

if (
    $user_id ===
    (int) $_SESSION["user_id"]
) {

    header(
        "Location: /myhome/admin/users.php?error=self"
    );

    exit;
}


// =========================================
// FIND USER
// =========================================

try {

    $stmt = $pdo->prepare(
        "SELECT
            id,
            role
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
            "Location: /myhome/admin/users.php?error=notfound"
        );

        exit;
    }

}

catch (PDOException $e) {

    header(
        "Location: /myhome/admin/users.php?error=invalid"
    );

    exit;
}


// =========================================
// CHANGE ROLE
// =========================================

if ($action === "change_role") {


    $new_role =
        $user["role"] === "admin"
            ? "user"
            : "admin";


    // =====================================
    // PROTECT LAST ADMIN
    // =====================================

    if (
        $user["role"] === "admin" &&
        $new_role === "user"
    ) {

        try {

            $stmt = $pdo->query(
                "SELECT COUNT(*)
                 FROM users
                 WHERE role = 'admin'"
            );


            $admin_count =
                (int) $stmt->fetchColumn();


            if ($admin_count <= 1) {

                header(
                    "Location: /myhome/admin/users.php?error=last_admin"
                );

                exit;
            }

        }

        catch (PDOException $e) {

            header(
                "Location: /myhome/admin/users.php?error=invalid"
            );

            exit;
        }
    }


    // =====================================
    // UPDATE ROLE
    // =====================================

    try {

        $stmt = $pdo->prepare(
            "UPDATE users
             SET role = :role
             WHERE id = :user_id"
        );


        $stmt->execute([
            "role" => $new_role,
            "user_id" => $user_id
        ]);


        header(
            "Location: /myhome/admin/users.php?success=role"
        );

        exit;

    }

    catch (PDOException $e) {

        header(
            "Location: /myhome/admin/users.php?error=invalid"
        );

        exit;
    }
}


// =========================================
// DELETE USER
// =========================================

if ($action === "delete") {


    // =====================================
    // PROTECT LAST ADMIN
    // =====================================

    if ($user["role"] === "admin") {

        try {

            $stmt = $pdo->query(
                "SELECT COUNT(*)
                 FROM users
                 WHERE role = 'admin'"
            );


            $admin_count =
                (int) $stmt->fetchColumn();


            if ($admin_count <= 1) {

                header(
                    "Location: /myhome/admin/users.php?error=last_admin"
                );

                exit;
            }

        }

        catch (PDOException $e) {

            header(
                "Location: /myhome/admin/users.php?error=invalid"
            );

            exit;
        }
    }


    // =====================================
    // DELETE USER
    // =====================================

    try {

        $stmt = $pdo->prepare(
            "DELETE FROM users
             WHERE id = :user_id"
        );


        $stmt->execute([
            "user_id" => $user_id
        ]);


        header(
            "Location: /myhome/admin/users.php?success=deleted"
        );

        exit;

    }

    catch (PDOException $e) {

        header(
            "Location: /myhome/admin/users.php?error=invalid"
        );

        exit;
    }
}


// =========================================
// INVALID ACTION
// =========================================

header(
    "Location: /myhome/admin/users.php?error=invalid"
);

exit;
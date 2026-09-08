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
// GET USERS
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
         ORDER BY created_at DESC"
    );


    $stmt->execute();


    $users =
        $stmt->fetchAll();


    $total_users =
        count($users);

}

catch (PDOException $e) {

    $users = [];

    $total_users = 0;
}


// =========================================
// PAGE TITLE
// =========================================

$page_title =
    "Users | MyHome Admin";


include "../includes/header.php";

?>


<main class="admin-page">

    <div class="admin-layout">


        <!-- =========================
             SIDEBAR
        ========================== -->

        <?php
        include "../includes/admin-sidebar.php";
        ?>


        <!-- =========================
             MAIN CONTENT
        ========================== -->

        <section class="admin-content">


            <!-- =========================
                 HEADER
            ========================== -->

            <div class="admin-header">

                <div>

                    <p class="section-label">
                        USER MANAGEMENT
                    </p>

                    <h1>
                        Users
                    </h1>

                    <p>
                        Manage registered MyHome accounts.
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


            <!-- =========================
                 SUCCESS MESSAGE
            ========================== -->

            <?php if (isset($_GET["success"])): ?>

                <div class="profile-success-message">

                    <i class="fa-solid fa-circle-check"></i>

                    <?php

                    if ($_GET["success"] === "role") {

                        echo "User role updated successfully.";

                    } elseif (
                        $_GET["success"] === "deleted"
                    ) {

                        echo "User deleted successfully.";
                    }

                    ?>

                </div>

            <?php endif; ?>


            <!-- =========================
                 ERROR MESSAGE
            ========================== -->

            <?php if (isset($_GET["error"])): ?>

                <div
                    class="profile-success-message"
                    style="
                        background:#fff0f0;
                        border-color:#f1c6c6;
                        color:#b63b3b;
                    "
                >

                    <i class="fa-solid fa-circle-exclamation"></i>

                    <?php

                    if ($_GET["error"] === "self") {

                        echo "You cannot modify your own account.";

                    } elseif (
                        $_GET["error"] === "last_admin"
                    ) {

                        echo "The last administrator cannot be removed.";

                    } elseif (
                        $_GET["error"] === "notfound"
                    ) {

                        echo "User could not be found.";

                    } else {

                        echo "Something went wrong.";
                    }

                    ?>

                </div>

            <?php endif; ?>


            <!-- =========================
                 USERS CARD
            ========================== -->

            <section class="admin-users-card">


                <div class="admin-users-header">

                    <div>

                        <h2>
                            Registered Users
                        </h2>

                        <p>

                            <?php
                            echo $total_users;
                            ?>

                            registered account<?php
                            echo $total_users !== 1
                                ? "s"
                                : "";
                            ?>.

                        </p>

                    </div>

                </div>


                <?php if ($total_users > 0): ?>


                    <div class="admin-table-wrapper">

                        <table class="admin-table">


                            <thead>

                                <tr>

                                    <th>User</th>

                                    <th>Email</th>

                                    <th>Role</th>

                                    <th>Joined</th>

                                    <th>Actions</th>

                                </tr>

                            </thead>


                            <tbody>


                                <?php foreach ($users as $user): ?>


                                    <tr>


                                        <!-- USER -->

                                        <td>

                                            <div class="admin-user-cell">


                                                <div class="admin-user-avatar">

                                                    <i class="fa-solid fa-user"></i>

                                                </div>


                                                <div>

                                                    <strong>

                                                        <?php
                                                        echo htmlspecialchars(
                                                            $user["full_name"]
                                                        );
                                                        ?>

                                                    </strong>


                                                    <small>

                                                        ID:
                                                        <?php
                                                        echo (int) $user["id"];
                                                        ?>

                                                    </small>

                                                </div>

                                            </div>

                                        </td>


                                        <!-- EMAIL -->

                                        <td>

                                            <?php
                                            echo htmlspecialchars(
                                                $user["email"]
                                            );
                                            ?>

                                        </td>


                                        <!-- ROLE -->

                                        <td>

                                            <span
                                                class="admin-role-badge <?php
                                                echo htmlspecialchars(
                                                    $user["role"]
                                                );
                                                ?>"
                                            >

                                                <?php
                                                echo ucfirst(
                                                    htmlspecialchars(
                                                        $user["role"]
                                                    )
                                                );
                                                ?>

                                            </span>

                                        </td>


                                        <!-- JOINED -->

                                        <td>

                                            <?php
                                            echo date(
                                                "M d, Y",
                                                strtotime(
                                                    $user["created_at"]
                                                )
                                            );
                                            ?>

                                        </td>


                                        <!-- ACTIONS -->

                                        <td>


                                            <?php
                                            if (
                                                (int) $user["id"] ===
                                                (int) $_SESSION["user_id"]
                                            ):
                                            ?>


                                                <span class="admin-current-user">

                                                    <i class="fa-solid fa-user-shield"></i>

                                                    You

                                                </span>


                                            <?php else: ?>


                                                <div class="admin-action-menu">


                                                    <!-- THREE DOT BUTTON -->

                                                    <button
                                                        type="button"
                                                        class="admin-action-btn"
                                                        onclick="toggleUserMenu(this)"
                                                        title="User actions"
                                                    >

                                                        <i class="fa-solid fa-ellipsis"></i>

                                                    </button>


                                                    <!-- DROPDOWN -->

                                                    <div class="admin-action-dropdown">


                                                        <!-- CHANGE ROLE -->

                                                        <form
                                                            action="/myhome/admin/user-action.php"
                                                            method="POST"
                                                        >

                                                            <input
                                                                type="hidden"
                                                                name="user_id"
                                                                value="<?php
                                                                echo (int) $user["id"];
                                                                ?>"
                                                            >

                                                            <input
                                                                type="hidden"
                                                                name="action"
                                                                value="change_role"
                                                            >


                                                            <button
                                                                type="submit"
                                                                class="admin-dropdown-item"
                                                            >

                                                                <i class="fa-solid fa-user-gear"></i>

                                                                <?php

                                                                if (
                                                                    $user["role"] === "admin"
                                                                ) {

                                                                    echo "Make User";

                                                                } else {

                                                                    echo "Make Admin";
                                                                }

                                                                ?>

                                                            </button>

                                                        </form>


                                                        <!-- DELETE USER -->

                                                        <button
                                                            type="button"
                                                            class="admin-dropdown-item delete"
                                                            onclick='openDeleteUserModal(
                                                                <?php
                                                                echo (int) $user["id"];
                                                                ?>,
                                                                <?php
                                                                echo json_encode(
                                                                    $user["full_name"]
                                                                );
                                                                ?>
                                                            )'
                                                        >

                                                            <i class="fa-solid fa-trash"></i>

                                                            Delete User

                                                        </button>


                                                    </div>

                                                </div>


                                            <?php endif; ?>


                                        </td>


                                    </tr>


                                <?php endforeach; ?>


                            </tbody>


                        </table>

                    </div>


                <?php else: ?>


                    <div class="admin-empty-state">

                        <i class="fa-solid fa-users"></i>

                        <h3>
                            No users found
                        </h3>

                        <p>
                            Registered accounts will appear here.
                        </p>

                    </div>


                <?php endif; ?>


            </section>


            <!-- =========================
                 DELETE USER MODAL
            ========================== -->

            <div
                class="admin-modal-overlay"
                id="deleteUserModal"
            >

                <div class="admin-modal">


                    <div class="admin-modal-icon">

                        <i class="fa-solid fa-triangle-exclamation"></i>

                    </div>


                    <h2>
                        Delete User?
                    </h2>


                    <p>

                        Are you sure you want to delete

                        <strong id="deleteUserName"></strong>?

                        This action cannot be undone.

                    </p>


                    <div class="admin-modal-actions">


                        <button
                            type="button"
                            class="admin-modal-cancel"
                            onclick="closeDeleteUserModal()"
                        >

                            Cancel

                        </button>


                        <form
                            action="/myhome/admin/user-action.php"
                            method="POST"
                        >

                            <input
                                type="hidden"
                                name="user_id"
                                id="deleteUserId"
                            >

                            <input
                                type="hidden"
                                name="action"
                                value="delete"
                            >


                            <button
                                type="submit"
                                class="admin-modal-delete"
                            >

                                Delete User

                            </button>

                        </form>


                    </div>

                </div>

            </div>


        </section>


    </div>

</main>


<!-- =========================
     USER ACTION JAVASCRIPT
========================== -->

<script>


function toggleUserMenu(button) {

    const menu =
        button.nextElementSibling;


    document
        .querySelectorAll(
            ".admin-action-dropdown"
        )
        .forEach(function(dropdown) {

            if (dropdown !== menu) {

                dropdown.classList.remove(
                    "show"
                );
            }

        });


    menu.classList.toggle("show");

}


// CLOSE DROPDOWN WHEN CLICKING OUTSIDE

document.addEventListener(
    "click",
    function(event) {

        if (
            !event.target.closest(
                ".admin-action-menu"
            )
        ) {

            document
                .querySelectorAll(
                    ".admin-action-dropdown"
                )
                .forEach(function(dropdown) {

                    dropdown.classList.remove(
                        "show"
                    );

                });
        }

    }
);


// OPEN DELETE MODAL

function openDeleteUserModal(
    userId,
    userName
) {

    const modal =
        document.getElementById(
            "deleteUserModal"
        );

    const idInput =
        document.getElementById(
            "deleteUserId"
        );

    const nameText =
        document.getElementById(
            "deleteUserName"
        );


    idInput.value = userId;

    nameText.textContent = userName;

    modal.classList.add("show");

}


// CLOSE DELETE MODAL

function closeDeleteUserModal() {

    document
        .getElementById(
            "deleteUserModal"
        )
        .classList.remove("show");

}


// CLOSE MODAL IF CLICKING BACKGROUND

document
    .getElementById("deleteUserModal")
    .addEventListener(
        "click",
        function(event) {

            if (event.target === this) {

                closeDeleteUserModal();
            }

        }
    );


// CLOSE MODAL WITH ESC KEY

document.addEventListener(
    "keydown",
    function(event) {

        if (event.key === "Escape") {

            closeDeleteUserModal();
        }

    }
);


</script>
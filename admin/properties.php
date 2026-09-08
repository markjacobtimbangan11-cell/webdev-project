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
// GET ALL PROPERTIES
// =========================================

try {

    $stmt = $pdo->prepare(
        "SELECT
            p.id,
            p.title,
            p.listing_type,
            p.property_type,
            p.price,
            p.location,
            p.status,
            p.created_at,
            u.full_name AS owner_name,
            (
                SELECT pi.image_path
                FROM property_images pi
                WHERE pi.property_id = p.id
                ORDER BY pi.id ASC
                LIMIT 1
            ) AS main_image
         FROM properties p
         INNER JOIN users u
            ON u.id = p.user_id
         ORDER BY p.created_at DESC"
    );


    $stmt->execute();


    $properties =
        $stmt->fetchAll();


    $total_properties =
        count($properties);

}

catch (PDOException $e) {

    $properties = [];

    $total_properties = 0;
}


// =========================================
// PAGE TITLE
// =========================================

$page_title =
    "Properties | MyHome Admin";


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
                        PROPERTY MANAGEMENT
                    </p>

                    <h1>
                        Properties
                    </h1>

                    <p>
                        Manage all property listings on MyHome.
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

                    if ($_GET["success"] === "status") {

                        echo "Property status updated successfully.";

                    } elseif (
                        $_GET["success"] === "deleted"
                    ) {

                        echo "Property deleted successfully.";
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

                    if ($_GET["error"] === "notfound") {

                        echo "Property could not be found.";

                    } elseif (
                        $_GET["error"] === "invalid_status"
                    ) {

                        echo "Invalid property status.";

                    } else {

                        echo "Something went wrong.";
                    }

                    ?>

                </div>

            <?php endif; ?>


            <!-- =========================
                 PROPERTY LISTINGS CARD
            ========================== -->

            <section class="admin-users-card">


                <div class="admin-users-header">

                    <div>

                        <h2>
                            Property Listings
                        </h2>

                        <p>

                            <?php
                            echo $total_properties;
                            ?>

                            propert<?php
                            echo $total_properties === 1
                                ? "y"
                                : "ies";
                            ?> found.

                        </p>

                    </div>

                </div>


                <?php if ($total_properties > 0): ?>


                    <div class="admin-table-wrapper">

                        <table class="admin-table">


                            <thead>

                                <tr>

                                    <th>Property</th>

                                    <th>Owner</th>

                                    <th>Type</th>

                                    <th>Price</th>

                                    <th>Status</th>

                                    <th>Actions</th>

                                </tr>

                            </thead>


                            <tbody>


                                <?php foreach ($properties as $property): ?>


                                    <tr>


                                        <!-- PROPERTY -->

                                        <td>

                                            <div class="admin-property-cell">


                                                <div class="admin-property-thumb">

                                                    <?php if (
                                                        !empty(
                                                            $property["main_image"]
                                                        )
                                                    ): ?>

                                                        <img
                                                            src="/myhome/<?php
                                                            echo htmlspecialchars(
                                                                $property["main_image"]
                                                            );
                                                            ?>"
                                                            alt="Property Image"
                                                        >

                                                    <?php else: ?>

                                                        <i class="fa-solid fa-house"></i>

                                                    <?php endif; ?>

                                                </div>


                                                <div>

                                                    <strong>

                                                        <?php
                                                        echo htmlspecialchars(
                                                            $property["title"]
                                                        );
                                                        ?>

                                                    </strong>


                                                    <small>

                                                        <?php
                                                        echo htmlspecialchars(
                                                            $property["location"]
                                                        );
                                                        ?>

                                                    </small>

                                                </div>

                                            </div>

                                        </td>


                                        <!-- OWNER -->

                                        <td>

                                            <?php
                                            echo htmlspecialchars(
                                                $property["owner_name"]
                                            );
                                            ?>

                                        </td>


                                        <!-- TYPE -->

                                        <td>

                                            <?php
                                            echo ucfirst(
                                                htmlspecialchars(
                                                    $property["listing_type"]
                                                )
                                            );
                                            ?>

                                            <br>

                                            <small>

                                                <?php
                                                echo htmlspecialchars(
                                                    $property["property_type"]
                                                );
                                                ?>

                                            </small>

                                        </td>


                                        <!-- PRICE -->

                                        <td>

                                            ₱<?php
                                            echo number_format(
                                                (float) $property["price"],
                                                2
                                            );
                                            ?>

                                        </td>


                                        <!-- STATUS -->

                                        <td>

                                            <span
                                                class="admin-status-badge <?php
                                                echo htmlspecialchars(
                                                    $property["status"]
                                                );
                                                ?>"
                                            >

                                                <?php
                                                echo ucfirst(
                                                    htmlspecialchars(
                                                        $property["status"]
                                                    )
                                                );
                                                ?>

                                            </span>

                                        </td>


                                        <!-- ACTIONS -->

                                        <td>

                                            <div class="admin-action-menu">


                                                <button
                                                    type="button"
                                                    class="admin-action-btn"
                                                    onclick="togglePropertyMenu(this)"
                                                    title="Property actions"
                                                >

                                                    <i class="fa-solid fa-ellipsis"></i>

                                                </button>


                                                <div class="admin-action-dropdown">


                                                    <!-- VIEW PROPERTY -->

                                                    <a
                                                        href="/myhome/properties/view-property.php?id=<?php
                                                        echo (int) $property["id"];
                                                        ?>"
                                                        class="admin-dropdown-item"
                                                    >

                                                        <i class="fa-solid fa-eye"></i>

                                                        View Property

                                                    </a>


                                                    <!-- CHANGE STATUS -->

                                                    <form
                                                        action="/myhome/admin/property-action.php"
                                                        method="POST"
                                                    >

                                                        <input
                                                            type="hidden"
                                                            name="property_id"
                                                            value="<?php
                                                            echo (int) $property["id"];
                                                            ?>"
                                                        >

                                                        <input
                                                            type="hidden"
                                                            name="action"
                                                            value="change_status"
                                                        >


                                                        <?php

                                                        if (
                                                            $property["status"] ===
                                                            "available"
                                                        ) {

                                                            if (
                                                                $property["listing_type"] ===
                                                                "sale"
                                                            ) {

                                                                $next_status =
                                                                    "sold";

                                                                $status_text =
                                                                    "Mark as Sold";

                                                                $status_icon =
                                                                    "fa-solid fa-circle-check";

                                                            } else {

                                                                $next_status =
                                                                    "rented";

                                                                $status_text =
                                                                    "Mark as Rented";

                                                                $status_icon =
                                                                    "fa-solid fa-key";
                                                            }

                                                        } else {

                                                            $next_status =
                                                                "available";

                                                            $status_text =
                                                                "Mark as Available";

                                                            $status_icon =
                                                                "fa-solid fa-rotate-left";
                                                        }

                                                        ?>


                                                        <input
                                                            type="hidden"
                                                            name="new_status"
                                                            value="<?php
                                                            echo htmlspecialchars(
                                                                $next_status
                                                            );
                                                            ?>"
                                                        >


                                                        <button
                                                            type="submit"
                                                            class="admin-dropdown-item"
                                                        >

                                                            <i class="<?php
                                                            echo htmlspecialchars(
                                                                $status_icon
                                                            );
                                                            ?>"></i>

                                                            <?php
                                                            echo htmlspecialchars(
                                                                $status_text
                                                            );
                                                            ?>

                                                        </button>

                                                    </form>


                                                    <!-- DELETE PROPERTY -->

                                                    <button
                                                        type="button"
                                                        class="admin-dropdown-item delete"
                                                        onclick='openDeletePropertyModal(
                                                            <?php
                                                            echo (int) $property["id"];
                                                            ?>,
                                                            <?php
                                                            echo json_encode(
                                                                $property["title"]
                                                            );
                                                            ?>
                                                        )'
                                                    >

                                                        <i class="fa-solid fa-trash"></i>

                                                        Delete Property

                                                    </button>


                                                </div>

                                            </div>

                                        </td>


                                    </tr>


                                <?php endforeach; ?>


                            </tbody>


                        </table>

                    </div>


                <?php else: ?>


                    <div class="admin-empty-state">

                        <i class="fa-solid fa-house"></i>

                        <h3>
                            No properties found
                        </h3>

                        <p>
                            Property listings will appear here.
                        </p>

                    </div>


                <?php endif; ?>


            </section>


            <!-- =========================
                 DELETE PROPERTY MODAL
            ========================== -->

            <div
                class="admin-modal-overlay"
                id="deletePropertyModal"
            >

                <div class="admin-modal">


                    <div class="admin-modal-icon">

                        <i class="fa-solid fa-triangle-exclamation"></i>

                    </div>


                    <h2>
                        Delete Property?
                    </h2>


                    <p>

                        Are you sure you want to delete

                        <strong id="deletePropertyName"></strong>?

                        This action cannot be undone.

                    </p>


                    <div class="admin-modal-actions">


                        <button
                            type="button"
                            class="admin-modal-cancel"
                            onclick="closeDeletePropertyModal()"
                        >

                            Cancel

                        </button>


                        <form
                            action="/myhome/admin/property-action.php"
                            method="POST"
                        >

                            <input
                                type="hidden"
                                name="property_id"
                                id="deletePropertyId"
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

                                Delete Property

                            </button>

                        </form>


                    </div>

                </div>

            </div>


        </section>


    </div>

</main>


<!-- =========================
     PROPERTY ACTION JAVASCRIPT
========================== -->

<script>


function togglePropertyMenu(button) {

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


// CLOSE ACTION MENU WHEN CLICKING OUTSIDE

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

function openDeletePropertyModal(
    propertyId,
    propertyName
) {

    document.getElementById(
        "deletePropertyId"
    ).value = propertyId;


    document.getElementById(
        "deletePropertyName"
    ).textContent = propertyName;


    document.getElementById(
        "deletePropertyModal"
    ).classList.add("show");

}


// CLOSE DELETE MODAL

function closeDeletePropertyModal() {

    document.getElementById(
        "deletePropertyModal"
    ).classList.remove("show");

}


// CLICK OUTSIDE MODAL TO CLOSE

document
    .getElementById("deletePropertyModal")
    .addEventListener(
        "click",
        function(event) {

            if (event.target === this) {

                closeDeletePropertyModal();
            }

        }
    );


// ESC KEY TO CLOSE

document.addEventListener(
    "keydown",
    function(event) {

        if (event.key === "Escape") {

            closeDeletePropertyModal();
        }

    }
);


</script>
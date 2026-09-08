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
// GET ALL INQUIRIES
// =========================================

try {

    $stmt = $pdo->prepare(
        "SELECT
            i.id,
            i.message,
            i.created_at,

            u.full_name AS sender_name,
            u.email AS sender_email,

            p.id AS property_id,
            p.title AS property_title,

            owner.full_name AS owner_name,

            (
                SELECT pi.image_path
                FROM property_images pi
                WHERE pi.property_id = p.id
                ORDER BY pi.id ASC
                LIMIT 1
            ) AS main_image

         FROM inquiries i

         INNER JOIN users u
            ON u.id = i.user_id

         INNER JOIN properties p
            ON p.id = i.property_id

         INNER JOIN users owner
            ON owner.id = p.user_id

         ORDER BY i.created_at DESC"
    );


    $stmt->execute();


    $inquiries =
        $stmt->fetchAll();


    $total_inquiries =
        count($inquiries);

}

catch (PDOException $e) {

    $inquiries = [];

    $total_inquiries = 0;
}


// =========================================
// PAGE TITLE
// =========================================

$page_title =
    "Inquiries | MyHome Admin";


include "../includes/header.php";

?>


<main class="admin-page">

    <div class="admin-layout">


        <!-- =================================
             SIDEBAR
        ================================== -->

        <?php
        include "../includes/admin-sidebar.php";
        ?>


        <!-- =================================
             CONTENT
        ================================== -->

        <section class="admin-content">


            <!-- HEADER -->

            <div class="admin-header">

                <div>

                    <p class="section-label">
                        INQUIRY MANAGEMENT
                    </p>

                    <h1>
                        Inquiries
                    </h1>

                    <p>
                        View all property inquiries on MyHome.
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


            <!-- =================================
                 SUCCESS MESSAGE
            ================================== -->

            <?php if (
                isset($_GET["success"]) &&
                $_GET["success"] === "deleted"
            ): ?>

                <div class="profile-success-message">

                    <i class="fa-solid fa-circle-check"></i>

                    Inquiry deleted successfully.

                </div>

            <?php endif; ?>


            <!-- =================================
                 ERROR MESSAGE
            ================================== -->

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

                    Inquiry could not be found.

                </div>

            <?php endif; ?>


            <!-- =================================
                 INQUIRIES CARD
            ================================== -->

            <section class="admin-users-card admin-inquiries-card">


                <div class="admin-users-header">

                    <div>

                        <h2>
                            All Inquiries
                        </h2>

                        <p>

                            <?php
                            echo $total_inquiries;
                            ?>

                            inquir<?php
                            echo $total_inquiries === 1
                                ? "y"
                                : "ies";
                            ?> found.

                        </p>

                    </div>

                </div>


                <?php if ($total_inquiries > 0): ?>


                    <div class="admin-inquiries-table-wrapper">

                        <table class="admin-table admin-inquiry-table">


                            <colgroup>

                                <col class="inquiry-col-property">

                                <col class="inquiry-col-sender">

                                <col class="inquiry-col-owner">

                                <col class="inquiry-col-message">

                                <col class="inquiry-col-date">

                                <col class="inquiry-col-actions">

                            </colgroup>


                            <thead>

                                <tr>

                                    <th>Property</th>

                                    <th>Sender</th>

                                    <th>Owner</th>

                                    <th>Message</th>

                                    <th>Date</th>

                                    <th class="inquiry-actions-heading">
                                        Actions
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                                <?php foreach ($inquiries as $inquiry): ?>


                                    <tr>


                                        <!-- PROPERTY -->

                                        <td>

                                            <div class="admin-property-cell">


                                                <div class="admin-property-thumb">

                                                    <?php if (
                                                        !empty(
                                                            $inquiry["main_image"]
                                                        )
                                                    ): ?>

                                                        <img
                                                            src="/myhome/<?php
                                                            echo htmlspecialchars(
                                                                $inquiry["main_image"]
                                                            );
                                                            ?>"
                                                            alt="Property Image"
                                                        >

                                                    <?php else: ?>

                                                        <i class="fa-solid fa-house"></i>

                                                    <?php endif; ?>

                                                </div>


                                                <div class="inquiry-property-info">

                                                    <strong>

                                                        <?php
                                                        echo htmlspecialchars(
                                                            $inquiry["property_title"]
                                                        );
                                                        ?>

                                                    </strong>


                                                    <small>

                                                        Property ID:
                                                        <?php
                                                        echo (int) $inquiry["property_id"];
                                                        ?>

                                                    </small>

                                                </div>

                                            </div>

                                        </td>


                                        <!-- SENDER -->

                                        <td>

                                            <div class="inquiry-sender">

                                                <strong>

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $inquiry["sender_name"]
                                                    );
                                                    ?>

                                                </strong>


                                                <small>

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $inquiry["sender_email"]
                                                    );
                                                    ?>

                                                </small>

                                            </div>

                                        </td>


                                        <!-- OWNER -->

                                        <td>

                                            <span class="inquiry-owner">

                                                <?php
                                                echo htmlspecialchars(
                                                    $inquiry["owner_name"]
                                                );
                                                ?>

                                            </span>

                                        </td>


                                        <!-- MESSAGE -->

                                        <td>

                                            <div class="admin-inquiry-message">

                                                <?php
                                                echo htmlspecialchars(
                                                    $inquiry["message"]
                                                );
                                                ?>

                                            </div>

                                        </td>


                                        <!-- DATE -->

                                        <td>

                                            <div class="admin-inquiry-date">

                                                <span>

                                                    <?php
                                                    echo date(
                                                        "M d, Y",
                                                        strtotime(
                                                            $inquiry["created_at"]
                                                        )
                                                    );
                                                    ?>

                                                </span>


                                                <small>

                                                    <?php
                                                    echo date(
                                                        "h:i A",
                                                        strtotime(
                                                            $inquiry["created_at"]
                                                        )
                                                    );
                                                    ?>

                                                </small>

                                            </div>

                                        </td>


                                        <!-- ACTIONS -->

                                        <td class="admin-inquiry-actions">

                                            <div class="admin-action-menu">


                                                <button
                                                    type="button"
                                                    class="admin-action-btn"
                                                    onclick="toggleInquiryMenu(this)"
                                                    aria-label="Inquiry actions"
                                                >

                                                    <i class="fa-solid fa-ellipsis"></i>

                                                </button>


                                                <div class="admin-action-dropdown">


                                                    <!-- VIEW PROPERTY -->

                                                    <a
                                                        href="/myhome/properties/view-property.php?id=<?php
                                                        echo (int) $inquiry["property_id"];
                                                        ?>"
                                                        class="admin-dropdown-item"
                                                    >

                                                        <i class="fa-solid fa-house"></i>

                                                        View Property

                                                    </a>


                                                    <!-- VIEW FULL INQUIRY -->

                                                    <button
                                                        type="button"
                                                        class="admin-dropdown-item"
                                                        onclick='openInquiryModal(
                                                            <?php echo json_encode($inquiry["sender_name"]); ?>,
                                                            <?php echo json_encode($inquiry["sender_email"]); ?>,
                                                            <?php echo json_encode($inquiry["property_title"]); ?>,
                                                            <?php echo json_encode($inquiry["owner_name"]); ?>,
                                                            <?php echo json_encode($inquiry["message"]); ?>,
                                                            <?php
                                                            echo json_encode(
                                                                date(
                                                                    "M d, Y - h:i A",
                                                                    strtotime(
                                                                        $inquiry["created_at"]
                                                                    )
                                                                )
                                                            );
                                                            ?>
                                                        )'
                                                    >

                                                        <i class="fa-solid fa-envelope-open-text"></i>

                                                        View Full Inquiry

                                                    </button>


                                                    <!-- DELETE -->

                                                    <button
                                                        type="button"
                                                        class="admin-dropdown-item delete"
                                                        onclick='openDeleteInquiryModal(
                                                            <?php
                                                            echo (int) $inquiry["id"];
                                                            ?>,
                                                            <?php
                                                            echo json_encode(
                                                                $inquiry["sender_name"]
                                                            );
                                                            ?>
                                                        )'
                                                    >

                                                        <i class="fa-solid fa-trash"></i>

                                                        Delete Inquiry

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

                        <i class="fa-solid fa-envelope-open-text"></i>

                        <h3>
                            No inquiries found
                        </h3>

                        <p>
                            User inquiries will appear here.
                        </p>

                    </div>


                <?php endif; ?>


            </section>


            <!-- =================================
                 VIEW INQUIRY MODAL
            ================================== -->

            <div
                class="admin-modal-overlay"
                id="viewInquiryModal"
            >

                <div class="admin-modal admin-inquiry-modal">


                    <div class="admin-modal-icon inquiry">

                        <i class="fa-solid fa-envelope-open-text"></i>

                    </div>


                    <h2>
                        Inquiry Details
                    </h2>


                    <div class="admin-inquiry-details">


                        <div>

                            <span>
                                Sender
                            </span>

                            <strong id="modalSender"></strong>

                        </div>


                        <div>

                            <span>
                                Email
                            </span>

                            <strong id="modalEmail"></strong>

                        </div>


                        <div>

                            <span>
                                Property
                            </span>

                            <strong id="modalProperty"></strong>

                        </div>


                        <div>

                            <span>
                                Property Owner
                            </span>

                            <strong id="modalOwner"></strong>

                        </div>


                        <div>

                            <span>
                                Date Sent
                            </span>

                            <strong id="modalDate"></strong>

                        </div>


                        <div class="admin-inquiry-full-message">

                            <span>
                                Message
                            </span>

                            <p id="modalMessage"></p>

                        </div>


                    </div>


                    <div class="admin-modal-actions">

                        <button
                            type="button"
                            class="admin-modal-cancel"
                            onclick="closeInquiryModal()"
                        >

                            Close

                        </button>

                    </div>


                </div>

            </div>


            <!-- =================================
                 DELETE INQUIRY MODAL
            ================================== -->

            <div
                class="admin-modal-overlay"
                id="deleteInquiryModal"
            >

                <div class="admin-modal">


                    <div class="admin-modal-icon">

                        <i class="fa-solid fa-triangle-exclamation"></i>

                    </div>


                    <h2>
                        Delete Inquiry?
                    </h2>


                    <p>

                        Are you sure you want to delete the inquiry from

                        <strong id="deleteInquirySender"></strong>?

                        This action cannot be undone.

                    </p>


                    <div class="admin-modal-actions">


                        <button
                            type="button"
                            class="admin-modal-cancel"
                            onclick="closeDeleteInquiryModal()"
                        >

                            Cancel

                        </button>


                        <form
                            action="/myhome/admin/inquiry-action.php"
                            method="POST"
                        >

                            <input
                                type="hidden"
                                name="inquiry_id"
                                id="deleteInquiryId"
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

                                Delete Inquiry

                            </button>

                        </form>


                    </div>


                </div>

            </div>


        </section>


    </div>

</main>


<script>


// =========================================
// ACTION MENU
// =========================================

function toggleInquiryMenu(button) {

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


    menu.classList.toggle(
        "show"
    );

}


// =========================================
// CLOSE ACTION MENU
// =========================================

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


// =========================================
// VIEW INQUIRY
// =========================================

function openInquiryModal(
    sender,
    email,
    property,
    owner,
    message,
    date
) {

    document.getElementById(
        "modalSender"
    ).textContent = sender;


    document.getElementById(
        "modalEmail"
    ).textContent = email;


    document.getElementById(
        "modalProperty"
    ).textContent = property;


    document.getElementById(
        "modalOwner"
    ).textContent = owner;


    document.getElementById(
        "modalMessage"
    ).textContent = message;


    document.getElementById(
        "modalDate"
    ).textContent = date;


    document.getElementById(
        "viewInquiryModal"
    ).classList.add(
        "show"
    );

}


// =========================================
// CLOSE VIEW MODAL
// =========================================

function closeInquiryModal() {

    document.getElementById(
        "viewInquiryModal"
    ).classList.remove(
        "show"
    );

}


// =========================================
// DELETE INQUIRY
// =========================================

function openDeleteInquiryModal(
    inquiryId,
    sender
) {

    document.getElementById(
        "deleteInquiryId"
    ).value = inquiryId;


    document.getElementById(
        "deleteInquirySender"
    ).textContent = sender;


    document.getElementById(
        "deleteInquiryModal"
    ).classList.add(
        "show"
    );

}


// =========================================
// CLOSE DELETE MODAL
// =========================================

function closeDeleteInquiryModal() {

    document.getElementById(
        "deleteInquiryModal"
    ).classList.remove(
        "show"
    );

}


// =========================================
// CLICK OUTSIDE VIEW MODAL
// =========================================

document
    .getElementById(
        "viewInquiryModal"
    )
    .addEventListener(
        "click",
        function(event) {

            if (
                event.target === this
            ) {

                closeInquiryModal();
            }

        }
    );


// =========================================
// CLICK OUTSIDE DELETE MODAL
// =========================================

document
    .getElementById(
        "deleteInquiryModal"
    )
    .addEventListener(
        "click",
        function(event) {

            if (
                event.target === this
            ) {

                closeDeleteInquiryModal();
            }

        }
    );


// =========================================
// ESCAPE KEY
// =========================================

document.addEventListener(
    "keydown",
    function(event) {

        if (
            event.key === "Escape"
        ) {

            closeInquiryModal();

            closeDeleteInquiryModal();
        }

    }
);


</script>
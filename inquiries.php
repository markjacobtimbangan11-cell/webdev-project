<?php

session_start();

$pdo = require "config/database.php";


// =========================================
// NORMAL USER ACCESS ONLY
// =========================================

if (!isset($_SESSION["user_id"])) {

    header("Location: /myhome/login.php");

    exit;
}


if (
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "user"
) {

    header("Location: /myhome/index.php");

    exit;
}

$page_title = "Inquiries | MyHome";

$user_id = (int) $_SESSION["user_id"];


// =========================================
// GET INQUIRIES FOR MY PROPERTIES
// =========================================

try {

    $stmt = $pdo->prepare(
        "SELECT
            i.id,
            i.message,
            i.created_at,

            p.id AS property_id,
            p.title AS property_title,

            u.full_name AS sender_name,
            u.email AS sender_email,

            (
                SELECT pi.image_path
                FROM property_images pi
                WHERE pi.property_id = p.id
                ORDER BY pi.id ASC
                LIMIT 1
            ) AS property_image

         FROM inquiries i

         INNER JOIN properties p
            ON p.id = i.property_id

         INNER JOIN users u
            ON u.id = i.user_id

         WHERE p.user_id = :user_id

         ORDER BY i.created_at DESC"
    );


    $stmt->execute([
        "user_id" => $user_id
    ]);


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
// HEADER
// =========================================

include "includes/header.php";

include "includes/navbar.php";

?>


<main class="inquiries-page">

    <section class="inquiries-container">


        <!-- =========================
             HEADER
        ========================== -->

        <div class="inquiries-header">

            <p class="section-label">
                PROPERTY MESSAGES
            </p>

            <h1>
                Inquiries
            </h1>

            <p>
                Messages from users interested in your properties.
            </p>

        </div>


        <!-- =========================
             INQUIRY COUNT
        ========================== -->

        <?php if ($total_inquiries > 0): ?>

            <div class="inquiries-count">

                <strong>
                    <?php echo $total_inquiries; ?>
                </strong>

                <?php
                echo $total_inquiries === 1
                    ? "inquiry received"
                    : "inquiries received";
                ?>

            </div>

        <?php endif; ?>


        <!-- =========================
             INQUIRY LIST
        ========================== -->

        <?php if ($total_inquiries > 0): ?>

            <div class="inquiries-list">


                <?php foreach ($inquiries as $inquiry): ?>


                    <article class="inquiry-card">


                        <!-- =========================
                             PROPERTY IMAGE
                        ========================== -->

                        <div class="inquiry-property-image">


                            <?php if (!empty($inquiry["property_image"])): ?>

                                <img
                                    src="/myhome/<?php
                                    echo htmlspecialchars(
                                        $inquiry["property_image"]
                                    );
                                    ?>"
                                    alt="<?php
                                    echo htmlspecialchars(
                                        $inquiry["property_title"]
                                    );
                                    ?>"
                                >

                            <?php else: ?>

                                <div class="inquiry-no-image">

                                    <i class="fa-solid fa-house"></i>

                                </div>

                            <?php endif; ?>


                        </div>


                        <!-- =========================
                             CONTENT
                        ========================== -->

                        <div class="inquiry-content">


                            <!-- TOP -->

                            <div class="inquiry-top">

                                <div>

                                    <span class="inquiry-property-label">
                                        INQUIRY FOR
                                    </span>

                                    <h2>

                                        <a
                                            href="/myhome/properties/view-property.php?id=<?php
                                            echo (int) $inquiry["property_id"];
                                            ?>"
                                        >

                                            <?php
                                            echo htmlspecialchars(
                                                $inquiry["property_title"]
                                            );
                                            ?>

                                        </a>

                                    </h2>

                                </div>


                                <time>

                                    <?php

                                    $date =
                                        new DateTime(
                                            $inquiry["created_at"]
                                        );

                                    echo $date->format(
                                        "M j, Y • g:i A"
                                    );

                                    ?>

                                </time>

                            </div>


                            <!-- =========================
                                 SENDER
                            ========================== -->

                            <div class="inquiry-sender">


                                <div class="inquiry-sender-icon">

                                    <i class="fa-solid fa-user"></i>

                                </div>


                                <div>

                                    <strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $inquiry["sender_name"]
                                        );
                                        ?>

                                    </strong>


                                    <span>

                                        <?php
                                        echo htmlspecialchars(
                                            $inquiry["sender_email"]
                                        );
                                        ?>

                                    </span>

                                </div>


                            </div>


                            <!-- =========================
                                 MESSAGE
                            ========================== -->

                            <div class="inquiry-message">

                                <i class="fa-solid fa-quote-left"></i>

                                <p>

                                    <?php
                                    echo nl2br(
                                        htmlspecialchars(
                                            $inquiry["message"]
                                        )
                                    );
                                    ?>

                                </p>

                            </div>


                            <!-- =========================
                                 ACTIONS
                            ========================== -->

                            <div class="inquiry-actions">


                                <a
                                    href="/myhome/properties/view-property.php?id=<?php
                                    echo (int) $inquiry["property_id"];
                                    ?>"
                                    class="inquiry-view-btn"
                                >

                                    <i class="fa-solid fa-house"></i>

                                    View Property

                                </a>


                                <a
                                    href="mailto:<?php
                                    echo htmlspecialchars(
                                        $inquiry["sender_email"]
                                    );
                                    ?>"
                                    class="inquiry-reply-btn"
                                >

                                    <i class="fa-solid fa-envelope"></i>

                                    Reply by Email

                                </a>


                            </div>


                        </div>


                    </article>


                <?php endforeach; ?>


            </div>


        <?php else: ?>


            <!-- =========================
                 EMPTY STATE
            ========================== -->

            <div class="empty-listings">


                <i class="fa-regular fa-envelope"></i>


                <h2>
                    No inquiries yet
                </h2>


                <p>
                    Messages from interested users will appear here.
                </p>


                <a
                    href="/myhome/properties/my-listings.php"
                    class="add-property-btn"
                >
                    View My Listings
                </a>


            </div>


        <?php endif; ?>


    </section>

</main>


<?php

include "includes/footer.php";

?>

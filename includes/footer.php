<!-- =========================
     FOOTER
========================= -->

<footer id="footer">

    <div class="footer-container">


        <!-- LOGO AND SUBSCRIBE -->

        <div class="footer-about">

            <div class="footer-logo">

                <img
                    src="/myhome/images/logo.png"
                    alt="MyHome Logo"
                >

            </div>


            <p>
                Your comfort is our priority.
                Verified properties with zero
                hidden fees.
            </p>


            <!-- SUBSCRIBE -->

            <div class="subscribe">

                <input
                    type="email"
                    id="subscriberEmail"
                    placeholder="E-MAIL"
                    autocomplete="email"
                >

                <button
                    type="button"
                    id="subscriberBtn"
                >
                    SUBSCRIBE NOW
                </button>

            </div>


            <div
                class="subscribe-message"
                id="subscriberMessage"
            ></div>

        </div>


        <!-- PROPERTY TYPES -->

        <div class="footer-column">

            <h3>
                PROPERTY TYPES
            </h3>

            <a href="/myhome/properties/listings.php?type=rent">
                House for rent
            </a>

            <a href="/myhome/properties/listings.php?type=rent">
                Apartment for rent
            </a>

            <a href="/myhome/properties/listings.php?type=sale">
                Houses for sale
            </a>

            <a href="/myhome/properties/listings.php">
                Condos and Townhomes
            </a>

            <a href="/myhome/properties/listings.php">
                Luxury Properties
            </a>

        </div>


        <!-- QUICK LINKS -->

        <div class="footer-column">

            <h3>
                QUICK LINKS
            </h3>

            <a href="/myhome/index.php">
                Home
            </a>

            <a href="/myhome/properties/listings.php?type=rent">
                Properties for rent
            </a>

            <a href="/myhome/properties/listings.php?type=sale">
                Properties for sale
            </a>

            <a href="/myhome/index.php#about">
                About Us
            </a>

            <a href="/myhome/index.php#testimonials">
                Customer Reviews
            </a>

            <a href="/myhome/index.php#footer">
                Contact Us
            </a>

        </div>


        <!-- CONTACT AND SUPPORT -->

        <div class="footer-column">

            <h3>
                CONTACT AND SUPPORT
            </h3>

            <a href="#">
                Help Center / FAQ
            </a>

            <a href="#">
                Schedule a Viewing
            </a>

            <a href="/myhome/properties/add-property.php">
                List your Property
            </a>

            <a href="#">
                Terms and Conditions
            </a>

            <a href="#">
                Privacy Policy
            </a>

            <a href="mailto:Support@MyHome.com">
                Support@MyHome.com
            </a>

        </div>

    </div>


    <!-- =========================
         FOOTER BOTTOM
    ========================== -->

    <div class="footer-bottom">

        <p>
            © 2026 MyHome Rent &amp; Sale.
            All rights reserved.
        </p>


        <!-- SOCIAL MEDIA -->

        <div class="social-icons">


            <!-- FACEBOOK -->

            <a
                href="https://www.facebook.com/mark.jacob.timbangan"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="Facebook"
                title="Facebook"
            >

                <i
                    class="fa-brands fa-facebook-f"
                ></i>

            </a>


            <!-- INSTAGRAM -->

            <a
                href="https://www.instagram.com/markeyyy_11/"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="Instagram"
                title="Instagram"
            >

                <i
                    class="fa-brands fa-instagram"
                ></i>

            </a>


            <!-- TIKTOK -->

            <a
                href="https://www.tiktok.com/@hotdognasunogz"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="TikTok"
                title="TikTok"
            >

                <i
                    class="fa-brands fa-tiktok"
                ></i>

            </a>

        </div>

    </div>

</footer>


<!-- =========================
     MAIN JAVASCRIPT
========================= -->

<script src="/myhome/js/script.js"></script>


<!-- =========================
     SUBSCRIBE SCRIPT
========================= -->

<script>

const subscriberBtn =
    document.getElementById(
        "subscriberBtn"
    );

const subscriberEmail =
    document.getElementById(
        "subscriberEmail"
    );

const subscriberMessage =
    document.getElementById(
        "subscriberMessage"
    );


if (
    subscriberBtn &&
    subscriberEmail &&
    subscriberMessage
) {

    subscriberBtn.addEventListener(
        "click",
        subscribeUser
    );


    subscriberEmail.addEventListener(
        "keydown",
        function (event) {

            if (event.key === "Enter") {

                event.preventDefault();

                subscribeUser();
            }
        }
    );
}


async function subscribeUser() {

    const email =
        subscriberEmail.value.trim();


    subscriberMessage.innerHTML = "";


    // EMPTY EMAIL

    if (email === "") {

        showSubscribeMessage(
            "error",
            "Please enter your email address."
        );

        return;
    }


    // EMAIL VALIDATION

    const emailPattern =
        /^[^\s@]+@[^\s@]+\.[^\s@]+$/;


    if (!emailPattern.test(email)) {

        showSubscribeMessage(
            "error",
            "Please enter a valid email address."
        );

        return;
    }


    // DISABLE BUTTON WHILE SAVING

    subscriberBtn.disabled = true;

    subscriberBtn.textContent =
        "SUBSCRIBING...";


    try {

        const response =
            await fetch(
                "/myhome/subscribe.php",
                {
                    method: "POST",

                    headers: {
                        "Content-Type":
                            "application/x-www-form-urlencoded"
                    },

                    body:
                        "email=" +
                        encodeURIComponent(email)
                }
            );


        const data =
            await response.json();


        // SUCCESS

        if (data.success) {

            showSubscribeMessage(
                "success",
                data.message
            );


            subscriberEmail.value = "";

        }

        // SERVER ERROR MESSAGE

        else {

            showSubscribeMessage(
                "error",
                data.message
            );
        }

    }

    catch (error) {

        showSubscribeMessage(
            "error",
            "Something went wrong. Please try again."
        );
    }

    finally {

        subscriberBtn.disabled = false;

        subscriberBtn.textContent =
            "SUBSCRIBE NOW";
    }
}


// =========================================
// SHOW SUBSCRIBE MESSAGE
// =========================================

function showSubscribeMessage(
    type,
    message
) {

    let icon = "";


    if (type === "success") {

        icon =
            '<i class="fa-solid fa-circle-check"></i>';

    } else {

        icon =
            '<i class="fa-solid fa-circle-exclamation"></i>';
    }


    subscriberMessage.innerHTML = `
        <div class="subscribe-alert ${type}">

            ${icon}

            <div>
                <span>${message}</span>
            </div>

        </div>
    `;


    // HIDE SUCCESS MESSAGE AFTER 3 SECONDS

    if (type === "success") {

        setTimeout(
            function () {

                subscriberMessage.innerHTML =
                    "";

            },
            3000
        );
    }
}

</script>


</body>

</html>
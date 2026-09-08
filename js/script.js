const menuToggle = document.getElementById("menuToggle");
const navMenu = document.getElementById("navMenu");

if (menuToggle && navMenu) {

    menuToggle.addEventListener("click", function () {

        navMenu.classList.toggle("active");

    });

}


/* =========================================
   CLOSE MOBILE MENU AFTER CLICK
========================================= */

const navLinks = document.querySelectorAll(".nav-link");

navLinks.forEach(function(link) {

    link.addEventListener("click", function() {

        navMenu.classList.remove("active");

    });

});


/* =========================================
   ACTIVE NAVIGATION
========================================= */

const sections = document.querySelectorAll("section");
const links = document.querySelectorAll(".nav-link");

window.addEventListener("scroll", function() {

    let current = "";

    sections.forEach(function(section) {

        const sectionTop = section.offsetTop - 150;

        if (window.scrollY >= sectionTop) {

            current = section.getAttribute("id");

        }

    });

    links.forEach(function(link) {

        link.classList.remove("active");

        if (
            link.getAttribute("href") === "#" + current
        ) {

            link.classList.add("active");

        }

    });

});


/* =========================================
   NAVBAR SHADOW WHEN SCROLLING
========================================= */

const navbar = document.querySelector(".navbar");

window.addEventListener("scroll", function() {

    if (window.scrollY > 50) {

        navbar.style.boxShadow =
            "0 5px 25px rgba(0,0,0,0.15)";

    } else {

        navbar.style.boxShadow =
            "0 3px 20px rgba(0,0,0,0.08)";

    }

});


/* =========================================
   SCROLL REVEAL ANIMATION
========================================= */

const animatedElements =
    document.querySelectorAll(
        ".feature, .property-card, .testimonial, .family-content, .family-images"
    );


const observer = new IntersectionObserver(

    function(entries) {

        entries.forEach(function(entry) {

            if (entry.isIntersecting) {

                entry.target.classList.add("show");

            }

        });

    },

    {
        threshold: 0.15
    }

);


animatedElements.forEach(function(element) {

    observer.observe(element);

});


/* =========================================
   RESERVE NOW BUTTONS
========================================= */

const reserveButtons =
    document.querySelectorAll(".primary-btn");

reserveButtons.forEach(function(button) {

    button.addEventListener("click", function() {

        console.log("Reserve button clicked");

    });

});


/* =========================================
   PROPERTY VIEW BUTTON
========================================= */

const viewButtons =
    document.querySelectorAll(".property-bottom button");

viewButtons.forEach(function(button) {

    button.addEventListener("click", function() {

        alert(
            "Property details will be available soon."
        );

    });

});


/* =========================================
   DELETE PROPERTY MODAL
========================================= */

const deleteModal =
    document.getElementById("deleteModal");

const confirmDeleteBtn =
    document.getElementById("confirmDeleteBtn");

const deletePropertyName =
    document.getElementById("deletePropertyName");


function openDeleteModal(
    propertyId,
    propertyName
) {

    if (
        !deleteModal ||
        !confirmDeleteBtn ||
        !deletePropertyName
    ) {
        return;
    }

    deletePropertyName.textContent =
        propertyName;

    confirmDeleteBtn.href =
        "/myhome/properties/delete-property.php?id="
        + propertyId;

    deleteModal.classList.add("active");

}


function closeDeleteModal() {

    if (!deleteModal) {
        return;
    }

    deleteModal.classList.remove("active");

}


if (deleteModal) {

    deleteModal.addEventListener(
        "click",
        function(event) {

            if (event.target === deleteModal) {
                closeDeleteModal();
            }

        }
    );

}


document.addEventListener(
    "keydown",
    function(event) {

        if (event.key === "Escape") {
            closeDeleteModal();
        }

    }
);
/* =========================================
   CHANGE PASSWORD
========================================= */

const passwordToggles =
    document.querySelectorAll(
        ".password-toggle"
    );

passwordToggles.forEach(
    function(button) {

        button.addEventListener(
            "click",
            function() {

                const targetId =
                    button.getAttribute(
                        "data-target"
                    );

                const input =
                    document.getElementById(
                        targetId
                    );

                if (!input) {
                    return;
                }

                const icon =
                    button.querySelector("i");

                if (
                    input.type === "password"
                ) {

                    input.type = "text";

                    icon.classList.remove(
                        "fa-eye"
                    );

                    icon.classList.add(
                        "fa-eye-slash"
                    );

                } else {

                    input.type =
                        "password";

                    icon.classList.remove(
                        "fa-eye-slash"
                    );

                    icon.classList.add(
                        "fa-eye"
                    );
                }

            }
        );

    }
);


// PASSWORD REQUIREMENTS

const newPasswordInput =
    document.getElementById(
        "new_password"
    );

const confirmPasswordInput =
    document.getElementById(
        "confirm_password"
    );

const matchMessage =
    document.getElementById(
        "passwordMatchMessage"
    );


if (newPasswordInput) {

    const requirementItems =
        document.querySelectorAll(
            ".password-requirements span"
        );


    newPasswordInput.addEventListener(
        "input",
        function() {

            const value =
                newPasswordInput.value;

            const checks = [
                value.length >= 8,
                /[A-Z]/.test(value),
                /[a-z]/.test(value),
                /[0-9]/.test(value),
                /[^A-Za-z0-9]/.test(value)
            ];


            requirementItems.forEach(
                function(item, index) {

                    const icon =
                        item.querySelector("i");

                    if (checks[index]) {

                        item.classList.add(
                            "valid"
                        );

                        icon.classList.remove(
                            "fa-xmark"
                        );

                        icon.classList.add(
                            "fa-check"
                        );

                    } else {

                        item.classList.remove(
                            "valid"
                        );

                        icon.classList.remove(
                            "fa-check"
                        );

                        icon.classList.add(
                            "fa-xmark"
                        );
                    }

                }
            );

            checkPasswordMatch();

        }
    );
}


if (confirmPasswordInput) {

    confirmPasswordInput.addEventListener(
        "input",
        checkPasswordMatch
    );

}


function checkPasswordMatch() {

    if (
        !newPasswordInput ||
        !confirmPasswordInput ||
        !matchMessage
    ) {
        return;
    }

    if (
        confirmPasswordInput.value === ""
    ) {

        matchMessage.textContent = "";

        matchMessage.className = "";

        return;
    }

    if (
        newPasswordInput.value ===
        confirmPasswordInput.value
    ) {

        matchMessage.textContent =
            "Passwords match.";

        matchMessage.className =
            "match";

    } else {

        matchMessage.textContent =
            "Passwords do not match.";

        matchMessage.className =
            "no-match";
    }
}

function openFavoriteModal(propertyId, propertyTitle) {

    const modal =
        document.getElementById("favoriteModal");

    const propertyIdInput =
        document.getElementById("favoritePropertyId");

    const propertyTitleText =
        document.getElementById("favoritePropertyTitle");


    if (
        !modal ||
        !propertyIdInput ||
        !propertyTitleText
    ) {
        return;
    }


    propertyIdInput.value = propertyId;

    propertyTitleText.textContent =
        '"' + propertyTitle + '"';


    modal.classList.add("active");

    document.body.style.overflow = "hidden";
}


function closeFavoriteModal() {

    const modal =
        document.getElementById("favoriteModal");


    if (!modal) {
        return;
    }


    modal.classList.remove("active");

    document.body.style.overflow = "";
}


/* CLOSE WHEN CLICKING BACKDROP */

document.addEventListener(
    "click",
    function (event) {

        const modal =
            document.getElementById(
                "favoriteModal"
            );


        if (
            modal &&
            event.target === modal
        ) {

            closeFavoriteModal();

        }

    }
);


/* CLOSE WITH ESC KEY */

document.addEventListener(
    "keydown",
    function (event) {

        if (event.key === "Escape") {

            closeFavoriteModal();

        }

    }
);

/* =========================================
   SUBSCRIBE
========================================= */

const subscribeBtn =
    document.getElementById("subscribeBtn");

const subscribeEmail =
    document.getElementById("subscribeEmail");

const subscribeMessage =
    document.getElementById("subscribeMessage");


if (
    subscribeBtn &&
    subscribeEmail &&
    subscribeMessage
) {

    subscribeBtn.addEventListener(
        "click",
        function () {

            const email =
                subscribeEmail.value.trim();


            // EMPTY EMAIL

            if (email === "") {

                subscribeMessage.innerHTML = `
                    <div class="subscribe-alert error">

                        <i class="fa-solid fa-circle-exclamation"></i>

                        <span>
                            Please enter your email address.
                        </span>

                    </div>
                `;

                return;
            }


            // INVALID EMAIL

            if (!subscribeEmail.checkValidity()) {

                subscribeMessage.innerHTML = `
                    <div class="subscribe-alert error">

                        <i class="fa-solid fa-circle-exclamation"></i>

                        <span>
                            Please enter a valid email address.
                        </span>

                    </div>
                `;

                return;
            }


            // SUCCESS

            subscribeMessage.innerHTML = `
                <div class="subscribe-alert success">

                    <i class="fa-solid fa-circle-check"></i>

                    <div>

                        <strong>
                            Subscription Successful!
                        </strong>

                        <span>
                            Thank you for subscribing to MyHome.
                        </span>

                    </div>

                </div>
            `;


            subscribeEmail.value = "";


            // HIDE AFTER 3 SECONDS

            setTimeout(function () {

                subscribeMessage.innerHTML = "";

            }, 3000);

        }
    );
}
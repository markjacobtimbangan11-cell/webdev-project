<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?php
        echo isset($page_title)
            ? htmlspecialchars($page_title)
            : "MyHome | Rent & Sale";
        ?>
    </title>


    <!-- GOOGLE FONTS -->

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >


    <!-- FONT AWESOME -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <!-- MAIN CSS -->

    <link
        rel="stylesheet"
        href="/myhome/css/style.css"
    >


    <!-- ACCOUNT CSS -->

    <link
        rel="stylesheet"
        href="/myhome/css/account.css"
    >


    <!-- PROPERTIES CSS -->

    <link
        rel="stylesheet"
        href="/myhome/css/properties.css"
    >

</head>

<body>
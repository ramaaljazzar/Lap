<?php
// Vallant Felipe
// 04.09.2024
// LAP - Beispiel: Start-Page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
        require_once "./includes/loadRessources.inc.php";
        echo "<script>setTitle('". basename(__FILE__, '.php') ."');</script>";
    ?>
    <title>Filmverwaltung</title>
</head>
<body>
    <?php
        require_once "./includes/navbar.inc.php";   // load Navbar
    ?>

    <section class="text-center container">
        <h1>Startseite</h1>
        <h2>Herzlich Willkommen auf dieser Website.</h2>
        <br>
        <p>Hier haben Sie die Möglichkeit sich Filme mittels Filter anzeigen zu lassen.
        <br>Wählen Sie hierzu einen der Navigations-Punkte</p>
    </section>
</body>
</html>
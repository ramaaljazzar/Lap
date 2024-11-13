<?php
// Start des PHP-Codes
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    // Ressourcen einbinden
    require_once "./includes/loadRessources.inc.php";
    echo "<script>setTitle('" . basename(__FILE__, '.php') . "');</script>";
    ?>
</head>

<body>
    <?php
    // Navigation einbinden
    require_once "./includes/navbar.inc.php";
    ?>

    <div class="container">
        <h1>Schulklassen</h1>

        <?php
        // Beispielhafte Verbindung zur Datenbank (dies muss durch deine tatsächliche Verbindung ersetzt werden)
        // $conn = new PDO("mysql:host=localhost;dbname=deine_datenbank", "username", "password");

        $searchValue = '4';  // Beispielhafter Suchwert
        $searchValue = '%' . $searchValue . '%';

        // SQL-Abfrage vorbereiten
        $query = 'SELECT * FROM plz WHERE plz_nr LIKE ? ORDER BY plz_nr';
        $stmt = $conn->prepare($query);
        $stmt->execute([$searchValue]);

        $results = $stmt->fetchAll();

        if (count($results) > 0) {
            echo '<table class="table table-striped">';
            echo '<thead>';
            echo '<tr>';
            echo '<th scope="col">PLZ ID</th>';
            echo '<th scope="col">Postleitzahl</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            // Ergebnisse in einer Tabelle anzeigen
            foreach ($results as $row) {
                echo '<tr>';
                echo '<td>' . $row['plz_id'] . '</td>';
                echo '<td>' . $row['plz_nr'] . '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>Keine Ergebnisse gefunden.</p>';
        }
        ?>
    </div>
</body>

</html>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once "./includes/loadRessources.inc.php";
    echo "<script>setTitle('Datenbankübersicht');</script>";
    ?>
    <style>
        .table-container {
            margin-bottom: 40px;
        }
    </style>
</head>

<body>
    <?php
    // Verbindung zur Datenbank
    try {
        // Setze $conn auf deine bestehende PDO-Datenbankverbindung
        // z.B., $conn = new PDO('mysql:host=localhost;dbname=deine_datenbank', 'benutzername', 'passwort');

        // Alle Tabellen der Datenbank abrufen
        $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

        if (empty($tables)) {
            echo "<p>Keine Tabellen in der Datenbank gefunden.</p>";
        } else {
            echo "<h1>Inhalt der Datenbank</h1>";

            foreach ($tables as $table) {
                echo "<div class='table-container'>";
                echo "<h2>Tabelle: $table</h2>";

                // Alle Daten aus der aktuellen Tabelle abrufen
                $query = "SELECT * FROM `$table`";
                $stmt = $conn->query($query);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($results) > 0) {
                    // HTML-Tabelle für die Daten der aktuellen Tabelle
                    echo '<table class="table table-striped">';
                    echo '<thead><tr>';

                    // Tabellenüberschriften dynamisch aus den Spaltennamen erzeugen
                    foreach (array_keys($results[0]) as $column) {
                        echo '<th scope="col">' . htmlspecialchars($column) . '</th>';
                    }
                    echo '</tr></thead><tbody>';

                    // Tabelleninhalte ausgeben
                    foreach ($results as $row) {
                        echo '<tr>';
                        foreach ($row as $cell) {
                            echo '<td>' . htmlspecialchars($cell) . '</td>';
                        }
                        echo '</tr>';
                    }

                    echo '</tbody></table>';
                } else {
                    echo "<p>Keine Daten in der Tabelle $table gefunden.</p>";
                }

                echo "</div>";
            }
        }
    } catch (PDOException $e) {
        echo "<p>Fehler bei der Datenbankverbindung: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    ?>
</body>

</html>


<?php
// Vallant Felipe
// 04.09.2024
// LAP - Beispiel: Connection String

$servername = "localhost:3307";
$username = "root";
$password = "";

try {
    // Verbindung nur zum MySQL-Server, ohne spezifische Datenbank
    $conn = new PDO("mysql:host=$servername", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<script>console.log('Connected successfully');</script>";

    // Abfrage, um alle Datenbanken aufzulisten
    $query = "SHOW DATABASES";
    $stmt = $conn->query($query);
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "<h1>Alle Datenbanken auf dem Server</h1>";
    echo "<ul>";
    foreach ($databases as $database) {
        echo "<li>" . htmlspecialchars($database) . "</li>";
    }
    echo "</ul>";

} catch(PDOException $e) {
    echo "<script>console.log('Connection failed');</script>";
    # echo "Connection failed: " . $e->getMessage();
}








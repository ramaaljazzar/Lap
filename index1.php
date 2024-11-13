<?php
// Datenbankverbindung herstellen
$servername = "localhost:3307";
$username = "root";
$password = "";

try {
    $con = new PDO("mysql:host=$servername", $username, $password);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<script>console.log('Connected successfully');</script>";
} catch (PDOException $e) {
    echo "<script>console.log('Connection failed');</script>";
    die("Connection failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once "./includes/loadRessources.inc.php";
    echo "<script>setTitle('" . basename(__FILE__, '.php') . "');</script>";
    ?>
    <title>Datenbankübersicht</title>
    <style>
        .table-container {
            margin-bottom: 40px;
        }
    </style>
</head>
<body>
<main>
    <?php
    if (isset($_POST['showSchema'])) {
        $schema = $_POST['schema'];

        /* Schemaauswahl */
        $useSchema = 'USE ' . $schema;
        $use = $con->prepare($useSchema);
        $use->execute();

        /* Alle Tabellen anzeigen */
        $allTables = "SHOW TABLES";
        $showTables = $con->prepare($allTables);
        $showTables->execute();

        echo '<form method="post">';
        echo '<table>';
        while ($row = $showTables->fetch(PDO::FETCH_NUM)) {
            echo '<tr>';
            foreach ($row as $r) {
                echo '<td><input type="checkbox" name="tables[]" value="' . $r . '">' . htmlspecialchars($r) . '</td>';
            }
            echo '</tr>';
        }
        echo '</table>';
        echo '<input type="hidden" name="schema" value="' . htmlspecialchars($schema) . '">';
        echo '<button name="showStruct">Tabellenstruktur anzeigen</button>';
        echo '<button name="showContent">Tabelleninhalt anzeigen</button>';
        echo '</form>';
    } elseif (isset($_POST['showStruct']) || isset($_POST['showContent'])) {
        $schema = $_POST['schema'];

        // Schema auswählen
        $useSchema = 'USE ' . $schema;
        $use = $con->prepare($useSchema);
        $use->execute();

        // Tabellen prüfen und auswählen
        if (isset($_POST['tables']) && !empty($_POST['tables'])) {
            foreach ($_POST['tables'] as $table) {
                echo "<h3>Tabelle: " . htmlspecialchars($table) . "</h3>";

                if (isset($_POST['showStruct'])) {
                    $query = 'DESCRIBE ' . $table;
                } else {
                    $query = 'SELECT * FROM ' . $table;
                }

                $stmt = $con->prepare($query);
                $stmt->execute();
                echo '<table class="table">
                    <tr class="table">';

                // Spaltenbeschriftung
                for ($i = 0; $i < $stmt->columnCount(); $i++) {
                    echo '<th class="table">' . htmlspecialchars($stmt->getColumnMeta($i)['name']) . '</th>';
                }
                echo '</tr>';

                // Spalteninhalt
                while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                    echo '<tr class="table">';
                    foreach ($row as $r) {
                        echo '<td class="table">' . htmlspecialchars($r) . '</td>';
                    }
                    echo '</tr>';
                }
                echo '</table><br>';
            }
        } else {
            echo "<p>Bitte wählen Sie mindestens eine Tabelle aus.</p>";
        }
    } else {
        // Alle Schemas anzeigen
        $allSchemas = "SHOW DATABASES";
        $showSchema = $con->prepare($allSchemas);
        $showSchema->execute();

        echo '<form method="post">';
        echo '<table>';
        while ($row = $showSchema->fetch( PDO::FETCH_NUM)) {
            echo '<tr>';
            foreach ($row as $r) {
                echo '<td><input type="radio" name="schema" value="' . htmlspecialchars($r) . '">' . htmlspecialchars($r) . '</td>';
            }
            echo '</tr>';
        }
        echo '</table>';
        echo '<button name="showSchema">Schema auswählen</button>';
        echo '</form>';
    }
    ?>
</main>
</body>
</html>

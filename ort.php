<?php
// Felipe Vallant
// 04.09.2024
// LAP - Beispiel: Ort hinzufügen per PLZ

?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once "./includes/loadRessources.inc.php";
    echo "<script>setTitle('" . basename(__FILE__, '.php') . "');</script>";
    ?>
</head>

<body>
    <?php
    require_once "./includes/navbar.inc.php";   // load Navbar

    // Datenbankverbindung herstellen

    // Funktion zum Hinzufügen eines neuen Ortes
    function addLocation($conn, $locName, $plzId)
    {
        try {
            // Überprüfen, ob der Ort bereits existiert
            $query = 'SELECT ort_id FROM ort WHERE ort_name = ?';
            $stmt = $conn->prepare($query);
            $stmt->execute([$locName]);

            if ($stmt->rowCount() > 0) {
                return "Der Ort '$locName' existiert bereits.";
            }

            // Neuen Ort hinzufügen
            $query = 'INSERT INTO ort (ort_name) VALUES (?)';
            $stmt = $conn->prepare($query);
            $stmt->execute([$locName]);
            $newOrtId = $conn->lastInsertId();

            // Verbindung zwischen Ort und PLZ herstellen
            $query = 'INSERT INTO ort_plz (ort_id, plz_id) VALUES (?, ?)';
            $stmt = $conn->prepare($query);
            $stmt->execute([$newOrtId, $plzId]);

            return "successful";
        } catch (Exception $e) {
            return "Fehler: " . $e->getMessage();
        }
    }

    ?>

    <!-- Formular zum Hinzufügen eines neuen Ortes -->
    <section class="text-center container">
        <form method="post">
            <h1>Neuen Ort hinzufügen</h1>
            <label>Orts-/Stadtname: </label> 
            <input type="text" name="addingLocation" required><br><br>

            <label>PLZ: </label> 
            <select name="selectedPLZ" required>
                <option selected value="none">Wähle eine Postleitzahl</option>
                <?php
                // PLZs aus der Datenbank abfragen
                $query = 'SELECT * FROM plz ORDER BY plz_nr';
                $stmt = $conn->query($query);

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<option value="' . $row['plz_id'] . '">' . $row['plz_nr'] . '</option>';
                }
                ?>
            </select><br><br>

            <button type="submit" class="btn btn-primary" name="addLocationBtn" value="1">Hinzufügen</button>
            <button type="button" class="btn btn-secondary" onclick="window.location.href = 'index.php'; ">Abbrechen</button>
        </form>
    </section>

    <!-- Tabelle mit den hinzugefügten Orten -->
    <section class="text-center container">
        <?php
        if (isset($_POST["addLocationBtn"])) {
            $locName = trim($_POST["addingLocation"]);
            $plzId = $_POST["selectedPLZ"];

            // Ort hinzufügen
            $result = addLocation($conn, $locName, $plzId);
            if ($result == "successful") {
                echo "Ort erfolgreich hinzugefügt!";
            } else {
                echo "<span style='color: red;'>$result</span>";
            }

            // Abfrage und Anzeige der hinzugefügten Orte
            $query = "SELECT 
                        o.ort_id AS id, 
                        o.ort_name AS ort, 
                        p.plz_nr AS plz 
                      FROM 
                        ort AS o
                      INNER JOIN 
                        ort_plz AS op 
                        ON o.ort_id = op.ort_id
                      INNER JOIN 
                        plz AS p 
                        ON op.plz_id = p.plz_id
                      ORDER BY 
                        p.plz_nr;";
            $stmt = $conn->prepare($query);
            $stmt->execute();

            $results = $stmt->fetchAll();

            ?>
            <div class="searchInfo">
                <p><strong>Hinzufügte Orte</strong></p>
                <p>Anzahl der Orte: <strong><?php echo count($results); ?></strong></p>
            </div>
            <table class="table table-striped">
                <thead>
                    <tr class="table-dark">
                        <th scope="col"># OrtID</th>
                        <th scope="col">Ort</th>
                        <th scope="col">PLZ</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if (empty($results)) {
                    echo "<tr><td colspan='3'>Keine Orte gefunden</td></tr>";
                } else {
                    // Für jeden Eintrag eine Zeile erstellen
                    foreach ($results as $entry) {
                        echo "<tr>";
                        echo "<th scope='row'>" . $entry["id"] . "</th>";
                        echo "<td>" . $entry["ort"] . "</td>";
                        echo "<td>" . $entry["plz"] . "</td>";
                        echo "</tr>";
                    }
                }
                ?>
                </tbody>
            </table>
        <?php
        }
        ?>
    </section>
</body>

</html>

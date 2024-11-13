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
    require_once "./includes/navbar.inc.php";   // Navbar laden
?>
    <div class="container">
        <h1 class="mt-5">Scooterverleihungen</h1>

        <!-- Filterformular -->
        <form method="post" class="my-4">
            <div class="form-row">
                <div class="col">
                    <label for="startDatum">Startdatum</label>
                    <input type="date" class="form-control" id="startDatum" name="startDatum">
                </div>
                <div class="col">
                    <label for="endDatum">Enddatum</label>
                    <input type="date" class="form-control" id="endDatum" name="endDatum">
                </div>
                <div class="col">
                    <label for="previousMonth">Vorheriger Monat</label>
                    <input type="checkbox" id="previousMonth" name="previousMonth" class="form-check-input mt-3">
                </div>
                <div class="col">
                    <label for="currentMonth">Laufender Monat</label>
                    <input type="checkbox" id="currentMonth" name="currentMonth" class="form-check-input mt-3">
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Filtern</button>
        </form>

        <?php
        // Standardmäßig alle Verleihungen abrufen
        $query = 'SELECT kunde_id, verleih_start, verleih_end FROM verleihungen';
        $params = [];

        // Filter anwenden, wenn Startdatum, Enddatum, der vorherige Monat oder der laufende Monat ausgewählt sind
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $startDatum = $_POST['startDatum'] ?? null;
            $endDatum = $_POST['endDatum'] ?? null;
            $previousMonth = isset($_POST['previousMonth']);
            $currentMonth = isset($_POST['currentMonth']);
            
            // Berechnung für den vorherigen Monat
            if ($previousMonth && empty($startDatum) && empty($endDatum)) {
                $startDatum = date("Y-m-01", strtotime("first day of last month"));
                $endDatum = date("Y-m-t", strtotime("last day of last month"));
            }

            // Berechnung für den laufenden Monat
            if ($currentMonth && empty($startDatum) && empty($endDatum)) {
                $startDatum = date("Y-m-01");  // Erster Tag des laufenden Monats
                $endDatum = date("Y-m-t");     // Letzter Tag des laufenden Monats
            }

            // Abfrage erweitern, wenn ein Zeitraumfilter vorhanden ist
            if ($startDatum && $endDatum) {
                $query = 'SELECT kunde_id, COUNT(*) as rental_count, 
                                 GROUP_CONCAT(CONCAT(verleih_start, " bis ", verleih_end) SEPARATOR "<br>") as rental_dates
                          FROM verleihungen
                          WHERE verleih_start >= :startDatum AND verleih_end <= :endDatum
                          GROUP BY kunde_id';
                $params = [':startDatum' => $startDatum, ':endDatum' => $endDatum];
            }
        }

        // SQL-Abfrage ausführen
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Anzeige der Verleihungen
        if (count($results) > 0) {
            echo "<div class='searchInfo'>";
            if (!empty($params)) {
                echo "<p>Suche nach Verleihungen von <strong>$startDatum</strong> bis <strong>$endDatum</strong></p>";
            } else {
                echo "<p>Alle Verleihungen anzeigen</p>";
            }
            echo "<p>Anzahl der Einträge: <strong>" . count($results) . "</strong></p>";
            echo "</div>";

            echo '<table class="table table-striped">';
            echo '<thead><tr><th scope="col">Kunde ID</th>';

            if (!empty($params)) {
                echo '<th scope="col">Anzahl der Verleihungen</th><th scope="col">Verleih-Zeitraum(e)</th>';
            } else {
                echo '<th scope="col">Verleih Start</th><th scope="col">Verleih Ende</th>';
            }
            echo '</tr></thead><tbody>';

            foreach ($results as $row) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['kunde_id']) . '</td>';

                if (!empty($params)) {
                    echo '<td>' . htmlspecialchars($row['rental_count']) . '</td>';
                    echo '<td>' . $row['rental_dates'] . '</td>';
                } else {
                    echo '<td>' . htmlspecialchars($row['verleih_start']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['verleih_end']) . '</td>';
                }
                echo '</tr>';
            }

            echo '</tbody></table>';
        } else {
            echo '<p>Keine Verleihungen im angegebenen Zeitraum gefunden.</p>';
        }
        ?>
    </div>
</body>
</html>

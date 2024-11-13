<!DOCTYPE html>
<html>
<head>
    <title>Patientensuche</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<?php
// Funktion zum Erstellen einer Datenbankverbindung und zur Ausführung einer Abfrage
function makeStatement($query, $arrayValue = null) {
    $servername = "localhost:3307";
    $database = "arztpraxis";
    $username = "root";
    $password = "";

    try {
        $con = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $con->prepare($query);
        $stmt->execute($arrayValue);
        return $stmt; // Gibt das PDOStatement zurück
    } catch (PDOException $e) {
        echo "Verbindung fehlgeschlagen: " . $e->getMessage();
        return null;
    }
}

// Funktion zur Ausgabe der Tabellen-Ergebnisse
function displayResultsTable($stmt) {
    if ($stmt) {
        echo '<table class="table table-striped"><tr>';
        for ($i = 0; $i < $stmt->columnCount(); $i++) {
            echo '<th>' . htmlspecialchars($stmt->getColumnMeta($i)['name']) . '</th>';
        }
        echo '</tr>';

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<tr>';
            foreach ($row as $r) {
                echo '<td>' . htmlspecialchars($r) . '</td>';
            }
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo "<p>Keine Ergebnisse gefunden.</p>";
    }
}

// Eingabefelder für Formular
function makeInputDate($name, $label, $required = '') {
    echo '<div class="mb-3 row">
            <label for="' . $name . '" class="col-2 col-form-label">' . $label . '</label> 
            <div class="col-3">
                <input id="' . $name . '" name="' . $name . '" type="date" class="form-control" ' . $required . '>
            </div>
          </div>';
}

function makeCheckBox1($name, $value, $label, $colNum, $checked = null) {
    $checkedAttribute = $checked ? 'checked' : '';
    echo '<div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="' . $name . '" value="' . $value . '" ' . $checkedAttribute . '>
            <label class="form-check-label">' . $label . '</label>
          </div>';
}

function makeInputTextRegular($name, $label) {
    echo '<div class="mb-3 row">
            <label for="' . $name . '" class="col-2 col-form-label">' . $label . '</label> 
            <div class="col-3">
                <input id="' . $name . '" name="' . $name . '" type="text" class="form-control" required>
            </div>
          </div>';
}

function makeSubmitButton($name, $value) {
    echo '<div class="mb-3 row">
            <div class="col-3">
                <button type="submit" name="' . $name . '" class="btn btn-primary">' . $value . '</button>
            </div>
          </div>';
}

function makeInputGroup1($name, $type, $id, $label, $colNum, $value) {
    echo '<div class="mb-3 row">
            <label for="' . $id . '" class="col-2 col-form-label">' . $label . '</label>
            <div class="col-' . $colNum . '">
                <input id="' . $id . '" name="' . $name . '" type="' . $type . '" class="form-control" value="' . $value . '">
            </div>
          </div>';
}

// Überprüfen, ob das Formular abgeschickt wurde
if (isset($_POST['show'])) {
    $svnr = $_POST['svnr'];
    $gebdt = $_POST['gebdt'];

    // SQL-Abfrage zur Patienten-Suche
    $query1 = 'SELECT CONCAT_WS(\'/\', per_svnr, per_geburt) AS "SV-Nr" FROM person WHERE per_svnr = ? AND per_geburt = ?';
    $stmt = makeStatement($query1, [$svnr, $gebdt]);

    if ($stmt) {
        $row = $stmt->fetch(PDO::FETCH_NUM);
        if ($row) {
            echo '<h4><span style="color: red">SV-Nr.:</span> ' . $row[0] . '</h4>';
        } else {
            echo "<p>Keine Übereinstimmung für die SV-Nr. und das Geburtsdatum gefunden.</p>";
        }
    }

    // Diagnose-Abfrage mit Zeitraumfilter
    $query = 'SELECT CONCAT_WS(\' bis \', ter_beginn, ter_ende) AS "Zeitraum", 
                     CONCAT_WS(\' \', per_vname, per_nname) AS "Patient", 
                     CONCAT_WS(\'/\', per_svnr, per_geburt) AS "SVNr", 
                     dia_name AS Diagnose 
              FROM behandlungszeitraum b 
              NATURAL JOIN (person, diagnose) 
              WHERE per_svnr = ? AND ter_beginn >= ? AND ter_beginn <= ?';

    $arrayValue = [$svnr];
    $zeitpunkt = $_POST['zeitpunkt'] ?? null;
    $monat = date('m');
    $jahr = date('Y');

    if ($zeitpunkt !== 'a' && $zeitpunkt !== 'b') {
        $zeitpunktinput = $_POST['zeitpunktinput'];
        if ($zeitpunktinput > $monat) {
            echo '<h4 class="h4">Sie haben als Monat ' . $zeitpunktinput . ' gewählt, wir haben aber erst ' . $monat . '</h4>';
        } else {
            $sucheDatumStart = strtotime($jahr . '-' . $zeitpunktinput . '-01');
            $startDate = date('Y-m-d', $sucheDatumStart);
            $lastDate = date('Y-m-t', $sucheDatumStart);
            echo '<h4><span style="color: red">Zeitraum:</span> ' . $startDate . ' - ' . $lastDate . '</h4>';

            array_push($arrayValue, $startDate, $lastDate);
        }
    } else {
        $monat = $zeitpunkt == 'b' ? $monat : $monat - 1;
        $sucheDatumStart = strtotime($jahr . '-' . $monat . '-01');
        $startDate = date('Y-m-d', $sucheDatumStart);
        $lastDate = date('Y-m-t', $sucheDatumStart);
        echo '<h4><span style="color: red">Zeitraum:</span> ' . $startDate . ' - ' . $lastDate . '</h4>';

        array_push($arrayValue, $startDate, $lastDate);
    }

    // Ergebnisse der Diagnose-Abfrage anzeigen
    if (count($arrayValue) == 3) {
        $stmt2 = makeStatement($query, $arrayValue);
        displayResultsTable($stmt2);
    } else {
        echo "<p>Kein gültiger Zeitraum gewählt.</p>";
    }
} else {
    // Formular für die Eingabe anzeigen
    echo '<form method="post">';
    makeInputTextRegular('svnr', 'SV-Nr: ');
    makeInputDate('gebdt', 'Geburtsdatum', 'required');
    echo '<h3>Behandlungsbeginn:</h3>';
    makeCheckBox1('zeitpunkt', 'a', 'Letzter Monat', 1, 'checked');
    makeCheckBox1('zeitpunkt', 'b', 'Laufender Monat', 2, null);
    makeInputGroup1('zeitpunktinput', 'number', 'zeitpunktinput', 'Monat des laufenden Jahres angeben (z.B. 4)', 3, null);
    makeSubmitButton('show', 'Anzeigen');
    echo '</form>';
}
?>

</body>
</html>

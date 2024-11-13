<?php
// Patienten - Diagnosen
// Datum: 04.09.2024

function makeInputDate($name, $label, $required = '') {
    echo '<div class="mb-3 row">
            <label for="' . htmlspecialchars($name) . '" class="col-2 col-form-label">' . htmlspecialchars($label) . '</label>
            <div class="col-3">
                <input id="' . htmlspecialchars($name) . '" name="' . htmlspecialchars($name) . '" type="date" class="form-control" ' . $required . '>
            </div>
          </div>';
}

function makeSubmitButton($name, $label) {
    echo '<div class="mb-3">
            <button type="submit" name="' . htmlspecialchars($name) . '" class="btn btn-primary">' . htmlspecialchars($label) . '</button>
          </div>';
}

function makeTable($query, $arrayValue = null) {
    // Verbindung zur Datenbank
    $servername = "localhost:3307";
    $database = "arztpraxis";
    $username = "root";
    $password = "";
  
    try {
        $con = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $con->prepare($query);
        $stmt->execute($arrayValue);

        echo '<table class="table table-striped"><tr>';
        // Tabellenüberschriften dynamisch generieren
        for ($i = 0; $i < $stmt->columnCount(); $i++) {
            echo '<th>' . htmlspecialchars($stmt->getColumnMeta($i)['name']) . '</th>';
        }
        echo '</tr>';

        // Tabelleninhalte dynamisch generieren
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<tr>';
            foreach ($row as $r) {
                echo '<td>' . htmlspecialchars($r) . '</td>';
            }
            echo '</tr>';
        }
        echo '</table>';
    } catch (PDOException $e) {
        echo "Verbindung fehlgeschlagen: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patientensuche</title>
</head>
<body>
    <a href="searchPatient.php" target="_self">Suche 1</a> | <a href="searchPatient2.php" target="_self">Suche 2</a>
    <h2 class="h2">Patienten - Diagnosen</h2>
    <script>
        function checkIt(value) {
            var pattern = /\d{4}/;
            var result = value.match(pattern);
            if(result === null || value.length > 4) {
                document.getElementById("errorMessage").innerText = "Sie müssen eine vierstellige Zahl eingeben!";
                document.getElementById("svnr").focus();
            } else {
                document.getElementById("errorMessage").innerText = "";
            }
        }
    </script>

    <?php
    if (isset($_POST['show'])) {
        $zeit1 = $_POST['zeit1'];
        $zeit2 = $_POST['zeit2'];
        $svnr = $_POST['svnr'];
        $gebdt = $_POST['gebdt'];
        $arrayValue = [];

        echo '<h4 class="h4">Suchkriterien:</h4>';
        $query = 'SELECT CONCAT_WS(" bis ", ter_beginn, ter_ende) AS Zeitraum, 
                         CONCAT_WS(" ", per_vname, per_nname) AS Patient, 
                         CONCAT_WS("/", per_svnr, per_geburt) AS SVNr, 
                         dia_name AS Diagnose 
                  FROM behandlungszeitraum 
                  NATURAL JOIN person 
                  NATURAL JOIN diagnose';

        if (empty($zeit1) && empty($zeit2) && empty($svnr)) {
            echo '<h5>Keine Suchkriterien angegeben</h5>';
            $query .= ' ORDER BY per_nname, per_vname, ter_beginn';
        } else {
            $conditions = [];
            if (!empty($svnr)) {
                echo '<h5 class="h5">SV-Nr: ' . htmlspecialchars($svnr) . '/' . htmlspecialchars($gebdt) . '</h5>';
                $conditions[] = 'per_svnr = ? AND per_geburt = ?';
                $arrayValue[] = $svnr;
                $arrayValue[] = $gebdt;
            }
            if (!empty($zeit1)) {
                echo '<h5 class="h5">Startzeitraum: ' . htmlspecialchars($zeit1) . '</h5>';
                $conditions[] = 'ter_beginn >= ?';
                $arrayValue[] = $zeit1;
            }
            if (!empty($zeit2)) {
                echo '<h5 class="h5">Zeitraum Ende: ' . htmlspecialchars($zeit2) . '</h5>';
                $conditions[] = 'ter_ende <= ?';
                $arrayValue[] = $zeit2;
            }
            if (!empty($conditions)) {
                $query .= ' WHERE ' . implode(' AND ', $conditions);
            }
        }

        makeTable($query, $arrayValue);
    } else {
        echo '<form method="post">';
        echo '<div class="mb-3 row">
                <label for="svnr" class="col-2 col-form-label">SV-Nr.</label>
                <div class="col-3">
                    <input id="svnr" onfocusout="checkIt(this.value)" name="svnr" type="number" class="form-control" required placeholder="vierstellige Zahl">
                </div>
                <span id="errorMessage"></span>
              </div>';
        
        makeInputDate('gebdt', 'Geburtsdatum', 'required');
        echo '<h3>Behandlungszeitraum:</h3>';
        
        makeInputDate('zeit1', 'Start: ');
        makeInputDate('zeit2', 'Ende: ');
        makeSubmitButton('show', 'anzeigen');
        echo '</form>';
    }
    ?>
</body>
</html>

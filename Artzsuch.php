
<?php
// Patienten - Diagnosen
// Datum: 04.09.2024

?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once "./includes/loadRessources.inc.php";
    echo "<script>setTitle('Patientensuche');</script>";
    ?>
</head>

<body>
    <?php
    require_once "./includes/navbar.inc.php";   // load Navbar
  
    ?>

    <!-- Suchfeld -->
    <section class="text-center container">
        <form method="post">
            <h1>Patientensuche</h1>

            <!-- SV-Nr Eingabefeld mit Prüfung -->
            <div class="mb-3 row">
                <label for="svnr" class="col-2 col-form-label">SV-Nr.</label>
                <div class="col-3">
                    <input id="svnr" onfocusout="checkIt(this.value)" name="svnr" type="number" class="form-control" required placeholder="vierstellige Zahl">
                </div>
                <span id="errorMessage"></span>
            </div>

            <!-- Geburtsdatum Eingabefeld -->
            <?php makeInputDate('gebdt', 'Geburtsdatum', 'required'); ?>

            <!-- Behandlungszeitraum -->
            <h3>Behandlungszeitraum:</h3>
            <?php
            makeInputDate('zeit1', 'Start: ');
            makeInputDate('zeit2', 'Ende: ');
            ?>

            <button type="submit" class="btn btn-primary" name="show" value="1">Suchen</button>
            <button type="button" class="btn btn-secondary" onclick="window.location.href = 'index.php';">Abbrechen</button>
        </form>
    </section>

    <!-- Ergebnisbereich -->
    <section class="text-center container">
        <?php
       if(isset($_POST['show']))
       {
           $zeit1 = $_POST['zeit1'];
           $zeit2 = $_POST['zeit2'];
           $svnr = $_POST['svnr'];
           $arrayValue = null;
           echo '<h4 class="h4">Suchkriterien:</h4>';
           $query = 'select concat_ws(\' bis \', ter_beginn, ter_ende) as "Zeitraum", 
                            concat_ws(\' \', per_vname, per_nname) as "Patient", 
                            concat_ws(\'/\', per_svnr, per_geburt) as "SVNr", 
                            dia_name as Diagnose 
                            from behandlungszeitraum natural join (person, diagnose)';
           if(empty($zeit1) && empty($zeit2) && empty($svnr))
           {
               echo '<h5>keine Suchkriterieneinschränkung</h5>';
               $query .= 'order by per_nname, per_vname, ter_beginn';
           } else {
       
               if (!empty($svnr)) {
                   echo '<h5 class="h5">SV-Nr: ' . $svnr . '</h5>';
                   $query .= ' where concat(per_svnr, \'/\', per_geburt) = ?';
                   $arrayValue = array($svnr);
               }
               if (!empty($zeit1)) {
                   echo '<h5 class="h5">Startzeitraum: ' . $zeit1 . '</h5>';
                   $query .= ' and ter_beginn >= ? ';
                   array_push($arrayValue, $zeit1);
       
               }
               if (!empty($zeit2)) {
                   echo '<h5 class="h5">Zeitraum Ende: ' . $zeit2 . '</h5>';
                   $query .= ' and ter_ende <= ?';
                   array_push($arrayValue, $zeit2);
               }
       
           }
            // Ausgabe der Suchkriterien
   

            // Abfrage ausführen und Ergebnisse anzeigen
            $stmt = $conn->prepare($query);
            $stmt->execute($arrayValue);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Tabelle mit den Ergebnissen anzeigen
            ?>
            <table class="table table-striped">
                <thead>
                    <tr class="table-dark">
                        <th scope="col">Zeitraum</th>
                        <th scope="col">Patient</th>
                        <th scope="col">SVNr</th>
                        <th scope="col">Diagnose</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (empty($results)) {
                        echo "<tr><td colspan='4'>Keine Ergebnisse gefunden</td></tr>";
                    } else {
                        // Ergebnisse Zeile für Zeile ausgeben
                        foreach ($results as $row) {
                            echo "<tr>";
                            echo "<td>{$row['Zeitraum']}</td>";
                            echo "<td>{$row['Patient']}</td>";
                            echo "<td>{$row['SVNr']}</td>";
                            echo "<td>{$row['Diagnose']}</td>";
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

    <!-- Skript zur Validierung der SV-Nr -->
    <script>
        function checkIt(value) {
            var pattern = /\d{4}/;
            var result = value.match(pattern);

            if (result === null || value.length > 4) {
                document.getElementById("errorMessage").innerText = "Sie müssen eine vierstellige Zahl eingeben!";
                document.getElementById("svnr").focus();
            } else {
                document.getElementById("errorMessage").innerText = "";
            }
        }
    </script>
</body>

</html>

<?php
// Funktionen


// Funktion zur Erstellung eines Datums-Eingabefeldes
function makeInputDate($name, $label, $required = '') {
    echo '<div class="mb-3 row">';
    echo '<label for="' . $name . '" class="col-2 col-form-label">' . $label . '</label>';
    echo '<div class="col-3">';
    echo '<input type="date" class="form-control" name="' . $name . '" id="' . $name . '" ' . $required . '>';
    echo '</div>';
    echo '</div>';
}
?>

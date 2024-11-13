<?php
// Vallant Felipe
// 04.09.2024
// LAP - Beispiel: Filmsuche per Produktionsfirma

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
    ?>

    <!-- Such feld-->
    <section class="text-center container">
        <form method="post">
            <h1>Filmsuche</h1>
            <label>Suche Film nach Produktionsfirma: </label> <input type="text" name="produktionsfirma"><br><br>
            <button type="submit" class="btn btn-primary" name="suchePerProduktionsFirma" value="1">Suchen</button>
            <button type="button" class="btn btn-secondary" onclick="window.location.href = 'index.php'; ">Abbrechen</button>
        </form>
    </section>

    <!-- Table Bereich -->
    <section class="text-center container">
        <?php
        if (isset($_POST["suchePerProduktionsFirma"])) {
            $search = (isset($_POST["produktionsfirma"])) ? $_POST["produktionsfirma"] : "";
            
            // SQL
            $query = "SELECT 
    f.Film_ID AS id, 
    f.Titel AS titel, 
    f.`Erscheinungs-Datum` AS datum, 
    p.Bezeichnung AS prodFirma 
FROM 
    produktionsfirma AS p
INNER JOIN 
    filme AS f 
    ON f.Produktionsfirma_ID = p.Produktionsfirma_ID
WHERE 
    p.Bezeichnung LIKE ?
ORDER BY 
    f.`Erscheinungs-Datum`;";
            $stmt = $conn->prepare($query);

            
            $params = ["%" . $search . "%"];
            $stmt->execute($params);

            $results = $stmt->fetchAll();

            # Suchkriterien Ergebnisse 
            {
                echo "<div class='searchInfo'>";
                    echo "<p><strong>Suchergebnis</strong></p>";
                    echo "<p>Suche nach: <strong>$search</strong></p>";
                    # erstelle string mit jeder Produktionsfirma im ergebnis (1x) und gib diese aus
                    {
                        $tmpString = "";
                        $tmpArray = [];
                        foreach($results as $res)
                        {
                            if(!in_array($res["prodFirma"], $tmpArray))     // wenn firma bereits hinzugefügt wurde, lass aus
                            {   // firma wurde noch nicht hinzugefügt => Füge hinzu
                                $tmpString .= ", ". $res["prodFirma"];
                                $tmpArray[] = $res["prodFirma"];
                            }
                        }

                        $tmpString = trim(ltrim($tmpString, ","));    // entferne ", " am anfang
                        echo "<p>Gefundene Produktionsfirmen: <strong>". $tmpString ."</strong></p>";     // gib gefundene Firmen aus
                    }
                    echo "<p>Anzahl der gefundenen Titel: <strong>". count($results) ."</strong></p>";

                echo "</div>";
            }
            ?>
            <table class="table table-striped">
                <thead>
                    <tr class="table-dark">
                        <th scope="col"># FilmID</th>
                        <th scope="col">Titel</th>
                        <th scope="col">Erscheinungs-Datum</th>
                        <th scope="col">Produktionsfirma</th>
                    </tr>
                </thead>
                <tbody>
                
                <?php
                if(empty($results))
                {
                    echo "<tr><td colspan='4'>Keine Produktionsfirma gefunden</td></th>";
                }

                // Für jeden Eintrag eine Zeile erstellen
                foreach ($results as $entry) {
                    echo "<tr>";
                        echo "<th scope='row'>". $entry["id"] ."</th>";
                        echo "<td>". $entry["titel"] ."</td>";
                        echo "<td>". $entry["datum"] ."</td>";
                        echo "<td>". $entry["prodFirma"] ."</td>";
                    echo "</tr>";
                }
                ?>
                    </tr>
                </tbody>
            </table>
        <?php
        }
        ?>
    </section>
</body>

</html>
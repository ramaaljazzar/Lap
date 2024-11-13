<a href="searchPatient.php" target="_self">Suche 1</a> | <a href="searchPatient2.php" target="_self">Suche 2</a>
<h2 class="h2">Patienten - Diagnosen</h2>
<script>
    // Regular Expression
    function checkIt(value)
    {
        var pattern = /\d{4}\/{1}\d{4}-\d{2}-\d{2}/;
        var result = value.match(pattern);
        if((result === null && value != "") || value.length > 15) // leeres Eingabefeld wird mit required überprüft
        {
            document.getElementById("errorMessage").innerText = "Die SVNr wurde nicht korrekt eingegeben! " +
             "Format: xxxx/JJJJ-MM-TT" ;
            document.getElementById("svnr").focus();
        } else
        {
            document.getElementById("errorMessage").innerText = "";
        }
    }
</script>
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

    if($arrayValue != null)
      makeTable($query, $arrayValue);
    else
        makeTable($query);

} else {
    echo '<form method="post">';
    //makeInputTextRegular('svnr', 'SV-Nr: ');
    echo '<div class="mb-3 row">
    <label for="svnr" class="col-1 col-form-label">SV-Nr.</label> 
    <div class="col-5">
      <input id="svnr" onfocusout="checkIt(this.value)" name="svnr" onblur="Ausgabe(\'svnr\')" type="text" class="form-control" required placeholder="Format: xxxx/JJJJ-MM-TT z.B. 1234/1980-10-05">
    <span id="errorMessage"></span>
    </div>
  </div> 
  <p style="font-size: 75%; font-style: italic ">Es wird nur das Eingabeformat überprüft! Korrektheit des Datums wird nicht geprüft!</p>';
    echo '<h3>Behandlungszeitraum:</h3>';

    makeInputDate('zeit1', 'Start: ');
    makeInputDate('zeit2', 'Ende: ');
    makeSubmitButton('show', 'anzeigen');
    echo '</form>';
}
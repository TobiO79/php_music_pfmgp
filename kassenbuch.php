<?php

include("conf/config.php");
include("incl/header.php");

if(logged_in()){
    if($_SESSION["Kasse"]){
if(isset($_POST["Buchung"]) and $_POST["Buchung"]=="speichern"){
$sql="INSERT INTO Kassenbuch (buchungsDatum, userID, buchungsBetreff, buchungsBetrag) VALUES
	('".$_POST["buchungsDatum"]."','".$_POST["userID"]."','".$_POST["buchungsBetreff"]."','".$_POST["buchungsBetrag"]."')";
mysqli_query($conn,$sql);
}
    }

$spielerliste="";
$sql="SELECT * FROM Spieler ORDER BY vorname, nachname";
$result=mysqli_query($conn,$sql);
while($erg=mysqli_fetch_object($result)){
    $spielerliste.="<option value='$erg->userID'>$erg->vorname $erg->nachname</option>\n";
}


$heute=date("Y-m-d");
$buchungsliste="";
$bestand=0;
$sql="SELECT * FROM Kassenbuch
	LEFT JOIN Spieler USING (userID)
        ORDER BY buchungsDatum";
$result=mysqli_query($conn,$sql);
while($erg=mysqli_fetch_object($result)){
	$bestand=$bestand+$erg->buchungsBetrag;
	$datum=date_mysql2german($erg->buchungsDatum);
	$buchungsliste.="<tr><td>$datum</td><td>$erg->userName</td><td>$erg->buchungsBetreff</td><td>".number_format($erg->buchungsBetrag,2,',','.')." &euro;</td><td>".number_format($bestand,2,',','.')." &euro;</td></tr>\n";
}

$buchungsliste2="";
$bestand=0;
$sql="SELECT * FROM Kassenbuch
	LEFT JOIN Spieler USING (userID)
        WHERE Kassenbuch.userID=".$_SESSION["UserID"]
        . " ORDER BY buchungsDatum";
$result=mysqli_query($conn,$sql);
while($erg=mysqli_fetch_object($result)){
	$bestand=$bestand+$erg->buchungsBetrag;
	$datum=date_mysql2german($erg->buchungsDatum);
	$buchungsliste2.="<tr><td>$datum</td><td>$erg->userName</td><td>$erg->buchungsBetreff</td><td>".number_format($erg->buchungsBetrag,2,',','.')." &euro;</td><td>".number_format($bestand,2,',','.')." &euro;</td></tr>\n";
}

print <<<html

<h2>Das Kassenbuch</h2>
<table width="100%">
<tr><th>Datum</th><th>Spieler</th><th>Zweck</th><th>Betrag</th><th>Bestand</th></tr>
$buchungsliste
</table>

<h2>Meine Zahlungen</h2>
<table width="100%">
<tr><th>Datum</th><th>Spieler</th><th>Zweck</th><th>Betrag</th><th>Bestand</th></tr>
$buchungsliste2
</table>

html;
if($_SESSION["Kasse"]){
print <<<html
        
<h2>Buchungen erfassen</h2>
<form name="Buchungen" method="POST" action="kassenbuch.php">
Datum: <input type="date" name="buchungsDatum" value="$heute"><br>
Zweck: <input type="text" name="buchungsBetreff"><br>
Betrag: <input type="number" name="buchungsBetrag" step=0.01><br>
Spieler: <select name="userID" size=1><option value="">bitte wählen</option>$spielerliste</select>
<button type="submit" name="Buchung" value="speichern" class="button">Zahlung erfassen</button>
</form>
html;
}
}
include("incl/footer.php");

?>

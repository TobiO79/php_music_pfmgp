<?php

include("conf/config.php");
include("incl/header.php");
if(logged_in()){
$pfmgp=aktueller_pfmgp();

$anmeldung=0;
if($pfmgp[2]=="Anmeldung"){
	$sql="SELECT * FROM Titel WHERE userID=".$_SESSION["UserID"]." AND wettbewerbsID=".$pfmgp[1];
	$result=mysqli_query($conn,$sql);
	if(mysqli_num_rows($result)+$pfmgp[3]<2) $anmeldung=1;
}
if(isset($_POST["Vorrunde"]) and $_POST["Vorrunde"]=="Abstimmung Vorrunde"){
    $pl=$_POST["Platzliste"];
    foreach($pl as $titel){
        $sql="INSERT INTO Vorrunde (teilnahmeID, titelID, punkte) VALUES (".$_POST["teilnahmeID"].",$titel,".$_POST["Radio".$titel].")";
        mysqli_query($conn,$sql);
    }
    $erg=mysqli_fetch_object(mysqli_query($conn,"SELECT email, vorname, nachname FROM Spieler WHERE userID=".$_SESSION["UserID"]));
$text="Hiermit bestätigen wir das Voting, dass $erg->vorname $erg->nachname für die Vorrunden abgegeben hat:\n";
$sql="SELECT * FROM Vorrunde"
        . " LEFT JOIN Titel USING (titelID)"
        . " WHERE teilnahmeID=".$_POST["teilnahmeID"]
        . " ORDER BY punkte DESC, titelName";
$result=mysqli_query($conn,$sql);
while($terg=mysqli_fetch_object($result))    {
    $text.="Punkte $terg->punkte: $terg->titelName - $terg->titelInterpret\n";
}
$tendenz=$_POST["tendenz"];
$text.="Tendenz $tendenz\n";
pfmgp_mail("Voting für Vorrunde abgegeben",$text,$erg->email);
logbuch($_SESSION["UserID"],"Voting Vorrunde abgegeben");
telegchan("$erg->vorname $erg->nachname hat ein Voting für die Vorrunde abgegeben.");

}
if(isset($_POST["Voting"]) and $_POST["Voting"]=="Voting abgeben"){
    $test=0;
    $vt[]=$_POST["Platz1"];
    if(in_array($_POST["Platz2"], $vt)) $test=1; else $vt[]=$_POST["Platz2"];
    if(in_array($_POST["Platz3"], $vt)) $test=1; else $vt[]=$_POST["Platz3"];
    if(in_array($_POST["Platz4"], $vt)) $test=1; else $vt[]=$_POST["Platz4"];
    if(in_array($_POST["Platz5"], $vt)) $test=1; else $vt[]=$_POST["Platz5"];
    if(in_array($_POST["Platz6"], $vt)) $test=1; else $vt[]=$_POST["Platz6"];
    if(in_array($_POST["Platz7"], $vt)) $test=1; else $vt[]=$_POST["Platz7"];
    if(in_array($_POST["Platz8"], $vt)) $test=1; else $vt[]=$_POST["Platz8"];
    if(in_array($_POST["Platz9"], $vt)) $test=1; else $vt[]=$_POST["Platz9"];
    if(in_array($_POST["Platz10"], $vt)) $test=1; else $vt[]=$_POST["Platz10"];
    if($test==0){
    $sql="INSERT INTO Voting (teilnahmeID, votingPunkte12,votingPunkte10,votingPunkte08,votingPunkte07,votingPunkte06,votingPunkte05,votingPunkte04,votingPunkte03,votingPunkte02,votingPunkte01) "
            . " VALUES (".$_POST["teilnahmeID"].",".$_POST["Platz1"].",".$_POST["Platz2"].",".$_POST["Platz3"].",".$_POST["Platz4"].",".$_POST["Platz5"].",".$_POST["Platz6"].",".$_POST["Platz7"].",".$_POST["Platz8"].",".$_POST["Platz9"].",".$_POST["Platz10"].")";
    mysqli_query($conn,$sql) or die(mysqli_error($conn).": $sql");
$erg=mysqli_fetch_object(mysqli_query($conn,"SELECT email, vorname, nachname FROM Spieler WHERE userID=".$_SESSION["UserID"]));
$text="$erg->vorname $erg->nachname hat ein Voting abgegeben:\n";
$i=0;
foreach($vt as $titelid)    {
    $i++;
    $terg=mysqli_fetch_object(mysqli_query($conn,"SELECT titelName, titelInterpret FROM Titel where titelID=$titelid"));
    $text.="Platz $i: $terg->titelName - $terg->titelInterpret\n";
    
}
$text.="Bitte ruft nachdem ihr das Voting abgegeben habt auf +49 (55 71) 92 73 10 an. Dort begrüßt Euch unser freundlicher Anrufbeantworter. Bitte hinterlasst auf dem Anrufbeantworter noch einmal Euer Voting für Eure TOP 3 Titel. Wir wollen eure Aufnahmen auf dem Anrufbeantworter für die Auswertungsshow des PFMGP verwenden. Bitte sprecht auf dem Anrufbeantworter auf jeden Fall Euren Namen und die Punktevergabe für Eure drei Lieblingstitel. Ihr dürft gerne auch noch Grüße oder Ähnliches an die anderen PFMGP-Teilnehmer aufsprechen, die dann mit Eurem Voting veröffentlicht werden. Damit ihr auch von den anderen verstanden werdet, solltet ihr Eurer Voting in deutsch, englisch oder französisch aufsprechen.\n";
$text.="Ihr könnt auch gerne ein kurzes Handy-Video an meine Nr. +49 (178) 5461679 senden. Auch dieses Video findet mit seinen Weg in unsere PFMGP-Auswertung.\n";
$to_addr[]=$erg->email;
pfmgp_mail("Voting abgegeben",$text,$to_addr);
logbuch($_SESSION["UserID"],"Voting abgegeben");
telegchan("$erg->vorname $erg->nachname hat ein Voting abgegeben.");
    }
    else {
	logbuch($_SESSION["UserID"],"Ein Voting wurde abgelehnt, da Titel doppelt gevotet wurden.");
	die("Bitte jeden Titel nur einmal voten! Dein Voting wurde nicht gespeichert. Bitte wiederhole sein Voting.");
}
    
}

$voting=0;
if($pfmgp[2]=="Voting"){
	$sql="SELECT * FROM Teilnahme WHERE userID=".$_SESSION["UserID"]." AND wettbewerbsID=".$pfmgp[1];
        $result=mysqli_query($conn,$sql);
	if(mysqli_num_rows($result)==1) {
		$erg=mysqli_fetch_object($result);
                $tnid=$erg->teilnahmeID;
		$sql="SELECT * FROM Voting WHERE teilnahmeID=$erg->teilnahmeID";
		$result=mysqli_query($conn,$sql);
		if(mysqli_num_rows($result)==0) $voting=1;
	}
}
if($pfmgp[2]=="Vorrunde"){
	$sql="SELECT * FROM Teilnahme WHERE userID=".$_SESSION["UserID"]." AND wettbewerbsID=".$pfmgp[1];
        $result=mysqli_query($conn,$sql);
	if(mysqli_num_rows($result)==1) {
		$erg=mysqli_fetch_object($result);
                $tnid=$erg->teilnahmeID;
		$sql="SELECT * FROM Vorrunde WHERE teilnahmeID=$erg->teilnahmeID";
		$result=mysqli_query($conn,$sql);
		if(mysqli_num_rows($result)==0) $voting=2;
	}
}



if(isset($_POST["userspeichern"]) and $_POST["userspeichern"]=="speichern"){
if(strlen($_POST["passwort"])>0){
		
		$newhash=password_hash($_POST["passwort"],PASSWORD_DEFAULT);
		$sql="UPDATE Spieler SET"
                        . " userName='".mysqli_real_escape_string($conn,$_POST["userName"])."', "
                        . "vorname='".mysqli_real_escape_string($conn,$_POST["vorname"])."', "
                        . "nachname='".mysqli_real_escape_string($conn,$_POST["nachname"])."', "
                        . "passwort='".$newhash."', "
                        . "email='".mysqli_real_escape_string($conn,$_POST["email"])."', "
                        . "anschrift='".mysqli_real_escape_string($conn,$_POST["anschrift"])."', "
                        . "benutzerText='".mysqli_real_escape_string($conn,$_POST["benutzerText"])."' "
                        . "WHERE userID=".$_POST["userID"];
}
else{
		$sql="UPDATE Spieler SET"
                        . " userName='".mysqli_real_escape_string($conn,$_POST["userName"])."', "
                        . "vorname='".mysqli_real_escape_string($conn,$_POST["vorname"])."', "
                        . "nachname='".mysqli_real_escape_string($conn,$_POST["nachname"])."', "
                        . "email='".mysqli_real_escape_string($conn,$_POST["email"])."', "
                        . "anschrift='".mysqli_real_escape_string($conn,$_POST["anschrift"])."', "
                        . "benutzerText='".mysqli_real_escape_string($conn,$_POST["benutzerText"])."' "
                        . "WHERE userID=".$_POST["userID"];
}
mysqli_query($conn,$sql) or die(mysqli_error($conn).": $sql");
}

if(isset($_POST["titelspeichern"]) and $_POST["titelspeichern"]=="speichern"){

$sql="INSERT INTO Titel (userID,wettbewerbsID,titelName,titelInterpret,titelJahr,titelSprache,Youtube) VALUES ('".$_SESSION["UserID"]."','".$pfmgp[1]."','".mysqli_real_escape_string($conn,$_POST["titelName"])."','".mysqli_real_escape_string($conn,$_POST["titelInterpret"])."','".mysqli_real_escape_string($conn,$_POST["titelJahr"])."','".mysqli_real_escape_string($conn,$_POST["titelSprache"])."','".mysqli_real_escape_string($conn,$_POST["Youtube"])."')";
$result=mysqli_query($conn,$sql);
if (!$result){
	$fehler=mysqli_error($conn).": $sql";
	logbuch($_SESSION["UserID"],$fehler);
	print "Die Titel-Einreichung hat leider nicht funktioniert. Ggf. wurde der Titel doppelt eingereicht. Falls sich der Fehler wiederholt, informiere bitte die PFMGP-Orga.";
 	die($fehler);
}
$erg=mysqli_fetch_object(mysqli_query($conn,"SELECT email, vorname, nachname FROM Spieler WHERE userID=".$_SESSION["UserID"]));
$text="$erg->vorname $erg->nachname hat einen neuen Titel eingereicht:\n"
        ."Titel: ".$_POST["titelName"]."\n"
        ."Interpret: ".$_POST["titelInterpret"]."\n"
        ."Jahr: ".$_POST["titelJahr"]."\n"
        ."Sprache: ".$_POST["titelSprache"]."\n"
        ."Youtube: ".$_POST["Youtube"]."\n"
	."Kommentar: ".$_POST["Einreichung"]."\n";
$to_addr[]=$erg->email;
pfmgp_mail("neuer Titel eingereicht",$text,$to_addr);
logbuch($_SESSION["UserID"],"Titel eingereicht");
telegchan("$erg->vorname $erg->nachname hat einen neuen Titel zum aktuellen PFMGP eingereicht.");
}

if(isset($_FILES['lied']['tmp_name'])){
	$uploaddir='/var/www/pfmgp.de/web/uploads/';
	$uploadfile=$uploaddir.$_SESSION["User"]."_".date("Ymd")."_".basename($_FILES['lied']['name']);
	if(move_uploaded_file($_FILES['lied']['tmp_name'],$uploadfile)){
		echo "Lied ist in Ordnung und wurde erfolgreich hochgeladen.\n";
		logbuch($_SESSION["UserID"],"Titel hochgeladen: $uploadfile");
	}
	else {
		echo "Beim Hochladen ging etwas schief.";
		logbuch($_SESSION["UserID"],"Titel-Upload fehlgeschlagen");
		print_r($_FILES['lied']['error']);
	}
}

$vorliste="";
$sql="SELECT * FROM Titel WHERE wettbewerbsid=".$pfmgp[1]." and userID<>".$_SESSION["UserID"]
        ." ORDER BY titelName";
$result=mysqli_query($conn,$sql);
$neutralwert=mysqli_num_rows($result)*3;
while($erg=mysqli_fetch_object($result)){
    $radioname="Radio".$erg->titelID;
    if ($erg->Youtube<>"") $vorliste.="<tr><td><input type='hidden' name='Platzliste[]' value='$erg->titelID'><a href='$erg->Youtube' target='Youtube'>$erg->titelName</a></td><td>$erg->titelInterpret</td><td><input class='BR' type='radio' name='$radioname' value='1'> 1 <input class='BR' type='radio' name='$radioname' value='2'> 2 <input class='BR' type='radio' name='$radioname' value='3' checked> 3 <input class='BR' type='radio' name='$radioname' value='4'> 4 <input class='BR' type='radio' name='$radioname' value='5'> 5</td></tr>\n";
    else $vorliste.="<tr><td><input type='hidden' name='Platzliste[]' value='$erg->titelID'>$erg->titelName</td><td>$erg->titelInterpret</td><td><input class='BR' type='radio' name='$radioname' value='1'> 1 <input class='BR' type='radio' name='$radioname' value='2'> 2 <input class='BR' type='radio' name='$radioname' value='3' checked> 3 <input class='BR' type='radio' name='$radioname' value='4'> 4 <input class='BR' type='radio' name='$radioname' value='5'> 5</td></tr>\n";
}


$titelliste="";
if($pfmgp[4]==0){
$sql="SELECT * FROM Titel WHERE wettbewerbsid=".$pfmgp[1]." and userID<>".$_SESSION["UserID"]
        ." ORDER BY titelName";
}
else{
$sql="SELECT * FROM Titel WHERE Runde='F' AND wettbewerbsid=".$pfmgp[1]." and userID<>".$_SESSION["UserID"]
        ." ORDER BY titelName";
}
$result=mysqli_query($conn,$sql);
while($erg=mysqli_fetch_object($result)){
    $titelliste.="<option value='$erg->titelID'>$erg->titelName ($erg->titelInterpret)</option>\n";
}

$titelspieler="";
$sql="SELECT * FROM Titel LEFT JOIN Wettbewerb USING (wettbewerbsID) WHERE userID=".$_SESSION["UserID"]." ORDER BY wettbewerbsJahr, Titel.wettbewerbsID";
$result=mysqli_query($conn,$sql);
while($erg=mysqli_fetch_object($result)){
    if ($erg->Youtube<>"") $titelspieler.="<tr><td>$erg->wettbewerbsJahr</td><td>$erg->wettbewerbsName</td><td><a href='$erg->Youtube' target='Youtube'>$erg->titelName</a></td><td>$erg->titelInterpret</td><td>$erg->titelSprache</td><td>$erg->titelPunkte</td><td>$erg->titelPlatzierung</td></tr>\n";
    else $titelspieler.="<tr><td>$erg->wettbewerbsJahr</td><td>$erg->wettbewerbsName</td><td>$erg->titelName</td><td>$erg->titelInterpret</td><td>$erg->titelSprache</td><td>$erg->titelPunkte</td><td>$erg->titelPlatzierung</td></tr>\n";
}

    $sql="SELECT * FROM Spieler"
            . " WHERE userID=".$_SESSION["UserID"];
    $result=  mysqli_query($conn,$sql);
    $serg=mysqli_fetch_object($result);
    $sid=$serg->userID;



print <<<html

<h2>Mein Profil</h2>

<form name="spielerverwaltung" method="POST" action="spieler.php">
<input type="hidden" name="userID" value="$sid">
  <div class="grid-x">
    <div class="small-12 cell">
        <label for="userName">Benutzername</label>
        <input type="text" id="userName" name="userName" value="$serg->userName" required>
    </div>
    <div class="small-12 cell">
        <label for="vorname">Vorname</label>
        <input type="text" id="vorname" name="vorname" value="$serg->vorname" placeholder="Max" required>
    </div>
    <div class="small-12 cell">
        <label for="nachname">Nachname</label>
        <input type="text" id="nachname" name="nachname" value="$serg->nachname" placeholder="Mustermann" required>
    </div>
    <div class="small-12 cell">
        <label for="passwort">Passwort</label>
        <input type="password" id="passwort" name="passwort" placeholder="Geheim123!">
    </div>
    <div class="small-12 cell">
        <label for="email">Mail-Adresse</label>
        <input type="email" id="email" name="email" value="$serg->email" placeholder="mitspieler@pfmgp.de" required>
    </div>
    <div class="small-12 cell">
        <label for="anschrift">Anschrift</label>
        <textarea name="anschrift" id="anschrift" rows="5" placeholder="Max Mustermann\nMusikgasse 42\n12345 Musikantenstadel">$serg->anschrift</textarea>
      </div>
    <div class="small-12 cell">
        <label for="benutzerText">Sonstige Infos</label>
        <textarea name="benutzerText" id="benutzerText" rows="5" placeholder="">$serg->benutzerText</textarea>
    </div>
  </div>

<button type="submit" name="userspeichern" value="speichern" class="button">Speichern</button>
</form>

<h2>Meine Titel</h2>
<table width="100%" border=1>
<tr><th>Jahr</th><th>Wettbewerb</th><th>Titel</th><th>Interpret</th><th>Sprache</th><th>Punkte</th><th>Platzierung</th></tr>
$titelspieler
</table>
        
html;
if($anmeldung==1){
	$iliste="<datalist id='int'>\n";
	$sql="SELECT titelInterpret from Titel GROUP BY titelInterpret";
	$result=mysqli_query($conn,$sql);
	while($erg=mysqli_fetch_object($result)){
		$iliste.="<option value='$erg->titelInterpret' />\n";
	}
	$iliste.="</datalist>\n";

	$lliste="<datalist id='lang'>\n";
	$sql="SELECT titelSprache from Titel GROUP BY titelSprache";
	$result=mysqli_query($conn,$sql);
	while($erg=mysqli_fetch_object($result)){
		$lliste.="<option value='$erg->titelSprache' />\n";
	}
	$lliste.="</datalist>\n";

print <<<html

<h2>Titel einreichen</h2>

<form name="Titel" method="POST" action="spieler.php">
<div class="grid-X">
	<div class="small-12 cell">
			<label for="titelname">Name des Titels</label>
			<input type="text" name="titelName" id="titelName" required>
	</div>
	<div class="small-12 cell">
			<label for="titelInterpret">Name des Interpreten</label>
			<input type="text" name="titelInterpret" id="titelInterpret" list="int" required>$iliste</input>
	</div>
	<div class="small-12 cell">
			<label for="titelJahr">Jahr der Veröffentlichung</label>
			<input type="number" name="titelJahr" id="titelJahr" min=1900 step=1>
	</div>
	<div class="small-12 cell">
			<label for="titelSprache">Sprache des Titels</label>
			<input type="text" name="titelSprache" id="titelSprache" list="lang">$lliste</input>
	</div>
	<div class="small-12 cell">
			<label for="Youtube">Video des Titels</label>
			<input type="url" name="Youtube" id="Youtube">
	</div>
	<div class="small-12 cell">
			<label for="Einreichung">Bereitstellung</label>
			<select id="Einreichung" name="Einreichung"><option>Der Titel wird kurzfristig an die Spielleitung geschickt.</option><option>Ich benötige Hilfe bei der Bereitstellung des Titels.</option></select>
	</div>
</div>
<button type="submit" name="titelspeichern" value="speichern" class="button">speichern</button>
</form>

html;
}

print <<<html
<h2>Titel bereitstellen</h2>
<p>Über dieses Formular könnt ihr dem Team direkt eure Lieder als MP3 oder ähnliches zur Verfügung stellen ohne Sie per Mail schicken zu müssen. Einfach die Musikdatei in dem folgenden Formular auswählen und auf hochladen klicken. Das dauert je nach Internetverbindung ein paar Sekunden - also etwas Gedult.</p>
<form action="spieler.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="MAX_FILE_SIZE" value="8000000" />    
<input type="file" name="lied">
<button type="submit" name="btn[upload]" value="hochladen" class="button">Titel hochladen</button>
</form>
html;

if($voting==2){
print <<<html
<script src="js/functions.js" async></script>
<h2>Vorrunde</h2>
<form name="vorrunde" method="POST" action="spieler.php">
<input type="hidden" name="teilnahmeID" value=$tnid>
<input type="hidden" id="neutral" name="neutral" value=$neutralwert>
<input type="hidden" id="tendenz" name="tendenz">
<table width="100%" border=1>
<tr><th>Titel</th><th>Interpret</th><th>Punkte (1= sehr schlecht, 3= mittel, 5= sehr gut)</th></tr>
$vorliste
</table>
<span id="Kommentar" style='color: #FF0000; font-weight: bolder;'></span>
<button class="button" type="submit" name="Vorrunde" value="Abstimmung Vorrunde">Vorrunde voten!</button>
</form>
html;
}
if($voting==1){
print <<<html
<h2>Voting</h2>
<form name="voting" method="POST" action="spieler.php">
    <input type="hidden" name="teilnahmeID" value=$tnid>
<table width="100%" border=1>
<tr><th>Platz</th><th>Punkte</th><th>Titel</th></tr>
<tr><td>1</td><td>12</td><td><select name="Platz1" size="1">$titelliste</select></td></tr>
<tr><td>2</td><td>10</td><td><select name="Platz2" size="1">$titelliste</select></td></tr>
<tr><td>3</td><td>8</td><td><select name="Platz3" size="1">$titelliste</select></td></tr>
<tr><td>4</td><td>7</td><td><select name="Platz4" size="1">$titelliste</select></td></tr>
<tr><td>5</td><td>6</td><td><select name="Platz5" size="1">$titelliste</select></td></tr>
<tr><td>6</td><td>5</td><td><select name="Platz6" size="1">$titelliste</select></td></tr>
<tr><td>7</td><td>4</td><td><select name="Platz7" size="1">$titelliste</select></td></tr>
<tr><td>8</td><td>3</td><td><select name="Platz8" size="1">$titelliste</select></td></tr>
<tr><td>9</td><td>2</td><td><select name="Platz9" size="1">$titelliste</select></td></tr>
<tr><td>10</td><td>1</td><td><select name="Platz10" size="1">$titelliste</select></td></tr>
</table>
<button class="button" type="submit" name="Voting" value="Voting abgeben">Voting abgeben!</button>
</form>
html;
}
}
include("incl/footer.php");

?>

<?php

include("conf/config.php");
include("incl/header.php");

if($_SESSION["Leiter"]==1){

//Anmeldung abschlie�en

if(isset($_GET["startVoting"])){
	startVoting();
}

//Voting abschlie�en

if(isset($_GET["closeVoting"])){
	closeVoting($_GET["closeVoting"]);
}

//Vorrunde abschlie�en

if(isset($_GET["closeVorrunde"])){
	closeVorrunde($_GET["closeVorrunde"]);
}

//Vorrunde abschlie�en

if(isset($_GET["closeFinale"])){
	closeFinale($_GET["closeFinale"]);
}

//Datenbank reparieren

if(isset($_GET["repareDatabase"])){
	$sql="SELECT userID, wettbewerbsID From Titel where titelPlatzierung>0 and userID>0 Group by userID, wettbewerbsID";
        $result=mysqli_query($conn,$sql);
        while($erg=mysqli_fetch_object($result)){
            $sql2="SELECT * FROM Teilnahme where userID=$erg->userID and wettbewerbsID=$erg->wettbewerbsID";
            $result2=mysqli_query($conn,$sql2);
            if (mysqli_num_rows($result2)==0) {
                mysqli_query($conn,"INSERT INTO Teilnahme (userID, wettbewerbsID) VALUES ($erg->userID, $erg->wettbewerbsID)");
            }
        }
}


//Eingaben speichern

if(isset($_POST["userspeichern"]) and $_POST["userspeichern"]=="speichern"){
if(isset($_POST["aktiv"]) and $_POST["aktiv"]==1) $aktiv=1; else $aktiv=0;
if(isset($_POST["spielleiter"]) and $_POST["spielleiter"]==1) $spielleiter=1; else $spielleiter=0;
if(isset($_POST["kassenwart"]) and $_POST["kassenwart"]==1) $kassenwart=1; else $kassenwart=0;	
if($_POST["userID"]==""){
		$newhash=password_hash($_POST["passwort"],PASSWORD_DEFAULT);
		$sql="INSERT INTO Spieler (userID, userName, vorname, nachname, passwort, email, aktiv, punkteAlltime, punkteSpecialAlltime) VALUES ('".$_POST["userID"]."','".mysqli_real_escape_string($conn,$_POST["userName"])."','".mysqli_real_escape_string($conn,$_POST["vorname"])."','".mysqli_real_escape_string($conn,$_POST["nachname"])."','".$newhash."','".mysqli_real_escape_string($conn,$_POST["email"])."','".$aktiv."','".mysqli_real_escape_string($conn,$_POST["punkteAlltime"])."','".mysqli_real_escape_string($conn,$_POST["punkteSpecialAlltime"])."')";
                $_POST["userID"]=  mysqli_insert_id($conn);
}
elseif(strlen($_POST["passwort"])>0){
		
		$newhash=password_hash($_POST["passwort"],PASSWORD_DEFAULT);
		$sql="UPDATE Spieler SET"
                        . " userName='".mysqli_real_escape_string($conn,$_POST["userName"])."',"
                        . " vorname='".mysqli_real_escape_string($conn,$_POST["vorname"])."',"
                        . " nachname='".mysqli_real_escape_string($conn,$_POST["nachname"])."',"
                        . " passwort='".$newhash."',"
                        . " email='".mysqli_real_escape_string($conn,$_POST["email"])."',"
                        . " aktiv='".$aktiv."',"
                        . " punkteAlltime='".mysqli_real_escape_string($conn,$_POST["punkteAlltime"])."',"
                        . " punkteSpecialAlltime='".mysqli_real_escape_string($conn,$_POST["punkteSpecialAlltime"])."'"
                        . " WHERE userID=".$_POST["userID"];
}
else{
		$sql="UPDATE Spieler SET"
                        . " userName='".mysqli_real_escape_string($conn,$_POST["userName"])."',"
                        . " vorname='".mysqli_real_escape_string($conn,$_POST["vorname"])."',"
                        . " nachname='".mysqli_real_escape_string($conn,$_POST["nachname"])."',"
                        . " email='".mysqli_real_escape_string($conn,$_POST["email"])."',"
                        . " aktiv='".$aktiv."',"
                        . " punkteAlltime='".mysqli_real_escape_string($conn,$_POST["punkteAlltime"])."',"
                        . " punkteSpecialAlltime='".mysqli_real_escape_string($conn,$_POST["punkteSpecialAlltime"])."'"
                        . " WHERE userID=".$_POST["userID"];

}
mysqli_query($conn,$sql) or die(mysqli_error($conn).":$sql");
$sql="REPLACE INTO Rollen (userID, spielleiter, kassenwart) VALUES (".$_POST["userID"].",$spielleiter, $kassenwart)";
mysqli_query($conn,$sql) or die(mysqli_error($conn).":$sql");
}


if(isset($_POST["wettbewerbspeichern"]) and $_POST["wettbewerbspeichern"]=="speichern"){
if(isset($_POST["isSpecialWettbewerb"]) and $_POST["isSpecialWettbewerb"]==1) $special=1; else $special=0;
if(isset($_POST["mitVorrunde"]) and $_POST["mitVorrunde"]==1) $vorrunde=1; else $vorrunde=0;
if($_POST["wettbewerbsID"]==""){
    $sql="INSERT INTO Wettbewerb (wettbewerbsName, wettbewerbsJahr, isSpecialWettbewerb) VALUES ('".$_POST["wettbewerbsName"]."','".$_POST["wettbewerbsJahr"]."','".$special."')";
    mysqli_query($conn,$sql) or die(mysqli_error($conn).":$sql");
    $wettbewerbsid=  mysqli_insert_id($conn);
    $sql="INSERT INTO Fristen (wettbewerbsID, eroeffnungsDatum, anmeldeFrist, votingFrist, vorrundeFrist) VALUES ($wettbewerbsid,'".$_POST["eroeffnungsDatum"]."', '".$_POST["anmeldeFrist"]."', '".$_POST["votingFrist"]."', '".$_POST["vorrundeFrist"]."')";
    mysqli_query($conn,$sql) or die(mysqli_error($conn).":$sql");
}
else{
    $sql="Update Wettbewerb SET "
            . "wettbewerbsName='".mysqli_real_escape_string($conn,$_POST["wettbewerbsName"])."',"
            . "wettbewerbsJahr='".$_POST["wettbewerbsJahr"]."',"
            . "isSpecialWettbewerb='".$special."',"
            . "mitVorrunde='".$vorrunde."' "
            . "WHERE wettbewerbsID=".$_POST["wettbewerbsID"];
    mysqli_query($conn,$sql) or die(mysqli_error($conn).":$sql");
    $sql="Update Fristen SET "
            . "eroeffnungsDatum='".$_POST["eroeffnungsDatum"]."',"
            . "anmeldeFrist= '".$_POST["anmeldeFrist"]."',"
            . "votingFrist='".$_POST["votingFrist"]."',"
            . "vorrundeFrist='".$_POST["vorrundeFrist"]."' "
            . "WHERE wettbewerbsID=".$_POST["wettbewerbsID"];
    mysqli_query($conn,$sql) or die(mysqli_error($conn).":$sql");
}

}

if(isset($_POST["titelspeichern"]) and $_POST["titelspeichern"]=="speichern"){
    $p_userID=($_POST["userID"]=="") ? "NULL":$_POST["userID"];
    $p_wettbewerbsID=($_POST["wettbewerbsID"]=="") ? "NULL":$_POST["wettbewerbsID"];

if($_POST["titelID"]==""){
    $sql="INSERT INTO Titel (userID,wettbewerbsID,titelName,titelInterpret,titelJahr,titelSprache, titelPlatzierung,titelPunkte,Youtube) VALUES ($p_userID,$p_wettbewerbsID,'".mysqli_real_escape_string($conn,$_POST["titelName"])."','".mysqli_real_escape_string($conn,$_POST["titelInterpret"])."','".mysqli_real_escape_string($conn,$_POST["titelJahr"])."','".mysqli_real_escape_string($conn,$_POST["titelSprache"])."','".mysqli_real_escape_string($conn,$_POST["titelPlatzierung"])."','".mysqli_real_escape_string($conn,$_POST["titelPunkte"])."','".mysqli_real_escape_string($conn,$_POST["Youtube"])."')";
}
else{
    $sql="UPDATE Titel SET"
            . " userID=$p_userID,"
            . " wettbewerbsID=$p_wettbewerbsID,"
            . " titelName='".mysqli_real_escape_string($conn,$_POST["titelName"])."',"
            . " titelInterpret='".mysqli_real_escape_string($conn,$_POST["titelInterpret"])."',"
            . " titelJahr='".mysqli_real_escape_string($conn,$_POST["titelJahr"])."',"
            . " titelSprache='".mysqli_real_escape_string($conn,$_POST["titelSprache"])."',"
            . " titelPlatzierung='".mysqli_real_escape_string($conn,$_POST["titelPlatzierung"])."',"
            . " titelPunkte='".mysqli_real_escape_string($conn,$_POST["titelPunkte"])."',"
            . " Youtube='".mysqli_real_escape_string($conn,$_POST["Youtube"])."'"
            . " WHERE titelID='".mysqli_real_escape_string($conn,$_POST["titelID"])."'";
}

mysqli_query($conn,$sql) or die(mysqli_error($conn).":$sql");
}

        if(isset($_POST["Seite"])){
	$sql="Replace INTO Seiten
		(seitenID, seitenTitel, text)
		VALUES
		('".$_POST["seitenID"]."','".mysqli_real_escape_string($conn,$_POST["seitenTitel"])."','".mysqli_real_escape_string($conn,$_POST["text"])."')";
	mysqli_query($conn,$sql) or die (mysqli_error($conn));
	}


//Checkbuttons initialisieren

$checkaktiv="";
$checkleiter="";
$checkkasse="";
$checkspecial="";
$sid="";
$wid="";
$tid="";

//Spieler laden

if(isset($_GET["sedit"])){
    $sql="SELECT * FROM Spieler"
            . " LEFT JOIN Rollen USING (userID)"
            . " WHERE Spieler.userID=".$_GET["sedit"]
            . " ORDER BY vorname, nachname";
    $result=  mysqli_query($conn,$sql);
    $serg=mysqli_fetch_object($result);
    if($serg->aktiv==1) {$checkaktiv="checked";}
    if($serg->spielleiter==1) {$checkleiter="checked";}
    if($serg->kassenwart==1) {$checkkasse="checked";}
    $sid=$serg->userID;
}

//Wettbewerb laden

if(isset($_GET["wedit"])){
    $sql="SELECT * FROM Wettbewerb"
            . " LEFT JOIN Fristen USING (wettbewerbsID)"
            . " WHERE Wettbewerb.wettbewerbsID=".$_GET["wedit"];
    $result=  mysqli_query($conn,$sql);
    $werg=mysqli_fetch_object($result);
    if($werg->isSpecialWettbewerb==1) {$checkspecial="checked";}
    if($werg->mitVorrunde==1) {$checkvorrunde="checked";}
    $wid=$werg->wettbewerbsID;    
}

//Titel laden

if(isset($_GET["tedit"])){
    $sql="SELECT * FROM Titel"
            . " WHERE titelID=".$_GET["tedit"];
    $result=  mysqli_query($conn,$sql);
    $terg=mysqli_fetch_object($result);
    $tid=$terg->titelID;        
}


//Auswahllisten bauen

$spielerliste="";
$spielerliste2="";
$sql="SELECT * FROM Spieler ORDER BY vorname, nachname";
$result=mysqli_query($conn,$sql);
while($erg=mysqli_fetch_object($result)){
    $spielerliste.="<option value='admin.php?sedit=$erg->userID#Spieler'>$erg->vorname $erg->nachname</option>\n";
    if(isset($_GET["tedit"]) and $terg->userID==$erg->userID){
        $spielerliste2.="<option value='$erg->userID' selected>$erg->vorname $erg->nachname</option>\n";
    }
    else{
    $spielerliste2.="<option value='$erg->userID'>$erg->vorname $erg->nachname</option>\n";
    }
}
$wettbewerbliste="";
$wettbewerbliste2="";
$sql="SELECT * FROM Wettbewerb ORDER BY wettbewerbsJahr";
$result=mysqli_query($conn,$sql);
while($erg=mysqli_fetch_object($result)){
    $wettbewerbliste.="<option value='admin.php?wedit=$erg->wettbewerbsID#Wettbewerb'>$erg->wettbewerbsJahr - $erg->wettbewerbsName</option>\n";
    if(isset($_GET["tedit"]) and $terg->wettbewerbsID==$erg->wettbewerbsID){
    $wettbewerbliste2.="<option value='$erg->wettbewerbsID' selected>$erg->wettbewerbsJahr - $erg->wettbewerbsName</option>\n";
    }
    else{
    $wettbewerbliste2.="<option value='$erg->wettbewerbsID'>$erg->wettbewerbsJahr - $erg->wettbewerbsName</option>\n";
    }
}
$titelliste="";
$sql="SELECT * FROM Titel ORDER BY titelName, titelInterpret";
$result=mysqli_query($conn,$sql);
while($erg=mysqli_fetch_object($result)){
    $titelliste.="<option value='admin.php?tedit=$erg->titelID#Titel'>$erg->titelName ($erg->titelInterpret)</option>\n";
}

$clvt="";
$sql="SELECT * FROM Wettbewerb WHERE closeSignIn=1 and closeVoting=0 ORDER BY wettbewerbsJahr";
$result=mysqli_query($conn,$sql);
while($erg=mysqli_fetch_object($result)){
    $clvt.="<li><a href='admin.php?closeVoting=$erg->wettbewerbsID'>$erg->wettbewerbsName</li>\n";
}
$clvo="";
$sql="SELECT * FROM Wettbewerb WHERE mitVorrunde=1 and closeSignIn=1 and closeVoting=0 ORDER BY wettbewerbsJahr";
$result=mysqli_query($conn,$sql);
while($erg=mysqli_fetch_object($result)){
    $clvo.="<li>Vorrunde <a href='admin.php?closeVorrunde=$erg->wettbewerbsID'>$erg->wettbewerbsName</a></li><li>Finale <a href='admin.php?closeFinale=$erg->wettbewerbsID'>$erg->wettbewerbsName</a></li>\n";
}


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

//HTML-Seitenaufbau

print <<<html

<h2><a name="Spieler">Nutzerverwaltung</a></h2>

<select name="userID" size=1 onchange="if (this.value) window.location.href=this.value">
<option value="">neuer Mitspieler</option>
$spielerliste
</select>
<form name="spielerverwaltung" method="POST" action="admin.php">
<input type="hidden" name="userID" value="$sid">
<table>
        <tr><td>Username</td><td><input name="userName" type="text" value="$serg->userName"></td></tr>
        <tr><td>Vorname Nachname</td><td><input name="vorname" type="text" value="$serg->vorname"> <input name="nachname" type="text" value="$serg->nachname" ></td></tr>
        <tr><td>Passwort</td><td><input name="passwort" type="password"></td></tr>
        <tr><td>Mail-Adresse</td><td><input name="email" type="email" value="$serg->email"></td></tr>
        <tr><td>Punkte</td><td>Alltime <input name="punkteAlltime" type="number" value="$serg->punkteAlltime">, Special <input name="punkteSpecialAlltime" type="number" value="$serg->punkteSpecialAlltime"></td></tr>
        <tr><td>Rollen</td><td>Aktiv <input name="aktiv" type="checkbox" value=1 $checkaktiv>, Spielleiter <input name="spielleiter" type="checkbox" value=1 $checkleiter>, Kassenwart <input name="kassenwart" type="checkbox" value=1 $checkkasse></td></tr>
</table>
<button type="submit" name="userspeichern" value="speichern" class="button">Spieler speichern</button>
   </form>
        

<h2><a name="Wettbewerb">Wettbewerbe verwalten</a></h2>
        
<select name="wettbewerbsID" size=1 onchange="if (this.value) window.location.href=this.value">
<option value="">neuer Wettbewerb</option>
$wettbewerbliste
</select>
<form name="wettbewerbverwaltung" method="POST" action="admin.php">
<input type="hidden" name="wettbewerbsID" value="$wid">
<table>
        <tr><td>Wettbewerbsname</td><td><input name="wettbewerbsName" type="text" value="$werg->wettbewerbsName"></td></tr>
	<tr><td>Wettbewerbsjahr</td><td><input name="wettbewerbsJahr" type="number" value="$werg->wettbewerbsJahr"> 
		Special: <input name="isSpecialWettbewerb" type="checkbox" value=1 $checkspecial>
		Vorrunde: <input name="mitVorrunde" type="checkbox" value=1 $checkvorrunde>
	</td></tr>
        <tr><td>Start-Datum</td><td><input name="eroeffnungsDatum" type="date" value="$werg->eroeffnungsDatum"></td></tr>
        <tr><td>Anmeldefrist</td><td><input name="anmeldeFrist" type="date" value="$werg->anmeldeFrist"></td></tr>
        <tr><td>Votingfrist</td><td><input name="votingFrist" type="date" value="$werg->votingFrist"></td></tr>            
        <tr><td>Vorrunde</td><td><input name="vorrundeFrist" type="date" value="$werg->vorrundeFrist"></td></tr>            
</table>        
<button type="submit" name="wettbewerbspeichern" value="speichern" class="button">Wettbewerb speichern</button>
   </form>
        
        
<h2><a name="Titel">Titel verwalten</a></h2>

<select name="titelID" size=1 onchange="if (this.value) window.location.href=this.value">
<option value="">neuer Titel</option>
$titelliste
</select>
<form name="titelverwaltung" method="POST" action="admin.php">
<input type="hidden" name="titelID" value="$tid">
<table>
        <tr><td>Titel</td><td><input name="titelName" type="text" value="$terg->titelName"></td></tr>
        <tr><td>Interpret</td><td><input name="titelInterpret" type="text" value="$terg->titelInterpret" list="int">$iliste</input></td></tr>
        <tr><td>Jahr</td><td><input name="titelJahr" type="number" value="$terg->titelJahr"></td></tr>
        <tr><td>Sprache</td><td><input name="titelSprache" type="text" value="$terg->titelSprache" list="lang">$lliste</input></td></tr>
        <tr><td>Youtube</td><td><input name="Youtube" type="url" value="$terg->Youtube"></td></tr>
        <tr><td>Platzierung</td><td><input name="titelPlatzierung" type="number" value="$terg->titelPlatzierung"></td></tr>
        <tr><td>Punkte</td><td><input name="titelPunkte" type="number" value="$terg->titelPunkte"></td></tr>        
        <tr><td>Spieler</td><td><select name="userID" size=1><option value="">bitte wählen</option>$spielerliste2</select></td></tr>
        <tr><td>Wettbewerb</td><td><select name="wettbewerbsID" size=1><option value="">bitte wählen</option>$wettbewerbliste2</select></td></tr>
        
</table>        
<button type="submit" name="titelspeichern" value="speichern" class="button">Titel speichern</button>
   </form>
        
<a name="Seitendaten"></a>
<h2>Seiten bearteiten</h2>
<script src="//cdn.ckeditor.com/4.7.1/full/ckeditor.js"></script>
<p>Seite laden:
html;
$sql="SELECT * FROM Seiten";
$result=mysqli_query($conn,$sql);
while($erg=mysqli_fetch_object($result)) print "<a href='admin.php?sedit=$erg->seitenID#Seitendaten'>$erg->seitenTitel</a> ";
if(isset($_GET["sedit"])){
	$sql="SELECT * FROM Seiten WHERE seitenID=".$_GET["sedit"];
	$result=mysqli_query($conn,$sql);
	$tauf=mysqli_fetch_object($result);
}

print <<<html
</p>
<p>
<form name="Seite" action="admin.php" method="POST">
Nr <input type="number" name="seitenID" value="$tauf->seitenID"><br>
Titel <input type="text" name="seitenTitel" value="$tauf->seitenTitel"><br>
<textarea id="text" name="text" cols=60 rows=10>
$tauf->text
</textarea>
<script>
CKEDITOR.replace( 'text' );
</script>
<br>
<button class="button" type="submit" name="Seite" value="erstellen">Seite speichern</button>
</form>

<h2><a name="Titel">PFMGP verwalten</a></h2>
<ul>
<li><a href="admin.php?startVoting">Anmeldung abschließen</a></li>
<li>Mehrstufiges Voting abschließen</li>
<li><ul>$clvo</ul></li>
</ul>
<li>Voting abschließen</li>
<li><ul>$clvt</ul></li>
<li><a href="admin.php?repareDatabase">Datenbank reparieren</a></li>
</ul>


html;
}
include("incl/footer.php");

?>

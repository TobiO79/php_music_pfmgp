<?php

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'vendor/autoload.php';

// Ueberprueft ob ein Benutzer mit $name und $pass in der Datenbank vorhanden ist, und liefer die SpielerID zur&uuml;ck
function check_user($name, $pass)
{
    global $conn;
    $iha = new iha();
    
    $sql="SELECT userID, passwort, sessionID
          FROM Spieler
          WHERE aktiv=1 AND userName='".$name."'";
    $result=mysqli_query($conn,$sql) or die(mysqli_error($conn));
    if (mysqli_num_rows($result)<>1) die("Benutzername wurde nicht gefunden!");
    $user=mysqli_fetch_assoc($result);

if(substr($user["passwort"],0,6)=="AAAJxA"){
	if($iha->compare($pass,$user["passwort"])){
		$hash=password_hash($pass,PASSWORD_DEFAULT);
		mysqli_query($conn,"UPDATE Spieler SET passwort='$hash' WHERE userID=".$user["userID"]);
		logbuch($user["userID"],"Verschluesselungs-Hash-Funktion auf password_hash umgestellt.");
	    return $user["userID"];
    }
    else return false; 
}
    if(password_verify($pass,$user["passwort"]))
      return $user["userID"];
    else return false; 
}

// Meldet den Benutzer mit UserID an.
function login($userid)
{
    global $conn;   
    $sql="UPDATE Spieler
             SET sessionID='".session_id()."'
             WHERE aktiv=1 AND userID=".$userid;
       mysqli_query($conn,$sql);
       mysqli_query($conn,"INSERT INTO Log (userID,logText) VALUES ($userid,'Anmeldung von ".$_SERVER["REMOTE_ADDR"]." - ".gethostbyaddr($_SERVER["REMOTE_ADDR"])." - ".$_SERVER["HTTP_USER_AGENT"]."')");
    
}
// Testet ob ein Benutzer mit der aktuellen SessionID angemeldet ist
function logged_in()
{
    if(session_id()=="") return false;
    global $conn;
    $sql="SELECT userID, userName, spielleiter, kassenwart
    FROM Spieler
    LEFT JOIN Rollen USING (userID)
    WHERE aktiv=1 AND sessionID='".session_id()."'";
    $result= mysqli_query($conn,$sql);
    if (mysqli_num_rows($result)==1){
	    $erg=mysqli_fetch_object($result);
	    if (!isset($_SESSION["UserID"])) $_SESSION["UserID"]=$erg->userID;
	    if (!isset($_SESSION["User"])) $_SESSION["User"]=$erg->userName;
	    if (!isset($_SESSION["Leiter"])) $_SESSION["Leiter"]=$erg->spielleiter;
	    if (!isset($_SESSION["Kasse"])) $_SESSION["Kasse"]=$erg->kassenwart;
    }
    return ( mysqli_num_rows($result)==1);
}
function new_password($mail){
    global $conn;
    $sql="SELECT * FROM Spieler where aktiv=1 AND email='$mail'";
    $result=mysqli_query($conn,$sql);
    if (mysqli_num_rows($result)>0){
        $erg=mysqli_fetch_object($result);
        $newpassword=zufallsstring(10);
                $text="Dein neues Login für PFMGP.de lautet:\n"
                . "Benutzer: $erg->userName\n"
                . "Passwort: $newpassword\n\n"
                . "Du kannst das Passwort jederzeit in der Spielerverwaltung ändern.";

        $newhash=password_hash($newpassword,PASSWORD_DEFAULT);
        $sql="UPDATE Spieler SET passwort='$newhash' WHERE email='$mail'";
	mysqli_query($conn,$sql);
	$empf[]=$mail;
        pfmgp_mail("Dein neues Passwort",$text,$empf);
	logbuch($erg->UserID,"neues Passwort an $mail geschickt.");
    }
}
// Meldet den Benutzer mit der aktuellen SessionID ab und entfernt die Session
function logout()
{
    global $conn;
    $sql="UPDATE Spieler
    SET sessionID=NULL
    WHERE sessionID='".session_id()."'";
     mysqli_query($conn,$sql) or die(mysqli_error($conn).": $sql");
     session_destroy();
}

// Umwandlung Datum von Datenbank nach Deutsch
function date_mysql2german($date,$short=false) {
    $d    =    explode("-",$date);
    if($short) $d[0]=substr($d[0],2);
    return    sprintf("%02d.%02d.%02d", $d[2], $d[1], $d[0]);
}
// Umwandlung Datum von Deutsch nach Datenbank
function date_german2mysql($date) {
    $d    =    explode(".",$date);
    if ($d[2]>1900 or ($d[0]==0 and $d[1]==0 and $d[2]==0))
      return    sprintf("%04d-%02d-%02d", $d[2], $d[1], $d[0]);
    elseif ($d[2]>=0 and $d[2]<=30)
      return    sprintf("%04d-%02d-%02d", 2000+$d[2], $d[1], $d[0]);
    elseif ($d[2]>=31 and $d[2]<=99)
      return    sprintf("%04d-%02d-%02d", 1900+$d[2], $d[1], $d[0]);
}

function zufallsstring($laenge) {
   //M�gliche Zeichen f�r den String
   $zeichen = '123456789';
   $zeichen .= 'abcdefghijkmnopqrstuvwxyz';
   $zeichen .= 'ABCDEFGHJKLMNPQRSTUVWXYZ';

 
   //String wird generiert
   $str = '';
   $anz = strlen($zeichen);
   for ($i=0; $i<$laenge; $i++) {
      $str .= $zeichen[rand(0,$anz-1)];
   }
   return $str;
}

function aktueller_pfmgp() {
    global $conn;
	$heute=date("Y-m-d");
	$sql="SELECT * FROM Wettbewerb
		LEFT JOIN Fristen USING (wettbewerbsID)
		WHERE eroeffnungsDatum<='$heute' and votingFrist>='$heute'";
        $result=mysqli_query($conn,$sql);
	if(mysqli_num_rows($result)>0){
		$erg=mysqli_fetch_object($result);
		$pfmgp[]=$erg->wettbewerbsName;
		$pfmgp[]=$erg->wettbewerbsID;
		if(strtotime($erg->anmeldeFrist) >= strtotime($heute) or $erg->closeSignIn==0) $pfmgp[]="Anmeldung"; 
                else
                    { 
                    if ($erg->mitVorrunde==0) $pfmgp[]="Voting";
                    elseif (strtotime($erg->vorrundeFrist) >= strtotime($heute)) $pfmgp[]="Vorrunde";
                    else $pfmgp[]="Voting";
                  
                    }
		$pfmgp[]=$erg->isSpecialWettbewerb;
                $pfmgp[]=$erg->mitVorrunde;
	}
	else {
	$pfmgp[]="Kein PFMGP aktiv";
	$pfmgp[]=0;
	$pfmgp[]="Kein PFMGP aktiv";
	$pfmgp[]=0;
        $pfmgp[]=0;
	}
	return $pfmgp;
}

function startVoting() {
    global $conn;
//Spieler mit Titeleinreichungen in Teilnahme-Tabelle schreiben
//unvollst�ndige Titeleinreichungen l�schen
//Anmeldung als abgeschlossen markieren
//Info-Mail an Teilnehmer senden
    $pfmgp=aktueller_pfmgp();
    $sql="SELECT userID, Count(*) TZ FROM Titel"
            . " WHERE wettbewerbsID=".$pfmgp[1]
            . " GROUP By userID";
    $result=mysqli_query($conn,$sql);
    while($erg=mysqli_fetch_object($result)){
        if($erg->TZ+$pfmgp[3]==2){
            mysqli_query($conn,"INSERT INTO Teilnahme (userID, wettbewerbsID) VALUES ($erg->userID, $pfmgp[1])") or die(mysqli_error($conn).": $sql");
    }
    else{
        mysqli_query($conn,"DELETE FROM Titel WHERE userID=$erg->userID AND wettbewerbsID=$pfmgp[1]") or die(mysqli_error($conn).": $sql");
    }
    }
    mysqli_query($conn,"UPDATE Wettbewerb SET closeSignIn=1 WHERE wettbewerbsID=$pfmgp[1]") or die(mysqli_error($conn).": $sql");
    mysqli_query($conn,"INSERT INTO Log (userID,logText) VALUES ($userid,'Voting gestartet')");
    telegchan("Die Anmeldung des aktuellen PFMGP ist abgeschlossen und das Voting hat begonnen. Ihr könnt euch nun auf euerer Teilnehmerseite auf www.pfmgp.de einloggen und abstimmen.");
}

function closeVorrunde($wettbewerb) {
    global $conn;
//Strafpunkte berechnen
//Titel f�r Finale markieren
//Vorrundentitel platzieren

$sql="SELECT * FROM Wettbewerb WHERE wettbewerbsID=$wettbewerb";
    $result=mysqli_query($conn,$sql);
    $erg=mysqli_fetch_object($result);
    if($erg->closeVoting==1) die("Wettbewerb ist bereits abgeschlossen!");
    if($erg->isSpecialWettbewerb==0) $typ="n"; else $typ="s";
    
    if($typ=="n"){
            $sql="SELECT * FROM Spieler"
            . " WHERE punkteAlltime>0"
            . " ORDER BY punkteAlltime DESC";
    }
    elseif($typ=="s"){
            $sql="SELECT * FROM Spieler"
            . " WHERE punkteSpecialAlltime>0"
            . " ORDER BY punkteSpecialAlltime DESC";
    }
    $rresult=mysqli_query($conn,$sql);
    $i=0;
    while($rerg=mysqli_fetch_object($rresult)){
        $i++;
        if ($typ=="n") $sql="UPDATE Spieler SET platzAlltimeVorjahr=$i WHERE userID=$rerg->userID";
        elseif ($typ=="s") $sql="UPDATE Spieler SET platzSpecialVorjahr=$i WHERE userID=$rerg->userID";
        mysqli_query($conn,$sql);
     
    }
    
    $sql="SELECT teilnahmeID, titelID FROM `teilnahmeUebersicht`, Titel, Teilnahme
WHERE `teilnahmeUebersicht`.userID=Titel.userID
AND `teilnahmeUebersicht`.wettbewerbsID=Teilnahme.wettbewerbsID
AND `teilnahmeUebersicht`.userID=Teilnahme.userID
AND `teilnahmeUebersicht`.wettbewerbsID=Titel.wettbewerbsID
AND `NumberOfVorrunde`=0
AND teilnahmeUebersicht.wettbewerbsID=$wettbewerb
ORDER BY teilnahmeID";
    $result=mysqli_query($conn,$sql);
    while($erg=mysqli_fetch_object($result)){
        
        $sql="INSERT INTO Vorrunde (teilnahmeID, titelID, punkte) VALUES ($erg->teilnahmeID, $erg->titelID, '-5')";
        mysqli_query($conn,$sql) or die(mysqli_error($conn).": $sql");
        
    }
    $sql="UPDATE Titel SET Runde='F' WHERE wettbewerbsID=$wettbewerb";
    mysqli_query($conn,$sql);
    $sql="SELECT * FROM VorrundeErgebnisse WHERE wettbewerbsID=$wettbewerb ORDER BY Summe DESC, Punkte5 DESC, Punkte4 DESC, Punkte3 DESC, Punkte2 DESC, Punkte1 DESC";
    $result=mysqli_query($conn,$sql);
    $teilnehmer=mysqli_num_rows($result);
    $i=0;
    $p01=0; $p02=0; $p03=0; $p04=0; $p05=0; $sp=0;
    $apkt=$teilnehmer-20;
    while($erg=mysqli_fetch_object($result)){
        $i++;
        if($i>20){
        if (!($sp==$erg->Summe AND $p5==$erg->Punkte5 AND $p4==$erg->Punkte4 AND $p3==$erg->Punkte3 AND $p2==$erg->Punkte2 AND $p1==$erg->Punkte1)){
            $platz=$i;
            $pkt=$apkt;
        }
        $sql="UPDATE Titel SET Runde='V', titelPlatzierung=$platz, titelPunkte=$erg->Summe WHERE titelID=$erg->titelID";
        
        mysqli_query($conn,$sql) or die(mysqli_error().": $sql");
        
        if($typ=="n"){
            $sql="UPDATE Spieler SET PunkteAlltime=PunkteAlltime+$pkt WHERE userID=$erg->UserID";
        }
        if($typ=="s"){
            $sql="UPDATE Spieler SET PunkteSpecialAlltime=PunkteSpecialAlltime+$pkt WHERE userID=$erg->UserID";
        }
        mysqli_query($conn,$sql) or die(mysqli_error($conn).": $sql");
        
        $apkt=$apkt-1;
        $p5=$erg->Punkte5;
        $p4=$erg->Punkte4;
        $p3=$erg->Punkte3;
        $p2=$erg->Punkte2;
        $p1=$erg->Punkte1;
        $sp=$erg->Summe;
        }        
    }
    $sql="UPDATE Fristen SET vorrundeFrist='".date("Y-m-d")."' WHERE wettbewerbsID=$wettbewerb";
        mysqli_query($conn,$sql) or die(mysqli_error($conn).": $sql");
    mysqli_query($conn,"INSERT INTO Log (userID,logText) VALUES ($userid,'Vorrunde abgeschlossen, Alltime aktualisiert, Final gestartet.')");
    telegchan("Das Voting für die Vorrunde wurde abgeschlossen. Die Punktzahlen im All-Time-Ranking wurden aktualisiert. Das Voting für die Finalrunde ist eröffnet.");    
    
}

function closeFinale($wettbewerb) {
    global $conn;
//Strafpunkte berechnen
//Voting als abschlossen markieren
//Info-Mail an Teilnehmer senden    

$sql="SELECT * FROM Wettbewerb WHERE wettbewerbsID=$wettbewerb";
    $result=mysqli_query($conn,$sql);
    $erg=mysqli_fetch_object($result);
    if($erg->closeVoting==1) die("Wettbewerb ist bereits abgeschlossen!");
    if($erg->isSpecialWettbewerb==0) $typ="n"; else $typ="s";
  
    
    $sql="SELECT teilnahmeID, titelID,NumberOfTitlesForPFMGP FROM `teilnahmeUebersicht`, Titel, Teilnahme
WHERE `teilnahmeUebersicht`.userID=Titel.userID
AND `teilnahmeUebersicht`.wettbewerbsID=Teilnahme.wettbewerbsID
AND `teilnahmeUebersicht`.userID=Teilnahme.userID
AND `teilnahmeUebersicht`.wettbewerbsID=Titel.wettbewerbsID
AND `NumberOfVotings`=0
AND teilnahmeUebersicht.wettbewerbsID=$wettbewerb
ORDER BY teilnahmeID";
    $result=mysqli_query($conn,$sql);
    while($erg=mysqli_fetch_object($result)){
        if($erg->NumberOfTitlesForPFMGP==1){
            $sql="INSERT INTO Voting (teilnahmeID, abzug1) VALUES ($erg->teilnahmeID, $erg->titelID)";
        }
        else{
            $titel1=$erg->titelID;
            $erg=mysqli_fetch_object($result);
            $sql="INSERT INTO Voting (teilnahmeID, abzug1, abzug2) VALUES ($erg->teilnahmeID, $titel1, $erg->titelID)";
        }
        mysqli_query($conn,$sql) or die(mysqli_error().": $sql");
        
    }
    $sql="SELECT * FROM titelErgebnisse WHERE wettbewerbsID=$wettbewerb ORDER BY Summe DESC, Punkte12 DESC, Punkte10 DESC, Punkte08 DESC, Punkte07 DESC, Punkte06 DESC, Punkte05 DESC, Punkte04 DESC, Punkte03 DESC, Punkte02 DESC, Punkte01 DESC";
    $result=mysqli_query($conn,$sql);
    $teilnehmer=mysqli_num_rows($result);
    $i=0;
    $p01=0; $p02=0; $p03=0; $p04=0; $p05=0; $p06=0; $p07=0; $p08=0; $p10=0; $p12=0;
    $apkt=$teilnehmer+5;
    while($erg=mysqli_fetch_object($result)){
        $i++;
        if($i<=20){
        if (!($sp==$erg->Summe AND $p12==$erg->Punkte12 AND $p10==$erg->Punkte10 AND $p08==$erg->Punkte08 AND $p07==$erg->Punkte07 AND $p06==$erg->Punkte06 AND $p05==$erg->Punkte05 AND $p04==$erg->Punkte04 AND $p03==$erg->Punkte03 AND $p02==$erg->Punkte02 AND $p01==$erg->Punkte01)){
            $platz=$i;
            $pkt=$apkt;
        }
        $sql="UPDATE Titel SET titelPlatzierung=$platz, titelPunkte=$erg->Summe WHERE titelID=$erg->titelID";
        mysqli_query($conn,$sql) or die(mysqli_error().": $sql");
        
        if($typ=="n"){
            $sql="UPDATE Spieler SET PunkteAlltime=PunkteAlltime+$pkt WHERE userID=$erg->UserID";
        }
        if($typ=="s"){
            $sql="UPDATE Spieler SET PunkteSpecialAlltime=PunkteSpecialAlltime+$pkt WHERE userID=$erg->UserID";
        }
        mysqli_query($conn,$sql) or die(mysqli_error($conn).": $sql");
        
        if($i==1) $apkt=$apkt-3;
        if($i==2) $apkt=$apkt-3;
        if($i==3) $apkt=$apkt-2;
        if($i>3) $apkt=$apkt-1;
        $p12=$erg->Punkte12;
        $p10=$erg->Punkte10;
        $p08=$erg->Punkte08;
        $p07=$erg->Punkte07;
        $p06=$erg->Punkte06;
        $p05=$erg->Punkte05;
        $p04=$erg->Punkte04;
        $p03=$erg->Punkte03;
        $p02=$erg->Punkte02;
        $p01=$erg->Punkte01;
        $sp=$erg->Summe;        
    }}
    $sql="UPDATE Wettbewerb SET closeVoting=1 WHERE wettbewerbsID=$wettbewerb";
        mysqli_query($conn,$sql) or die(mysqli_error($conn).": $sql");
    mysqli_query($conn,"INSERT INTO Log (userID,logText) VALUES ($userid,'Final-Voting abgeschlossen.')");
    telegchan("Das Voting für den aktuellen PFMGP wurde ausgewertet. Der Gewinner steht fest und alle Statistiken auf www.pfmgp.de sind aktualisiert.");
    
    
}


function closeVoting($wettbewerb) {
    global $conn;
//Punkte berechnen, Platzierungen berechnen, AllTime-Punkte berechnen
//Voting als abschlossen markieren
//Info-Mail an Teilnehmer senden
    
    $sql="SELECT * FROM Wettbewerb WHERE wettbewerbsID=$wettbewerb";
    $result=mysqli_query($conn,$sql);
    $erg=mysqli_fetch_object($result);
    if($erg->closeVoting==1) die("Wettbewerb ist bereits abgeschlossen!");
    if($erg->isSpecialWettbewerb==0) $typ="n"; else $typ="s";
    
    if($typ=="n"){
            $sql="SELECT * FROM Spieler"
            . " WHERE punkteAlltime>0"
            . " ORDER BY punkteAlltime DESC";
    }
    elseif($typ=="s"){
            $sql="SELECT * FROM Spieler"
            . " WHERE punkteSpecialAlltime>0"
            . " ORDER BY punkteSpecialAlltime DESC";
    }
    $rresult=mysqli_query($conn,$sql);
    $i=0;
    while($rerg=mysqli_fetch_object($rresult)){
        $i++;
        if ($typ=="n") $sql="UPDATE Spieler SET platzAlltimeVorjahr=$i WHERE userID=$rerg->userID";
        elseif ($typ=="s") $sql="UPDATE Spieler SET platzSpecialVorjahr=$i WHERE userID=$rerg->userID";
        mysqli_query($conn,$sql);
     
    }
    
    $sql="SELECT teilnahmeID, titelID,NumberOfTitlesForPFMGP FROM `teilnahmeUebersicht`, Titel, Teilnahme
WHERE `teilnahmeUebersicht`.userID=Titel.userID
AND `teilnahmeUebersicht`.wettbewerbsID=Teilnahme.wettbewerbsID
AND `teilnahmeUebersicht`.userID=Teilnahme.userID
AND `teilnahmeUebersicht`.wettbewerbsID=Titel.wettbewerbsID
AND `NumberOfVotings`=0
AND teilnahmeUebersicht.wettbewerbsID=$wettbewerb
ORDER BY teilnahmeID";
    $result=mysqli_query($conn,$sql);
    while($erg=mysqli_fetch_object($result)){
        if($erg->NumberOfTitlesForPFMGP==1){
            $sql="INSERT INTO Voting (teilnahmeID, abzug1) VALUES ($erg->teilnahmeID, $erg->titelID)";
        }
        else{
            $titel1=$erg->titelID;
            $erg=mysqli_fetch_object($result);
            $sql="INSERT INTO Voting (teilnahmeID, abzug1, abzug2) VALUES ($erg->teilnahmeID, $titel1, $erg->titelID)";
        }
        mysqli_query($conn,$sql) or die(mysqli_error($conn).": $sql");
        
    }
    $sql="SELECT * FROM titelErgebnisse WHERE wettbewerbsID=$wettbewerb ORDER BY Summe DESC, Punkte12 DESC, Punkte10 DESC, Punkte08 DESC, Punkte07 DESC, Punkte06 DESC, Punkte05 DESC, Punkte04 DESC, Punkte03 DESC, Punkte02 DESC, Punkte01 DESC";
    $result=mysqli_query($conn,$sql);
    $teilnehmer=mysqli_num_rows($result);
    $i=0;
    $p01=0; $p02=0; $p03=0; $p04=0; $p05=0; $p06=0; $p07=0; $p08=0; $p10=0; $p12=0;
    $apkt=$teilnehmer+5;
    while($erg=mysqli_fetch_object($result)){
        $i++;
        if (!($sp==$erg->Summe AND $p12==$erg->Punkte12 AND $p10==$erg->Punkte10 AND $p08==$erg->Punkte08 AND $p07==$erg->Punkte07 AND $p06==$erg->Punkte06 AND $p05==$erg->Punkte05 AND $p04==$erg->Punkte04 AND $p03==$erg->Punkte03 AND $p02==$erg->Punkte02 AND $p01==$erg->Punkte01)){
            $platz=$i;
            $pkt=$apkt;
        }
        $sql="UPDATE Titel SET titelPlatzierung=$platz, titelPunkte=$erg->Summe WHERE titelID=$erg->titelID";
        mysqli_query($conn,$sql) or die(mysqli_error($conn).": $sql");
        
        if($typ=="n"){
            $sql="UPDATE Spieler SET PunkteAlltime=PunkteAlltime+$pkt WHERE userID=$erg->UserID";
        }
        if($typ=="s"){
            $sql="UPDATE Spieler SET PunkteSpecialAlltime=PunkteSpecialAlltime+$pkt WHERE userID=$erg->UserID";
        }
        mysqli_query($conn,$sql) or die(mysqli_error($conn).": $sql");
        
        if($i==1) $apkt=$apkt-3;
        if($i==2) $apkt=$apkt-3;
        if($i==3) $apkt=$apkt-2;
        if($i>3) $apkt=$apkt-1;
        $p12=$erg->Punkte12;
        $p10=$erg->Punkte10;
        $p08=$erg->Punkte08;
        $p07=$erg->Punkte07;
        $p06=$erg->Punkte06;
        $p05=$erg->Punkte05;
        $p04=$erg->Punkte04;
        $p03=$erg->Punkte03;
        $p02=$erg->Punkte02;
        $p01=$erg->Punkte01;
        $sp=$erg->Summe;        
    }
    $sql="UPDATE Wettbewerb SET closeVoting=1 WHERE wettbewerbsID=$wettbewerb";
        mysqli_query($conn,$sql) or die(mysqli_error($conn).": $sql");
   
        mysqli_query($conn,"INSERT INTO Log (userID,logText) VALUES ($userid,'Final-Voting abgeschlossen.')");
    telegchan("Das Voting für den aktuellen PFMGP wurde ausgewertet. Der Gewinner steht fest und alle Statistiken auf www.pfmgp.de sind aktualisiert.");     
    
}

function leiter_addr(){
    global $conn;
    $sql="SELECT * FROM Spieler"
            . " LEFT JOIN Rollen USING (userID)"
            . "WHERE spielleiter=1";
    $result=mysqli_query($conn,$sql);
    while($erg=mysqli_fetch_object($result)){
        $empf[]=$erg->email;
    }
    return $empf;
}
function pfmgp_mail($titel,$text,$empf,$html=false){
	//Create an instance; passing `true` enables exceptions
	$mail = new PHPMailer(true);

	try {
    		//Server settings
    		$mail->isSMTP();                                            //Send using SMTP
    		$mail->Host       = 'localhost';                     //Set the SMTP server to send through
    		$mail->SMTPAuth   = false;                                   //Enable SMTP authentication
		$mail->Port       = 25;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
		$mail->SMTPAutoTLS = false;

    		//Recipients
    		$mail->setFrom('Info@PFMGP.de', 'Private Friends Music Grand Prix');
		foreach ($empf as $addr)
			$mail->addAddress($addr);               //Name is optional
		$leiter=leiter_addr();
		foreach($leiter as $addr)
			$mail->addCC($addr);

    		//Content
		$mail->isHTML($html);                                  //Set email format to HTML
		$mail->CharSet='utf-8';
		$mail->Encoding='base64';
    		$mail->Subject = '[PFMGP] '.$titel;
    		$mail->Body    = $text;

    		$mail->send();
    		echo 'Message has been sent';
	} catch (Exception $e) {
    		echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
	}
}
function logbuch($id,$text){
    global $conn;
    mysqli_query($conn,"INSERT INTO Log (userID, logText) VALUES ($id,'$text');");
}

//Telegram Funktionen

function apiRequestWebhook($method, $parameters) {
  if (!is_string($method)) {
    error_log("Method name must be a string\n");
    return false;
  }

  if (!$parameters) {
    $parameters = array();
  } else if (!is_array($parameters)) {
    error_log("Parameters must be an array\n");
    return false;
  }

  $parameters["method"] = $method;

  header("Content-Type: application/json");
  echo json_encode($parameters);
  return true;
}

function exec_curl_request($handle) {
  $response = curl_exec($handle);

  if ($response === false) {
    $errno = curl_errno($handle);
    $error = curl_error($handle);
    error_log("Curl returned error $errno: $error\n");
    curl_close($handle);
    return false;
  }

  $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
  curl_close($handle);

  if ($http_code >= 500) {
    // do not wat to DDOS server if something goes wrong
    sleep(10);
    return false;
  } else if ($http_code != 200) {
    $response = json_decode($response, true);
    error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
    if ($http_code == 401) {
      throw new Exception('Invalid access token provided');
    }
    return false;
  } else {
    $response = json_decode($response, true);
    if (isset($response['description'])) {
      error_log("Request was successfull: {$response['description']}\n");
    }
    $response = $response['result'];
  }

  return $response;
}

function apiRequest($method, $parameters) {
  if (!is_string($method)) {
    error_log("Method name must be a string\n");
    return false;
  }

  if (!$parameters) {
    $parameters = array();
  } else if (!is_array($parameters)) {
    error_log("Parameters must be an array\n");
    return false;
  }

  foreach ($parameters as $key => &$val) {
    // encoding to JSON array parameters, for example reply_markup
    if (!is_numeric($val) && !is_string($val)) {
      $val = json_encode($val);
    }
  }
  $url = API_URL.$method.'?'.http_build_query($parameters);

  $handle = curl_init($url);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($handle, CURLOPT_TIMEOUT, 60);

  return exec_curl_request($handle);
}

function apiRequestJson($method, $parameters) {
  if (!is_string($method)) {
    error_log("Method name must be a string\n");
    return false;
  }

  if (!$parameters) {
    $parameters = array();
  } else if (!is_array($parameters)) {
    error_log("Parameters must be an array\n");
    return false;
  }

  $parameters["method"] = $method;

  $handle = curl_init(API_URL);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($handle, CURLOPT_TIMEOUT, 60);
  curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
  curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

  return exec_curl_request($handle);
}

function processMessage($message) {
    global $conn;
  // process incoming message
  $message_id = $message['message_id'];
  $chat_id = $message['chat']['id'];
  if (isset($message['text'])) {
    // incoming text message
    $text = $message['text'];

    if (strpos($text, "/start") === 0) {
      apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => 'Hello', 'reply_markup' => array(
        'keyboard' => array(array('Hello', 'Hi')),
        'one_time_keyboard' => true,
        'resize_keyboard' => true)));
    } else if ($text === "Hello" || $text === "Hi") {
      apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Nice to meet you'));
    } else if (strpos($text, "/stop") === 0) {
      // stop now
    } 
    else if (strpos(strtolower ($text), "/titel") === 0) {
        $rest= trim(mysqli_real_escape_string($conn,substr($text,6)));
        if ($rest=="") apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Bitte gib einen Titelnamen ein!'));
        else{
        $sql="SELECT * FROM Titel WHERE titelName LIKE '%$rest%'";
        $result=mysqli_query($conn,$sql);
        if (mysqli_num_rows($result)>10) apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Zu viele Ergebnisse, bitte grenze Titel mehr ein!'));
        elseif (mysqli_num_rows($result)==0) apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Keine Ergebnisse gefunden!'));
        else 
        while($erg=mysqli_fetch_object($result)) 
                apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "Titel: $erg->titelName\nInterpret: $erg->titelInterpret\nPlatz: $erg->titelPlatzierung"));
        }
    }
    else if (strpos(strtolower($text), "/interpret") === 0) {
        $rest= trim(mysqli_real_escape_string($conn,substr($text,10)));
        apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $rest));
        if ($rest=="") apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Bitte gib einen Interpreten ein!'));
        else {
        $sql="SELECT * FROM Titel WHERE titelInterpret LIKE '%$rest%'";
        $result=mysqli_query($conn,$sql);
        if (mysqli_num_rows($result)>10) apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Zu viele Ergebnisse, bitte grenze Titel mehr ein!'));
        elseif (mysqli_num_rows($result)==0) apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Keine Ergebnisse gefunden!'));
        else 
        while($erg=mysqli_fetch_object($result)) 
                apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "Titel: $erg->titelName\nInterpret: $erg->titelInterpret\nPlatz: $erg->titelPlatzierung"));
        }
    }
    
    else {
      apiRequestWebhook("sendMessage", array('chat_id' => $chat_id, "reply_to_message_id" => $message_id, "text" => 'Cool'));
    }
  } else {
    apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'I understand only text messages'));
  }
}

function telegchan($message){
    apiRequest("sendMessage", array('chat_id' => "@PFMGP", "text" => $message, "parse_mode"=>"HTML"));
}

?>

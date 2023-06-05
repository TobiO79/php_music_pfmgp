<?php

include("incl/functions.php");
include("incl/iha.php");

if (isset($_POST['login'])) {
    $userid = check_user($_POST['username'], $_POST['userpass']);
    if ($userid != false) {
        login($userid);
    } else {
	logbuch(1,$_POST["username"].": Fehlgeschlagener Anmeldeversuch.");
        echo 'Ihre Anmeldedaten waren nicht korrekt!';
    }
}
if (isset($_GET["logout"])) {
    logout();
}

if (isset($_POST["lostpassword"])){
	logbuch(1,$_POST["email"].": neues Passwort angefordert.");
    new_password($_POST["email"]);
}

print <<<html

<!DOCTYPE html>
<html lang="de" class="no-js" dir="ltr">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="x-ua-compatible" content="ie=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="author" content="Tobias Otto">
		<meta name="publisher" content="Tobias Otto">
		<meta name="copyright" content="Tobias Otto">
		<meta name="description" content="Wir sind ein Grüppchen Verrückter Leute, die sich hier gegenseitig Ihren persönlichen Musikgeschmack vorstellen und daraus einen kleinen Wettbewerb im Stil des Eurovision Song Contestes machen.">
		<meta name="keywords" content="PFMGP, Musik, Voting, Wettbewerb, Song, Contest, ESC, Musikwettbewerb">
		<meta name="page-topic" content="Kultur">
		<meta name="page-type" content="Private Homepage">
		<meta name="audience" content="Erwachsene, Frauen, Jugendliche, Männer">
		<meta name="robots" content="index, nofollow">
		<meta name="DC.Creator" content="Tobias Otto">
		<meta name="DC.Publisher" content="Tobias Otto">
		<meta name="DC.Rights" content="Tobias Otto">
		<meta name="DC.Description" content="Wir sind ein Grüppchen Verrückter Leute, die sich hier gegenseitig Ihren persönlichen Musikgeschmack vorstellen und daraus einen kleinen Wettbewerb im Stil des Eurovision Song Contestes machen.">
		<meta name="DC.Language" content="de">
		<title>PFMGP.de - Private Friends Music Grand Prix</title>
     		<link rel="stylesheet" href="css/foundation.min.css">
     		<link rel="stylesheet" href="css/app.css">
	</head>
<body>
<div class="title-bar" data-responsive-toggle="main-menu" data-hide-for="medium">
<button class="menu-icon" type="button" data-toggle></button>
<div class="title-bar-title">Menu</div>
</div>
<div class="top-bar" id="main-menu">
	<div class="top-bar-left">
		<ul class="menu vertical medium-horizontal expanded medium-text-center" data-responsive-menu="drilldown medium-dropdown">
			<li><a href="index.php">Homepage</a></li>
html;

if (logged_in()) {
if($_SESSION["Leiter"]==0){
    $sql="SELECT * FROM Wettbewerb WHERE closeVoting=1 ORDER BY wettbewerbsJahr DESC, wettbewerbsID DESC";
}
else $sql="SELECT * FROM Wettbewerb ORDER BY wettbewerbsJahr DESC, wettbewerbsID DESC";
$result=mysqli_query($conn,$sql);
while($erg=mysqli_fetch_object($result)){
    $mv.="<li><a href='auswertung.php?wett=$erg->wettbewerbsID'>$erg->wettbewerbsName</a></li>";
}

    if($_SESSION["Leiter"]==1) $admin="<li><a href='admin.php'>Admin</a></li><li><a href='mitspieler.php'>Mitspieler</a><li><a href='logview.php'>Logbuch</a></li>"; else $admin="";
    print <<<html
					<li><a href="spieler.php">Teilnehmer</a></li>
					$admin
   <li><a href="news.php">News-Archiv</a></li>                            
				<li><a href="ranking.php">Das All-Time-Ranking</a></li>
				<li class="has-submenu"><a href="auswertung.php">Statistik</a>
<ul class="submenu menu vertical" data-submenu>
<li><a href='auswertung.php'>Komplette Statistik</a></li>
	    $mv
</ul></li>
				<li><a href="archiv.php">Titel-Archiv</a></li>
				<li><a href="kassenbuch.php">Kassenbuch</a></li>    
				<li><a href="index.php?logout">Logout</a></li>
html;
}
if (!logged_in()) print '<li><a href="archiv.php">bisherige Titel</a></li>';
print <<<html

<li><a href="regeln.php">Die Regeln</a></li>				
			</ul>
		</div>
</div>

		<div class="callout large primary">
			<div class="row column text-center">
			<h1>PFMGP</h1>
			<h2 class="subheader">Der Private Friends Music Grand Prix</h2>
		</div>
</div>
html;
?>

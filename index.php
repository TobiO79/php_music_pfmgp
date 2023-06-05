<?php
include("conf/config.php");
include("incl/header.php");

$text=mysqli_fetch_object(mysqli_query($conn,"SELECT text FROM Seiten WHERE seitenID=1"));

print <<<html

<div class="grid-x grid-margin-x">
<div class="cell small-9">
html;
print $text->text;
if(!logged_in()){
print <<<html
</div>
<div class="cell medium-3 sticky">
<form class="log-in-form" method="POST" action="index.php">
  <h4 class="text-center">Melde dich mit deiner Kennung an</h4>
  <label>Benutzername
    <input type="text" placeholder="Nickname" name="username">
  </label>
  <label>Passwort
    <input type="password" placeholder="Password" name="userpass">
  </label>
  <p><button type="submit" class="button expanded" value="Einloggen" name="login">Einloggen</button></p>
  </form>
  <p></p>

  <form class="log-in-form" action="index.php" method="POST">
  <h4 class="text-center">Passwort vergessen?</h4>
<label>Email-Adresse </label><input name="email" type="email" placeholder="somebody@pfmgp.de"><br>
<input name="lostpassword" type="submit" value="anfordern" class="button expanded">

</form>
</div>
</div>
html;
}
else {
	print <<<html
</div></div>
<div class="grid-x cell small-8">
html;
	$sql="SELECT * FROM News 
		LEFT JOIN Teilnahme USING (wettbewerbsID)
		LEFT JOIN Wettbewerb USING (wettbewerbsID)
		WHERE userID=".$_SESSION["UserID"]." OR News.wettbewerbsID is NULL
		ORDER BY Datum DESC
		LIMIT 10";
	$result=mysqli_query($conn,$sql);
while($erg=mysqli_fetch_object($result)){
	print <<<html
<div class="blog-post">
<h3>$erg->betreff <small>$erg->datum</small></h3>
$erg->text
<div class="callout">
<ul class="menu simple">
<li><a href="auswertung.php?wett=$erg->wettbewerbsID">Wettbewerb: $erg->wettbewerbsName</a></li>
</ul>
</div>
</div>
html;
	}
}

include("incl/footer.php");

?>

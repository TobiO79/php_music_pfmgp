<?php
/*
print <<<html

	</div>
	<div id="sidebar" class="grid_2">
			<p>
			<img src="img/Pokal.jpg" alt="PFMGP-Pokal">
			</p>
html;



if(!logged_in())
{
	print <<<html
<p>Du bist nicht angemeldet: bitte melde dich an!</p>
<form method="post" action="index.php">
<fieldset class="sideform"><legend>Benutzeranmeldung</legend>
<label for="username">Login </label><input class="sidefield" id="username" name="username" type="text"><br>
<label for="userpass">PIN </label><input class="sidefield" name="userpass" type="password" id="userpass"><br>
<input class="sidefield" name="login" type="submit" id="login" value="Einloggen">
</fieldset></form>
    
<form method="post" action="index.php">
<fieldset class="sideform"><legend>Passwort vergessen?</legend>
<label for="email">Email </label><input class="sidefield" id="email" name="email" type="text"><br>
<input class="sidefield" name="lostpassword" type="submit" id="lostpassword" value="anfordern">
</fieldset></form>

html;
}
else{
	print "<p>angemeldet als: ".$_SESSION["User"]."<br><a href='index.php?logout'>abmelden</a></p>\n";
        
}
 */
print <<<html
	

<div class="grid-x grid-margin-x grid-padding-x callout secondary">
<div class="medium-6 cell">
<ul class="menu">
<li><a href="impressum.php">Impressum</a></li>
<li><a href="datenschutz.php">Datenschutz</a></li>
</ul>
</div>
<div class="medium-6 cell">
<ul class="menu align-right">
<li class="menu-text">Copyright © 2018 PFMGP.de - TobiO@PFMGP.de</li>
</ul>
</div>
</div>
<script src="js/vendor/jquery.js"></script>
<script src="js/vendor/what-input.js"></script>
<script src="js/vendor/foundation.js"></script>
<script src="js/app.js"></script>


</body>
</html>
html;
?>

<?php

include("conf/config.php");
include("incl/header.php");

if($_SESSION["Leiter"]==1){
    $logtabelle="";
    $sql="SELECT * FROM Spieler Where aktiv=1 ORDER BY vorname, nachname";
    $result=mysqli_query($conn,$sql);
    while($erg=mysqli_fetch_object($result)){
        $logtabelle.="<tr><td>$erg->userName</td><td><a href='mailto:$erg->email'>$erg->vorname $erg->nachname</a></td><td>".str_replace("\n", "<br>", $erg->anschrift)."</td><td>".str_replace("\n", "<br>",$erg->benutzerText)."</td></tr>";
    }
    print <<<html
    <h2>Mitspieler</h2>
    <table>
    <tr><th>Benutzer</th><th>Name</th><th>Anschrift</th><th>Hinweise</th></tr>
    $logtabelle
    </table>
html;
    }
include("incl/footer.php");

?>

<?php

include("conf/config.php");
include("incl/header.php");

if($_SESSION["Leiter"]==1){
    $logtabelle="";
    $sql="SELECT * FROM Log LEFT JOIN Spieler USING (userID) ORDER BY logDatum DESC LIMIT 200";
    $result=mysqli_query($conn,$sql);
    while($erg=mysqli_fetch_object($result)){
        $logtabelle.="<tr><td>$erg->logDatum</td><td>$erg->userName</td><td>$erg->logText</td></tr>";
    }
    print <<<html
    <h2>Logbuch</h2>
    <table>
    <tr><th>Zeit</th><th>Benutzer</th><th>Text</th></tr>
    $logtabelle
    </table>
html;
    }
include("incl/footer.php");

?>

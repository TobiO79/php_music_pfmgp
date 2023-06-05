<?php

include("conf/config.php");
include("incl/header.php");

if(logged_in()){
    $sieger="";
    $ssieger="";
    $ranking="";
    $sranking="";
    $rankingnr=1;
    $srankingnr=1;
    
    $sql="SELECT * FROM Titel"
            . " LEFT JOIN Wettbewerb USING (wettbewerbsID)"
            . " LEFT JOIN Spieler USING (userID)"
            . " WHERE isSpecialWettbewerb=0 AND titelPlatzierung=1"
            . " ORDER BY wettbewerbsJahr";
    $result=mysqli_query($conn,$sql);
    while($erg=mysqli_fetch_object($result)){
        if($erg->aktiv==0){$erg->vorname=substr($erg->vorname,0,1)."."; $erg->nachname=substr($erg->nachname,0,1).".";}
        if($erg->Youtube<>"") $titel="<a href='$erg->Youtube' target='Youtube'>$erg->titelName</a>"; else $titel=$erg->titelName;
        $sieger.="<tr><td>$erg->wettbewerbsJahr</td><td>$erg->vorname $erg->nachname</td><td>$titel</td><td>$erg->titelInterpret</td></tr>";
    }
    
    $sql="SELECT * FROM Titel"
            . " LEFT JOIN Wettbewerb USING (wettbewerbsID)"
            . " LEFT JOIN Spieler USING (userID)"
            . " WHERE isSpecialWettbewerb=1 AND titelPlatzierung=1"
            . " ORDER BY wettbewerbsJahr";
    $result=mysqli_query($conn,$sql);
    while($erg=mysqli_fetch_object($result)){
        if($erg->aktiv==0){$erg->vorname=substr($erg->vorname,0,1)."."; $erg->nachname=substr($erg->nachname,0,1).".";}
        if($erg->Youtube<>"") $titel="<a href='$erg->Youtube' target='Youtube'>$erg->titelName</a>"; else $titel=$erg->titelName;
        $ssieger.="<tr><td>$erg->wettbewerbsJahr</td><td>$erg->wettbewerbsName</td><td>$erg->vorname $erg->nachname</td><td>$titel</td><td>$erg->titelInterpret</td></tr>";
    }
    
    $sql="SELECT * FROM Spieler"
            . " WHERE punkteAlltime>0"
            . " ORDER BY punkteAlltime DESC";
    $result=mysqli_query($conn,$sql);
    while($erg=mysqli_fetch_object($result)){
        if($erg->aktiv==0){$erg->vorname=substr($erg->vorname,0,1)."."; $erg->nachname=substr($erg->nachname,0,1).".";}
        if($erg->platzAlltimeVorjahr>$rankingnr OR $erg->platzAlltimeVorjahr==NULL) $updown="Aufsteiger";
        elseif($erg->platzAlltimeVorjahr<$rankingnr) $updown="Absteiger";
        else $updown="";
        $ranking.="<tr class='$updown'><td>$rankingnr</td><td>$erg->vorname $erg->nachname</td><td>$erg->punkteAlltime</td><td>".($erg->platzAlltimeVorjahr==NULL ? "(neu)" : $erg->platzAlltimeVorjahr-$rankingnr)."</td></tr>";
        $rankingnr++;
    }
    
    $sql="SELECT * FROM Spieler"
            . " WHERE punkteSpecialAlltime>0"
            . " ORDER BY punkteSpecialAlltime DESC";
    $result=mysqli_query($conn,$sql);
    while($erg=mysqli_fetch_object($result)){
        if($erg->aktiv==0){$erg->vorname=substr($erg->vorname,0,1)."."; $erg->nachname=substr($erg->nachname,0,1).".";}
        if($erg->platzSpecialVorjahr>$srankingnr OR $erg->platzSpecialVorjahr==NULL) $updown="Aufsteiger";
        elseif($erg->platzSpecialVorjahr<$srankingnr) $updown="Absteiger";
        else $updown="";
        $sranking.="<tr class='$updown'><td>$srankingnr</td><td>$erg->vorname $erg->nachname</td><td>$erg->punkteSpecialAlltime</td><td>".($erg->platzSpecialVorjahr==NULL ? "(neu)" : $erg->platzSpecialVorjahr-$srankingnr)."</td></tr>";
        $srankingnr++;
    }
    
print <<<html

<h2>Das All-Time-Ranking</h2>
    <h3>Die Sieger</h3>
    <table width="100%">
    <tr><th>Jahr</th><th>Spieler</th><th>Titel</th><th>Interpret</th></tr>
    $sieger
    </table>
    
    <h3>Das Ranking</h3>
    <table width="100%">
    <tr><th>Platz</th><th>Spieler</th><th>Punkte</th><th>Veränderung</th></tr>
    $ranking
    </table>
        
<h2>Das Special-Ranking</h2>
<h3>Die Sieger</h3>
    <table width="100%">
    <tr><th>Jahr</th><th>Wettbewerb</th><th>Spieler</th><th>Titel</th><th>Interpret</th></tr>
    $ssieger
    </table>
    
    <h3>Das Ranking</h3>
    <table width="100%">
    <tr><th>Platz</th><th>Spieler</th><th>Punkte</th><th>Veränderung</th></tr>
    $sranking
    </table>

html;
}
include("incl/footer.php");

?>

<?php

include("conf/config.php");
include("incl/header.php");

$pfmgp=aktueller_pfmgp();
$titel="";
if(!logged_in() OR isset($_GET["sort"]) AND $_GET["sort"]=="Titel"){
    $titel.= "<h3>alphabetisch nach Titel</h3>"
                ." <table width='100%'>"
                . "<tr><th>Titel</th><th>Interpret</th><th>Jahr</th><th>Sprache</th><th>Platzierung</th></tr>";
        if($pfmgp[2]=="Anmeldung" and $_SESSION["Leiter"]==0){
        $sql="SELECT * FROM Titel WHERE wettbewerbsID<>".$pfmgp[1]." ORDER BY titelName";
        }
        else $sql="SELECT * FROM Titel ORDER BY titelName";
        $tresult=mysqli_query($conn,$sql);
        while($terg=mysqli_fetch_object($tresult)){
            if($terg->Youtube<>"") $tn="<a href='$terg->Youtube' target='Youtube'>$terg->titelName</a>"; else $tn=$terg->titelName;
            $titel.= "<tr><td>$tn</td><td>$terg->titelInterpret</td><td>$terg->titelJahr</td><td>$terg->titelSprache</td><td>$terg->titelPlatzierung</td></tr>\n";
        }
        $titel.= "</table>";
}
elseif(isset($_GET["sort"]) AND $_GET["sort"]=="Interpret"){
    $sql="SELECT titelInterpret FROM Titel GROUP BY titelInterpret ORDER BY titelInterpret";
    $result=mysqli_query($conn,$sql);
    while($erg=mysqli_fetch_object($result)){
        $titel.= "<h3>$erg->titelInterpret</h3>"
                ." <table width='100%'>"
		."<colgroup><col width=60%><col width=10%><col width=20%><col width=10%></colgroup>"
                . "<tr><th>Titel</th><th>Jahr</th><th>Sprache</th><th>Platzierung</th></tr>";
        if($pfmgp[2]=="Anmeldung" and $_SESSION["Leiter"]==0){
        $sql="SELECT * FROM Titel WHERE wettbewerbsID<>".$pfmgp[1]." AND titelInterpret='".mysqli_real_escape_string($conn,$erg->titelInterpret)."' ORDER BY titelName";
        }
        else $sql="SELECT * FROM Titel WHERE titelInterpret='".mysqli_real_escape_string($conn,$erg->titelInterpret)."' ORDER BY titelName";
        $tresult=mysqli_query($conn,$sql);
        while($terg=mysqli_fetch_object($tresult)){
            if($terg->Youtube<>"") $tn="<a href='$terg->Youtube' target='Youtube'>$terg->titelName</a>"; else $tn=$terg->titelName;
            $titel.= "<tr><td>$tn</td><td>$terg->titelJahr</td><td>$terg->titelSprache</td><td>$terg->titelPlatzierung</td></tr>\n";
        }
        $titel.= "</table>";
    }
}
elseif(isset($_GET["sort"]) AND $_GET["sort"]=="Jahr"){
    $sql="SELECT titelJahr FROM Titel WHERE titelJahr is not NULL GROUP BY titelJahr ORDER BY titelJahr";
    $result=mysqli_query($conn,$sql);
    while($erg=mysqli_fetch_object($result)){
        $titel.= "<h3>$erg->titelJahr</h3>"
                ." <table width='100%'>"
                ."<colgroup><col width=35%><col width=35%><col width=20%><col width=10%></colgroup>"
                . "<tr><th>Titel</th><th>Interpret</th><th>Sprache</th><th>Platzierung</th></tr>";
        if($pfmgp[2]=="Anmeldung" and $_SESSION["Leiter"]==0){
        $sql="SELECT * FROM Titel WHERE wettbewerbsID<>".$pfmgp[1]." AND titelJahr='$erg->titelJahr' ORDER BY titelName";
        }
        else $sql="SELECT * FROM Titel WHERE titelJahr='$erg->titelJahr' ORDER BY titelName";
        $tresult=mysqli_query($conn,$sql);
        while($terg=mysqli_fetch_object($tresult)){
            if($terg->Youtube<>"") $tn="<a href='$terg->Youtube' target='Youtube'>$terg->titelName</a>"; else $tn=$terg->titelName;
            $titel.= "<tr><td>$tn</td><td>$terg->titelInterpret</td><td>$terg->titelSprache</td><td>$terg->titelPlatzierung</td></tr>\n";
        }
        $titel.= "</table>";
    }
}
elseif(isset($_GET["sort"]) AND $_GET["sort"]=="Sprache"){
    $sql="SELECT titelSprache FROM Titel where titelSprache<>'' GROUP BY titelSprache ORDER BY titelSprache";
    $result=mysqli_query($conn,$sql);
    while($erg=mysqli_fetch_object($result)){
        $titel.= "<h3>$erg->titelSprache</h3>"
                ." <table width='100%'>"
                ."<colgroup><col width=40%><col width=40%><col width=10%><col width=10%></colgroup>"
                . "<tr><th>Titel</th><th>Interpret</th><th>Jahr</th><th>Platzierung</th></tr>";
        if($pfmgp[2]=="Anmeldung" and $_SESSION["Leiter"]==0){
        $sql="SELECT * FROM Titel WHERE wettbewerbsID<>".$pfmgp[1]." AND titelSprache='$erg->titelSprache' ORDER BY titelName";
        }
        else $sql="SELECT * FROM Titel WHERE titelSprache='$erg->titelSprache' ORDER BY titelName";
        $tresult=mysqli_query($conn,$sql);
        while($terg=mysqli_fetch_object($tresult)){
            if($terg->Youtube<>"") $tn="<a href='$terg->Youtube' target='Youtube'>$terg->titelName</a>"; else $tn=$terg->titelName;
            $titel.= "<tr><td>$tn</td><td>$terg->titelInterpret</td><td>$terg->titelJahr</td><td>$terg->titelPlatzierung</td></tr>\n";
        }
        $titel.= "</table>";
    }
}


else{
    if($pfmgp[2]=="Anmeldung" and $_SESSION["Leiter"]==0){
        $sql="SELECT * FROM Wettbewerb WHERE wettbewerbsID<>".$pfmgp[1]." ORDER BY wettbewerbsJahr DESC";
    }
    else $sql="SELECT * FROM Wettbewerb ORDER BY wettbewerbsJahr DESC";
    $result=mysqli_query($conn,$sql);
    while($erg=mysqli_fetch_object($result)){
        $titel.= "<h3>$erg->wettbewerbsJahr - $erg->wettbewerbsName</h3>"
                ." <table width='100%'>"
                . "<tr><th>Platz</th><th>Titel</th><th>Interpret</th><th>Punkte</th></tr>";
        $sql="SELECT * FROM Titel WHERE wettbewerbsID=$erg->wettbewerbsID ORDER BY titelPlatzierung, titelName";
        $tresult=mysqli_query($conn,$sql);
        while($terg=mysqli_fetch_object($tresult)){
            if($terg->Youtube<>"") $tn="<a href='$terg->Youtube' target='Youtube'>$terg->titelName</a>"; else $tn=$terg->titelName;
            $titel.= "<tr><td>$terg->titelPlatzierung</td><td>$tn</td><td>$terg->titelInterpret</td><td>$terg->titelPunkte</td></tr>\n";
        }
        $titel.= "</table>";
    }
}

print "<h2>Das Titel-Archiv</h2>";
if(logged_in()) print '<p>bitte Sortierung wählen: <a href="archiv.php?sort=Titel">nach Titel</a>, <a href="archiv.php?sort=Interpret">nach Interpret</a>, <a href="archiv.php?sort=Jahr">nach Veröffentlichungsjahr</a>, <a href="archiv.php?sort=Sprache">nach Sprache</a>';
print $titel;

include('incl/footer.php');

?>

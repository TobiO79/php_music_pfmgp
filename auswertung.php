<?php

include("conf/config.php");
include("incl/header.php");

if(logged_in()){

    $pfmgp=aktueller_pfmgp();
    $teilnahme="<p>Kein aktueller PFMGP</p>";
if ($pfmgp[1]>0){
    $teilnahme="<h3>$pfmgp[0]</h3>"
            . "<table border=1 width='100%'>\n"
            . "<tr><th>Spieler</th><th>Titel</th><th>Vorrunde</th><th>Voting</th></tr>\n";
    $sql="SELECT * FROM teilnahmeUebersicht WHERE wettbewerbsID=$pfmgp[1] ORDER BY NumberOfTitles DESC, NumberOfVotings DESC, Spieler";
    $result=mysqli_query($conn,$sql);
    while($erg=mysqli_fetch_object($result)){
        $teilnahme.="<tr><td>$erg->Spieler</td><td>$erg->NumberOfTitles von $erg->NumberOfTitlesForPFMGP</td><td>$erg->NumberOfVorrunde</td><td>$erg->NumberOfVotings</td></tr>\n";
    }
    $teilnahme.="</table>";
} 

    $live="<p>Kein aktueller PFMGP</p>";
if ($pfmgp[1]>0 and $pfmgp[2]=="Voting"){
    $live="<h3>$pfmgp[0]</h3>"
            . "<table border=1 width='100%'>\n"
            . "<tr><th>Titel</th><th>Votings</th></tr>\n";
    $sql="SELECT Titel, Interpret, Youtube, Punkte01+Punkte02+Punkte03+Punkte04+Punkte05+Punkte06+Punkte07+Punkte08+Punkte10+Punkte12 AS Stimmen FROM titelErgebnisse WHERE wettbewerbsID=$pfmgp[1] ORDER BY Stimmen DESC, Titel";
    $result=mysqli_query($conn,$sql);
    while($erg=mysqli_fetch_object($result)){
        if($erg->Youtube<>"") $titel="<a href='$erg->Youtube' target='Youtube'>$erg->Titel</a>"; else $titel=$erg->Titel;
        $live.="<tr><td>$titel - $erg->Interpret</td><td>$erg->Stimmen</td></tr>\n";
    }
    $live.="</table>";
} 
elseif ($pfmgp[1]>0 and $pfmgp[2]=="Vorrunde"){
    $live="<h3>$pfmgp[0]</h3>"
            . "<table border=1 width='100%'>\n"
            . "<tr><th>Platz</th><th>Titel</th><th>5</th><th>4</th><th>3</th><th>2</th><th>1</th><th>Punkte</th></tr>\n";
    $sql="SELECT Titel, Interpret, Spieler, Summe, Punkte1, Punkte2, Punkte3, Punkte4, Punkte5 FROM VorrundeErgebnisse WHERE wettbewerbsID=$pfmgp[1] ORDER BY Summe DESC, Punkte5 DESC, Punkte4 DESC, Punkte3 DESC, Punkte2 DESC, Punkte1 DESC";
    $result=mysqli_query($conn,$sql);
    $i=0;
    $platz=1;
    $p1=0; $p2=0; $p3=0; $p4=0; $p5=0; $su=0; $fuenfer=0; $vierer=0; $dreier=0; $zweier=0; $einser=0; $summen=0;
    while($erg=mysqli_fetch_object($result)){
        $i++;
        if(!($erg->Summe==$su and $erg->Punkte1==$p1 and $erg->Punkte2==$p2 and $erg->Punkte3==$p3 and $erg->Punkte4==$p4 and $erg->Punkte5==$p5)) $platz=$i;
        if($_SESSION["Leiter"]==1) $live.="<tr><td>$platz</td><td>$erg->Titel - $erg->Interpret ($erg->Spieler)</td><td>$erg->Punkte5</td><td>$erg->Punkte4</td><td>$erg->Punkte3</td><td>$erg->Punkte2</td><td>$erg->Punkte1</td><td>$erg->Summe</td></tr>\n";
        else $live.="<tr><td>$platz</td><td>*****</td><td>$erg->Punkte5</td><td>$erg->Punkte4</td><td>$erg->Punkte3</td><td>$erg->Punkte2</td><td>$erg->Punkte1</td><td>$erg->Summe</td></tr>\n";
        $p1=$erg->Punkte1;
        $p2=$erg->Punkte2;
        $p3=$erg->Punkte3;
        $p4=$erg->Punkte4;
        $p5=$erg->Punkte5;
        $su=$erg->Summe;
	$einser=$einser+$p1;
	$zweier=$zweier+$p2;
	$dreier=$dreier+$p3;
	$vierer=$vierer+$p4;
	$fuenfer=$fuenfer+$p5;
	$summen=$summen+$su;
    }
    $live.="<tr><td colspan=2>Summe</td><td>$fuenfer</td><td>$vierer</td><td>$dreier</td><td>$zweier</td><td>$einser</td><td>$summen</td></tr>";
    $live.="</table>";
} 

if (isset($_GET["wett"])) $ww="wettbewerbsID=".$_GET["wett"]; else $ww="true";
$votings="";
if($_SESSION["Leiter"]==0){
    $sql="SELECT * FROM Wettbewerb WHERE $ww AND closeVoting=1 ORDER BY wettbewerbsJahr DESC, wettbewerbsID DESC";
}
else $sql="SELECT * FROM Wettbewerb WHERE $ww ORDER BY wettbewerbsJahr DESC, wettbewerbsID DESC";
$result=mysqli_query($conn,$sql);
while($erg=mysqli_fetch_object($result)){
    $votings.="<h3><a href='charts.php?id=$erg->wettbewerbsID'>$erg->wettbewerbsName</a></h3>\n"
            . "<table class='Punktetabelle'>\n"
            . "<colgroup class='Platz'><col></colgroup><colgroup class='Titel'><col><col></colgroup><colgroup class='Punkte'><col><col><col><col><col><col><col><col><col><col><col></colgroup>"
            . "<tr><th>PL</th><th>Titel</th><th>Spieler</th><th>12</th><th>10</th><th>8</th><th>7</th><th>6</th><th>5</th><th>4</th><th>3</th><th>2</th><th>1</th><th>SM</th></tr>\n";
    if ($erg->mitVorrunde==0) $sql="SELECT * FROM titelErgebnisse WHERE wettbewerbsid=$erg->wettbewerbsID ORDER BY Summe DESC, Punkte12 DESC, Punkte10 DESC, Punkte08 DESC, Punkte07 DESC, Punkte06 DESC, Punkte05 DESC, Punkte04 DESC, Punkte03 DESC, Punkte02 DESC, Punkte01 DESC, Titel, Spieler";
    else $sql="SELECT * FROM titelErgebnisse WHERE Runde='F' AND wettbewerbsid=$erg->wettbewerbsID ORDER BY Summe DESC, Punkte12 DESC, Punkte10 DESC, Punkte08 DESC, Punkte07 DESC, Punkte06 DESC, Punkte05 DESC, Punkte04 DESC, Punkte03 DESC, Punkte02 DESC, Punkte01 DESC";
    $tresult=mysqli_query($conn,$sql);
    $i=0;
    $platz=0;
    $p01=0; $p02=0; $p03=0; $p04=0; $p05=0; $p06=0; $p07=0; $p08=0; $p10=0; $p12=0;
    $sp=0;
    while($terg=mysqli_fetch_object($tresult)){
        $i++;
        if (!($sp==$terg->Summe AND $p12==$terg->Punkte12 AND $p10==$terg->Punkte10 AND $p08==$terg->Punkte08 AND $p07==$terg->Punkte07 AND $p06==$terg->Punkte06 AND $p05==$terg->Punkte05 AND $p04==$terg->Punkte04 AND $p03==$terg->Punkte03 AND $p02==$terg->Punkte02 AND $p01==$terg->Punkte01)){
            $platz=$i;
        }
        if($terg->Youtube<>"") $titel="<a href='$terg->Youtube' target='Youtube'>$terg->Titel</a>"; else $titel=$terg->Titel;
        $votings.="<tr><td>$platz</td><td>$titel</td><td>$terg->Spieler</td><td>$terg->Punkte12</td><td>$terg->Punkte10</td><td>$terg->Punkte08</td><td>$terg->Punkte07</td><td>$terg->Punkte06</td><td>$terg->Punkte05</td><td>$terg->Punkte04</td><td>$terg->Punkte03</td><td>$terg->Punkte02</td><td>$terg->Punkte01</td><td>$terg->Summe</td></tr>\n";
        $p12=$terg->Punkte12;
        $p10=$terg->Punkte10;
        $p08=$terg->Punkte08;
        $p07=$terg->Punkte07;
        $p06=$terg->Punkte06;
        $p05=$terg->Punkte05;
        $p04=$terg->Punkte04;
        $p03=$terg->Punkte03;
        $p02=$terg->Punkte02;
        $p01=$terg->Punkte01;
        $sp=$terg->Summe;
        
    }
    if($erg->mitVorrunde==1){
        $sql="SELECT Spieler, Titel, Interpret, Summe, Punkte1, Punkte2, Punkte3, Punkte4, Punkte5, Youtube FROM VorrundeErgebnisse WHERE wettbewerbsID=$erg->wettbewerbsID ORDER BY Summe DESC, Punkte5 DESC, Punkte4 DESC, Punkte3 DESC, Punkte2 DESC, Punkte1 DESC LIMIT 20,1000";
        $tresult=mysqli_query($conn,$sql);
        $i=20;
    $platz=21;
    $p1=0; $p2=0; $p3=0; $p4=0; $p5=0; $su=0;
    while($terg=mysqli_fetch_object($tresult)){
        $i++;
        if(!($terg->Summe==$su and $terg->Punkte1==$p1 and $terg->Punkte2==$p2 and $terg->Punkte3==$p3 and $terg->Punkte4==$p4 and $terg->Punkte5==$p5)) $platz=$i;
        if($terg->Youtube<>"") $titel="<a href='$terg->Youtube' target='Youtube'>$terg->Titel</a>"; else $titel=$terg->Titel;
        $votings.="<tr><td>$platz</td><td>$titel</td><td>$terg->Spieler</td><td colspan=5>-</td><td>$terg->Punkte5</td><td>$terg->Punkte4</td><td>$terg->Punkte3</td><td>$terg->Punkte2</td><td>$terg->Punkte1</td><td>$terg->Summe</td></tr>\n";
        $p1=$terg->Punkte1;
        $p2=$terg->Punkte2;
        $p3=$terg->Punkte3;
        $p4=$terg->Punkte4;
        $p5=$terg->Punkte5;
        $su=$terg->Summe;
    }
    
    }
    $votings.="</table>\n"
            . "<table  class='Votingtabelle'>\n"
            . "<colgroup class='Spieler'><col></colgroup><colgroup class='Voting'><col><col><col><col><col><col><col><col><col><col></colgroup>\n"
    . "<tr><th>Spieler</th><th>Platz 1</th><th>Platz 2</th><th>Platz 3</th><th>Platz 4</th><th>Platz 5</th><th>Platz 6</th><th>Platz 7</th><th>Platz 8</th><th>Platz 9</th><th>Platz 10</th></tr>\n";
    $sql="SELECT * FROM VotingAuswertung WHERE wettbewerbsID=$erg->wettbewerbsID ORDER BY nachname, vorname";
    $tresult=mysqli_query($conn,$sql);
    while($terg=mysqli_fetch_object($tresult)){
        $votings.="<tr><td>$terg->vorname $terg->nachname</td><td>$terg->Platz1</td><td>$terg->Platz2</td><td>$terg->Platz3</td><td>$terg->Platz4</td><td>$terg->Platz5</td><td>$terg->Platz6</td><td>$terg->Platz7</td><td>$terg->Platz8</td><td>$terg->Platz9</td><td>$terg->Platz10</td></tr>\n";
    }
    $votings.="</table>\n";
}
$sprachen="";
$interpreten="";
$zeiten="";
$sql="SELECT titelSprache, COUNT(*) Anzahl FROM Titel WHERE titelSprache<>'' GROUP BY titelSprache ORDER BY Anzahl DESC";
$result=mysqli_query($conn,$sql);
while($erg=mysqli_fetch_object($result)) $sprarr["$erg->titelSprache"]=$erg->Anzahl;
$summe=array_sum($sprarr);
$sprachen="Folgende Sprachen sind vertreten: <ul>";
$csprachen="";
while(list($sprache,$anzahl)=each($sprarr)){
    $sprachen.="<li>$anzahl/$summe = ".round($anzahl/$summe*100,1)." % - $sprache</li>";
    $csprachen.="['$sprache',$anzahl],";
}
$sprachen.="</ul>";

$interpreten="Folgende Interpreten sind mindestens dreimal vertreten: <ul>";
$cinterpret="";
$sql="SELECT titelInterpret, COUNT(*) Anzahl FROM Titel GROUP BY titelInterpret HAVING Anzahl>=3 ORDER BY Anzahl DESC,titelInterpret";
$result=mysqli_query($conn,$sql);
while($erg=mysqli_fetch_object($result)) {
    $interpreten.="<li>$erg->titelInterpret ($erg->Anzahl)</li>";
    $cinterpret.="['$erg->titelInterpret',$erg->Anzahl],";
}
$interpreten.="</ul>";

$canfatitel="";
$canfainterpret="";

$sql="SELECT Left(titelInterpret,1) AS Anfa,COUNT(*) Anzahl FROM Titel GROUP BY Anfa ORDER BY Anfa";
$result=mysqli_query($conn,$sql);
while($erg=mysqli_fetch_object($result)) {
    $canfainterpret.="['$erg->Anfa',$erg->Anzahl],";
}
$sql="SELECT Left(titelName,1) AS Anfa, COUNT(*) Anzahl FROM Titel GROUP BY Anfa ORDER BY Anfa";
$result=mysqli_query($conn,$sql);
while($erg=mysqli_fetch_object($result)) {
    $canfatitel.="['$erg->Anfa',$erg->Anzahl],";
}

$zeiten="Die Titel verteilen sich auf folgende Jahrzehnte: <ul>";
$czeiten="";
$sql="SELECT Floor(titelJahr/10)*10 Jahrzehnt, COUNT(*) Anzahl FROM Titel GROUP BY Jahrzehnt  HAVING Jahrzehnt is not NULL AND Jahrzehnt<>0 ORDER BY Jahrzehnt";
$result=mysqli_query($conn,$sql);
while($erg=mysqli_fetch_object($result)) {
    $zeiten.="<li>$erg->Jahrzehnt ($erg->Anzahl)</li>";
    $czeiten.="['$erg->Jahrzehnt',$erg->Anzahl],";
}
$zeiten.="</ul>";

$cabweichung="['Abweichung','PFMGP 2016','PFMGP 2017','PFMGP 2018','PFMGP 2019'],";
$sql="SELECT * FROM AbwFuerChart WHERE PFMGP2016+PFMGP2017+PFMGP2018+PFMGP2019>0";
$result=mysqli_query($conn,$sql);
while($erg=mysqli_fetch_object($result)) {
    $cabweichung.="[$erg->Abw,$erg->PFMGP2016,$erg->PFMGP2017,$erg->PFMGP2018,$erg->PFMGP2019],";
}


$teilnehmer="Diese Wettbewerbe hatten die meisten Teilnehmer:<ol>";
$sql="SELECT Count(*) Anzahl, wettbewerbsName FROM Wettbewerb LEFT JOIN Teilnahme USING (wettbewerbsID) Group by wettbewerbsID Order by Anzahl DESC LIMIT 10";
$result=mysqli_query($conn,$sql);
while($erg=mysqli_fetch_object($result)) {
    $teilnehmer.="<li>$erg->wettbewerbsName ($erg->Anzahl Teilnehmer)</li>";
}
$interpreten.="</ol>";

$topvotings="Diese Titel hatten die meisten TOP-Votings (12 Punkte):<ol>";
$sql="SELECT Count(*) Anzahl, Titel.Youtube Youtube, Titel.titelName Titel, Titel.titelInterpret Interpret FROM `Voting` LEFT JOIN Titel ON Voting.votingPunkte12=Titel.titelID WHERE votingPunkte12 is not NULL GROUP by titelID ORDER BY Anzahl DESC LIMIT 10 ";
$result=mysqli_query($conn,$sql);
while($erg=mysqli_fetch_object($result)) {
    if($erg->Youtube<>"") $titel="<a href='$erg->Youtube' target='Youtube'>$erg->Titel</a>"; else $titel=$erg->Titel;
    $topvotings.="<li>$titel - $erg->Interpret ($erg->Anzahl TOP-Votings)</li>";
}
$topvotings.="</ol>";

    
if (!isset($_GET["wett"])) print <<<html
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load("current", {packages:["corechart"]});
      google.charts.setOnLoadCallback(drawChart);
    function drawChart() {

      var data1 = new google.visualization.DataTable();
      data1.addColumn('string', 'Jahrzehnt');
      data1.addColumn('number', 'Anzahl');
      data1.addRows([
      $czeiten
      ]);

      var options1 = {
        title: 'Jahrgänge der PFMGP-Titel',
        sliceVisibilityThreshold: .05,
        backgroundColor: '#505050',
        pieHole: 0.2
      };

      var chart1 = new google.visualization.PieChart(document.getElementById('zeiten'));
      chart1.draw(data1, options1);
        
      var data2 = new google.visualization.DataTable();
      data2.addColumn('string', 'Sprache');
      data2.addColumn('number', 'Anzahl');
      data2.addRows([
      $csprachen
      ]);

      var options2 = {
        title: 'Sprachen der PFMGP-Titel',
        sliceVisibilityThreshold: .1,
        backgroundColor: '#505050',
        pieHole: 0.2
      };

      var chart2 = new google.visualization.PieChart(document.getElementById('sprachen'));
      chart2.draw(data2, options2);
      
     var data3 = new google.visualization.DataTable();
      data3.addColumn('string', 'Interpret');
      data3.addColumn('number', 'Anzahl');
      data3.addRows([
      $cinterpret
      ]);

      var options3 = {
        title: 'Interpreten mit mehr als 3 Titeln',
        backgroundColor: '#505050'
      };

      var chart3 = new google.visualization.BarChart(document.getElementById('interpreten'));
      chart3.draw(data3, options3);

		var data4 = google.visualization.arrayToDataTable([$cabweichung]);
      
      var options4 = {
        title: 'Abweichung der Vorrundenvotings',
        backgroundColor: '#505050',
        curveType: 'function',
        legend: { position: 'bottom' }
      };

      var chart4 = new google.visualization.LineChart(document.getElementById('AbweichungChart'));
      chart4.draw(data4, options4);
     
      var data5 = new google.visualization.DataTable();
      data5.addColumn('string', 'Buchstabe');
      data5.addColumn('number', 'Anzahl');
      data5.addRows([
      $canfatitel
      ]);

      var options5 = {
        title: 'Anfangsbuchstabe der Titel',
        sliceVisibilityThreshold: .05,
        backgroundColor: '#505050',
        pieHole: 0.2
      };

      var chart5 = new google.visualization.PieChart(document.getElementById('interpreten2'));
      chart5.draw(data5, options5);
      
      var data6 = new google.visualization.DataTable();
      data6.addColumn('string', 'Buchstabe');
      data6.addColumn('number', 'Anzahl');
      data6.addRows([
      $canfainterpret
      ]);

      var options6 = {
        title: 'Anfangsbuchstabe der Interpreten',
        sliceVisibilityThreshold: .05,
        backgroundColor: '#505050',
        pieHole: 0.2
      };

      var chart6 = new google.visualization.PieChart(document.getElementById('interpreten3'));
      chart6.draw(data6, options6);
    }
</script>
<h2>Statistik</h2>
<p>Die Statistik bezieht sich immer nur auf die jeweils klassifizierten Titel. Titel, bei denen die jeweilige Angabe (Veröffentlichungsjahr, Interpret, Sprache) fehlt, werden nicht mitgezählt.</p>
<h3>Zeit-Statistik</h3>
<p>$zeiten</p>
<div id="zeiten" style="width: 770px; height: 500px;"></div>
<h3>Interpreten</h3>
<p>$interpreten</p>
<div id="interpreten" style="width: 770px; height: 400px;"></div>
<div id="interpreten2" style="width: 770px; height: 500px;"></div>
<div id="interpreten3" style="width: 770px; height: 500px;"></div>
<h3>Sprachen</h3>
<p>$sprachen</p>
<div id="sprachen" style="width: 770px; height: 500px;"></div>
<h3>PFMGP-Teilnehmer</h3>
<p>$teilnehmer</p>
<h3>TOP-Votings</h3>
<p>$topvotings</p>
<h3>Verteilung der Votings in der Vorrunde</h3>
<div id="AbweichungChart" style="width: 770px; height: 400px;"></div>
<h2>Teilnahme aktueller PFMGP</h2>
$teilnahme
<h2>Live-Ergebnisse</h2>
$live
html;
print <<<html
<h2>Ergebnisse der Votings</h2>
$votings

html;
}
include("incl/footer.php");

?>

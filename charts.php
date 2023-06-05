<?php

include("conf/config.php");
include("incl/header.php");

if(logged_in()){
$titel="";
$wwid=$_GET["id"];
$sql="SELECT * FROM Titel where wettbewerbsID=$wwid ORDER BY titelName";
$result=mysqli_query($conn,$sql);
while($erg=mysqli_fetch_object($result)){
    $titel.="[$erg->titelName],\n";
}

    
print <<<html

   <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load("current", {packages:["corechart"]});
      google.charts.setOnLoadCallback(drawChart);
	  
	  
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Titel',],
          $titel
        ]);
		

        var options = {
          title: 'Abstimmung zum PFMGP-Wertungsverfahren',
          is3D: true,
		  isStacked: true,
		  animation:{
        duration: 1000,
        easing: 'out',
		
		},
	    };

	
        var chart = new google.visualization.BarChart(document.getElementById('pfmgpchart'));
        $dchart
	data.addColumn("number","Spieler6");
	data.setValue(0,6,10);
	data.setValue(1,6,10);
	data.setValue(2,6,10);
	data.setValue(3,6,10);
	setTimeout(function(){chart.draw(data,options);},3000);
		
      }
    </script>        
        
<h2>Auswertung</h2>
<div id="pfmgpchart" style="width: 900px; height: 500px;"></div>
        
html;
}
include("incl/footer.php");

?>

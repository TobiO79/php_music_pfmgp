<?php

include("conf/config.php");
include("incl/header.php");
if(logged_in()){
if($_SESSION["Leiter"]){

if(isset($_POST["news"]) and $_POST["news"]=="absenden"){
    $sql="INSERT INTO News (betreff, text, wettbewerbsID, datum) VALUES ('".$_POST["betreff"]."','".$_POST["text"]."',".$_POST["wettbewerbsID"].",'".date("Y-m-d")."')";
    mysqli_query($conn,$sql);
    if($_POST["wettbewerbsID"]=="NULL"){
        $sql="SELECT * FROM Spieler WHERE aktiv=1";
    }
    else{
        $sql="SELECT * FROM Spieler"
                . " LEFT JOIN Teilnahme USING (userID)"
                . " WHERE wettbewerbsID=".$_POST["wettbewerbsID"]." and aktiv=1";
    }
    
    $result=mysqli_query($conn,$sql);
    while($erg=mysqli_fetch_object($result)){
	    $empf[]=$erg->email;
    }
        pfmgp_mail($_POST["betreff"],$_POST["text"],$empf,true);
        telegchan(html_entity_decode(strip_tags($_POST["text"])));
}
}
$wettbewerbliste="";
$sql="SELECT * FROM Wettbewerb ORDER BY wettbewerbsJahr";
$result=mysqli_query($conn,$sql);
while($erg=mysqli_fetch_object($result)){
    $wettbewerbliste.="<option value='$erg->wettbewerbsID'>$erg->wettbewerbsJahr - $erg->wettbewerbsName</option>\n";
}


$newsliste="";
$sql="SELECT * FROM News"
        . " LEFT JOIN Teilnahme USING (wettbewerbsID)"
        . " WHERE userID=".$_SESSION["UserID"]." OR News.wettbewerbsID is NULL"
        . " ORDER BY Datum DESC";
$result=mysqli_query($conn,$sql);
while($erg=mysqli_fetch_object($result)){
    $newsliste.="<li>$erg->datum - <a href='news.php?nid=$erg->newsID'>$erg->betreff</a></li>\n";
}

$news="";
if(isset($_GET["nid"])){
    $sql="SELECT * FROM News WHERE newsid=".$_GET["nid"];
    $result=mysqli_query($conn,$sql);
    $erg=mysqli_fetch_object($result);
    $news=$erg->text;
}

if($_SESSION["Leiter"]){
print <<<html
<h2>Rundmail verfassen</h2>
<!--<script src="//cdn.ckeditor.com/ckeditor5/12.1.0/classic/ckeditor.js"></script>-->
<script src="ckeditor/ckeditor.js"></script>
<form name="newsmail" method="POST" action="news.php">
Empfänger: <select name="wettbewerbsID" size=1>
    <option value="NULL">alle Teilnehmer</option>
    $wettbewerbliste
</select><br>
Betreff: <input type="text" name="betreff"><br>
        <textarea id="text" name="text" rows=10>
        </textarea><br>
<script>
	ClassicEditor
	.create(document.querySelector('#text'))
	.catch(error => {
		console.error(error);
});
</script>

        <input type="submit" name="news" value="absenden">
</form>

html;
}
print <<<html

<h2>News-Archiv</h2>

$news

<ul>
$newsliste
</ul>

html;
}
include("incl/footer.php");

?>

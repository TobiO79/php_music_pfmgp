<?php

include("conf/config.php");
include("incl/header.php");
$text=mysqli_fetch_object(mysqli_query($conn,"SELECT text FROM Seiten WHERE seitenID=3"));

print $text->text;
include("incl/footer.php");

?>

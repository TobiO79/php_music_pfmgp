<?php
header('Content-Type: text/html; charset=UTF-8');
//Session starten
session_start();
date_default_timezone_set("Europe/Berlin");

//Server-Konfiguration
$host = "";                                      //Mysql-Host
$user = "";                                //Mysql-Benutzer
$pass = "";                                      //Mysql-Passwort
$database = "";                            //Mysql-Datenbank
$dbprefix = "";                            //Datenbankprefix

//Allgemeine Angaben
$url = "";
$hproot = "/";

/*
//Verbindung zur Datenbank herstellen
$conn = mysql_connect($host, $user, $pass)
or die("Datenbank zur Zeit ausser Betrieb!");
mysql_select_db($database)
	or die("Wartungsarbeiten an der Datenbank!");
*/


//Verbindung zur Datenbank herstellen
$conn = mysqli_connect($host, $user, $pass,$database);
// Verbindung �berpr�fen
if (mysqli_connect_errno()) {
  printf("Verbindung fehlgeschlagen: %s\n", mysqli_connect_error());
  exit();
}

//Telegram-Konfiguration
define('BOT_TOKEN', '');
define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');
define('WEBHOOK_URL', 'https://pfmgp.de/telegrambot.php');

?>

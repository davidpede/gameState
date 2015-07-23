<?php
// Criteria for foreign Database
$host = 'localhost';
$username = 'root';
$password = '7js1citgp6';
$dbname = 'leagues';
$port = 3306;
$charset = 'utf-8';

$xpdo = $modx->getPlaceholder("my.xpdo");
if ($xpdo == "") {
  $xpdo = new xPDO('mysql:host=localhost;dbname=leagues',$username,$password);
  $modx->setPlaceholder("my.xpdo", $xpdo);
}

//$xpdo->setDebug(true);

// Test your connection
//echo $o = ($xpdo->connect()) ? 'setConnect - Connected' : 'setConnect - Not Connected';
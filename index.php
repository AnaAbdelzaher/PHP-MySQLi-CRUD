<?php
include 'database/database.php';
$db = new DB();
$db->connect();
$db->select("rank");
$res=$db->getResult();
var_dump($res);
//  foreach ($res as $key=>$value) {
//     echo $output . "<br />";
//  }

$db->disconnect();
echo"end";
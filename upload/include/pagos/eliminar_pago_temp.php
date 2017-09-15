<?php
session_start();
define("INCLUDE_DIR","../");
require_once '../ost-config.php';
$mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);

$sqlDel="DELETE FROM ost_pagos_temp WHERE id=".$_REQUEST["id"];
$qryDel=$mysqli->query($sqlDel);

//Cerramos conexion
$mysqli->close();
?>
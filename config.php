<?php
$host = 'mysql-arnauagudo.alwaysdata.net';
$dbname = 'arnauagudo_gestordegastos';
$username = 'arnauagudo';
$password = 'Daw_2526';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$mysqli = new mysqli($host, $username, $password, $dbname);

if ($mysqli->connect_error){
    die("Error de conexión: " . $mysqli->connect_error);
}
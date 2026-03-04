<?php 

$servidor = 'localhost';
$usuario = 'root';
$password = '';
$basededatos = 'ferreinver';
$puerto = '3306';

$conn = mysqli_connect($servidor,$usuario,$password,$basededatos, $puerto);

if (!$conn){
    die("La conexión a fallado: ". mysqli_connect_error());
}
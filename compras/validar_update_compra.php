<?php

session_start();
require_once 'conexion.php';

// Verificar que sea POST
if($_SERVER['REQUEST_METHOD'] != 'POST'){
    header("Location: index_compra.php");
    exit;
}

// Verificar ID
if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    die('ID inválido');
}

$id = $_GET['id'];

// Validar que todos los campos estén presentes
if(
    empty($_POST['Cantidad']) ||
    empty($_POST['Descripcion']) ||
    empty($_POST['ID_proveedor']) ||
    empty($_POST['ID_producto'])
){
    $error = 'Todos los campos son obligatorios.';
    header("Location: update_compra .php?id=$id&error=" . urlencode($error));
    exit;
}
// Validar que la cantidad sea numérica
if(!is_numeric($cantidad) || $cantidad <= 0){
    $error = 'la cantidad debe ser  un número válido.';
    header("Location: registrar_compra.php?error=" . urlencode($error));
    exit;
}
if (!preg_match('/^[a-zA-Z0-9\s]+$/', $descripcion)) {
    $error = 'la descripcion debe ser valida solo numero espacios y letras.';
    header("Location: registrar_compra.php?error=" . urlencode($error));
    exit;
}

// Actualizar en la base de datos
$stmt = mysqli_prepare($conn, "UPDATE compra SET Cantidad = ?, Descripcion = ?, WHERE ID_compra = ?");
mysqli_stmt_bind_param($stmt, 'issi', $cantidad, $descripcion);

if(mysqli_stmt_execute($stmt)){
    $success = 'compra actualizada exitosamente.';
    header("Location: index_compra.php?success=" . urlencode($success));
} else {
    $error = 'Error al actualizar la compra: ' . mysqli_error($conn);
    header("Location: update_compra.php?id=$id&error=" . urlencode($error));
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>
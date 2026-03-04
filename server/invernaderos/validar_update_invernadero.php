<?php

require_once 'conexion.php';

// Verificar que sea POST
if($_SERVER['REQUEST_METHOD'] != 'POST'){
    header("Location: index_invernaderos.php");
    exit;
}

// Verificar ID
if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    die('ID de invernadero inválido.');
}

$id = $_GET['id'];

// Verificar que el invernadero exista
$stmt_check = mysqli_prepare($conn, "SELECT id_invernadero FROM invernaderos WHERE id_invernadero = ?");
mysqli_stmt_bind_param($stmt_check, 'i', $id);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);
if(mysqli_num_rows($result_check) == 0){
    $error = 'El invernadero no existe.';
    mysqli_stmt_close($stmt_check);
    header("Location: index_invernaderos.php?error=" . urlencode($error));
    exit;
}
mysqli_stmt_close($stmt_check);

// Validar campos obligatorios
if(
    empty($_POST['nombre']) ||
    empty($_POST['precio_m2']) ||
    empty($_POST['estado'])
){
    $error = 'Los campos nombre, precio por m² y estado son obligatorios.';
    header("Location: update_invernadero.php?id=$id&error=" . urlencode($error));
    exit;
}

// Obtener y limpiar datos
$nombre      = trim($_POST['nombre']);
$descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
$precio_m2   = trim($_POST['precio_m2']);
$estado      = trim($_POST['estado']);

// Validar longitud del nombre
if(strlen($nombre) > 50){
    $error = 'El nombre no puede exceder 50 caracteres.';
    header("Location: update_invernadero.php?id=$id&error=" . urlencode($error));
    exit;
}

// Validar longitud de la descripción
if(strlen($descripcion) > 150){
    $error = 'La descripción no puede exceder 150 caracteres.';
    header("Location: update_invernadero.php?id=$id&error=" . urlencode($error));
    exit;
}

// Validar que precio_m2 sea numérico y positivo
if(!is_numeric($precio_m2) || $precio_m2 <= 0){
    $error = 'El precio por m² debe ser un número mayor a 0.';
    header("Location: update_invernadero.php?id=$id&error=" . urlencode($error));
    exit;
}

// Validar que precio_m2 no exceda los límites del DECIMAL(12,2)
if($precio_m2 >= 9999999999.99){
    $error = 'El precio por m² es demasiado alto.';
    header("Location: update_invernadero.php?id=$id&error=" . urlencode($error));
    exit;
}

// Validar estado
if($estado != 'activo' && $estado != 'inactivo'){
    $error = 'El estado no es válido.';
    header("Location: update_invernadero.php?id=$id&error=" . urlencode($error));
    exit;
}

// Verificar que el nombre no esté en uso por otro invernadero
$stmt_nombre = mysqli_prepare($conn, "SELECT id_invernadero FROM invernaderos WHERE nombre = ? AND id_invernadero != ?");
mysqli_stmt_bind_param($stmt_nombre, 'si', $nombre, $id);
mysqli_stmt_execute($stmt_nombre);
$result_nombre = mysqli_stmt_get_result($stmt_nombre);
if(mysqli_num_rows($result_nombre) > 0){
    $error = 'Ya existe otro invernadero con ese nombre.';
    mysqli_stmt_close($stmt_nombre);
    header("Location: update_invernadero.php?id=$id&error=" . urlencode($error));
    exit;
}
mysqli_stmt_close($stmt_nombre);

// Actualizar en la base de datos
$stmt = mysqli_prepare($conn, "UPDATE invernaderos SET nombre = ?, descripcion = ?, precio_m2 = ?, estado = ? WHERE id_invernadero = ?");
mysqli_stmt_bind_param($stmt, 'ssdsi', $nombre, $descripcion, $precio_m2, $estado, $id);

if(mysqli_stmt_execute($stmt)){
    $success = 'Invernadero actualizado exitosamente.';
    header("Location: index_invernaderos.php?success=" . urlencode($success));
} else {
    $error = 'Error al actualizar el invernadero: ' . mysqli_error($conn);
    header("Location: update_invernadero.php?id=$id&error=" . urlencode($error));
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
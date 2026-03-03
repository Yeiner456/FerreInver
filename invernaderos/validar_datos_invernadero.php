<?php

require_once 'conexion.php';

// Verificar que sea POST
if($_SERVER['REQUEST_METHOD'] != 'POST'){
    header("Location: registrar_invernadero.php");
    exit;
}

// Validar que los campos obligatorios estén presentes
if(
    empty($_POST['nombre']) ||
    empty($_POST['precio_m2']) ||
    empty($_POST['estado'])
){
    $error = 'Los campos nombre, precio por m² y estado son obligatorios.';
    header("Location: registrar_invernadero.php?error=" . urlencode($error));
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
    header("Location: registrar_invernadero.php?error=" . urlencode($error));
    exit;
}

// Validar longitud de la descripción
if(strlen($descripcion) > 150){
    $error = 'La descripción no puede exceder 150 caracteres.';
    header("Location: registrar_invernadero.php?error=" . urlencode($error));
    exit;
}

// Validar que precio_m2 sea numérico y positivo
if(!is_numeric($precio_m2) || $precio_m2 <= 0){
    $error = 'El precio por m² debe ser un número mayor a 0.';
    header("Location: registrar_invernadero.php?error=" . urlencode($error));
    exit;
}

// Validar que precio_m2 no exceda los límites del DECIMAL(12,2)
if($precio_m2 >= 9999999999.99){
    $error = 'El precio por m² es demasiado alto.';
    header("Location: registrar_invernadero.php?error=" . urlencode($error));
    exit;
}

// Validar estado
if($estado != 'activo' && $estado != 'inactivo'){
    $error = 'El estado no es válido.';
    header("Location: registrar_invernadero.php?error=" . urlencode($error));
    exit;
}

// Verificar si ya existe un invernadero con el mismo nombre
$stmt_check = mysqli_prepare($conn, "SELECT id_invernadero FROM invernaderos WHERE nombre = ?");
mysqli_stmt_bind_param($stmt_check, 's', $nombre);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);
if(mysqli_num_rows($result_check) > 0){
    $error = 'Ya existe un invernadero con ese nombre.';
    mysqli_stmt_close($stmt_check);
    header("Location: registrar_invernadero.php?error=" . urlencode($error));
    exit;
}
mysqli_stmt_close($stmt_check);

// Insertar en la base de datos
$stmt = mysqli_prepare($conn, "INSERT INTO invernaderos (nombre, descripcion, precio_m2, estado) VALUES (?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, 'ssds', $nombre, $descripcion, $precio_m2, $estado);

if(mysqli_stmt_execute($stmt)){
    $success = 'Invernadero registrado exitosamente.';
    header("Location: index_invernaderos.php?success=" . urlencode($success));
} else {
    $error = 'Error al registrar el invernadero: ' . mysqli_error($conn);
    header("Location: registrar_invernadero.php?error=" . urlencode($error));
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
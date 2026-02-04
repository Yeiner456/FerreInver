<?php

session_start();
require_once 'conexion.php';

// Verificar que sea POST
if($_SERVER['REQUEST_METHOD'] != 'POST'){
    header("Location: registrar_producto.php");
    exit;
}

// Validar que los campos obligatorios estén presentes
if(empty($_POST['nombre']) || empty($_POST['precio'])){
    $error = 'El nombre y el precio son obligatorios.';
    header("Location: registrar_producto.php?error=" . urlencode($error));
    exit;
}

// Obtener y limpiar datos
$nombre = trim($_POST['nombre']);
$precio = trim($_POST['precio']);
$descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : 'Producto de ferreinver disponible';

// Si la descripción está vacía, usar el valor por defecto
if(empty($descripcion)){
    $descripcion = 'Producto de ferreinver disponible';
}

// Validar longitud del nombre
if(strlen($nombre) > 30){
    $error = 'El nombre no puede exceder 30 caracteres.';
    header("Location: registrar_producto.php?error=" . urlencode($error));
    exit;
}

// Validar que el nombre no esté vacío después de trim
if(strlen($nombre) == 0){
    $error = 'El nombre del producto no puede estar vacío.';
    header("Location: registrar_producto.php?error=" . urlencode($error));
    exit;
}

// Validar que el precio sea numérico
if(!is_numeric($precio)){
    $error = 'El precio debe ser un número válido.';
    header("Location: registrar_producto.php?error=" . urlencode($error));
    exit;
}

// Validar que el precio sea mayor a 0
if($precio <= 0){
    $error = 'El precio debe ser mayor a 0.';
    header("Location: registrar_producto.php?error=" . urlencode($error));
    exit;
}

// Validar que el precio sea entero (BIGINT no permite decimales)
if(floor($precio) != $precio){
    $error = 'El precio debe ser un número entero.';
    header("Location: registrar_producto.php?error=" . urlencode($error));
    exit;
}

// Validar longitud de la descripción
if(strlen($descripcion) > 100){
    $error = 'La descripción no puede exceder 100 caracteres.';
    header("Location: registrar_producto.php?error=" . urlencode($error));
    exit;
}

// Insertar en la base de datos
$stmt = mysqli_prepare($conn, "INSERT INTO productos (nombre, Precio, Descripcion) VALUES (?, ?, ?)");
mysqli_stmt_bind_param($stmt, 'sis', $nombre, $precio, $descripcion);

if(mysqli_stmt_execute($stmt)){
    $success = 'Producto registrado exitosamente.';
    header("Location: index_productos.php?success=" . urlencode($success));
} else {
    $error = 'Error al registrar el producto: ' . mysqli_error($conn);
    header("Location: registrar_producto.php?error=" . urlencode($error));
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>
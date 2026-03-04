<?php

session_start();
require_once 'conexion.php';

// Verificar que sea POST
if($_SERVER['REQUEST_METHOD'] != 'POST'){
    header("Location: registrar_compra.php");
    exit;
}

// Validar que todos los campos estén presentes
if(
    empty($_POST['cantidad']) ||
    empty($_POST['descripcion']) ||
    empty($_POST['id_producto']) ||
    empty($_POST['id_proveedor'])
){
    $error = 'Todos los campos son obligatorios.';
    header("Location: registrar_cliente.php?error=" . urlencode($error));
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
// Verificar que el producto exista
$stmt_check_producto = mysqli_prepare($conn, "SELECT ID_producto FROM Productos WHERE ID_producto = ?");
mysqli_stmt_bind_param($stmt_check_producto, 'i', $id_producto);
mysqli_stmt_execute($stmt_check_producto);
$result_check_producto = mysqli_stmt_get_result($stmt_check_producto);

if(mysqli_num_rows($result_check_cliente) == 0){
    $error = 'producto seleccionado no existe.';
    mysqli_stmt_close($stmt_check_producto);
    header("Location: registrar_compra.php?error=" . urlencode($error));
    exit;
}
mysqli_stmt_close($stmt_check_producto);

// Verificar que el proveedor exista
$stmt_check_proveedor = mysqli_prepare($conn, "SELECT nit_proveedor FROM Proveedores WHERE nit_proveedor = ?");
mysqli_stmt_bind_param($stmt_check_proveedor, 'i', $id_proveedor);  
mysqli_stmt_execute($stmt_check_proveedor);
$result_check_proveedor = mysqli_stmt_get_result($stmt_check_proveedor);
if(mysqli_num_rows($result_check_proveedor) == 0){
    $error = 'proveedor seleccionado no existe.';
    mysqli_stmt_close($stmt_check_proveedor);
    header("Location: registrar_compra.php?error=" . urlencode($error));
    exit;
}

// Insertar en la base de datos

$stmt = mysqli_prepare($conn, "INSERT INTO compras (Cantidad, Descripcion, ID_proveedor, ID_producto,) VALUES (?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, 'isssss', $cantidad, $descripcion, $id_proveedor, $id_producto);

if(mysqli_stmt_execute($stmt)){
    $success = 'Compra registrada exitosamente.';
    header("Location: index_compra.php?success=" . urlencode($success));
} else {
    $error = 'Error al registrar la compra : ' . mysqli_error($conn);
    header("Location: registrar_compra.php?error=" . urlencode($error));
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>
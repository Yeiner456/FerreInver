<?php

session_start();
require_once 'conexion.php';

// Verificar que sea POST
if($_SERVER['REQUEST_METHOD'] != 'POST'){
    header("Location: index_stocks.php");
    exit;
}

// Verificar ID
if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    die('ID inválido');
}

$id = $_GET['id'];

// Validar que todos los campos estén presentes
if(empty($_POST['id_producto']) || !isset($_POST['cantidad'])){
    $error = 'Todos los campos son obligatorios.';
    header("Location: update_stock.php?id=$id&error=" . urlencode($error));
    exit;
}

// Obtener y limpiar datos
$id_producto = trim($_POST['id_producto']);
$cantidad = trim($_POST['cantidad']);

// Validar que el ID del producto sea numérico
if(!is_numeric($id_producto) || $id_producto <= 0){
    $error = 'El ID del producto debe ser un número válido.';
    header("Location: update_stock.php?id=$id&error=" . urlencode($error));
    exit;
}

// Verificar que el producto exista
$stmt_check_producto = mysqli_prepare($conn, "SELECT ID_producto FROM productos WHERE ID_producto = ?");
mysqli_stmt_bind_param($stmt_check_producto, 'i', $id_producto);
mysqli_stmt_execute($stmt_check_producto);
$result_check_producto = mysqli_stmt_get_result($stmt_check_producto);

if(mysqli_num_rows($result_check_producto) == 0){
    $error = 'El producto seleccionado no existe.';
    mysqli_stmt_close($stmt_check_producto);
    header("Location: update_stock.php?id=$id&error=" . urlencode($error));
    exit;
}
mysqli_stmt_close($stmt_check_producto);

// Verificar si ya existe otro stock para este producto (diferente al actual)
$stmt_check_stock = mysqli_prepare($conn, "SELECT id_stock FROM stocks WHERE ID_producto = ? AND id_stock != ?");
mysqli_stmt_bind_param($stmt_check_stock, 'ii', $id_producto, $id);
mysqli_stmt_execute($stmt_check_stock);
$result_check_stock = mysqli_stmt_get_result($stmt_check_stock);

if(mysqli_num_rows($result_check_stock) > 0){
    $error = 'Ya existe otro registro de stock para este producto.';
    mysqli_stmt_close($stmt_check_stock);
    header("Location: update_stock.php?id=$id&error=" . urlencode($error));
    exit;
}
mysqli_stmt_close($stmt_check_stock);

// Validar que la cantidad sea numérica
if(!is_numeric($cantidad)){
    $error = 'La cantidad debe ser un número válido.';
    header("Location: update_stock.php?id=$id&error=" . urlencode($error));
    exit;
}

// Validar que la cantidad sea mayor o igual a 0
if($cantidad < 0){
    $error = 'La cantidad no puede ser negativa.';
    header("Location: update_stock.php?id=$id&error=" . urlencode($error));
    exit;
}

// Validar que la cantidad sea un número entero
if(floor($cantidad) != $cantidad){
    $error = 'La cantidad debe ser un número entero.';
    header("Location: update_stock.php?id=$id&error=" . urlencode($error));
    exit;
}

// Actualizar en la base de datos
$stmt = mysqli_prepare($conn, "UPDATE stocks SET ID_producto = ?, Cantidad = ? WHERE id_stock = ?");
mysqli_stmt_bind_param($stmt, 'iii', $id_producto, $cantidad, $id);

if(mysqli_stmt_execute($stmt)){
    $success = 'Stock actualizado exitosamente.';
    header("Location: index_stocks.php?success=" . urlencode($success));
} else {
    $error = 'Error al actualizar el stock: ' . mysqli_error($conn);
    header("Location: update_stock.php?id=$id&error=" . urlencode($error));
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>
<?php

require_once 'conexion.php';

// Validar que el ID esté presente y sea numérico
if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    $error = 'ID inválido.';
    header("Location: index_pedidos.php?error=" . urlencode($error));
    exit;
}

$id = $_GET['id'];

// Verificar que el pedido exista antes de eliminar
$stmt_check = mysqli_prepare($conn, "SELECT id_pedido FROM pedidos WHERE id_pedido = ?");
mysqli_stmt_bind_param($stmt_check, 'i', $id);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

if(mysqli_num_rows($result_check) == 0){
    $error = 'El pedido no existe.';
    mysqli_stmt_close($stmt_check);
    header("Location: index_pedidos.php?error=" . urlencode($error));
    exit;
}
mysqli_stmt_close($stmt_check);

// Eliminar el pedido usando prepared statement
$stmt = mysqli_prepare($conn, "DELETE FROM pedidos WHERE id_pedido = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);

if(mysqli_stmt_execute($stmt)){
    $success = 'Pedido eliminado exitosamente.';
    header("Location: index_pedidos.php?success=" . urlencode($success));
} else {
    $error = 'Error al eliminar el pedido: ' . mysqli_error($conn);
    header("Location: index_pedidos.php?error=" . urlencode($error));
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>
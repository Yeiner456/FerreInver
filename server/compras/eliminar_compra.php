<?php

require_once 'conexion.php';

// Validar que el id este presente y sea nunmérico  
if(!isset($_GET['ID_compra']) || !is_numeric($_GET['ID_compra'])){
    $error = 'ID inválido.';
    header("Location: index_compra.php?error=" . urlencode($error));
    exit;
}

$idCompra = $_GET['ID_compra'];

// Verificar que la compra exista antes de eliminar
$stmt_check = mysqli_prepare($conn, "SELECT ID_compra FROM Compras WHERE ID_compra = ?");
mysqli_stmt_bind_param($stmt_check, 'i', $idCompra);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

if(mysqli_num_rows($result_check) == 0){
    $error = 'La compra no existe.';
    mysqli_stmt_close($stmt_check);
    header("Location: index_compra.php?error=" . urlencode($error));
    exit;
}
mysqli_stmt_close($stmt_check);

// Eliminar la compra usando prepared statement
$stmt = mysqli_prepare($conn, "DELETE FROM Compras WHERE ID_compra = ?");
mysqli_stmt_bind_param($stmt, 'i', $idCompra);

if(mysqli_stmt_execute($stmt)){
    $success = 'Compra eliminada exitosamente.';
    header("Location: index_compra.php?success=" . urlencode($success));
} else {
    $error = 'Error al eliminar la compra ' . mysqli_error($conn);
    header("Location: index_compra.php?error=" . urlencode($error));
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>
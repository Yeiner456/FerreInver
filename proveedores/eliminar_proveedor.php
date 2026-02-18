<?php

require_once 'conexion.php';

// Validar que el NIT esté presente y sea numérico
if(!isset($_GET['nit']) || !is_numeric($_GET['nit'])){
    $error = 'NIT inválido.';
    header("Location: index_proveedores.php?error=" . urlencode($error));
    exit;
}

$nit = $_GET['nit'];

// Verificar que el proveedor exista antes de eliminar
$stmt_check = mysqli_prepare($conn, "SELECT nit_proveedor FROM proveedores WHERE nit_proveedor = ?");
mysqli_stmt_bind_param($stmt_check, 'i', $nit);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

if(mysqli_num_rows($result_check) == 0){
    $error = 'El proveedor no existe.';
    mysqli_stmt_close($stmt_check);
    header("Location: index_proveedores.php?error=" . urlencode($error));
    exit;
}
mysqli_stmt_close($stmt_check);

// Eliminar el proveedor usando prepared statement
$stmt = mysqli_prepare($conn, "DELETE FROM proveedores WHERE nit_proveedor = ?");
mysqli_stmt_bind_param($stmt, 'i', $nit);

if(mysqli_stmt_execute($stmt)){
    $success = 'Proveedor eliminado exitosamente.';
    header("Location: index_proveedores.php?success=" . urlencode($success));
} else {
    $error = 'Error al eliminar el proveedor: ' . mysqli_error($conn);
    header("Location: index_proveedores.php?error=" . urlencode($error));
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>
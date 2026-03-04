<?php

require_once 'conexion.php';

// Validar ID
if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    $error = 'ID de cotización inválido.';
    header("Location: index_cotizaciones.php?error=" . urlencode($error));
    exit;
}

$id = $_GET['id'];

// Verificar que la cotización exista
$stmt_check = mysqli_prepare($conn, "SELECT id_cotizacion FROM cotizaciones WHERE id_cotizacion = ?");
mysqli_stmt_bind_param($stmt_check, 'i', $id);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);
if(mysqli_num_rows($result_check) == 0){
    $error = 'La cotización no existe.';
    mysqli_stmt_close($stmt_check);
    header("Location: index_cotizaciones.php?error=" . urlencode($error));
    exit;
}
mysqli_stmt_close($stmt_check);

// Eliminar la cotización
$stmt = mysqli_prepare($conn, "DELETE FROM cotizaciones WHERE id_cotizacion = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);

if(mysqli_stmt_execute($stmt)){
    $success = 'Cotización eliminada exitosamente.';
    header("Location: index_cotizaciones.php?success=" . urlencode($success));
} else {
    $error = 'Error al eliminar la cotización: ' . mysqli_error($conn);
    header("Location: index_cotizaciones.php?error=" . urlencode($error));
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
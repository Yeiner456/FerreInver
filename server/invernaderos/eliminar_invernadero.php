<?php

require_once 'conexion.php';

// Validar que el ID esté presente y sea numérico
if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    $error = 'ID de invernadero inválido.';
    header("Location: index_invernaderos.php?error=" . urlencode($error));
    exit;
}

$id = $_GET['id'];

// Verificar que el invernadero exista antes de eliminar
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

// Eliminar el invernadero
$stmt = mysqli_prepare($conn, "DELETE FROM invernaderos WHERE id_invernadero = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);

if(mysqli_stmt_execute($stmt)){
    $success = 'Invernadero eliminado exitosamente.';
    header("Location: index_invernaderos.php?success=" . urlencode($success));
} else {
    $error = 'Error al eliminar el invernadero: ' . mysqli_error($conn);
    header("Location: index_invernaderos.php?error=" . urlencode($error));
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
<?php

require_once 'conexion.php';

// Validar que el ID esté presente y sea numérico
if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    $error = 'ID inválido.';
    header("Location: index_tipos_usuarios.php?error=" . urlencode($error));
    exit;
}

$id = $_GET['id'];

// Verificar que el tipo exista
$stmt_check = mysqli_prepare($conn, "SELECT id_tipo_de_usuario FROM tipos_usuarios WHERE id_tipo_de_usuario = ?");
mysqli_stmt_bind_param($stmt_check, 'i', $id);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);
if(mysqli_num_rows($result_check) == 0){
    $error = 'El tipo de usuario no existe.';
    mysqli_stmt_close($stmt_check);
    header("Location: index_tipos_usuarios.php?error=" . urlencode($error));
    exit;
}
mysqli_stmt_close($stmt_check);

// Verificar que no haya clientes usando este tipo de usuario (integridad referencial)
$stmt_ref = mysqli_prepare($conn, "SELECT documento FROM clientes WHERE id_tipo_de_usuario = ? LIMIT 1");
mysqli_stmt_bind_param($stmt_ref, 'i', $id);
mysqli_stmt_execute($stmt_ref);
$result_ref = mysqli_stmt_get_result($stmt_ref);
if(mysqli_num_rows($result_ref) > 0){
    $error = 'No se puede eliminar: hay clientes asociados a este tipo de usuario.';
    mysqli_stmt_close($stmt_ref);
    header("Location: index_tipos_usuarios.php?error=" . urlencode($error));
    exit;
}
mysqli_stmt_close($stmt_ref);

// Eliminar el tipo de usuario
$stmt = mysqli_prepare($conn, "DELETE FROM tipos_usuarios WHERE id_tipo_de_usuario = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);

if(mysqli_stmt_execute($stmt)){
    $success = 'Tipo de usuario eliminado exitosamente.';
    header("Location: index_tipos_usuarios.php?success=" . urlencode($success));
} else {
    $error = 'Error al eliminar el tipo de usuario: ' . mysqli_error($conn);
    header("Location: index_tipos_usuarios.php?error=" . urlencode($error));
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
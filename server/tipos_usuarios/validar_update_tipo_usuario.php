<?php

require_once 'conexion.php';

// Verificar que sea POST
if($_SERVER['REQUEST_METHOD'] != 'POST'){
    header("Location: index_tipos_usuarios.php");
    exit;
}

// Verificar ID
if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    die('ID inválido.');
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

// Validar campos obligatorios
if(empty($_POST['nombre']) || empty($_POST['estado'])){
    $error = 'Todos los campos son obligatorios.';
    header("Location: update_tipo_usuario.php?id=$id&error=" . urlencode($error));
    exit;
}

// Obtener y limpiar datos
$nombre = trim($_POST['nombre']);
$estado = trim($_POST['estado']);

// Validar longitud del nombre
if(strlen($nombre) > 30){
    $error = 'El nombre no puede exceder 30 caracteres.';
    header("Location: update_tipo_usuario.php?id=$id&error=" . urlencode($error));
    exit;
}

// Validar que el nombre solo contenga letras y espacios
if(!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/", $nombre)){
    $error = 'El nombre solo puede contener letras y espacios.';
    header("Location: update_tipo_usuario.php?id=$id&error=" . urlencode($error));
    exit;
}

// Validar estado
if($estado != 'activo' && $estado != 'inactivo'){
    $error = 'El estado no es válido.';
    header("Location: update_tipo_usuario.php?id=$id&error=" . urlencode($error));
    exit;
}

// Verificar que el nombre no esté en uso por otro registro
$stmt_nombre = mysqli_prepare($conn, "SELECT id_tipo_de_usuario FROM tipos_usuarios WHERE nombre = ? AND id_tipo_de_usuario != ?");
mysqli_stmt_bind_param($stmt_nombre, 'si', $nombre, $id);
mysqli_stmt_execute($stmt_nombre);
$result_nombre = mysqli_stmt_get_result($stmt_nombre);
if(mysqli_num_rows($result_nombre) > 0){
    $error = 'Ya existe otro tipo de usuario con ese nombre.';
    mysqli_stmt_close($stmt_nombre);
    header("Location: update_tipo_usuario.php?id=$id&error=" . urlencode($error));
    exit;
}
mysqli_stmt_close($stmt_nombre);

// Actualizar en la base de datos
$stmt = mysqli_prepare($conn, "UPDATE tipos_usuarios SET nombre = ?, estado = ? WHERE id_tipo_de_usuario = ?");
mysqli_stmt_bind_param($stmt, 'ssi', $nombre, $estado, $id);

if(mysqli_stmt_execute($stmt)){
    $success = 'Tipo de usuario actualizado exitosamente.';
    header("Location: index_tipos_usuarios.php?success=" . urlencode($success));
} else {
    $error = 'Error al actualizar el tipo de usuario: ' . mysqli_error($conn);
    header("Location: update_tipo_usuario.php?id=$id&error=" . urlencode($error));
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
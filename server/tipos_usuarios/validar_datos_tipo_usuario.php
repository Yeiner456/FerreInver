<?php

require_once 'conexion.php';

// Verificar que sea POST
if($_SERVER['REQUEST_METHOD'] != 'POST'){
    header("Location: registrar_tipo_usuario.php");
    exit;
}

// Validar campos obligatorios
if(empty($_POST['nombre']) || empty($_POST['estado'])){
    $error = 'Todos los campos son obligatorios.';
    header("Location: registrar_tipo_usuario.php?error=" . urlencode($error));
    exit;
}

// Obtener y limpiar datos
$nombre = trim($_POST['nombre']);
$estado = trim($_POST['estado']);

// Validar longitud del nombre
if(strlen($nombre) > 30){
    $error = 'El nombre no puede exceder 30 caracteres.';
    header("Location: registrar_tipo_usuario.php?error=" . urlencode($error));
    exit;
}

// Validar que el nombre solo contenga letras y espacios
if(!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/", $nombre)){
    $error = 'El nombre solo puede contener letras y espacios.';
    header("Location: registrar_tipo_usuario.php?error=" . urlencode($error));
    exit;
}

// Validar estado
if($estado != 'activo' && $estado != 'inactivo'){
    $error = 'El estado no es válido.';
    header("Location: registrar_tipo_usuario.php?error=" . urlencode($error));
    exit;
}

// Verificar que el nombre no esté duplicado
$stmt_check = mysqli_prepare($conn, "SELECT id_tipo_de_usuario FROM tipos_usuarios WHERE nombre = ?");
mysqli_stmt_bind_param($stmt_check, 's', $nombre);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);
if(mysqli_num_rows($result_check) > 0){
    $error = 'Ya existe un tipo de usuario con ese nombre.';
    mysqli_stmt_close($stmt_check);
    header("Location: registrar_tipo_usuario.php?error=" . urlencode($error));
    exit;
}
mysqli_stmt_close($stmt_check);

// Insertar en la base de datos
$stmt = mysqli_prepare($conn, "INSERT INTO tipos_usuarios (nombre, estado) VALUES (?, ?)");
mysqli_stmt_bind_param($stmt, 'ss', $nombre, $estado);

if(mysqli_stmt_execute($stmt)){
    $success = 'Tipo de usuario registrado exitosamente.';
    header("Location: index_tipos_usuarios.php?success=" . urlencode($success));
} else {
    $error = 'Error al registrar el tipo de usuario: ' . mysqli_error($conn);
    header("Location: registrar_tipo_usuario.php?error=" . urlencode($error));
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
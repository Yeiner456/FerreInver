<?php

session_start();
require_once 'conexion.php';

// Verificar que sea POST
if($_SERVER['REQUEST_METHOD'] != 'POST'){
    header("Location: registrar_cliente.php");
    exit;
}

// Validar que todos los campos estén presentes
if(
    empty($_POST['documento']) ||
    empty($_POST['tipo_usuario']) ||
    empty($_POST['nombre']) ||
    empty($_POST['correo']) ||
    empty($_POST['password']) ||
    empty($_POST['confirmar_password']) ||
    empty($_POST['estado'])
){
    $error = 'Todos los campos son obligatorios.';
    header("Location: registrar_cliente.php?error=" . urlencode($error));
    exit;
}

// Obtener y limpiar datos
$documento = trim($_POST['documento']);
$tipo_usuario = trim($_POST['tipo_usuario']);
$nombre = trim($_POST['nombre']);
$correo = trim($_POST['correo']);
$password = trim($_POST['password']);
$confirmar_password = trim($_POST['confirmar_password']);
$estado = trim($_POST['estado']);

// Validar que el documento sea numérico
if(!is_numeric($documento) || $documento <= 0){
    $error = 'El documento debe ser un número válido.';
    header("Location: registrar_cliente.php?error=" . urlencode($error));
    exit;
}

// Validar que el documento no tenga más de 11 dígitos
if(strlen($documento) > 11){
    $error = 'El documento no puede tener más de 11 dígitos.';
    header("Location: registrar_cliente.php?error=" . urlencode($error));
    exit;
}

// Validar tipo de usuario
if($tipo_usuario != 'Cliente' && $tipo_usuario != 'Admin'){
    $error = 'El tipo de usuario no es válido.';
    header("Location: registrar_cliente.php?error=" . urlencode($error));
    exit;
}

// Validar nombre (solo letras y espacios)
if(!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/", $nombre)){
    $error = 'El nombre solo puede contener letras y espacios.';
    header("Location: registrar_cliente.php?error=" . urlencode($error));
    exit;
}

// Validar longitud del nombre
if(strlen($nombre) > 30){
    $error = 'El nombre no puede exceder 30 caracteres.';
    header("Location: registrar_cliente.php?error=" . urlencode($error));
    exit;
}

// Validar formato de correo
if(!filter_var($correo, FILTER_VALIDATE_EMAIL)){
    $error = 'El formato del correo electrónico no es válido.';
    header("Location: registrar_cliente.php?error=" . urlencode($error));
    exit;
}

// Validar longitud del correo
if(strlen($correo) > 50){
    $error = 'El correo no puede exceder 50 caracteres.';
    header("Location: registrar_cliente.php?error=" . urlencode($error));
    exit;
}

// Validar que las contraseñas coincidan
if($password !== $confirmar_password){
    $error = 'Las contraseñas no coinciden.';
    header("Location: registrar_cliente.php?error=" . urlencode($error));
    exit;
}

// Validar longitud mínima de contraseña
if(strlen($password) < 6){
    $error = 'La contraseña debe tener al menos 6 caracteres.';
    header("Location: registrar_cliente.php?error=" . urlencode($error));
    exit;
}

// Validar que la contraseña tenga al menos una letra y un número
if(!preg_match("/[a-zA-Z]/", $password) || !preg_match("/[0-9]/", $password)){
    $error = 'La contraseña debe contener al menos una letra y un número.';
    header("Location: registrar_cliente.php?error=" . urlencode($error));
    exit;
}

// Validar estado
if($estado != 'Activo' && $estado != 'Inactivo' && $estado != 'Bloqueado'){
    $error = 'El estado de inicio de sesión no es válido.';
    header("Location: registrar_cliente.php?error=" . urlencode($error));
    exit;
}

// Verificar si el documento ya existe
$stmt_check = mysqli_prepare($conn, "SELECT Documento FROM clientes WHERE Documento = ?");
mysqli_stmt_bind_param($stmt_check, 'i', $documento);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

if(mysqli_num_rows($result_check) > 0){
    $error = 'El documento ya está registrado.';
    mysqli_stmt_close($stmt_check);
    header("Location: registrar_cliente.php?error=" . urlencode($error));
    exit;
}
mysqli_stmt_close($stmt_check);

// Verificar si el correo ya existe
$stmt_check_correo = mysqli_prepare($conn, "SELECT Correo FROM clientes WHERE Correo = ?");
mysqli_stmt_bind_param($stmt_check_correo, 's', $correo);
mysqli_stmt_execute($stmt_check_correo);
$result_check_correo = mysqli_stmt_get_result($stmt_check_correo);

if(mysqli_num_rows($result_check_correo) > 0){
    $error = 'El correo electrónico ya está registrado.';
    mysqli_stmt_close($stmt_check_correo);
    header("Location: registrar_cliente.php?error=" . urlencode($error));
    exit;
}
mysqli_stmt_close($stmt_check_correo);

// Encriptar contraseña
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Insertar en la base de datos
$stmt = mysqli_prepare($conn, "INSERT INTO clientes (Documento, TipoUsuario, Password_hash, Nombre, Correo, EstadoInicioSesion) VALUES (?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, 'isssss', $documento, $tipo_usuario, $password_hash, $nombre, $correo, $estado);

if(mysqli_stmt_execute($stmt)){
    $success = 'Cliente registrado exitosamente.';
    header("Location: index_clientes.php?success=" . urlencode($success));
} else {
    $error = 'Error al registrar el cliente: ' . mysqli_error($conn);
    header("Location: registrar_cliente.php?error=" . urlencode($error));
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>
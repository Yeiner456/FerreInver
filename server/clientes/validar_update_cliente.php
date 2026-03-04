<?php

session_start();
require_once 'conexion.php';

// Verificar que sea POST
if($_SERVER['REQUEST_METHOD'] != 'POST'){
    header("Location: index_clientes.php");
    exit;
}

// Verificar documento
if(!isset($_GET['documento']) || !is_numeric($_GET['documento'])){
    die('Documento inválido');
}

$documento = $_GET['documento'];

// Validar que todos los campos obligatorios estén presentes
if(
    empty($_POST['id_tipo_de_usuario']) ||
    empty($_POST['nombre']) ||
    empty($_POST['correo']) ||
    empty($_POST['estado'])
){
    $error = 'Todos los campos obligatorios deben estar llenos.';
    header("Location: update_cliente.php?documento=$documento&error=" . urlencode($error));
    exit;
}

// Obtener y limpiar datos
$id_tipo_de_usuario = trim($_POST['id_tipo_de_usuario']);
$nombre             = trim($_POST['nombre']);
$correo             = trim($_POST['correo']);
$estado             = trim($_POST['estado']);
$password           = isset($_POST['password']) ? trim($_POST['password']) : '';
$confirmar_password = isset($_POST['confirmar_password']) ? trim($_POST['confirmar_password']) : '';

// Validar que el id_tipo_de_usuario sea numérico
if(!is_numeric($id_tipo_de_usuario) || $id_tipo_de_usuario <= 0){
    $error = 'El tipo de usuario no es válido.';
    header("Location: update_cliente.php?documento=$documento&error=" . urlencode($error));
    exit;
}

// Verificar que el id_tipo_de_usuario exista en la tabla tipos_usuarios
$stmt_tipo = mysqli_prepare($conn, "SELECT id_tipo_de_usuario FROM tipos_usuarios WHERE id_tipo_de_usuario = ?");
mysqli_stmt_bind_param($stmt_tipo, 'i', $id_tipo_de_usuario);
mysqli_stmt_execute($stmt_tipo);
$result_tipo = mysqli_stmt_get_result($stmt_tipo);
if(mysqli_num_rows($result_tipo) == 0){
    $error = 'El tipo de usuario seleccionado no existe.';
    mysqli_stmt_close($stmt_tipo);
    header("Location: update_cliente.php?documento=$documento&error=" . urlencode($error));
    exit;
}
mysqli_stmt_close($stmt_tipo);

// Validar nombre (solo letras y espacios)
if(!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/", $nombre)){
    $error = 'El nombre solo puede contener letras y espacios.';
    header("Location: update_cliente.php?documento=$documento&error=" . urlencode($error));
    exit;
}

// Validar longitud del nombre
if(strlen($nombre) > 30){
    $error = 'El nombre no puede exceder 30 caracteres.';
    header("Location: update_cliente.php?documento=$documento&error=" . urlencode($error));
    exit;
}

// Validar formato de correo
if(!filter_var($correo, FILTER_VALIDATE_EMAIL)){
    $error = 'El formato del correo electrónico no es válido.';
    header("Location: update_cliente.php?documento=$documento&error=" . urlencode($error));
    exit;
}

// Validar longitud del correo
if(strlen($correo) > 50){
    $error = 'El correo no puede exceder 50 caracteres.';
    header("Location: update_cliente.php?documento=$documento&error=" . urlencode($error));
    exit;
}

// Verificar si el correo ya existe en otro cliente
$stmt_check_correo = mysqli_prepare($conn, "SELECT documento FROM clientes WHERE correo = ? AND documento != ?");
mysqli_stmt_bind_param($stmt_check_correo, 'si', $correo, $documento);
mysqli_stmt_execute($stmt_check_correo);
$result_check_correo = mysqli_stmt_get_result($stmt_check_correo);
if(mysqli_num_rows($result_check_correo) > 0){
    $error = 'El correo electrónico ya está registrado en otro cliente.';
    mysqli_stmt_close($stmt_check_correo);
    header("Location: update_cliente.php?documento=$documento&error=" . urlencode($error));
    exit;
}
mysqli_stmt_close($stmt_check_correo);

// Validar estado (solo los valores del ENUM)
if($estado != 'activo' && $estado != 'inactivo'){
    $error = 'El estado de inicio de sesión no es válido.';
    header("Location: update_cliente.php?documento=$documento&error=" . urlencode($error));
    exit;
}

// Validar contraseña si se está intentando cambiar
$actualizar_password = false;
$password_hash = '';

if(!empty($password) || !empty($confirmar_password)){
    
    if(empty($password) || empty($confirmar_password)){
        $error = 'Debe llenar ambos campos de contraseña para cambiarla.';
        header("Location: update_cliente.php?documento=$documento&error=" . urlencode($error));
        exit;
    }
    
    if($password !== $confirmar_password){
        $error = 'Las contraseñas no coinciden.';
        header("Location: update_cliente.php?documento=$documento&error=" . urlencode($error));
        exit;
    }
    
    if(strlen($password) < 6){
        $error = 'La contraseña debe tener al menos 6 caracteres.';
        header("Location: update_cliente.php?documento=$documento&error=" . urlencode($error));
        exit;
    }
    
    if(!preg_match("/[a-zA-Z]/", $password) || !preg_match("/[0-9]/", $password)){
        $error = 'La contraseña debe contener al menos una letra y un número.';
        header("Location: update_cliente.php?documento=$documento&error=" . urlencode($error));
        exit;
    }
    
    $actualizar_password = true;
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
}

// Actualizar en la base de datos
if($actualizar_password){
    $stmt = mysqli_prepare($conn, "UPDATE clientes SET id_tipo_de_usuario = ?, password_hash = ?, nombre = ?, correo = ?, estado_inicio_sesion = ? WHERE documento = ?");
    mysqli_stmt_bind_param($stmt, 'issssi', $id_tipo_de_usuario, $password_hash, $nombre, $correo, $estado, $documento);
} else {
    $stmt = mysqli_prepare($conn, "UPDATE clientes SET id_tipo_de_usuario = ?, nombre = ?, correo = ?, estado_inicio_sesion = ? WHERE documento = ?");
    mysqli_stmt_bind_param($stmt, 'isssi', $id_tipo_de_usuario, $nombre, $correo, $estado, $documento);
}

if(mysqli_stmt_execute($stmt)){
    $success = 'Cliente actualizado exitosamente.';
    header("Location: index_clientes.php?success=" . urlencode($success));
} else {
    $error = 'Error al actualizar el cliente: ' . mysqli_error($conn);
    header("Location: update_cliente.php?documento=$documento&error=" . urlencode($error));
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
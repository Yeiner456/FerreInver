<?php

session_start();
require_once 'conexion.php';

// Verificar que sea POST
if($_SERVER['REQUEST_METHOD'] != 'POST'){
    header("Location: index_proveedores.php");
    exit;
}

// Verificar NIT
if(!isset($_GET['nit']) || !is_numeric($_GET['nit'])){
    die('NIT inválido');
}

$nit = $_GET['nit'];

// Validar que todos los campos estén presentes
if(
    empty($_POST['correo']) ||
    empty($_POST['direccion']) ||
    empty($_POST['telefono']) ||
    empty($_POST['estado'])
){
    $error = 'Todos los campos son obligatorios.';
    header("Location: update_proveedor.php?nit=$nit&error=" . urlencode($error));
    exit;
}

// Obtener y limpiar datos
$correo = trim($_POST['correo']);
$direccion = trim($_POST['direccion']);
$telefono = trim($_POST['telefono']);
$estado = trim($_POST['estado']);

// Validar formato de correo
if(!filter_var($correo, FILTER_VALIDATE_EMAIL)){
    $error = 'El formato del correo electrónico no es válido.';
    header("Location: update_proveedor.php?nit=$nit&error=" . urlencode($error));
    exit;
}

// Validar longitud del correo
if(strlen($correo) > 80){
    $error = 'El correo no puede exceder 80 caracteres.';
    header("Location: update_proveedor.php?nit=$nit&error=" . urlencode($error));
    exit;
}

// Verificar si el correo ya existe en otro proveedor
$stmt_check_correo = mysqli_prepare($conn, "SELECT nit_proveedor FROM proveedores WHERE correo = ? AND nit_proveedor != ?");
mysqli_stmt_bind_param($stmt_check_correo, 'si', $correo, $nit);
mysqli_stmt_execute($stmt_check_correo);
$result_check_correo = mysqli_stmt_get_result($stmt_check_correo);

if(mysqli_num_rows($result_check_correo) > 0){
    $error = 'El correo electrónico ya está registrado en otro proveedor.';
    mysqli_stmt_close($stmt_check_correo);
    header("Location: update_proveedor.php?nit=$nit&error=" . urlencode($error));
    exit;
}
mysqli_stmt_close($stmt_check_correo);

// Validar dirección
if(strlen($direccion) > 80){
    $error = 'La dirección no puede exceder 80 caracteres.';
    header("Location: update_proveedor.php?nit=$nit&error=" . urlencode($error));
    exit;
}

if(strlen($direccion) == 0){
    $error = 'La dirección no puede estar vacía.';
    header("Location: update_proveedor.php?nit=$nit&error=" . urlencode($error));
    exit;
}

// Validar teléfono (solo números, espacios, guiones y paréntesis)
if(!preg_match("/^[0-9\s\-\(\)\+]+$/", $telefono)){
    $error = 'El teléfono solo puede contener números, espacios, guiones, paréntesis y el símbolo +.';
    header("Location: update_proveedor.php?nit=$nit&error=" . urlencode($error));
    exit;
}

// Validar longitud del teléfono
if(strlen($telefono) > 20){
    $error = 'El teléfono no puede exceder 20 caracteres.';
    header("Location: update_proveedor.php?nit=$nit&error=" . urlencode($error));
    exit;
}

// Validar que el teléfono tenga al menos 7 dígitos
$solo_numeros = preg_replace("/[^0-9]/", "", $telefono);
if(strlen($solo_numeros) < 7){
    $error = 'El teléfono debe tener al menos 7 dígitos.';
    header("Location: update_proveedor.php?nit=$nit&error=" . urlencode($error));
    exit;
}

// Validar estado
if($estado != 'Activo' && $estado != 'Inactivo'){
    $error = 'El estado no es válido.';
    header("Location: update_proveedor.php?nit=$nit&error=" . urlencode($error));
    exit;
}

// Actualizar en la base de datos
$stmt = mysqli_prepare($conn, "UPDATE proveedores SET correo = ?, direccion = ?, telefono = ?, estado = ? WHERE nit_proveedor = ?");
mysqli_stmt_bind_param($stmt, 'ssssi', $correo, $direccion, $telefono, $estado, $nit);

if(mysqli_stmt_execute($stmt)){
    $success = 'Proveedor actualizado exitosamente.';
    header("Location: index_proveedores.php?success=" . urlencode($success));
} else {
    $error = 'Error al actualizar el proveedor: ' . mysqli_error($conn);
    header("Location: update_proveedor.php?nit=$nit&error=" . urlencode($error));
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>
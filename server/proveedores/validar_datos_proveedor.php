<?php

session_start();
require_once 'conexion.php';

// Verificar que sea POST
if($_SERVER['REQUEST_METHOD'] != 'POST'){
    header("Location: registrar_proveedor.php");
    exit;
}

// Validar que todos los campos estén presentes
if(
    empty($_POST['nit']) ||
    empty($_POST['correo']) ||
    empty($_POST['direccion']) ||
    empty($_POST['telefono']) ||
    empty($_POST['estado'])
){
    $error = 'Todos los campos son obligatorios.';
    header("Location: registrar_proveedor.php?error=" . urlencode($error));
    exit;
}

// Obtener y limpiar datos
$nit = trim($_POST['nit']);
$correo = trim($_POST['correo']);
$direccion = trim($_POST['direccion']);
$telefono = trim($_POST['telefono']);
$estado = trim($_POST['estado']);

// Validar que el NIT sea numérico
if(!is_numeric($nit) || $nit <= 0){
    $error = 'El NIT debe ser un número válido mayor a 0.';
    header("Location: registrar_proveedor.php?error=" . urlencode($error));
    exit;
}

// Validar que el NIT no tenga más de 11 dígitos
if(strlen($nit) > 11){
    $error = 'El NIT no puede tener más de 11 dígitos.';
    header("Location: registrar_proveedor.php?error=" . urlencode($error));
    exit;
}

// Verificar si el NIT ya existe
$stmt_check_nit = mysqli_prepare($conn, "SELECT nit_proveedor FROM proveedores WHERE nit_proveedor = ?");
mysqli_stmt_bind_param($stmt_check_nit, 'i', $nit);
mysqli_stmt_execute($stmt_check_nit);
$result_check_nit = mysqli_stmt_get_result($stmt_check_nit);

if(mysqli_num_rows($result_check_nit) > 0){
    $error = 'El NIT ya está registrado.';
    mysqli_stmt_close($stmt_check_nit);
    header("Location: registrar_proveedor.php?error=" . urlencode($error));
    exit;
}
mysqli_stmt_close($stmt_check_nit);

// Validar formato de correo
if(!filter_var($correo, FILTER_VALIDATE_EMAIL)){
    $error = 'El formato del correo electrónico no es válido.';
    header("Location: registrar_proveedor.php?error=" . urlencode($error));
    exit;
}

// Validar longitud del correo
if(strlen($correo) > 80){
    $error = 'El correo no puede exceder 80 caracteres.';
    header("Location: registrar_proveedor.php?error=" . urlencode($error));
    exit;
}

// Verificar si el correo ya existe
$stmt_check_correo = mysqli_prepare($conn, "SELECT correo FROM proveedores WHERE correo = ?");
mysqli_stmt_bind_param($stmt_check_correo, 's', $correo);
mysqli_stmt_execute($stmt_check_correo);
$result_check_correo = mysqli_stmt_get_result($stmt_check_correo);

if(mysqli_num_rows($result_check_correo) > 0){
    $error = 'El correo electrónico ya está registrado.';
    mysqli_stmt_close($stmt_check_correo);
    header("Location: registrar_proveedor.php?error=" . urlencode($error));
    exit;
}
mysqli_stmt_close($stmt_check_correo);

// Validar dirección
if(strlen($direccion) > 80){
    $error = 'La dirección no puede exceder 80 caracteres.';
    header("Location: registrar_proveedor.php?error=" . urlencode($error));
    exit;
}

if(strlen($direccion) == 0){
    $error = 'La dirección no puede estar vacía.';
    header("Location: registrar_proveedor.php?error=" . urlencode($error));
    exit;
}

// Validar teléfono (solo números, espacios, guiones y paréntesis)
if(!preg_match("/^[0-9\s\-\(\)\+]+$/", $telefono)){
    $error = 'El teléfono solo puede contener números, espacios, guiones, paréntesis y el símbolo +.';
    header("Location: registrar_proveedor.php?error=" . urlencode($error));
    exit;
}

// Validar longitud del teléfono
if(strlen($telefono) > 20){
    $error = 'El teléfono no puede exceder 20 caracteres.';
    header("Location: registrar_proveedor.php?error=" . urlencode($error));
    exit;
}

// Validar que el teléfono tenga al menos 7 dígitos
$solo_numeros = preg_replace("/[^0-9]/", "", $telefono);
if(strlen($solo_numeros) < 7){
    $error = 'El teléfono debe tener al menos 7 dígitos.';
    header("Location: registrar_proveedor.php?error=" . urlencode($error));
    exit;
}

// Validar estado
if($estado != 'Activo' && $estado != 'Inactivo'){
    $error = 'El estado no es válido.';
    header("Location: registrar_proveedor.php?error=" . urlencode($error));
    exit;
}

// Insertar en la base de datos
$stmt = mysqli_prepare($conn, "INSERT INTO proveedores (nit_proveedor, correo, direccion, telefono, estado) VALUES (?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, 'issss', $nit, $correo, $direccion, $telefono, $estado);

if(mysqli_stmt_execute($stmt)){
    $success = 'Proveedor registrado exitosamente.';
    header("Location: index_proveedores.php?success=" . urlencode($success));
} else {
    $error = 'Error al registrar el proveedor: ' . mysqli_error($conn);
    header("Location: registrar_proveedor.php?error=" . urlencode($error));
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>
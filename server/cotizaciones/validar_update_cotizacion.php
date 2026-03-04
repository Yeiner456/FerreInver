<?php

require_once 'conexion.php';

// Verificar que sea POST
if($_SERVER['REQUEST_METHOD'] != 'POST'){
    header("Location: index_cotizaciones.php");
    exit;
}

// Verificar ID
if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    die('ID de cotización inválido.');
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

// Validar campos obligatorios
if(
    empty($_POST['cliente_id']) ||
    empty($_POST['invernadero_id']) ||
    empty($_POST['largo']) ||
    empty($_POST['ancho']) ||
    empty($_POST['metros_cuadrados']) ||
    empty($_POST['valor_m2']) ||
    empty($_POST['total']) ||
    empty($_POST['estado'])
){
    $error = 'Todos los campos son obligatorios.';
    header("Location: update_cotizacion.php?id=$id&error=" . urlencode($error));
    exit;
}

// Obtener y limpiar datos
$cliente_id       = trim($_POST['cliente_id']);
$invernadero_id   = trim($_POST['invernadero_id']);
$largo            = trim($_POST['largo']);
$ancho            = trim($_POST['ancho']);
$metros_cuadrados = trim($_POST['metros_cuadrados']);
$valor_m2         = trim($_POST['valor_m2']);
$total            = trim($_POST['total']);
$estado           = trim($_POST['estado']);

// Validar numéricos y positivos
if(!is_numeric($cliente_id) || $cliente_id <= 0){
    $error = 'El cliente seleccionado no es válido.';
    header("Location: update_cotizacion.php?id=$id&error=" . urlencode($error));
    exit;
}
if(!is_numeric($invernadero_id) || $invernadero_id <= 0){
    $error = 'El invernadero seleccionado no es válido.';
    header("Location: update_cotizacion.php?id=$id&error=" . urlencode($error));
    exit;
}
if(!is_numeric($largo) || $largo <= 0){
    $error = 'El largo debe ser un número mayor a 0.';
    header("Location: update_cotizacion.php?id=$id&error=" . urlencode($error));
    exit;
}
if(!is_numeric($ancho) || $ancho <= 0){
    $error = 'El ancho debe ser un número mayor a 0.';
    header("Location: update_cotizacion.php?id=$id&error=" . urlencode($error));
    exit;
}
if(!is_numeric($metros_cuadrados) || $metros_cuadrados <= 0){
    $error = 'Los metros cuadrados deben ser un valor positivo.';
    header("Location: update_cotizacion.php?id=$id&error=" . urlencode($error));
    exit;
}
if(!is_numeric($valor_m2) || $valor_m2 <= 0){
    $error = 'El valor por m² debe ser un número mayor a 0.';
    header("Location: update_cotizacion.php?id=$id&error=" . urlencode($error));
    exit;
}
if(!is_numeric($total) || $total <= 0){
    $error = 'El total debe ser un número mayor a 0.';
    header("Location: update_cotizacion.php?id=$id&error=" . urlencode($error));
    exit;
}

// Validar consistencia: metros_cuadrados = largo * ancho
$m2_calculado = round($largo * $ancho, 2);
if(abs($m2_calculado - round($metros_cuadrados, 2)) > 0.01){
    $error = 'Los metros cuadrados no coinciden con largo × ancho.';
    header("Location: update_cotizacion.php?id=$id&error=" . urlencode($error));
    exit;
}

// Validar estado
$estados_validos = ['pendiente', 'aprobada', 'rechazada'];
if(!in_array($estado, $estados_validos)){
    $error = 'El estado no es válido.';
    header("Location: update_cotizacion.php?id=$id&error=" . urlencode($error));
    exit;
}

// Verificar que el cliente exista
$stmt_cl = mysqli_prepare($conn, "SELECT documento FROM clientes WHERE documento = ?");
mysqli_stmt_bind_param($stmt_cl, 'i', $cliente_id);
mysqli_stmt_execute($stmt_cl);
$result_cl = mysqli_stmt_get_result($stmt_cl);
if(mysqli_num_rows($result_cl) == 0){
    $error = 'El cliente seleccionado no existe.';
    mysqli_stmt_close($stmt_cl);
    header("Location: update_cotizacion.php?id=$id&error=" . urlencode($error));
    exit;
}
mysqli_stmt_close($stmt_cl);

// Verificar que el invernadero exista y obtener su precio actual
$stmt_inv = mysqli_prepare($conn, "SELECT precio_m2 FROM invernaderos WHERE id_invernadero = ?");
mysqli_stmt_bind_param($stmt_inv, 'i', $invernadero_id);
mysqli_stmt_execute($stmt_inv);
$result_inv = mysqli_stmt_get_result($stmt_inv);
if(mysqli_num_rows($result_inv) == 0){
    $error = 'El invernadero seleccionado no existe.';
    mysqli_stmt_close($stmt_inv);
    header("Location: update_cotizacion.php?id=$id&error=" . urlencode($error));
    exit;
}
$inv_data = mysqli_fetch_assoc($result_inv);
mysqli_stmt_close($stmt_inv);

// Validar consistencia del valor_m2 con el precio del invernadero
if(abs(round($inv_data['precio_m2'], 2) - round($valor_m2, 2)) > 0.01){
    $error = 'El valor por m² no coincide con el precio del invernadero seleccionado.';
    header("Location: update_cotizacion.php?id=$id&error=" . urlencode($error));
    exit;
}

// Validar consistencia del total
$total_calculado = round($metros_cuadrados * $valor_m2, 2);
if(abs($total_calculado - round($total, 2)) > 0.01){
    $error = 'El total no coincide con metros cuadrados × valor m².';
    header("Location: update_cotizacion.php?id=$id&error=" . urlencode($error));
    exit;
}

// Actualizar en la base de datos
$stmt = mysqli_prepare($conn, "UPDATE cotizaciones SET cliente_id = ?, invernadero_id = ?, largo = ?, ancho = ?, metros_cuadrados = ?, valor_m2 = ?, total = ?, estado = ? WHERE id_cotizacion = ?");
mysqli_stmt_bind_param($stmt, 'iidddddsi', $cliente_id, $invernadero_id, $largo, $ancho, $metros_cuadrados, $valor_m2, $total, $estado, $id);

if(mysqli_stmt_execute($stmt)){
    $success = 'Cotización actualizada exitosamente.';
    header("Location: index_cotizaciones.php?success=" . urlencode($success));
} else {
    $error = 'Error al actualizar la cotización: ' . mysqli_error($conn);
    header("Location: update_cotizacion.php?id=$id&error=" . urlencode($error));
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
<?php

session_start();
require_once 'conexion.php';

// Verificar que sea POST
if($_SERVER['REQUEST_METHOD'] != 'POST'){
    header("Location: index_pedidos.php");
    exit;
}

// Verificar ID
if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    die('ID inválido');
}

$id = $_GET['id'];

// Validar que todos los campos estén presentes
if(
    empty($_POST['id_cliente']) ||
    empty($_POST['medio_pago']) ||
    empty($_POST['estado_pedido'])
){
    $error = 'Todos los campos son obligatorios.';
    header("Location: update_pedido.php?id=$id&error=" . urlencode($error));
    exit;
}

// Obtener y limpiar datos
$id_cliente = trim($_POST['id_cliente']);
$medio_pago = trim($_POST['medio_pago']);
$estado_pedido = trim($_POST['estado_pedido']);

// Validar que el ID del cliente sea numérico
if(!is_numeric($id_cliente) || $id_cliente <= 0){
    $error = 'El ID del cliente debe ser un número válido.';
    header("Location: update_pedido.php?id=$id&error=" . urlencode($error));
    exit;
}

// Verificar que el cliente exista
$stmt_check_cliente = mysqli_prepare($conn, "SELECT Documento FROM clientes WHERE Documento = ?");
mysqli_stmt_bind_param($stmt_check_cliente, 'i', $id_cliente);
mysqli_stmt_execute($stmt_check_cliente);
$result_check_cliente = mysqli_stmt_get_result($stmt_check_cliente);

if(mysqli_num_rows($result_check_cliente) == 0){
    $error = 'El cliente seleccionado no existe.';
    mysqli_stmt_close($stmt_check_cliente);
    header("Location: update_pedido.php?id=$id&error=" . urlencode($error));
    exit;
}
mysqli_stmt_close($stmt_check_cliente);

// Validar medio de pago
if(strlen($medio_pago) > 30){
    $error = 'El medio de pago no puede exceder 30 caracteres.';
    header("Location: update_pedido.php?id=$id&error=" . urlencode($error));
    exit;
}

if(strlen($medio_pago) == 0){
    $error = 'El medio de pago no puede estar vacío.';
    header("Location: update_pedido.php?id=$id&error=" . urlencode($error));
    exit;
}

// Validar estado del pedido (ENUM)
$estados_validos = ['Pendiente', 'Recibido', 'Listo para recibir', 'Cancelado'];
if(!in_array($estado_pedido, $estados_validos)){
    $error = 'El estado del pedido no es válido.';
    header("Location: update_pedido.php?id=$id&error=" . urlencode($error));
    exit;
}

// Actualizar en la base de datos
$stmt = mysqli_prepare($conn, "UPDATE pedidos SET id_cliente = ?, Medio_pago = ?, Estado_pedido = ? WHERE id_pedido = ?");
mysqli_stmt_bind_param($stmt, 'issi', $id_cliente, $medio_pago, $estado_pedido, $id);

if(mysqli_stmt_execute($stmt)){
    $success = 'Pedido actualizado exitosamente.';
    header("Location: index_pedidos.php?success=" . urlencode($success));
} else {
    $error = 'Error al actualizar el pedido: ' . mysqli_error($conn);
    header("Location: update_pedido.php?id=$id&error=" . urlencode($error));
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>
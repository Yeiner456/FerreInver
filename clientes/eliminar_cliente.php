<?php

require_once 'conexion.php';

// Validar que el documento esté presente y sea numérico
if(!isset($_GET['documento']) || !is_numeric($_GET['documento'])){
    $error = 'Documento inválido.';
    header("Location: index_clientes.php?error=" . urlencode($error));
    exit;
}

$documento = $_GET['documento'];

// Verificar que el cliente exista antes de eliminar
$stmt_check = mysqli_prepare($conn, "SELECT Documento FROM clientes WHERE Documento = ?");
mysqli_stmt_bind_param($stmt_check, 'i', $documento);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

if(mysqli_num_rows($result_check) == 0){
    $error = 'El cliente no existe.';
    mysqli_stmt_close($stmt_check);
    header("Location: index_clientes.php?error=" . urlencode($error));
    exit;
}
mysqli_stmt_close($stmt_check);

// Eliminar el cliente usando prepared statement
$stmt = mysqli_prepare($conn, "DELETE FROM clientes WHERE Documento = ?");
mysqli_stmt_bind_param($stmt, 'i', $documento);

if(mysqli_stmt_execute($stmt)){
    $success = 'Cliente eliminado exitosamente.';
    header("Location: index_clientes.php?success=" . urlencode($success));
} else {
    $error = 'Error al eliminar el cliente: ' . mysqli_error($conn);
    header("Location: index_clientes.php?error=" . urlencode($error));
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>
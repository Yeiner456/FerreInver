<?php

require_once 'conexion.php';

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    die('ID inválido');
}

$id = $_GET['id'];
$stmt = mysqli_prepare($conn, "SELECT p.*, c.Nombre AS nombre_cliente 
                                FROM pedidos p 
                                INNER JOIN clientes c ON p.id_cliente = c.Documento 
                                WHERE p.id_pedido = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$pedido = mysqli_fetch_assoc($result);

if(!$pedido){
    die('Pedido no encontrado');
}

// Obtener todos los clientes para el select
$resultado_clientes = mysqli_query($conn, "SELECT Documento, Nombre, Correo FROM clientes WHERE EstadoInicioSesion = 'Activo' ORDER BY Nombre ASC");

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Pedido</title>
</head>
<body>
    <h1>Editar Pedido</h1>
    
    <?php if(isset($_GET['error'])): ?>
        <p style="color: red;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>
    
    <form action="validar_update_pedido.php?id=<?= $pedido['id_pedido'] ?>" method="POST">
        
        <label>ID Pedido (No editable): </label><br>
        <input type="text" value="<?= htmlspecialchars($pedido['id_pedido']) ?>" disabled><br><br>

        <label>Fecha y Hora (No editable): </label><br>
        <input type="text" value="<?= htmlspecialchars($pedido['fecha_hora']) ?>" disabled><br><br>

        <label>Cliente: </label><br>
        <select name="id_cliente" required>
            <?php while ($cliente = mysqli_fetch_assoc($resultado_clientes)) : ?>
            <option value="<?= $cliente['Documento'] ?>" <?= $cliente['Documento'] == $pedido['id_cliente'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cliente['Nombre']) ?> - <?= htmlspecialchars($cliente['Correo']) ?>
            </option>
            <?php endwhile; ?>
        </select><br><br>

        <label>Medio de Pago: </label><br>
        <select name="medio_pago" required>
            <option value="Efectivo" <?= $pedido['Medio_pago'] == 'Efectivo' ? 'selected' : '' ?>>Efectivo</option>
            <option value="Tarjeta Débito" <?= $pedido['Medio_pago'] == 'Tarjeta Débito' ? 'selected' : '' ?>>Tarjeta Débito</option>
            <option value="Tarjeta Crédito" <?= $pedido['Medio_pago'] == 'Tarjeta Crédito' ? 'selected' : '' ?>>Tarjeta Crédito</option>
            <option value="Transferencia" <?= $pedido['Medio_pago'] == 'Transferencia' ? 'selected' : '' ?>>Transferencia</option>
            <option value="PSE" <?= $pedido['Medio_pago'] == 'PSE' ? 'selected' : '' ?>>PSE</option>
            <option value="Nequi" <?= $pedido['Medio_pago'] == 'Nequi' ? 'selected' : '' ?>>Nequi</option>
            <option value="Daviplata" <?= $pedido['Medio_pago'] == 'Daviplata' ? 'selected' : '' ?>>Daviplata</option>
        </select><br><br>

        <label>Estado del Pedido: </label><br>
        <select name="estado_pedido" required>
            <option value="Pendiente" <?= $pedido['Estado_pedido'] == 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
            <option value="Recibido" <?= $pedido['Estado_pedido'] == 'Recibido' ? 'selected' : '' ?>>Recibido</option>
            <option value="Listo para recibir" <?= $pedido['Estado_pedido'] == 'Listo para recibir' ? 'selected' : '' ?>>Listo para recibir</option>
            <option value="Cancelado" <?= $pedido['Estado_pedido'] == 'Cancelado' ? 'selected' : '' ?>>Cancelado</option>
        </select><br><br>

        <button type="submit">Actualizar Pedido</button>
    </form>
    <br>
    <a href="index_pedidos.php">Volver a la Lista</a>
</body>
</html>
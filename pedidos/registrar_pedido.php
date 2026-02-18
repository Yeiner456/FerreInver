<?php

require_once 'conexion.php';

// Obtener todos los clientes para el select
$resultado_clientes = mysqli_query($conn, "SELECT Documento, Nombre, Correo FROM clientes WHERE EstadoInicioSesion = 'Activo' ORDER BY Nombre ASC");

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Pedido</title>
</head>
<body>
    <h1>Registrar Pedido</h1>
    
    <?php if(isset($_GET['error'])): ?>
        <p style="color: red;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>
    
    <form action="validar_datos_pedido.php" method="POST">
        <label>Cliente: </label><br>
        <select name="id_cliente" required>
            <option value="">Seleccione un cliente</option>
            <?php while ($cliente = mysqli_fetch_assoc($resultado_clientes)) : ?>
            <option value="<?= $cliente['Documento'] ?>">
                <?= htmlspecialchars($cliente['Nombre']) ?> - <?= htmlspecialchars($cliente['Correo']) ?>
            </option>
            <?php endwhile; ?>
        </select><br><br>

        <label>Medio de Pago: </label><br>
        <select name="medio_pago" required>
            <option value="">Seleccione un medio de pago</option>
            <option value="Efectivo">Efectivo</option>
            <option value="Tarjeta Débito">Tarjeta Débito</option>
            <option value="Tarjeta Crédito">Tarjeta Crédito</option>
            <option value="Transferencia">Transferencia</option>
            <option value="PSE">PSE</option>
            <option value="Nequi">Nequi</option>
            <option value="Daviplata">Daviplata</option>
        </select><br><br>

        <label>Estado del Pedido: </label><br>
        <select name="estado_pedido" required>
            <option value="Pendiente" selected>Pendiente</option>
            <option value="Recibido">Recibido</option>
            <option value="Listo para recibir">Listo para recibir</option>
            <option value="Cancelado">Cancelado</option>
        </select><br><br>

        <button type="submit">Registrar Pedido</button><br>

    </form><br>

    <a href="index_pedidos.php">Volver a la Lista</a>

</body>
</html>
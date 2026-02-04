<?php

require_once 'conexion.php';
$resultado = mysqli_query($conn, "SELECT p.*, c.Nombre AS nombre_cliente, c.Correo 
                                   FROM pedidos p 
                                   INNER JOIN clientes c ON p.id_cliente = c.Documento 
                                   ORDER BY p.fecha_hora DESC");

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos Registrados</title>
</head>

<body>
    <h1>Pedidos Registrados</h1>
    
    <a href="registrar_pedido.php">Registrar Nuevo Pedido</a><br><br>
    
    <?php if(isset($_GET['success'])): ?>
        <p style="color: green;"><?= htmlspecialchars($_GET['success']) ?></p>
    <?php endif; ?>
    
    <?php if(isset($_GET['error'])): ?>
        <p style="color: red;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>
    
    <table border="1" cellpadding="10">
        <tr>
            <th>ID Pedido</th>
            <th>Cliente</th>
            <th>Correo</th>
            <th>Fecha y Hora</th>
            <th>Medio de Pago</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
        <?php while ($fila = mysqli_fetch_assoc($resultado)) : ?>
        <tr>
            <td><?= htmlspecialchars($fila['id_pedido']) ?></td>
            <td><?= htmlspecialchars($fila['nombre_cliente']) ?></td>
            <td><?= htmlspecialchars($fila['Correo']) ?></td>
            <td><?= htmlspecialchars($fila['fecha_hora']) ?></td>
            <td><?= htmlspecialchars($fila['Medio_pago']) ?></td>
            <td><?= htmlspecialchars($fila['Estado_pedido']) ?></td>
            <td>
                <a href="update_pedido.php?id=<?= $fila['id_pedido'] ?>">Editar</a>
                <a href="eliminar_pedido.php?id=<?= $fila['id_pedido'] ?>" onclick="return confirm('¿Está seguro de eliminar este pedido?')">Eliminar</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <br>
    <a href="index.php">Volver al Inicio</a>
</body>

</html>
<?php
require_once 'conexion.php';

// Consulta con JOIN para obtener datos combinados
$sql = "SELECT 
            pp.id,
            p.nombre AS nombre_producto,
            pe.id_pedido,
            pp.descripcion,
            pp.cantidad
        FROM Productos_Pedidos pp
        INNER JOIN Productos p ON pp.id_producto = p.ID_producto
        INNER JOIN Pedidos pe ON pp.id_pedido = pe.id_pedido
        ORDER BY pp.id DESC";

$resultado = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productos en Pedidos</title>

</head>
<body>

<h1>Listado de Productos en Pedidos</h1>

<table>
    <tr>
        <th>ID</th>
        <th>Producto</th>
        <th>Pedido</th>
        <th>Descripción</th>
        <th>Cantidad</th>
        <th>Acciones</th>
    </tr>

    <?php if (mysqli_num_rows($resultado) > 0): ?>
        <?php while ($fila = mysqli_fetch_assoc($resultado)): ?>
            <tr>
                <td><?= htmlspecialchars($fila['id']) ?></td>
                <td><?= htmlspecialchars($fila['nombre_producto']) ?></td>
                <td>#<?= htmlspecialchars($fila['id_pedido']) ?></td>
                <td><?= htmlspecialchars($fila['descripcion']) ?></td>
                <td><?= htmlspecialchars($fila['cantidad']) ?></td>
                <td class="acciones">
                    <a href="editarProductosPedidos.php?id=<?= $fila['id'] ?>" class="btn editar">Editar</a>
                    <a href="eliminar.php?id=<?= $fila['id'] ?>" class="btn eliminar" onclick="return confirm('¿Seguro que deseas eliminar este registro?');">Eliminar</a>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="6">No hay registros en la tabla Productos_Pedidos</td></tr>
    <?php endif; ?>

</table>

<div style="text-align:center;">
    <a href="registrarProductoPedido.php">Registrar nuevo</a> |
    <a href="index.php">Volver al inicio</a>
</div>

</body>
</html>

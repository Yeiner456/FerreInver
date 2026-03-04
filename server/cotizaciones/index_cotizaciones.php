<?php

require_once 'conexion.php';
$resultado = mysqli_query($conn, "
    SELECT co.id_cotizacion, co.largo, co.ancho, co.metros_cuadrados,
           co.valor_m2, co.total, co.fecha, co.estado,
           cl.nombre AS cliente_nombre,
           inv.nombre AS invernadero_nombre
    FROM cotizaciones co
    INNER JOIN clientes cl ON co.cliente_id = cl.documento
    INNER JOIN invernaderos inv ON co.invernadero_id = inv.id_invernadero
    ORDER BY co.fecha DESC
");

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotizaciones</title>
</head>
<body>
    <h1>Cotizaciones</h1>

    <a href="registrar_cotizacion.php">Nueva Cotización</a><br><br>

    <?php if(isset($_GET['success'])): ?>
        <p style="color: green;"><?= htmlspecialchars($_GET['success']) ?></p>
    <?php endif; ?>

    <?php if(isset($_GET['error'])): ?>
        <p style="color: red;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>

    <table border="1" cellpadding="10">
        <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Invernadero</th>
            <th>Largo (m)</th>
            <th>Ancho (m)</th>
            <th>m²</th>
            <th>Valor m²</th>
            <th>Total</th>
            <th>Fecha</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
        <?php while ($fila = mysqli_fetch_assoc($resultado)) : ?>
        <tr>
            <td><?= htmlspecialchars($fila['id_cotizacion']) ?></td>
            <td><?= htmlspecialchars($fila['cliente_nombre']) ?></td>
            <td><?= htmlspecialchars($fila['invernadero_nombre']) ?></td>
            <td><?= number_format($fila['largo'], 2) ?></td>
            <td><?= number_format($fila['ancho'], 2) ?></td>
            <td><?= number_format($fila['metros_cuadrados'], 2) ?></td>
            <td>$ <?= number_format($fila['valor_m2'], 2) ?></td>
            <td>$ <?= number_format($fila['total'], 2) ?></td>
            <td><?= htmlspecialchars($fila['fecha']) ?></td>
            <td><?= htmlspecialchars($fila['estado']) ?></td>
            <td>
                <a href="update_cotizacion.php?id=<?= $fila['id_cotizacion'] ?>">Editar</a>
                <a href="eliminar_cotizacion.php?id=<?= $fila['id_cotizacion'] ?>" onclick="return confirm('¿Está seguro de eliminar esta cotización?')">Eliminar</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <br>
    <a href="index.php">Volver al Inicio</a>
</body>
</html>
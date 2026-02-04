<?php

require_once 'conexion.php';
$resultado = mysqli_query($conn, "SELECT s.*, p.nombre AS nombre_producto, p.Precio 
                                   FROM stocks s 
                                   INNER JOIN productos p ON s.ID_producto = p.ID_producto 
                                   ORDER BY s.id_stock DESC");

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stocks Registrados</title>
</head>

<body>
    <h1>Stocks Registrados</h1>
    
    <a href="registrar_stock.php">Registrar Nuevo Stock</a><br><br>
    
    <?php if(isset($_GET['success'])): ?>
        <p style="color: green;"><?= htmlspecialchars($_GET['success']) ?></p>
    <?php endif; ?>
    
    <?php if(isset($_GET['error'])): ?>
        <p style="color: red;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>
    
    <table border="1" cellpadding="10">
        <tr>
            <th>ID Stock</th>
            <th>Producto</th>
            <th>Precio Unitario</th>
            <th>Cantidad en Stock</th>
            <th>Valor Total</th>
            <th>Acciones</th>
        </tr>
        <?php while ($fila = mysqli_fetch_assoc($resultado)) : ?>
        <tr>
            <td><?= htmlspecialchars($fila['id_stock']) ?></td>
            <td><?= htmlspecialchars($fila['nombre_producto']) ?></td>
            <td>$<?= number_format($fila['Precio'], 0, ',', '.') ?></td>
            <td><?= htmlspecialchars($fila['Cantidad']) ?></td>
            <td>$<?= number_format($fila['Precio'] * $fila['Cantidad'], 0, ',', '.') ?></td>
            <td>
                <a href="update_stock.php?id=<?= $fila['id_stock'] ?>">Editar</a>
                <a href="eliminar_stock.php?id=<?= $fila['id_stock'] ?>" onclick="return confirm('¿Está seguro de eliminar este stock?')">Eliminar</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <br>
    <a href="index.php">Volver al Inicio</a>
</body>

</html>
<?php

require_once 'conexion.php';
$resultado = mysqli_query($conn, "SELECT 
        c.ID_Compra,
        c.Cantidad,
        c.Descripcion,
        p.ID_producto,
        p.nombre AS nombre_producto,
        pr.nit_proveedor,
        pr.correo AS correo_proveedor
    FROM Compras c
    INNER JOIN Productos p ON c.ID_producto = p.ID_producto
    INNER JOIN Proveedores pr ON c.ID_proveedor = pr.nit_proveedor
");
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>compras Registrados</title>
</head>

<body>
    <h1>compras Registrados</h1>
    
    <a href="registrar_compra.php">Registrar nueva compra</a><br><br>
    
    <?php if(isset($_GET['success'])): ?>
        <p style="color: green;"><?= htmlspecialchars($_GET['success']) ?></p>
    <?php endif; ?>
    
    <?php if(isset($_GET['error'])): ?>
        <p style="color: red;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>
    
    <table border="1" cellpadding="10">
        <tr>
            <th>ID_Compra</th>
            <th>Cantidad</th>
            <th>Descripcion</th>
            <th>ID_proveedor</th>
            <th>ID_producto</th>
        </tr>
        <?php while ($fila = mysqli_fetch_assoc($resultado)) : ?>
        <tr>
            <td><?= htmlspecialchars($fila['ID_Compra']) ?></td>
            <td><?= htmlspecialchars($fila['Cantidad']) ?></td>
            <td><?= htmlspecialchars($fila['Descripcion']) ?></td>
            <td><?= htmlspecialchars($fila['ID_proveedor']) ?></td>
            <td><?= htmlspecialchars($fila['ID_producto']) ?></td>
            <td>
                <a href="update_compra.php?documento=<?= $fila['ID_Compra'] ?>">Editar</a>
                <a href="eliminar_compra.php?documento=<?= $fila['ID_Compra'] ?>" onclick="return confirm('¿Está seguro de eliminar este cliente?')">Eliminar</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <br>
    <a href="index.php">Volver al Inicio</a>
</body>

</html>
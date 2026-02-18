<?php

require_once 'conexion.php';
$resultado = mysqli_query($conn, "SELECT * FROM productos ORDER BY ID_producto DESC");

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos Registrados</title>
</head>

<body>
    <h1>Productos Registrados</h1>
    
    <a href="registrar_producto.php">Registrar Nuevo Producto</a><br><br>
    
    <?php if(isset($_GET['success'])): ?>
        <p style="color: green;"><?= htmlspecialchars($_GET['success']) ?></p>
    <?php endif; ?>
    
    <?php if(isset($_GET['error'])): ?>
        <p style="color: red;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>
    
    <table border="1" cellpadding="10">
        <tr>
            <th>ID Producto</th>
            <th>Nombre</th>
            <th>Precio</th>
            <th>Descripción</th>
            <th>Acciones</th>
        </tr>
        <?php while ($fila = mysqli_fetch_assoc($resultado)) : ?>
        <tr>
            <td><?= htmlspecialchars($fila['ID_producto']) ?></td>
            <td><?= htmlspecialchars($fila['nombre']) ?></td>
            <td>$<?= number_format($fila['Precio'], 0, ',', '.') ?></td>
            <td><?= htmlspecialchars($fila['Descripcion']) ?></td>
            <td>
                <a href="update_producto.php?id=<?= $fila['ID_producto'] ?>">Editar</a>
                <a href="eliminar_producto.php?id=<?= $fila['ID_producto'] ?>" onclick="return confirm('¿Está seguro de eliminar este producto?')">Eliminar</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <br>
    <a href="index.php">Volver al Inicio</a>
</body>

</html>
<?php

require_once 'conexion.php';
$resultado = mysqli_query($conn, "SELECT * FROM proveedores ORDER BY nit_proveedor DESC");

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proveedores Registrados</title>
</head>

<body>
    <h1>Proveedores Registrados</h1>
    
    <a href="registrar_proveedor.php">Registrar Nuevo Proveedor</a><br><br>
    
    <?php if(isset($_GET['success'])): ?>
        <p style="color: green;"><?= htmlspecialchars($_GET['success']) ?></p>
    <?php endif; ?>
    
    <?php if(isset($_GET['error'])): ?>
        <p style="color: red;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>
    
    <table border="1" cellpadding="10">
        <tr>
            <th>NIT Proveedor</th>
            <th>Estado</th>
            <th>Correo</th>
            <th>Dirección</th>
            <th>Teléfono</th>
            <th>Acciones</th>
        </tr>
        <?php while ($fila = mysqli_fetch_assoc($resultado)) : ?>
        <tr>
            <td><?= htmlspecialchars($fila['nit_proveedor']) ?></td>
            <td><?= htmlspecialchars($fila['estado']) ?></td>
            <td><?= htmlspecialchars($fila['correo']) ?></td>
            <td><?= htmlspecialchars($fila['direccion']) ?></td>
            <td><?= htmlspecialchars($fila['telefono']) ?></td>
            <td>
                <a href="update_proveedor.php?nit=<?= $fila['nit_proveedor'] ?>">Editar</a>
                <a href="eliminar_proveedor.php?nit=<?= $fila['nit_proveedor'] ?>" onclick="return confirm('¿Está seguro de eliminar este proveedor?')">Eliminar</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <br>
    <a href="index.php">Volver al Inicio</a>
</body>

</html>
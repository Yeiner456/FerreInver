<?php

require_once 'conexion.php';
$resultado = mysqli_query($conn, "SELECT * FROM invernaderos ORDER BY id_invernadero DESC");

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invernaderos Registrados</title>
</head>
<body>
    <h1>Invernaderos Registrados</h1>

    <a href="registrar_invernadero.php">Registrar Nuevo Invernadero</a><br><br>

    <?php if(isset($_GET['success'])): ?>
        <p style="color: green;"><?= htmlspecialchars($_GET['success']) ?></p>
    <?php endif; ?>

    <?php if(isset($_GET['error'])): ?>
        <p style="color: red;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>

    <table border="1" cellpadding="10">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Precio m²</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
        <?php while ($fila = mysqli_fetch_assoc($resultado)) : ?>
        <tr>
            <td><?= htmlspecialchars($fila['id_invernadero']) ?></td>
            <td><?= htmlspecialchars($fila['nombre']) ?></td>
            <td><?= htmlspecialchars($fila['descripcion']) ?></td>
            <td>$ <?= number_format($fila['precio_m2'], 2) ?></td>
            <td><?= htmlspecialchars($fila['estado']) ?></td>
            <td>
                <a href="update_invernadero.php?id=<?= $fila['id_invernadero'] ?>">Editar</a>
                <a href="eliminar_invernadero.php?id=<?= $fila['id_invernadero'] ?>" onclick="return confirm('¿Está seguro de eliminar este invernadero?')">Eliminar</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <br>
    <a href="index.php">Volver al Inicio</a>
</body>
</html>
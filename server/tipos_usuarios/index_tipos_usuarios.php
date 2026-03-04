<?php

require_once 'conexion.php';
$resultado = mysqli_query($conn, "SELECT * FROM tipos_usuarios ORDER BY id_tipo_de_usuario DESC");

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tipos de Usuario</title>
</head>
<body>
    <h1>Tipos de Usuario</h1>

    <a href="registrar_tipo_usuario.php">Registrar Nuevo Tipo</a><br><br>

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
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
        <?php while ($fila = mysqli_fetch_assoc($resultado)) : ?>
        <tr>
            <td><?= htmlspecialchars($fila['id_tipo_de_usuario']) ?></td>
            <td><?= htmlspecialchars($fila['nombre']) ?></td>
            <td><?= htmlspecialchars($fila['estado']) ?></td>
            <td>
                <a href="update_tipo_usuario.php?id=<?= $fila['id_tipo_de_usuario'] ?>">Editar</a>
                <a href="eliminar_tipo_usuario.php?id=<?= $fila['id_tipo_de_usuario'] ?>" onclick="return confirm('¿Está seguro de eliminar este tipo de usuario?')">Eliminar</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <br>
    <a href="index.php">Volver al Inicio</a>
</body>
</html>
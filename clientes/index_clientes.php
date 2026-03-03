<?php

require_once 'conexion.php';
$resultado = mysqli_query($conn, "
    SELECT c.documento, c.nombre, c.correo, c.fecha_registro, c.estado_inicio_sesion,
        t.nombre AS tipo_usuario
    FROM clientes c
    LEFT JOIN tipos_usuarios t ON c.id_tipo_de_usuario = t.id_tipo_de_usuario
    ORDER BY c.fecha_registro DESC
");

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes Registrados</title>
</head>

<body>
    <h1>Clientes Registrados</h1>
    
    <a href="registrar_cliente.php">Registrar Nuevo Cliente</a><br><br>
    
    <?php if(isset($_GET['success'])): ?>
        <p style="color: green;"><?= htmlspecialchars($_GET['success']) ?></p>
    <?php endif; ?>
    
    <?php if(isset($_GET['error'])): ?>
        <p style="color: red;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>
    
    <table border="1" cellpadding="10">
        <tr>
            <th>Documento</th>
            <th>Tipo Usuario</th>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Fecha Registro</th>
            <th>Estado Inicio Sesión</th>
            <th>Acciones</th>
        </tr>
        <?php while ($fila = mysqli_fetch_assoc($resultado)) : ?>
        <tr>
            <td><?= htmlspecialchars($fila['documento']) ?></td>
            <td><?= htmlspecialchars($fila['tipo_usuario']) ?></td>
            <td><?= htmlspecialchars($fila['nombre']) ?></td>
            <td><?= htmlspecialchars($fila['correo']) ?></td>
            <td><?= htmlspecialchars($fila['fecha_registro']) ?></td>
            <td><?= htmlspecialchars($fila['estado_inicio_sesion']) ?></td>
            <td>
                <a href="update_cliente.php?documento=<?= $fila['documento'] ?>">Editar</a>
                <a href="eliminar_cliente.php?documento=<?= $fila['documento'] ?>" onclick="return confirm('¿Está seguro de eliminar este cliente?')">Eliminar</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <br>
    <a href="index.php">Volver al Inicio</a>
</body>

</html>
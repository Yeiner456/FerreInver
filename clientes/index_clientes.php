<?php

require_once 'conexion.php';
$resultado = mysqli_query($conn, "SELECT * FROM clientes ORDER BY FechaRegistro DESC");

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
            <td><?= htmlspecialchars($fila['Documento']) ?></td>
            <td><?= htmlspecialchars($fila['TipoUsuario']) ?></td>
            <td><?= htmlspecialchars($fila['Nombre']) ?></td>
            <td><?= htmlspecialchars($fila['Correo']) ?></td>
            <td><?= htmlspecialchars($fila['FechaRegistro']) ?></td>
            <td><?= htmlspecialchars($fila['EstadoInicioSesion']) ?></td>
            <td>
                <a href="update_cliente.php?documento=<?= $fila['Documento'] ?>">Editar</a>
                <a href="eliminar_cliente.php?documento=<?= $fila['Documento'] ?>" onclick="return confirm('¿Está seguro de eliminar este cliente?')">Eliminar</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <br>
    <a href="index.php">Volver al Inicio</a>
</body>

</html>
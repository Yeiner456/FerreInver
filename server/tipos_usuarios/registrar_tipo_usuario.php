<?php require_once 'conexion.php'; ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Tipo de Usuario</title>
</head>
<body>
    <h1>Registrar Tipo de Usuario</h1>

    <?php if(isset($_GET['error'])): ?>
        <p style="color: red;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>

    <form action="validar_datos_tipo_usuario.php" method="POST">

        <label>Nombre: </label><br>
        <input type="text" name="nombre" required maxlength="30"><br><br>

        <label>Estado: </label><br>
        <select name="estado" required>
            <option value="activo" selected>Activo</option>
            <option value="inactivo">Inactivo</option>
        </select><br><br>

        <button type="submit">Registrar Tipo de Usuario</button>

    </form><br>

    <a href="index_tipos_usuarios.php">Volver a la Lista</a>
</body>
</html>
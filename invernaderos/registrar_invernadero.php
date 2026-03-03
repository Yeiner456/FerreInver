<?php require_once 'conexion.php'; ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Invernadero</title>
</head>
<body>
    <h1>Registrar Invernadero</h1>

    <?php if(isset($_GET['error'])): ?>
        <p style="color: red;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>

    <form action="validar_datos_invernadero.php" method="POST">

        <label>Nombre: </label><br>
        <input type="text" name="nombre" required maxlength="50"><br><br>

        <label>Descripción (opcional): </label><br>
        <textarea name="descripcion" maxlength="150" rows="3" cols="40"></textarea><br><br>

        <label>Precio por m² ($): </label><br>
        <input type="number" name="precio_m2" required min="0.01" step="0.01"><br><br>

        <label>Estado: </label><br>
        <select name="estado" required>
            <option value="activo" selected>Activo</option>
            <option value="inactivo">Inactivo</option>
        </select><br><br>

        <button type="submit">Registrar Invernadero</button>

    </form><br>

    <a href="index_invernaderos.php">Volver a la Lista</a>
</body>
</html>
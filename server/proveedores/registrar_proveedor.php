<?php

require_once 'conexion.php';

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Proveedor</title>
</head>
<body>
    <h1>Registrar Proveedor</h1>
    
    <?php if(isset($_GET['error'])): ?>
        <p style="color: red;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>
    
    <form action="validar_datos_proveedor.php" method="POST">
        <label>NIT del Proveedor: </label><br>
        <input type="number" name="nit" required min="1"><br><br>

        <label>Correo Electrónico: </label><br>
        <input type="email" name="correo" required maxlength="80"><br><br>

        <label>Dirección: </label><br>
        <input type="text" name="direccion" required maxlength="80"><br><br>

        <label>Teléfono: </label><br>
        <input type="text" name="telefono" required maxlength="20" placeholder="Ej: 3001234567"><br><br>

        <label>Estado: </label><br>
        <select name="estado" required>
            <option value="Activo" selected>Activo</option>
            <option value="Inactivo">Inactivo</option>
        </select><br><br>

        <button type="submit">Registrar Proveedor</button><br>

    </form><br>

    <a href="index_proveedores.php">Volver a la Lista</a>

</body>
</html>
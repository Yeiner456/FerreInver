<?php

require_once 'conexion.php';

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Producto</title>
</head>
<body>
    <h1>Registrar Producto</h1>
    
    <?php if(isset($_GET['error'])): ?>
        <p style="color: red;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>
    
    <form action="validar_datos_producto.php" method="POST">
        <label>Nombre del Producto: </label><br>
        <input type="text" name="nombre" required maxlength="30"><br><br>

        <label>Precio: </label><br>
        <input type="number" name="precio" required min="1" step="1"><br><br>

        <label>Descripción: </label><br>
        <textarea name="descripcion" maxlength="100" rows="3" cols="50" placeholder="Producto de ferreinver disponible"></textarea><br><br>

        <button type="submit">Registrar Producto</button><br>

    </form><br>

    <a href="index_productos.php">Volver a la Lista</a>

</body>
</html>
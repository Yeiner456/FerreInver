<?php

require_once 'conexion.php';

// Obtener todos los productos para el select
$resultado_productos = mysqli_query($conn, "SELECT ID_producto, nombre FROM productos ORDER BY nombre ASC");

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Stock</title>
</head>
<body>
    <h1>Registrar Stock</h1>
    
    <?php if(isset($_GET['error'])): ?>
        <p style="color: red;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>
    
    <form action="validar_datos_stock.php" method="POST">
        <label>Producto: </label><br>
        <select name="id_producto" required>
            <option value="">Seleccione un producto</option>
            <?php while ($producto = mysqli_fetch_assoc($resultado_productos)) : ?>
            <option value="<?= $producto['ID_producto'] ?>"><?= htmlspecialchars($producto['nombre']) ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <label>Cantidad en Stock: </label><br>
        <input type="number" name="cantidad" required min="0" step="1"><br><br>

        <button type="submit">Registrar Stock</button><br>

    </form><br>

    <a href="index_stocks.php">Volver a la Lista</a>

</body>
</html>
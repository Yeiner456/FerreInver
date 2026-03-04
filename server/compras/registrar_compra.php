<?php

require_once 'conexion.php';
$resultado = mysqli_query($conn, "SELECT 
        c.ID_Compra,
        c.Cantidad,
        c.Descripcion,
        p.ID_producto,
        p.nombre AS nombre_producto,
        pr.nit_proveedor,
        pr.correo AS correo_proveedor
    FROM Compras c
    INNER JOIN Productos p ON c.ID_producto = p.ID_producto
    INNER JOIN Proveedores pr ON c.ID_proveedor = pr.nit_proveedor
");
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar compra</title>
</head>

<body>
    <h1>Registrar compra</h1>

    <?php if (isset($_GET['error'])): ?>
        <p style="color: red;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>

    <form action="validar_datos_compra.php" method="POST">

        <label>Cantidad </label><br>
        <input type="number" name="cantidad"><br><br>
        
        <label>descripcion </label><br>
        <input type="text" name="descripcion" required min="1" maxlength="150"><br><br>



        <label>Producto: </label><br>
        <select name="id_producto" required>
            <option value="">Seleccione un producto</option>
            <?php while ($producto = mysqli_fetch_assoc($resultado)) : ?>
            <option value="<?= $producto['ID_producto'] ?>"><?= htmlspecialchars($producto['nombre']) ?></option>
            <?php endwhile; ?>
        </select><br><br>

            <label>proveedor? </label><br>
        <select name="id_proveedor" required>
            <option value="">Elija el proveedor </option>
            <?php while ($producto = mysqli_fetch_assoc($resultado)) : ?>
            <option value="<?= $producto['ID_proveedor'] ?>"><?= htmlspecialchars($producto['correo']) ?></option>
            <?php endwhile; ?>
        </select><br><br>
    </form><br>

    <a href="index_clientes.php">Volver a la Lista</a>

</body>

</html>
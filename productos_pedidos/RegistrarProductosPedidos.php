<?php
require_once 'conexion.php';

// Obtener productos
$sqlProductos = "SELECT ID_producto, nombre FROM Productos ORDER BY nombre ASC";
$resultadoProductos = mysqli_query($conn, $sqlProductos);

// Obtener pedidos
$sqlPedidos = "SELECT id_pedido FROM Pedidos ORDER BY id_pedido ASC";
$resultadoPedidos = mysqli_query($conn, $sqlPedidos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Producto-Pedido</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            background: white;
            width: 400px;
            margin: 0 auto;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }
        select, input[type="text"], input[type="number"], input[type="submit"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #009879;
            color: white;
            cursor: pointer;
            margin-top: 15px;
        }
        input[type="submit"]:hover {
            background-color: #007a63;
        }
        a {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #009879;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

<h1>Registrar Producto en Pedido</h1>

<form action="validarProductosPedidos.php" method="POST">

    <label for="id_producto">Producto:</label>
    <select name="id_producto" required>
        <option value="">-- Selecciona un producto --</option>
        <?php while ($p = mysqli_fetch_assoc($resultadoProductos)): ?>
            <option value="<?= $p['ID_producto'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
        <?php endwhile; ?>
    </select>

    <label for="id_pedido">Pedido:</label>
    <select name="id_pedido" required>
        <option value="">-- Selecciona un pedido --</option>
        <?php while ($pe = mysqli_fetch_assoc($resultadoPedidos)): ?>
            <option value="<?= $pe['id_pedido'] ?>">Pedido #<?= $pe['id_pedido'] ?></option>
        <?php endwhile; ?>
    </select>

    <label for="descripcion">Descripción:</label>
    <input type="text" name="descripcion" maxlength="100" required>

    <label for="cantidad">Cantidad:</label>
    <input type="number" name="cantidad" min="1" required>

    <input type="submit" value="Registrar">
</form>

<a href=".php">← Volver al inicio</a>

</body>
</html>

<?php
require_once 'conexion.php';

// ✅ Si llega el ID por la URL, obtener los datos actuales
if (!empty($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM Productos_Pedidos WHERE id = $id";
    $resultado = mysqli_query($conn, $sql);
    $registro = mysqli_fetch_assoc($resultado);

    if (!$registro) {
        die("<h3>⚠️ No se encontró el registro.</h3><a href='verProductosPedidos.php'>Volver</a>");
    }
}

// ✅ Si se envía el formulario
if (!empty($_POST['id'])) {
    $id = intval($_POST['id']);
    $descripcion = trim($_POST['descripcion']);
    $cantidad = intval($_POST['cantidad']);

    // --- VALIDACIONES ---
    if (!preg_match("/^[A-Za-z0-9\s,.\-]+$/", $descripcion)) {
        die("<h3>❌ Error: la descripción contiene caracteres no permitidos.</h3><a href='editarProductoPedido.php?id=$id'>Volver</a>");
    }

    if ($cantidad <= 0 || $cantidad > 1000) {
        die("<h3>❌ Error: la cantidad debe ser mayor a 0 y menor o igual a 1000.</h3><a href='editarProductoPedido.php?id=$id'>Volver</a>");
    }

    // --- ACTUALIZAR REGISTRO ---
    $sqlUpdate = "UPDATE Productos_Pedidos 
                  SET descripcion='$descripcion', cantidad='$cantidad' 
                  WHERE id=$id";

    if (mysqli_query($conn, $sqlUpdate)) {
        echo "<h2>✅ Registro actualizado correctamente.</h2>";
        echo '<a href="verProductosPedidos.php">Volver a la lista</a>';
        exit;
    } else {
        echo "<h2>❌ Error al actualizar: " . mysqli_error($conn) . "</h2>";
        echo '<a href="verProductosPedidos.php">Volver</a>';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto-Pedido</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
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
        input[type="text"], input[type="number"], input[type="submit"] {
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

<h1>Editar Producto en Pedido</h1>

<?php if (!empty($registro)): ?>
<form method="POST">
    <input type="hidden" name="id" value="<?= $registro['id'] ?>">

    <label for="descripcion">Descripción:</label>
    <input 
        type="text" 
        name="descripcion" 
        maxlength="100" 
        pattern="[A-Za-z0-9\s,.\-]+" 
        value="<?= htmlspecialchars($registro['descripcion']) ?>" 
        required>

    <label for="cantidad">Cantidad:</label>
    <input 
        type="number" 
        name="cantidad" 
        min="1" 
        max="1000" 
        value="<?= htmlspecialchars($registro['cantidad']) ?>" 
        required>

    <input type="submit" value="Actualizar">
</form>
<?php endif; ?>

<a href="verProductosPedidos.php">← Volver a la lista</a>

</body>
</html>

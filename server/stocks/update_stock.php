<?php

require_once 'conexion.php';

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    die('ID inválido');
}

$id = $_GET['id'];
$stmt = mysqli_prepare($conn, "SELECT s.*, p.nombre AS nombre_producto 
                                FROM stocks s 
                                INNER JOIN productos p ON s.ID_producto = p.ID_producto 
                                WHERE s.id_stock = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$stock = mysqli_fetch_assoc($result);

if(!$stock){
    die('Stock no encontrado');
}

// Obtener todos los productos para el select
$resultado_productos = mysqli_query($conn, "SELECT ID_producto, nombre FROM productos ORDER BY nombre ASC");

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Stock</title>
</head>
<body>
    <h1>Editar Stock</h1>
    
    <?php if(isset($_GET['error'])): ?>
        <p style="color: red;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>
    
    <form action="validar_update_stock.php?id=<?= $stock['id_stock'] ?>" method="POST">
        
        <label>ID Stock (No editable): </label><br>
        <input type="text" value="<?= htmlspecialchars($stock['id_stock']) ?>" disabled><br><br>

        <label>Producto: </label><br>
        <select name="id_producto" required>
            <?php while ($producto = mysqli_fetch_assoc($resultado_productos)) : ?>
            <option value="<?= $producto['ID_producto'] ?>" <?= $producto['ID_producto'] == $stock['ID_producto'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($producto['nombre']) ?>
            </option>
            <?php endwhile; ?>
        </select><br><br>

        <label>Cantidad en Stock: </label><br>
        <input type="number" name="cantidad" value="<?= htmlspecialchars($stock['Cantidad']) ?>" required min="0" step="1"><br><br>

        <button type="submit">Actualizar Stock</button>
    </form>
    <br>
    <a href="index_stocks.php">Volver a la Lista</a>
</body>
</html>
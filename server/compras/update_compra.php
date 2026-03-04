<?php

require_once 'conexion.php';

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    die('ID inválido');
}

$id = $_GET['id'];
$stmt = mysqli_prepare($conn, "SELECT 
        c.ID_Compra,
        c.Cantidad,
        c.Descripcion,
        p.ID_producto,
        p.nombre AS nombre_producto,
        pr.nit_proveedor,
        pr.correo AS correo_proveedor
    FROM Compras c
    INNER JOIN Productos p ON c.ID_producto = p.ID_producto
    INNER JOIN Proveedores pr ON c.ID_proveedor = pr.nit_proveedor = ?"
    );
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$compra = mysqli_fetch_assoc($result);

if(!$compra){
    die('compra no encontrado');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar compra</title>
</head>
<body>
    <h1>Editar compra</h1>
    
    <?php if(isset($_GET['error'])): ?>
        <p style="color: red;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>
    
    <form action="validar_update_compra.php?id=<?= $compra['ID_Compra'] ?>" method="POST">
        
        <label>ID compra (No editable): </label><br>
        <input type="text" value="<?= htmlspecialchars($compra['ID_compra']) ?>" disabled><br><br>

        <label>Cantidad </label> <br>
        <input type="number" name="Cantidad" value="<?= htmlspecialchars($compra['Cantidad']) ?>" required><br><br>

        <label>Descripcion </label> <br>
        <input type="text" name="Descripcion" value="<?= htmlspecialchars($compra['Descripcion']) ?>" required><br><br>

        <label>ID proveedor (No editable): </label><br>
        <input type="text" value="<?= htmlspecialchars($pedido['ID_proveedor']) ?>" disabled><br><br>

        <label>ID producto (No editable): </label><br>
        <input type="text" value="<?= htmlspecialchars($pedido['ID_pedido']) ?>" disabled><br><br>

        <button type="submit">Actualizar compra</button>
    </form>
    <br>
    <a href="index_compra.php">Volver a la Lista</a>
</body>
</html>
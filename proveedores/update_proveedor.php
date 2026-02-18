<?php

require_once 'conexion.php';

if(!isset($_GET['nit']) || !is_numeric($_GET['nit'])){
    die('NIT inválido');
}

$nit = $_GET['nit'];
$stmt = mysqli_prepare($conn, "SELECT * FROM proveedores WHERE nit_proveedor = ?");
mysqli_stmt_bind_param($stmt, "i", $nit);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$proveedor = mysqli_fetch_assoc($result);

if(!$proveedor){
    die('Proveedor no encontrado');
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Proveedor</title>
</head>
<body>
    <h1>Editar Proveedor</h1>
    
    <?php if(isset($_GET['error'])): ?>
        <p style="color: red;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>
    
    <form action="validar_update_proveedor.php?nit=<?= $proveedor['nit_proveedor'] ?>" method="POST">
        
        <label>NIT Proveedor (No editable): </label><br>
        <input type="text" value="<?= htmlspecialchars($proveedor['nit_proveedor']) ?>" disabled><br><br>

        <label>Correo Electrónico: </label><br>
        <input type="email" name="correo" value="<?= htmlspecialchars($proveedor['correo']) ?>" required maxlength="80"><br><br>

        <label>Dirección: </label><br>
        <input type="text" name="direccion" value="<?= htmlspecialchars($proveedor['direccion']) ?>" required maxlength="80"><br><br>

        <label>Teléfono: </label><br>
        <input type="text" name="telefono" value="<?= htmlspecialchars($proveedor['telefono']) ?>" required maxlength="20"><br><br>

        <label>Estado: </label><br>
        <select name="estado" required>
            <option value="Activo" <?= $proveedor['estado'] == 'Activo' ? 'selected' : '' ?>>Activo</option>
            <option value="Inactivo" <?= $proveedor['estado'] == 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
        </select><br><br>

        <button type="submit">Actualizar Proveedor</button>
    </form>
    <br>
    <a href="index_proveedores.php">Volver a la Lista</a>
</body>
</html>
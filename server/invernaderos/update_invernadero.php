<?php

require_once 'conexion.php';

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    die('ID de invernadero inválido.');
}

$id = $_GET['id'];

$stmt = mysqli_prepare($conn, "SELECT * FROM invernaderos WHERE id_invernadero = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$invernadero = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if(!$invernadero){
    die('Invernadero no encontrado.');
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Invernadero</title>
</head>
<body>
    <h1>Editar Invernadero</h1>

    <?php if(isset($_GET['error'])): ?>
        <p style="color: red;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>

    <form action="validar_update_invernadero.php?id=<?= $invernadero['id_invernadero'] ?>" method="POST">

        <label>ID (No editable): </label><br>
        <input type="text" value="<?= htmlspecialchars($invernadero['id_invernadero']) ?>" disabled><br><br>

        <label>Nombre: </label><br>
        <input type="text" name="nombre" value="<?= htmlspecialchars($invernadero['nombre']) ?>" required maxlength="50"><br><br>

        <label>Descripción (opcional): </label><br>
        <textarea name="descripcion" maxlength="150" rows="3" cols="40"><?= htmlspecialchars($invernadero['descripcion']) ?></textarea><br><br>

        <label>Precio por m² ($): </label><br>
        <input type="number" name="precio_m2" value="<?= htmlspecialchars($invernadero['precio_m2']) ?>" required min="0.01" step="0.01"><br><br>

        <label>Estado: </label><br>
        <select name="estado" required>
            <option value="activo"   <?= $invernadero['estado'] == 'activo'   ? 'selected' : '' ?>>Activo</option>
            <option value="inactivo" <?= $invernadero['estado'] == 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
        </select><br><br>

        <button type="submit">Actualizar Invernadero</button>

    </form><br>

    <a href="index_invernaderos.php">Volver a la Lista</a>
</body>
</html>
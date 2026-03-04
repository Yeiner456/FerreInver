<?php

require_once 'conexion.php';

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    die('ID inválido.');
}

$id = $_GET['id'];

$stmt = mysqli_prepare($conn, "SELECT * FROM tipos_usuarios WHERE id_tipo_de_usuario = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$tipo = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if(!$tipo){
    die('Tipo de usuario no encontrado.');
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Tipo de Usuario</title>
</head>
<body>
    <h1>Editar Tipo de Usuario</h1>

    <?php if(isset($_GET['error'])): ?>
        <p style="color: red;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>

    <form action="validar_update_tipo_usuario.php?id=<?= $tipo['id_tipo_de_usuario'] ?>" method="POST">

        <label>ID (No editable): </label><br>
        <input type="text" value="<?= htmlspecialchars($tipo['id_tipo_de_usuario']) ?>" disabled><br><br>

        <label>Nombre: </label><br>
        <input type="text" name="nombre" value="<?= htmlspecialchars($tipo['nombre']) ?>" required maxlength="30"><br><br>

        <label>Estado: </label><br>
        <select name="estado" required>
            <option value="activo"   <?= $tipo['estado'] == 'activo'   ? 'selected' : '' ?>>Activo</option>
            <option value="inactivo" <?= $tipo['estado'] == 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
        </select><br><br>

        <button type="submit">Actualizar Tipo de Usuario</button>

    </form><br>

    <a href="index_tipos_usuarios.php">Volver a la Lista</a>
</body>
</html>
<?php

require_once 'conexion.php';

if(!isset($_GET['documento']) || !is_numeric($_GET['documento'])){
    die('Documento inválido');
}

$documento = $_GET['documento'];
$stmt = mysqli_prepare($conn, "SELECT * FROM clientes WHERE Documento = ?");
mysqli_stmt_bind_param($stmt, "i", $documento);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$cliente = mysqli_fetch_assoc($result);

if(!$cliente){
    die('Cliente no encontrado');
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente</title>
</head>
<body>
    <h1>Editar Cliente</h1>
    
    <?php if(isset($_GET['error'])): ?>
        <p style="color: red;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>
    
    <form action="validar_update_cliente.php?documento=<?= $cliente['Documento'] ?>" method="POST">
        
        <label>Documento (No editable): </label><br>
        <input type="text" value="<?= htmlspecialchars($cliente['Documento']) ?>" disabled><br><br>

        <label>Tipo de Usuario: </label><br>
        <select name="tipo_usuario" required>
            <option value="Cliente" <?= $cliente['TipoUsuario'] == 'Cliente' ? 'selected' : '' ?>>Cliente</option>
            <option value="Admin" <?= $cliente['TipoUsuario'] == 'Admin' ? 'selected' : '' ?>>Admin</option>
        </select><br><br>

        <label>Nombre Completo: </label><br>
        <input type="text" name="nombre" value="<?= htmlspecialchars($cliente['Nombre']) ?>" required maxlength="30"><br><br>

        <label>Correo Electrónico: </label><br>
        <input type="email" name="correo" value="<?= htmlspecialchars($cliente['Correo']) ?>" required maxlength="50"><br><br>

        <label>Estado Inicio Sesión: </label><br>
        <select name="estado" required>
            <option value="Activo" <?= $cliente['EstadoInicioSesion'] == 'Activo' ? 'selected' : '' ?>>Activo</option>
            <option value="Inactivo" <?= $cliente['EstadoInicioSesion'] == 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
            <option value="Bloqueado" <?= $cliente['EstadoInicioSesion'] == 'Bloqueado' ? 'selected' : '' ?>>Bloqueado</option>
        </select><br><br>

        <hr>
        <h3>Cambiar Contraseña (Dejar en blanco si no desea cambiarla)</h3>
        
        <label>Nueva Contraseña: </label><br>
        <input type="password" name="password" minlength="6"><br><br>

        <label>Confirmar Nueva Contraseña: </label><br>
        <input type="password" name="confirmar_password" minlength="6"><br><br>

        <button type="submit">Actualizar Cliente</button>
    </form>
    <br>
    <a href="index_clientes.php">Volver a la Lista</a>
</body>
</html>
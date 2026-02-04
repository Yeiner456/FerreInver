<?php

require_once 'conexion.php';

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Cliente</title>
</head>
<body>
    <h1>Registrar Cliente</h1>
    
    <?php if(isset($_GET['error'])): ?>
        <p style="color: red;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>
    
    <form action="validar_datos_cliente.php" method="POST">
        <label>Documento (Número de Identificación): </label><br>
        <input type="number" name="documento" required min="1" maxlength="11"><br><br>

        <label>Tipo de Usuario: </label><br>
        <select name="tipo_usuario" required>
            <option value="Cliente">Cliente</option>
            <option value="Admin">Admin</option>
        </select><br><br>

        <label>Nombre Completo: </label><br>
        <input type="text" name="nombre" required maxlength="30"><br><br>

        <label>Correo Electrónico: </label><br>
        <input type="email" name="correo" required maxlength="50"><br><br>

        <label>Contraseña: </label><br>
        <input type="password" name="password" required minlength="6"><br><br>

        <label>Confirmar Contraseña: </label><br>
        <input type="password" name="confirmar_password" required minlength="6"><br><br>

        <label>Estado Inicio Sesión: </label><br>
        <select name="estado" required>
            <option value="Activo" selected>Activo</option>
            <option value="Inactivo">Inactivo</option>
            <option value="Bloqueado">Bloqueado</option>
        </select><br><br>

        <button type="submit">Registrar Cliente</button><br>

    </form><br>

    <a href="index_clientes.php">Volver a la Lista</a>

</body>
</html>
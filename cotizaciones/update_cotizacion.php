<?php

require_once 'conexion.php';

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    die('ID de cotización inválido.');
}

$id = $_GET['id'];

// Obtener datos de la cotización
$stmt = mysqli_prepare($conn, "SELECT * FROM cotizaciones WHERE id_cotizacion = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$cotizacion = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if(!$cotizacion){
    die('Cotización no encontrada.');
}

// Cargar clientes activos
$clientes = mysqli_query($conn, "SELECT documento, nombre FROM clientes WHERE estado_inicio_sesion = 'activo' ORDER BY nombre");

// Cargar invernaderos activos con su precio
$invernaderos = mysqli_query($conn, "SELECT id_invernadero, nombre, precio_m2 FROM invernaderos WHERE estado = 'activo' ORDER BY nombre");

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cotización</title>
</head>
<body>
    <h1>Editar Cotización</h1>

    <?php if(isset($_GET['error'])): ?>
        <p style="color: red;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>

    <form action="validar_update_cotizacion.php?id=<?= $cotizacion['id_cotizacion'] ?>" method="POST">

        <label>ID (No editable): </label><br>
        <input type="text" value="<?= htmlspecialchars($cotizacion['id_cotizacion']) ?>" disabled><br><br>

        <label>Cliente: </label><br>
        <select name="cliente_id" required>
            <option value="">-- Seleccione un cliente --</option>
            <?php while($cl = mysqli_fetch_assoc($clientes)): ?>
                <option value="<?= $cl['documento'] ?>" <?= $cotizacion['cliente_id'] == $cl['documento'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cl['nombre']) ?> (Doc: <?= $cl['documento'] ?>)
                </option>
            <?php endwhile; ?>
        </select><br><br>

        <label>Invernadero: </label><br>
        <select name="invernadero_id" id="invernadero_id" required onchange="actualizarPrecio()">
            <option value="">-- Seleccione un invernadero --</option>
            <?php while($inv = mysqli_fetch_assoc($invernaderos)): ?>
                <option value="<?= $inv['id_invernadero'] ?>"
                    data-precio="<?= $inv['precio_m2'] ?>"
                    <?= $cotizacion['invernadero_id'] == $inv['id_invernadero'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($inv['nombre']) ?> ($ <?= number_format($inv['precio_m2'], 2) ?>/m²)
                </option>
            <?php endwhile; ?>
        </select><br><br>

        <label>Largo (metros): </label><br>
        <input type="number" name="largo" id="largo" value="<?= htmlspecialchars($cotizacion['largo']) ?>" required min="0.01" step="0.01" oninput="calcular()"><br><br>

        <label>Ancho (metros): </label><br>
        <input type="number" name="ancho" id="ancho" value="<?= htmlspecialchars($cotizacion['ancho']) ?>" required min="0.01" step="0.01" oninput="calcular()"><br><br>

        <label>Metros Cuadrados (calculado automáticamente): </label><br>
        <input type="number" name="metros_cuadrados" id="metros_cuadrados" value="<?= htmlspecialchars($cotizacion['metros_cuadrados']) ?>" step="0.01" readonly><br><br>

        <label>Valor por m² (según invernadero): </label><br>
        <input type="number" name="valor_m2" id="valor_m2" value="<?= htmlspecialchars($cotizacion['valor_m2']) ?>" step="0.01" readonly><br><br>

        <label>Total ($): </label><br>
        <input type="number" name="total" id="total" value="<?= htmlspecialchars($cotizacion['total']) ?>" step="0.01" readonly><br><br>

        <label>Estado: </label><br>
        <select name="estado" required>
            <option value="pendiente"  <?= $cotizacion['estado'] == 'pendiente'  ? 'selected' : '' ?>>Pendiente</option>
            <option value="aprobada"   <?= $cotizacion['estado'] == 'aprobada'   ? 'selected' : '' ?>>Aprobada</option>
            <option value="rechazada"  <?= $cotizacion['estado'] == 'rechazada'  ? 'selected' : '' ?>>Rechazada</option>
        </select><br><br>

        <button type="submit">Actualizar Cotización</button>

    </form><br>

    <a href="index_cotizaciones.php">Volver a la Lista</a>

    <script>
        function actualizarPrecio() {
            const select = document.getElementById('invernadero_id');
            const option = select.options[select.selectedIndex];
            const precio = option.dataset.precio || '';
            document.getElementById('valor_m2').value = precio;
            calcular();
        }

        function calcular() {
            const largo  = parseFloat(document.getElementById('largo').value)  || 0;
            const ancho  = parseFloat(document.getElementById('ancho').value)  || 0;
            const valorM2 = parseFloat(document.getElementById('valor_m2').value) || 0;

            const m2    = largo * ancho;
            const total = m2 * valorM2;

            document.getElementById('metros_cuadrados').value = m2.toFixed(2);
            document.getElementById('total').value = total.toFixed(2);
        }
    </script>
</body>
</html>
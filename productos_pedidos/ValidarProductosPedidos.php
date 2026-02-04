<?php
require_once 'conexion.php';

if (
    !empty($_POST['id_producto']) &&
    !empty($_POST['id_pedido']) &&
    !empty($_POST['descripcion']) &&
    !empty($_POST['cantidad'])
) {
    $id_producto = intval($_POST['id_producto']);
    $id_pedido = intval($_POST['id_pedido']);
    $descripcion = trim($_POST['descripcion']);
    $cantidad = intval($_POST['cantidad']);

     if (!preg_match("/^[A-Za-z0-9\s,.\-]+$/", $descripcion)) {
        die("<h3>❌ Error: la descripción contiene caracteres no permitidos.</h3><a href='registrarProductoPedido.php'>Volver</a>");
    }

    if ($cantidad <= 0 || $cantidad > 1000) {
        die("<h3>❌ Error: la cantidad debe ser mayor a 0 y menor o igual a 1000.</h3><a href='registrarProductoPedido.php'>Volver</a>");
    }
    

    $sql = "INSERT INTO Productos_Pedidos (id_producto, id_pedido, descripcion, cantidad)
            VALUES ('$id_producto', '$id_pedido', '$descripcion', '$cantidad')";

    if (mysqli_query($conn, $sql)) {
        echo "<h2>✅ Registro insertado correctamente.</h2>";
        echo '<a href="verProductosPedidos.php">Ver registros</a><br>';
        echo '<a href="index.php">Volver al inicio</a>';
    } else {
        echo "<h2>❌ Error al insertar: " . mysqli_error($conn) . "</h2>";
        echo '<a href="registrarProductoPedido.php">Intentar de nuevo</a>';
    }
} else {
    echo "<h2>⚠️ Faltan datos, completa todos los campos.</h2>";
    echo '<a href="registrarProductoPedido.php">Volver al formulario</a>';
}

mysqli_close($conn);
?>




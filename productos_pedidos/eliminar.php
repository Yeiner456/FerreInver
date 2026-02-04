<?php
require_once 'conexion.php';

if (!empty($_GET['id'])) {
    $id = intval($_GET['id']);

    // Verificar si el registro existe
    $consulta = "SELECT * FROM Productos_Pedidos WHERE id = $id";
    $resultado = mysqli_query($conn, $consulta);

    if (mysqli_num_rows($resultado) > 0) {
        // Eliminar el registro
        $sql = "DELETE FROM Productos_Pedidos WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            echo "<h2>✅ Registro eliminado correctamente.</h2>";
        } else {
            echo "<h2>❌ Error al eliminar el registro: " . mysqli_error($conn) . "</h2>";
        }
    } else {
        echo "<h2>⚠️ No se encontró el registro a eliminar.</h2>";
    }

    echo '<a href="verProductosPedidos.php">Volver al listado</a>';
} else {
    echo "<h2>⚠️ No se recibió el ID del registro.</h2>";
    echo '<a href="verProductosPedidos.php">Volver al listado</a>';
}

mysqli_close($conn);
?>

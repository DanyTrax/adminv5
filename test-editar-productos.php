<?php
session_start();

if(isset($_GET['editar'])) {
    $idDespacho = $_GET['editar'];
    
    require_once "api-transferencias/conexion-central.php";
    
    // Obtener despacho
    $stmt = ConexionCentral::conectar()->prepare("SELECT * FROM despachos WHERE id = :id");
    $stmt->bindParam(":id", $idDespacho);
    $stmt->execute();
    $despacho = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($despacho) {
        $productos = json_decode($despacho["productos_despacho"], true);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Editar Productos</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h2>🔧 Test de carga de productos para edición</h2>
    
    <h3>📦 Información del despacho:</h3>
    <p><strong>Número:</strong> <?php echo $despacho['numero_despacho']; ?></p>
    <p><strong>Estado:</strong> <?php echo $despacho['estado']; ?></p>
    <p><strong>Total productos:</strong> <?php echo $despacho['total_productos']; ?></p>
    
    <h3>📋 Productos a cargar:</h3>
    <table border="1" style="border-collapse: collapse;">
        <tr>
            <th>Código</th>
            <th>Descripción</th>
            <th>Cantidad</th>
            <th>Observación</th>
        </tr>
        <?php foreach($productos as $producto): ?>
        <tr>
            <td><?php echo $producto['codigo']; ?></td>
            <td><?php echo $producto['descripcion']; ?></td>
            <td><?php echo $producto['cantidad']; ?></td>
            <td><?php echo $producto['observacion'] ?? 'Sin observación'; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <h3>🔄 Simulación de carga en JavaScript:</h3>
    <div id="contenedorProductos"></div>
    
    <script>
    $(document).ready(function() {
        console.log("🔄 Simulando carga de productos para edición");
        
        // Datos del despacho
        var despacho = <?php echo json_encode($despacho); ?>;
        var productos = <?php echo json_encode($productos); ?>;
        
        console.log("📦 Despacho:", despacho);
        console.log("📋 Productos:", productos);
        
        // Simular carga en tabla
        var html = "<h4>Productos cargados por JavaScript:</h4>";
        html += "<ul>";
        
        productos.forEach(function(producto, index) {
            html += "<li>";
            html += "<strong>" + producto.codigo + "</strong> - ";
            html += producto.descripcion + " ";
            html += "(Cantidad: " + producto.cantidad + ")";
            if(producto.observacion) {
                html += " - <em>" + producto.observacion + "</em>";
            }
            html += "</li>";
        });
        
        html += "</ul>";
        
        $("#contenedorProductos").html(html);
        
        console.log("✅ Productos cargados correctamente");
    });
    </script>
</body>
</html>
<?php
    } else {
        echo "<p>❌ Despacho no encontrado</p>";
    }
} else {
    echo "<p>❌ No se proporcionó ID de despacho</p>";
}
?>
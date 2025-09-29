<?php
require_once "api-transferencias/conexion-central.php";

if(isset($_GET['id'])) {
    $idDespacho = $_GET['id'];
    
    echo "<h2>🔍 Debug del historial del despacho ID: $idDespacho</h2>";
    
    try {
        $stmt = ConexionCentral::conectar()->prepare("SELECT * FROM despachos WHERE id = :id");
        $stmt->bindParam(":id", $idDespacho);
        $stmt->execute();
        $despacho = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($despacho) {
            echo "<h3>📋 Datos completos del despacho:</h3>";
            echo "<table border='1' style='border-collapse: collapse;'>";
            foreach($despacho as $campo => $valor) {
                $esImportante = in_array($campo, ['estado', 'fecha_creacion', 'fecha_actualizacion', 'motivo_cancelacion', 'nombre_transportador']);
                $estilo = $esImportante ? 'background: #fff3cd; font-weight: bold;' : '';
                echo "<tr style='$estilo'><td>$campo</td><td>" . htmlspecialchars($valor) . "</td></tr>";
            }
            echo "</table>";
            
            echo "<h3>📅 Análisis del timeline:</h3>";
            echo "<ul>";
            echo "<li><strong>Fecha creación:</strong> " . $despacho['fecha_creacion'] . "</li>";
            echo "<li><strong>Fecha última actualización:</strong> " . $despacho['fecha_actualizacion'] . "</li>";
            echo "<li><strong>Estado actual:</strong> " . $despacho['estado'] . "</li>";
            
            if($despacho['motivo_cancelacion']) {
                echo "<li><strong>Motivo cancelación:</strong> " . $despacho['motivo_cancelacion'] . "</li>";
            }
            
            if($despacho['nombre_transportador']) {
                echo "<li><strong>Transportador:</strong> " . $despacho['nombre_transportador'] . "</li>";
            }
            
            echo "</ul>";
        }
        
    } catch(Exception $e) {
        echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>Uso: debug-logs.php?id=ID_DEL_DESPACHO</p>";
}
?>
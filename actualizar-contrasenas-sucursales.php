<?php
require_once "config.php";
require_once "modelos/conexion.php";

echo "<h2>🔧 ACTUALIZAR CONTRASEÑAS DE SUCURSALES</h2>";

try {
    // Actualizar contraseñas con las correctas
    $sucursales = [
        [
            'id' => 1,
            'usuario_bd' => 'epicosie_ricaurte',
            'password_bd' => 'm5Wwg)~M{i~*kFr{',
            'nombre_bd' => 'epicosie_pruebas'
        ],
        [
            'id' => 2,
            'usuario_bd' => 'epicosie_ricaurte',
            'password_bd' => 'm5Wwg)~M{i~*kFr{',
            'nombre_bd' => 'epicosie_pruebas'
        ],
        [
            'id' => 3,
            'usuario_bd' => 'epicosie_ricaurte',
            'password_bd' => 'm5Wwg)~M{i~*kFr{',
            'nombre_bd' => 'epicosie_pruebas'
        ]
    ];
    
    foreach ($sucursales as $sucursal) {
        $sql = "UPDATE sucursales SET 
                usuario_bd = :usuario_bd,
                password_bd = :password_bd,
                nombre_bd = :nombre_bd
                WHERE id = :id";
        
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(':usuario_bd', $sucursal['usuario_bd']);
        $stmt->bindParam(':password_bd', $sucursal['password_bd']);
        $stmt->bindParam(':nombre_bd', $sucursal['nombre_bd']);
        $stmt->bindParam(':id', $sucursal['id']);
        $stmt->execute();
        
        echo "<p>✅ Sucursal ID {$sucursal['id']} actualizada con usuario: {$sucursal['usuario_bd']}</p>";
    }
    
    echo "<h3>🎉 CONTRASEÑAS ACTUALIZADAS</h3>";
    echo "<p>Las contraseñas han sido actualizadas con los valores correctos.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

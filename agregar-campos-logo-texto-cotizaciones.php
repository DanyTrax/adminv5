<?php
require_once __DIR__ . "/api-transferencias/conexion-central.php";

try {
    $pdo = ConexionCentral::conectar();

    // Verificar y agregar campos para logo
    $campos = [
        'logo_width' => "ALTER TABLE personalizacion_cotizaciones ADD COLUMN logo_width INT(3) NULL DEFAULT 80 AFTER header_logo",
        'logo_align_vertical' => "ALTER TABLE personalizacion_cotizaciones ADD COLUMN logo_align_vertical VARCHAR(20) NULL DEFAULT 'center' AFTER logo_width",
        'logo_align_horizontal' => "ALTER TABLE personalizacion_cotizaciones ADD COLUMN logo_align_horizontal VARCHAR(20) NULL DEFAULT 'center' AFTER logo_align_vertical",
        'header_font_size' => "ALTER TABLE personalizacion_cotizaciones ADD COLUMN header_font_size INT(3) NULL DEFAULT 14 AFTER header_color_texto",
        'body_font_size' => "ALTER TABLE personalizacion_cotizaciones ADD COLUMN body_font_size INT(3) NULL DEFAULT 13 AFTER header_font_size",
        'footer_font_size' => "ALTER TABLE personalizacion_cotizaciones ADD COLUMN footer_font_size INT(3) NULL DEFAULT 16 AFTER footer_color_texto"
    ];
    
    foreach ($campos as $nombreCampo => $sql) {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM personalizacion_cotizaciones LIKE ?");
        $stmt->execute([$nombreCampo]);
        if ($stmt->rowCount() == 0) {
            $pdo->exec($sql);
            echo "✅ Campo agregado: $nombreCampo\n";
        } else {
            echo "ℹ️  Campo ya existe: $nombreCampo\n";
        }
    }
    
    echo "\n✅ Proceso completado exitosamente.\n";

} catch (PDOException $e) {
    echo "❌ Error de base de datos: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>


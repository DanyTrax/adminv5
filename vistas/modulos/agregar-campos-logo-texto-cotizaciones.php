<?php
require_once __DIR__ . "/../../api-transferencias/conexion-central.php";

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
    
    $mensajes = [];
    $tipoMensaje = "success";
    
    foreach ($campos as $nombreCampo => $sql) {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM personalizacion_cotizaciones LIKE ?");
        $stmt->execute([$nombreCampo]);
        if ($stmt->rowCount() == 0) {
            $pdo->exec($sql);
            $mensajes[] = "✅ Campo agregado: $nombreCampo";
        } else {
            $mensajes[] = "ℹ️  Campo ya existe: $nombreCampo";
        }
    }
    
    $mensaje = implode("<br>", $mensajes);
    $mensaje .= "<br><br>✅ Proceso completado exitosamente.";

} catch (PDOException $e) {
    $mensaje = "❌ Error de base de datos: " . $e->getMessage();
    $tipoMensaje = "error";
} catch (Exception $e) {
    $mensaje = "❌ Error: " . $e->getMessage();
    $tipoMensaje = "error";
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Agregar Campos de Logo y Texto
            <small>Script de actualización</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li class="active">Agregar Campos</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-<?= $tipoMensaje == 'success' ? 'success' : 'danger' ?>">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-<?= $tipoMensaje == 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i> 
                            Resultado de la Ejecución
                        </h3>
                    </div>
                    <div class="box-body">
                        <div class="alert alert-<?= $tipoMensaje == 'success' ? 'success' : 'danger' ?>">
                            <?= nl2br(htmlspecialchars($mensaje)) ?>
                        </div>
                    </div>
                    <div class="box-footer">
                        <a href="personalizacion-cotizaciones" class="btn btn-primary">
                            <i class="fa fa-arrow-right"></i> Ir a Personalización de Cotizaciones
                        </a>
                        <a href="inicio" class="btn btn-default">
                            <i class="fa fa-home"></i> Volver al Inicio
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>


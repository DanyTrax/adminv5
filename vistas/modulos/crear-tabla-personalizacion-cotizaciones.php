<?php
// Módulo para ejecutar el script de creación de tabla de personalización de cotizaciones
require_once "../api-transferencias/conexion-central.php";

try {
    $pdo = ConexionCentral::conectar();

    // Crear tabla de personalización de cotizaciones
    $sql = "CREATE TABLE IF NOT EXISTS personalizacion_cotizaciones (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        id_sucursal INT(11) NULL,
        nombre_sucursal VARCHAR(255) NULL,
        
        -- Header
        header_logo VARCHAR(255) NULL DEFAULT 'vistas/img/cotizacion/Infinito1.png',
        header_nombre_empresa VARCHAR(255) NULL DEFAULT 'ACPLASTICOS',
        header_nit VARCHAR(255) NULL DEFAULT 'NIT: 901.718.358-2',
        header_regimen VARCHAR(255) NULL DEFAULT 'IVA E ICA RÉGIMEN COMÚN',
        header_servicios TEXT NULL,
        header_color_fondo VARCHAR(7) NULL DEFAULT '#873173',
        header_color_texto VARCHAR(7) NULL DEFAULT '#FFFFFF',
        
        -- Footer
        footer_direccion VARCHAR(255) NULL DEFAULT 'Carrera 27 # 10-65 Local 116',
        footer_telefono VARCHAR(255) NULL DEFAULT 'Tel: 601 569 9557',
        footer_movil VARCHAR(255) NULL DEFAULT 'Móvil: 322 744 5631',
        footer_correo VARCHAR(255) NULL DEFAULT 'Correo: ventas1@acplasticos.com',
        footer_color_fondo VARCHAR(7) NULL DEFAULT '#873173',
        footer_color_texto VARCHAR(7) NULL DEFAULT '#FFFFFF',
        
        activo TINYINT(1) DEFAULT 1,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        usuario_creador INT(11) NULL,
        
        INDEX idx_sucursal (id_sucursal),
        INDEX idx_activo (activo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    $mensaje = "✅ Tabla 'personalizacion_cotizaciones' creada exitosamente.";
    
    // Crear configuración por defecto global
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM personalizacion_cotizaciones WHERE id_sucursal IS NULL");
    $stmt->execute();
    $existe = $stmt->fetchColumn();
    
    if ($existe == 0) {
        $stmt = $pdo->prepare("
            INSERT INTO personalizacion_cotizaciones (
                id_sucursal,
                nombre_sucursal,
                header_logo,
                header_nombre_empresa,
                header_nit,
                header_regimen,
                header_servicios,
                header_color_fondo,
                header_color_texto,
                footer_direccion,
                footer_telefono,
                footer_movil,
                footer_correo,
                footer_color_fondo,
                footer_color_texto,
                activo,
                usuario_creador
            ) VALUES (
                NULL,
                'Global',
                'vistas/img/cotizacion/Infinito1.png',
                'ACPLASTICOS',
                'NIT: 901.718.358-2',
                'IVA E ICA RÉGIMEN COMÚN',
                'AVISOS\nLETRAS EN 3D\nTOMA UNO\nTRABAJOS ESPECIALES',
                '#873173',
                '#FFFFFF',
                'Carrera 27 # 10-65 Local 116',
                'Tel: 601 569 9557',
                'Móvil: 322 744 5631',
                'Correo: ventas1@acplasticos.com',
                '#873173',
                '#FFFFFF',
                1,
                1
            )
        ");
        $stmt->execute();
        $mensaje .= "<br>✅ Configuración global por defecto creada.";
    } else {
        $mensaje .= "<br>ℹ️  Ya existe una configuración global.";
    }
    
    $mensaje .= "<br><br>✅ Proceso completado exitosamente.";
    $tipoMensaje = "success";
    
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
            Crear Tabla de Personalización de Cotizaciones
            <small>Script de inicialización</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li class="active">Crear Tabla</li>
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
                        
                        <?php if ($tipoMensaje == 'success'): ?>
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i> 
                                <strong>Información:</strong> La tabla ha sido creada exitosamente. 
                                Ahora puedes acceder al módulo de <a href="personalizacion-cotizaciones" class="alert-link">Personalización de Cotizaciones</a> 
                                para configurar el header y footer de las cotizaciones.
                            </div>
                        <?php endif; ?>
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


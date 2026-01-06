<?php
/*=============================================
SCRIPT DE INSTALACIÓN SQL COMPLETA
Aplica todos los cambios necesarios para personalización
=============================================*/

require_once __DIR__ . "/../../api-transferencias/conexion-central.php";

$mensajes = [];
$errores = [];
$tipoMensaje = "success";

try {
    $conexion = ConexionCentral::conectar();
    $conexion->beginTransaction();
    
    // ============================================
    // 1. TABLA personalizacion_colores
    // ============================================
    $mensajes[] = "<h4><i class='fa fa-database'></i> Tabla: personalizacion_colores</h4>";
    
    // Verificar si la tabla existe
    $stmt = $conexion->query("SHOW TABLES LIKE 'personalizacion_colores'");
    if ($stmt->rowCount() == 0) {
        $mensajes[] = "⚠️  La tabla 'personalizacion_colores' no existe. Debe crearse primero manualmente.";
    } else {
        // 1.1. Agregar campo id_sucursal
        $stmt = $conexion->query("SHOW COLUMNS FROM personalizacion_colores LIKE 'id_sucursal'");
        if ($stmt->rowCount() == 0) {
            $conexion->exec("
                ALTER TABLE personalizacion_colores 
                ADD COLUMN id_sucursal INT(11) NULL DEFAULT NULL AFTER activo,
                ADD INDEX idx_id_sucursal (id_sucursal)
            ");
            $mensajes[] = "✅ Campo 'id_sucursal' agregado a personalizacion_colores";
        } else {
            $mensajes[] = "ℹ️  Campo 'id_sucursal' ya existe en personalizacion_colores";
        }
        
        // 1.2. Agregar campo nombre_sucursal
        $stmt = $conexion->query("SHOW COLUMNS FROM personalizacion_colores LIKE 'nombre_sucursal'");
        if ($stmt->rowCount() == 0) {
            $conexion->exec("
                ALTER TABLE personalizacion_colores 
                ADD COLUMN nombre_sucursal VARCHAR(255) NULL AFTER id_sucursal
            ");
            $mensajes[] = "✅ Campo 'nombre_sucursal' agregado a personalizacion_colores";
            
            // Actualizar registros existentes con nombres de sucursales
            $stmt = $conexion->query("SELECT id, id_sucursal FROM personalizacion_colores WHERE id_sucursal IS NOT NULL");
            $configuraciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $actualizados = 0;
            foreach ($configuraciones as $config) {
                $stmt = $conexion->prepare("SELECT nombre FROM sucursales WHERE id = ?");
                $stmt->execute([$config['id_sucursal']]);
                $sucursal = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($sucursal) {
                    $updateStmt = $conexion->prepare("UPDATE personalizacion_colores SET nombre_sucursal = ? WHERE id = ?");
                    $updateStmt->execute([$sucursal['nombre'], $config['id']]);
                    $actualizados++;
                }
            }
            if ($actualizados > 0) {
                $mensajes[] = "✅ Actualizados $actualizados registros con nombres de sucursales";
            }
        } else {
            $mensajes[] = "ℹ️  Campo 'nombre_sucursal' ya existe en personalizacion_colores";
        }
    }
    
    // ============================================
    // 2. TABLA personalizacion_cotizaciones
    // ============================================
    $mensajes[] = "<br><h4><i class='fa fa-file-text'></i> Tabla: personalizacion_cotizaciones</h4>";
    
    // 2.1. Crear tabla si no existe
    $stmt = $conexion->query("SHOW TABLES LIKE 'personalizacion_cotizaciones'");
    if ($stmt->rowCount() == 0) {
        $conexion->exec("
            CREATE TABLE personalizacion_cotizaciones (
                id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                id_sucursal INT(11) NULL,
                nombre_sucursal VARCHAR(255) NULL,
                
                -- Header
                header_logo VARCHAR(255) NULL,
                header_nombre_empresa VARCHAR(255) NULL,
                header_nit VARCHAR(255) NULL,
                header_regimen VARCHAR(255) NULL,
                header_servicios TEXT NULL,
                header_color_fondo VARCHAR(7) NULL,
                header_color_texto VARCHAR(7) NULL,
                
                -- Footer
                footer_direccion VARCHAR(255) NULL,
                footer_telefono VARCHAR(255) NULL,
                footer_movil VARCHAR(255) NULL,
                footer_correo VARCHAR(255) NULL,
                footer_color_fondo VARCHAR(7) NULL,
                footer_color_texto VARCHAR(7) NULL,
                
                activo TINYINT(1) DEFAULT 1,
                fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
                fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                usuario_creador INT(11) NULL,
                
                INDEX idx_sucursal (id_sucursal),
                INDEX idx_activo (activo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $mensajes[] = "✅ Tabla 'personalizacion_cotizaciones' creada";
        
        // Crear configuración global por defecto
        $stmt = $conexion->prepare("
            INSERT INTO personalizacion_cotizaciones (
                id_sucursal, nombre_sucursal,
                header_logo, header_nombre_empresa, header_nit, header_regimen, header_servicios,
                header_color_fondo, header_color_texto,
                footer_direccion, footer_telefono, footer_movil, footer_correo,
                footer_color_fondo, footer_color_texto,
                activo, usuario_creador
            ) VALUES (
                NULL, 'Global',
                'vistas/img/cotizacion/Infinito1.png', 'ACPLASTICOS', 'NIT: 901.718.358-2', 
                'IVA E ICA RÉGIMEN COMÚN', 'AVISOS\nLETRAS EN 3D\nTOMA UNO\nTRABAJOS ESPECIALES',
                '#873173', '#FFFFFF',
                'Carrera 27 # 10-65 Local 116', 'Tel: 601 569 9557', 'Móvil: 322 744 5631', 
                'Correo: ventas1@acplasticos.com',
                '#873173', '#FFFFFF',
                1, 1
            )
        ");
        $stmt->execute();
        $mensajes[] = "✅ Configuración global por defecto creada";
    } else {
        $mensajes[] = "ℹ️  Tabla 'personalizacion_cotizaciones' ya existe";
    }
    
    // 2.2. Agregar campos de logo y texto si no existen
    $camposLogo = [
        'logo_width' => "INT(3) NULL DEFAULT 80",
        'logo_align_vertical' => "VARCHAR(20) NULL DEFAULT 'center'",
        'logo_align_horizontal' => "VARCHAR(20) NULL DEFAULT 'center'",
        'header_font_size' => "INT(3) NULL DEFAULT 14",
        'body_font_size' => "INT(3) NULL DEFAULT 13",
        'footer_font_size' => "INT(3) NULL DEFAULT 16"
    ];
    
    foreach ($camposLogo as $nombreCampo => $definicion) {
        $stmt = $conexion->prepare("SHOW COLUMNS FROM personalizacion_cotizaciones LIKE ?");
        $stmt->execute([$nombreCampo]);
        if ($stmt->rowCount() == 0) {
            // Determinar después de qué campo agregarlo
            $despuesDe = '';
            switch ($nombreCampo) {
                case 'logo_width':
                    $despuesDe = 'AFTER header_logo';
                    break;
                case 'logo_align_vertical':
                    $despuesDe = 'AFTER logo_width';
                    break;
                case 'logo_align_horizontal':
                    $despuesDe = 'AFTER logo_align_vertical';
                    break;
                case 'header_font_size':
                    $despuesDe = 'AFTER header_color_texto';
                    break;
                case 'body_font_size':
                    $despuesDe = 'AFTER header_font_size';
                    break;
                case 'footer_font_size':
                    $despuesDe = 'AFTER footer_color_texto';
                    break;
            }
            
            $conexion->exec("ALTER TABLE personalizacion_cotizaciones ADD COLUMN $nombreCampo $definicion $despuesDe");
            $mensajes[] = "✅ Campo '$nombreCampo' agregado a personalizacion_cotizaciones";
        } else {
            $mensajes[] = "ℹ️  Campo '$nombreCampo' ya existe en personalizacion_cotizaciones";
        }
    }
    
    $conexion->commit();
    $mensajeFinal = implode("<br>", $mensajes);
    $mensajeFinal .= "<br><br><strong>✅ Instalación completada exitosamente.</strong>";
    
} catch (PDOException $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }
    $mensajeFinal = "❌ Error de base de datos: " . $e->getMessage();
    $tipoMensaje = "danger";
    $errores[] = $e->getMessage();
} catch (Exception $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }
    $mensajeFinal = "❌ Error: " . $e->getMessage();
    $tipoMensaje = "danger";
    $errores[] = $e->getMessage();
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-database"></i> Instalación SQL Completa
            <small>Script de instalación y migración</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li class="active">Instalación SQL</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-<?= $tipoMensaje == 'success' ? 'success' : 'danger' ?>">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-<?= $tipoMensaje == 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i> 
                            Resultado de la Instalación
                        </h3>
                    </div>
                    <div class="box-body">
                        <div class="alert alert-<?= $tipoMensaje == 'success' ? 'success' : 'danger' ?>">
                            <?= $mensajeFinal ?>
                        </div>
                        
                        <?php if ($tipoMensaje == 'success'): ?>
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i> 
                                <strong>Información:</strong> Todos los cambios SQL necesarios han sido aplicados. 
                                El sistema está listo para usar las funcionalidades de personalización.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fa fa-exclamation-triangle"></i> 
                                <strong>Atención:</strong> Hubo errores durante la instalación. 
                                Por favor, revisa los mensajes de error arriba y verifica los permisos de la base de datos.
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="box-footer">
                        <?php if ($tipoMensaje == 'success'): ?>
                            <a href="personalizacion-colores-simplificado" class="btn btn-primary">
                                <i class="fa fa-paint-brush"></i> Personalización de Colores
                            </a>
                            <a href="personalizacion-cotizaciones" class="btn btn-primary">
                                <i class="fa fa-file-text"></i> Personalización de Cotizaciones
                            </a>
                        <?php endif; ?>
                        <a href="inicio" class="btn btn-default">
                            <i class="fa fa-home"></i> Volver al Inicio
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>


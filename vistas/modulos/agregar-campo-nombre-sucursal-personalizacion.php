<?php
/*=============================================
AGREGAR CAMPO nombre_sucursal A TABLA personalizacion_colores
=============================================*/

require_once __DIR__ . "/../../api-transferencias/conexion-central.php";

$mensaje = '';
$tipoMensaje = 'info';

try {
    $conexion = ConexionCentral::conectar();
    
    // Verificar si la columna ya existe
    $stmt = $conexion->query("SHOW COLUMNS FROM personalizacion_colores LIKE 'nombre_sucursal'");
    $existe = $stmt->rowCount() > 0;
    
    if ($existe) {
        $mensaje = "✅ El campo 'nombre_sucursal' ya existe en la tabla personalizacion_colores.";
        $tipoMensaje = 'success';
    } else {
        // Agregar la columna nombre_sucursal después de id_sucursal
        $sql = "ALTER TABLE personalizacion_colores 
                ADD COLUMN nombre_sucursal VARCHAR(255) NULL AFTER id_sucursal";
        
        $conexion->exec($sql);
        
        $mensaje = "✅ Campo 'nombre_sucursal' agregado exitosamente.\n";
        $tipoMensaje = 'success';
        
        // Actualizar registros existentes con el nombre de la sucursal
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
        
        $mensaje .= "<br>✅ Se actualizaron $actualizados registros existentes con nombres de sucursales.";
    }
    
} catch (PDOException $e) {
    $mensaje = "❌ Error: " . $e->getMessage();
    $tipoMensaje = 'danger';
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Agregar Campo nombre_sucursal
            <small>Actualizar tabla personalizacion_colores</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li class="active">Agregar Campo nombre_sucursal</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-<?= $tipoMensaje == 'success' ? 'success' : ($tipoMensaje == 'danger' ? 'danger' : 'info') ?>">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-database"></i> Resultado de la Operación
                        </h3>
                    </div>
                    <div class="box-body">
                        <div class="alert alert-<?= $tipoMensaje ?>">
                            <?= nl2br(htmlspecialchars($mensaje)) ?>
                        </div>
                        
                        <div class="text-center" style="margin-top: 20px;">
                            <a href="personalizacion-colores-simplificado" class="btn btn-primary">
                                <i class="fa fa-arrow-left"></i> Volver a Personalización de Colores
                            </a>
                            <a href="inicio" class="btn btn-default">
                                <i class="fa fa-home"></i> Ir al Inicio
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

